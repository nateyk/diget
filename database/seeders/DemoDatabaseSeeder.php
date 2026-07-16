<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemComment;
use App\Models\ItemReview;
use App\Models\KycVerification;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Models\Faq;
use App\Models\HomeCategory;
use App\Models\Page;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\Refund;
use App\Models\Reviewer;
use App\Models\Sale;
use App\Models\SubCategory;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Testimonial;
use App\Models\UploadedFile;
use App\Models\User;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class DemoDatabaseSeeder extends Seeder
{
    private array $users = [];
    private array $categories = [];
    private array $items = [];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Demo data seeding is disabled in production.');
        }

        $password = config('demo.user_password')
            ?: config('demo.super_admin_password')
            ?: env('DEMO_USER_PASSWORD')
            ?: env('DEMO_SUPER_ADMIN_PASSWORD');
        if (!$password) {
            throw new RuntimeException('Set DEMO_USER_PASSWORD or DEMO_SUPER_ADMIN_PASSWORD before seeding demo data.');
        }

        DB::transaction(function () use ($password): void {
            $this->seedAdmin($password);
            $reviewers = $this->seedReviewers($password);
            $this->seedUsers($password);
            $this->seedRuntimeDefaults();
            $this->seedCategories($reviewers);
            $this->seedItems();
            $this->seedFiles();
            $this->seedActivity();
            $this->seedFinancialRecords();
            $this->seedSupport();
            $this->seedKyc();
            $this->seedContent();
            $this->seedHomepageContent();
        });

        $this->command?->info('Demo marketplace data is ready.');
        $this->command?->line('Admin: ' . env('DEMO_SUPER_ADMIN_EMAIL', 'admin@diget.test'));
    }

    private function seedRuntimeDefaults(): void
    {
        DB::table('storage_providers')->updateOrInsert(
            ['alias' => 'local'],
            [
                'name' => 'Local',
                'processor' => 'App\\Http\\Controllers\\Storage\\LocalController',
                'credentials' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('currencies')->updateOrInsert(
            ['code' => 'ETB'],
            [
                'symbol' => 'Br',
                'position' => 2,
                'rate' => 1,
                'icon' => 'images/currencies/etb.png',
                'sort_id' => 1,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        foreach ([
            'general' => [
                'site_name' => 'Diget',
                'site_url' => '',
                'date_format' => '10',
                'timezone' => 'Africa/Addis_Ababa',
                'contact_email' => null,
            ],
            'smtp' => [
                'status' => 0,
                'mailer' => 'smtp',
                'host' => null,
                'port' => null,
                'username' => null,
                'password' => null,
                'encryption' => 'tls',
                'from_email' => null,
                'from_name' => null,
            ],
            'actions' => [
                'registration' => 1,
                'email_verification' => 0,
                'become_an_author' => 1,
                'api' => 1,
                'gdpr_cookie' => 1,
                'force_ssl' => 0,
                'blog' => 1,
                'tickets' => 1,
                'refunds' => 1,
                'contact_page' => 0,
            ],
            'currency' => ['code' => 'ETB', 'symbol' => 'Br', 'position' => '2'],
            'item' => [
                'buy_now_button' => 1,
                'free_item_option' => 1,
                'external_file_link_option' => 1,
                'reviews_status' => 1,
                'comments_status' => 1,
                'changelogs_status' => 1,
                'support_status' => 1,
                'discount_status' => 1,
                'maximum_tags' => '15',
                'minimum_price' => '1.00',
                'maximum_price' => '5000.00',
                'free_item_total_downloads' => 3,
                'free_items_require_login' => 1,
                'discount_max_percentage' => '70',
                'discount_max_days' => '20',
                'discount_tb' => '30',
                'discount_tb_pch' => '30',
                'trending_number' => '20',
                'best_selling_number' => '20',
                'max_files' => '12',
                'max_file_size' => 314572800,
                'convert_images_webp' => '1',
                'file_duration' => '24',
                'adding_require_review' => 1,
                'updating_require_review' => 0,
            ],
            'profile' => [
                'default_avatar' => 'images/profiles/default/fymG7nwhBiXI12c_1733601562.png',
                'default_cover' => 'images/profiles/default/bjhPVvmXixCNqAH_1733601554.png',
            ],
            'language' => ['code' => 'en', 'direction' => 'ltr'],
            'maintenance' => ['status' => 0, 'title' => 'Under Maintenance', 'body' => '', 'icon' => null],
        ] as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => json_encode($value, JSON_UNESCAPED_UNICODE)]
            );
        }
    }

    private function seedHomepageContent(): void
    {
        foreach ([
            ['name' => 'WordPress Themes', 'icon' => 'images/home-categories/w2KxPEK8FjNjbfs_1733598767.jpg', 'link' => '/categories/themes/wordpress', 'sort_id' => 1],
            ['name' => 'PHP Scripts', 'icon' => 'images/home-categories/VukPsIdffapI0Ty_1733598772.jpg', 'link' => '/categories/code/php-scripts', 'sort_id' => 2],
            ['name' => 'HTML5 Codes', 'icon' => 'images/home-categories/T9qm8Gaj3ZzsuRd_1733598777.jpg', 'link' => '/categories/code/html5', 'sort_id' => 3],
            ['name' => 'Graphics', 'icon' => 'images/home-categories/UbBiYqbKpcNXrsS_1733598799.jpg', 'link' => '/categories/graphics', 'sort_id' => 4],
        ] as $category) {
            HomeCategory::updateOrCreate(['name' => $category['name']], $category);
        }

        foreach ([
            ['name' => 'Explore Categories', 'alias' => 'categories', 'items_number' => null, 'cache_expiry_time' => 10, 'sort_id' => 1],
            ['name' => 'Our Latest Items', 'alias' => 'latest_items', 'description' => 'Explore the latest creator storefront products and digital releases.', 'items_number' => 8, 'cache_expiry_time' => 60, 'sort_id' => 8],
            ['name' => "FAQ's", 'alias' => 'faqs', 'description' => 'Answers to common questions about buying, selling, and creator storefronts.', 'items_number' => null, 'cache_expiry_time' => 30, 'sort_id' => 10],
            ['name' => 'Testimonials', 'alias' => 'testimonials', 'description' => 'Discover what creators and buyers say about Diget.', 'items_number' => null, 'cache_expiry_time' => 1440, 'sort_id' => 11],
        ] as $section) {
            DB::table('home_sections')->updateOrInsert(
                ['alias' => $section['alias']],
                $section + ['status' => 1]
            );
        }

        foreach ([
            ['title' => 'What can I sell on Diget?', 'body' => '<p>Creators can sell digital products such as templates, plugins, scripts, graphics, and other downloadable resources.</p>', 'sort_id' => 1],
            ['title' => 'How do buyers receive their files?', 'body' => '<p>After a successful purchase, buyers can access eligible downloads from their account library.</p>', 'sort_id' => 2],
            ['title' => 'Can I create a public storefront?', 'body' => '<p>Yes. Approved creators receive a public storefront at <code>/@username</code> for sharing their profile and products.</p>', 'sort_id' => 3],
            ['title' => 'How do I contact a creator?', 'body' => '<p>Use the message action on a creator storefront when contact is enabled.</p>', 'sort_id' => 4],
        ] as $faq) {
            Faq::updateOrCreate(['title' => $faq['title']], $faq);
        }

        foreach ([
            ['name' => 'Emma Carter', 'avatar' => 'images/sections/testimonials/s1Qvpw5INDTo89B_1733598825.jpg', 'title' => 'Graphic Designer', 'body' => 'Diget gives creators a practical place to showcase and sell digital work.', 'sort_id' => 1],
            ['name' => 'Amanda Evans', 'avatar' => 'images/sections/testimonials/mWN8YLCJBoDNFcT_1733598820.jpg', 'title' => 'Startup Founder', 'body' => 'The curated digital products helped us move from idea to launch faster.', 'sort_id' => 2],
        ] as $testimonial) {
            Testimonial::updateOrCreate(['name' => $testimonial['name']], $testimonial);
        }
    }

    private function seedAdmin(string $password): void
    {
        Admin::updateOrCreate(
            ['email' => env('DEMO_SUPER_ADMIN_EMAIL', 'admin@diget.test')],
            [
                'firstname' => 'Diget',
                'lastname' => 'Super Admin',
                'username' => 'digetadmin',
                'password' => Hash::make($password),
                'google2fa_status' => false,
            ]
        );
    }

    private function seedReviewers(string $password): array
    {
        $reviewers = [];
        foreach ([
            ['reviewer1', 'Marta', 'Bekele'],
            ['reviewer2', 'Dawit', 'Kebede'],
        ] as [$username, $firstname, $lastname]) {
            $reviewers[] = Reviewer::updateOrCreate(
                ['email' => $username . '@diget.test'],
                compact('firstname', 'lastname', 'username') + ['password' => Hash::make($password)]
            );
        }
        return $reviewers;
    }

    private function seedUsers(string $password): void
    {
        $records = [
            ['buyer1', 'Abel', 'Tesfaye', false, User::STATUS_ACTIVE, false],
            ['buyer2', 'Mimi', 'Ayele', false, User::STATUS_ACTIVE, false],
            ['buyer3', 'Liya', 'Kassa', false, User::STATUS_ACTIVE, false],
            ['buyer4', 'Solomon', 'Fikru', false, User::STATUS_ACTIVE, false],
            ['buyer5', 'Hana', 'Worku', false, User::STATUS_ACTIVE, false],
            ['buyer6', 'Yonas', 'Mekonnen', false, User::STATUS_ACTIVE, false],
            ['buyer7', 'Ruth', 'Tadesse', false, User::STATUS_ACTIVE, false],
            ['buyer8', 'Samuel', 'Girma', false, User::STATUS_ACTIVE, false],
            ['abenezerdesign', 'Abenezer', 'Design', true, User::STATUS_ACTIVE, true],
            ['nahomdeveloper', 'Nahom', 'Developer', true, User::STATUS_ACTIVE, true],
            ['selamstudio', 'Selam', 'Studio', true, User::STATUS_ACTIVE, true],
            ['bettyux', 'Betty', 'Mulu', true, User::STATUS_ACTIVE, true],
            ['michaelcode', 'Michael', 'Tadesse', true, User::STATUS_ACTIVE, true],
            ['edencreative', 'Eden', 'Creative', true, User::STATUS_ACTIVE, true],
            ['pendingmaker', 'Pending', 'Maker', false, User::STATUS_ACTIVE, false],
            ['pendingstudio', 'Pending', 'Studio', false, User::STATUS_ACTIVE, false],
            ['kycpending', 'Kyc', 'Pending', true, User::STATUS_ACTIVE, false],
            ['disabledbuyer', 'Disabled', 'Buyer', false, User::STATUS_BANNED, false],
        ];

        foreach ($records as [$username, $firstname, $lastname, $isAuthor, $status, $verifiedKyc]) {
            $this->users[$username] = User::updateOrCreate(
                ['email' => $username . '@diget.test'],
                [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'username' => $username,
                    'password' => Hash::make($password),
                    'address' => ['country' => 'ET'],
                    'is_author' => $isAuthor,
                    'is_featured_author' => $isAuthor && in_array($username, ['abenezerdesign', 'selamstudio'], true),
                    'exclusivity' => $isAuthor ? User::AUTHOR_NON_EXCLUSIVE : null,
                    'profile_heading' => $isAuthor ? 'Digital creator' : 'Diget member',
                    'profile_description' => $isAuthor ? 'Independent creator building practical digital products for modern teams.' : 'Exploring useful digital products from independent creators.',
                    'profile_card_description' => $isAuthor ? 'A focused creator storefront for practical, polished digital products.' : null,
                    'profile_contact_email' => $username . '@diget.test',
                    'profile_social_links' => $isAuthor ? ['website' => 'https://example.test/' . $username, 'instagram' => '@' . $username] : null,
                    'email_verified_at' => now(),
                    'kyc_status' => $verifiedKyc,
                    'status' => $status,
                ]
            );
        }
    }

    private function seedCategories(array $reviewers): void
    {
        $definitions = [
            ['website-templates', 'Website Templates', 1, 'zip,rar,pdf'],
            ['ui-design-assets', 'UI & Design Assets', 1, 'zip,rar,pdf'],
            ['source-code', 'Source Code', 1, 'zip,rar'],
            ['graphics', 'Graphics', 1, 'zip,rar,pdf'],
            ['business-documents', 'Business Documents', 1, 'zip,rar,pdf'],
        ];

        foreach ($definitions as $sort => [$slug, $name, $fileType, $types]) {
            $category = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'title' => $name, 'description' => 'Demo ' . strtolower($name) . ' for the Diget catalog.', 'file_type' => $fileType, 'main_file_types' => $types, 'sort_id' => $sort + 1]
            );
            $this->categories[$slug] = $category;
            foreach (['Starter', 'Professional'] as $subSort => $subName) {
                SubCategory::updateOrCreate(
                    ['slug' => $slug . '-' . strtolower($subName)],
                    ['name' => $subName, 'title' => $subName . ' ' . $name, 'description' => 'Demo ' . strtolower($subName) . ' collection.', 'category_id' => $category->id, 'sort_id' => $subSort + 1]
                );
            }
        }

        foreach ($reviewers as $reviewer) {
            $reviewer->categories()->syncWithoutDetaching(array_values(array_map(fn ($category) => $category->id, $this->categories)));
        }
    }

    private function seedItems(): void
    {
        $catalog = [
            ['laravel-saas-starter-kit', 'Laravel SaaS Starter Kit', 'source-code', 'nahomdeveloper', 49.99],
            ['restaurant-website-template', 'Restaurant Website Template', 'website-templates', 'abenezerdesign', 29.00],
            ['ethiopian-business-invoice-template', 'Ethiopian Business Invoice Template', 'business-documents', 'selamstudio', 9.99],
            ['mobile-banking-ui-kit', 'Mobile Banking UI Kit', 'ui-design-assets', 'bettyux', 15.00],
            ['social-media-post-bundle', 'Social Media Post Bundle', 'graphics', 'edencreative', 5.00],
            ['portfolio-website-template', 'Portfolio Website Template', 'website-templates', 'abenezerdesign', 15.00],
            ['inventory-management-source-code', 'Inventory Management Source Code', 'source-code', 'michaelcode', 99.00],
            ['resume-and-cv-template-pack', 'Resume and CV Template Pack', 'business-documents', 'selamstudio', 9.99],
            ['admin-dashboard-ui-kit', 'Admin Dashboard UI Kit', 'ui-design-assets', 'bettyux', 29.00],
            ['ecommerce-landing-page', 'E-commerce Landing Page', 'website-templates', 'abenezerdesign', 15.00],
            ['amharic-font-presentation-template', 'Amharic Font Presentation Template', 'business-documents', 'edencreative', 5.00],
            ['chapa-payment-integration-starter', 'Chapa Payment Integration Starter', 'source-code', 'nahomdeveloper', 49.99],
            ['livewire-marketplace-components', 'Livewire Marketplace Components', 'source-code', 'michaelcode', 29.00],
            ['logo-presentation-mockup-pack', 'Logo Presentation Mockup Pack', 'graphics', 'edencreative', 9.99],
            ['small-business-accounting-spreadsheet', 'Small Business Accounting Spreadsheet', 'business-documents', 'selamstudio', 0.00],
            ['creator-link-in-bio-template', 'Creator Link-in-Bio Template', 'website-templates', 'abenezerdesign', 15.00],
            ['minimal-brand-guidelines', 'Minimal Brand Guidelines', 'graphics', 'edencreative', 9.99],
            ['react-admin-components', 'React Admin Components', 'source-code', 'michaelcode', 49.99],
            ['product-launch-social-kit', 'Product Launch Social Kit', 'graphics', 'bettyux', 5.00],
            ['freelancer-proposal-pack', 'Freelancer Proposal Pack', 'business-documents', 'selamstudio', 0.00],
        ];

        foreach ($catalog as $index => [$slug, $name, $categorySlug, $authorName, $price]) {
            $category = $this->categories[$categorySlug];
            $sub = SubCategory::where('category_id', $category->id)->orderBy('id')->first();
            $isFree = $price == 0.0;
            $item = Item::updateOrCreate(
                ['slug' => $slug],
                [
                    'author_id' => $this->users[$authorName]->id,
                    'name' => $name,
                    'description' => 'A production-ready demo product for creators, freelancers, and small teams. Includes clear documentation and a practical starting point.',
                    'category_id' => $category->id,
                    'sub_category_id' => $sub?->id,
                    'version' => '1.0.0',
                    'demo_link' => 'https://example.test/demo/' . $slug,
                    'tags' => 'demo,creator,digital-product',
                    'thumbnail' => 'demo/items/' . $slug . '.png',
                    'preview_type' => 'image',
                    'preview_image' => 'demo/items/' . $slug . '.png',
                    'main_file' => 'demo/items/' . $slug . '.zip',
                    'screenshots' => ['demo/items/' . $slug . '.png'],
                    'regular_price' => $price,
                    'extended_price' => $isFree ? 0 : round($price * 2.5, 2),
                    'is_supported' => true,
                    'support_instructions' => 'Demo support is available through the support workspace.',
                    'status' => $index === 18 ? 1 : ($index === 19 ? 2 : 4),
                    'purchasing_status' => $index !== 19,
                    'is_free' => $isFree,
                    'is_trending' => $index < 5,
                    'is_best_selling' => $index >= 5 && $index < 10,
                    'is_on_discount' => $index % 4 === 0 && !$isFree,
                    'is_featured' => $index < 6,
                    'last_update_at' => Carbon::now()->subDays($index),
                    'last_discount_at' => $index % 4 === 0 ? Carbon::now()->subDays(2) : null,
                    'price_updated_at' => Carbon::now()->subDays($index + 1),
                ]
            );
            $this->items[$slug] = $item;
        }
    }

    private function seedFiles(): void
    {
        foreach ($this->items as $item) {
            $base = 'demo/items/' . $item->slug;
            $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
            Storage::disk('local')->put($base . '.png', $png);
            $zipPath = storage_path('app/' . $base . '.zip');
            if (!is_dir(dirname($zipPath))) {
                mkdir(dirname($zipPath), 0755, true);
            }
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFromString('README.txt', "Diget demo package: {$item->name}\n");
                $zip->close();
            }
            UploadedFile::updateOrCreate(
                ['author_id' => $item->author_id, 'category_id' => $item->category_id, 'name' => $item->name . '.zip'],
                ['mime_type' => 'application/zip', 'extension' => 'zip', 'size' => filesize($zipPath), 'path' => $base . '.zip', 'expiry_at' => Carbon::now()->addYear()]
            );
        }
    }

    private function seedActivity(): void
    {
        $buyers = array_values(array_filter($this->users, fn ($user) => str_starts_with($user->username, 'buyer')));
        $publishedItems = array_values(array_filter($this->items, fn ($item) => $item->status === 4));
        foreach ($publishedItems as $index => $item) {
            for ($offset = 0; $offset < 2; $offset++) {
                $buyer = $buyers[($index + $offset) % count($buyers)];
                ItemReview::firstOrCreate(
                    ['user_id' => $buyer->id, 'item_id' => $item->id],
                    ['author_id' => $item->author_id, 'stars' => 3 + (($index + $offset) % 3), 'subject' => 'Useful demo product', 'body' => 'Clear, practical, and easy to explore.']
                );
            }
            ItemComment::firstOrCreate(['user_id' => $buyers[$index % count($buyers)]->id, 'item_id' => $item->id], ['author_id' => $item->author_id]);
        }
        foreach (array_slice($publishedItems, 0, 10) as $index => $item) {
            DB::table('favorites')->updateOrInsert(['user_id' => $buyers[$index % count($buyers)]->id, 'item_id' => $item->id], ['created_at' => now(), 'updated_at' => now()]);
        }
        foreach (array_values(array_slice($this->users, 8, 6)) as $index => $author) {
            $buyer = $buyers[$index % count($buyers)];
            DB::table('followers')->updateOrInsert(['follower_id' => $buyer->id, 'following_id' => $author->id], ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    private function seedFinancialRecords(): void
    {
        $buyers = array_values(array_filter($this->users, fn ($user) => str_starts_with($user->username, 'buyer')));
        $items = array_values(array_filter($this->items, fn ($item) => $item->status === 4 && !$item->is_free));
        foreach (array_slice($items, 0, 5) as $index => $item) {
            $buyer = $buyers[$index];
            $price = (float) $item->regular_price;
            $transaction = Transaction::updateOrCreate(
                ['payment_id' => 'DEMO-BANK-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                ['user_id' => $buyer->id, 'amount' => $price, 'fees' => 0, 'tax' => null, 'total' => $price, 'payment_id' => 'DEMO-BANK-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT), 'payer_id' => 'DEMO-PAYER-' . $buyer->id, 'payer_email' => $buyer->email, 'type' => Transaction::TYPE_PURCHASE, 'status' => Transaction::STATUS_PAID, 'fulfilled_at' => now()]
            );
            $sale = Sale::updateOrCreate(
                ['author_id' => $item->author_id, 'user_id' => $buyer->id, 'item_id' => $item->id],
                ['license_type' => Sale::LICENSE_TYPE_REGULAR, 'price' => $price, 'buyer_fee' => 0, 'author_fee' => 0, 'author_earning' => $price, 'country' => 'ET', 'status' => $index === 4 ? Sale::STATUS_REFUNDED : Sale::STATUS_ACTIVE]
            );
            $purchase = Purchase::updateOrCreate(
                ['code' => 'DEMO-PURCHASE-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT)],
                ['user_id' => $buyer->id, 'author_id' => $item->author_id, 'sale_id' => $sale->id, 'item_id' => $item->id, 'license_type' => Purchase::LICENSE_TYPE_REGULAR, 'support_expiry_at' => now()->addYear(), 'is_downloaded' => $index !== 4, 'status' => $index === 4 ? Purchase::STATUS_REFUNDED : Purchase::STATUS_ACTIVE]
            );
            $transaction->update(['purchase_id' => $purchase->id]);
            TransactionItem::updateOrCreate(['transaction_id' => $transaction->id, 'item_id' => $item->id], ['license_type' => TransactionItem::LICENSE_TYPE_REGULAR, 'price' => $price, 'quantity' => 1, 'total' => $price]);
            if ($index === 4) {
                Refund::updateOrCreate(['purchase_id' => $purchase->id], ['user_id' => $buyer->id, 'author_id' => $item->author_id, 'status' => Refund::STATUS_ACCEPTED]);
            }
        }
        $author = $this->users['abenezerdesign'];
        foreach ([Withdrawal::STATUS_PENDING, Withdrawal::STATUS_APPROVED, Withdrawal::STATUS_RETURNED] as $index => $status) {
            Withdrawal::updateOrCreate(['author_id' => $author->id, 'method' => 'Demo Bank ' . ($index + 1)], ['amount' => 25 + ($index * 10), 'account' => json_encode(['account_name' => 'Demo Creator', 'account_number' => 'DEMO-' . ($index + 1)]), 'status' => $status]);
        }
    }

    private function seedSupport(): void
    {
        $category = TicketCategory::firstOrCreate(['name' => 'Demo Support'], ['status' => true]);
        foreach (array_slice(array_values(array_filter($this->users, fn ($user) => str_starts_with($user->username, 'buyer'))), 0, 5) as $index => $user) {
            $ticket = Ticket::updateOrCreate(['user_id' => $user->id, 'subject' => 'Demo support request ' . ($index + 1)], ['ticket_category_id' => $category->id, 'status' => $index === 4 ? Ticket::STATUS_CLOSED : Ticket::STATUS_OPENED]);
            TicketReply::updateOrCreate(['ticket_id' => $ticket->id, 'admin_id' => Admin::where('email', env('DEMO_SUPER_ADMIN_EMAIL', 'admin@diget.test'))->value('id')], ['body' => 'This is a local demo support reply.']);
        }
    }

    private function seedKyc(): void
    {
        KycVerification::updateOrCreate(['user_id' => $this->users['kycpending']->id], ['document_type' => KycVerification::DOCUMENT_TYPE_PASSPORT, 'document_number' => 'DEMO-KYC-001', 'documents' => ['passport' => 'demo/kyc/passport.txt'], 'status' => KycVerification::STATUS_PENDING]);
        KycVerification::updateOrCreate(['user_id' => $this->users['selamstudio']->id], ['document_type' => KycVerification::DOCUMENT_TYPE_NATIONAL_ID, 'document_number' => 'DEMO-KYC-002', 'documents' => ['front' => 'demo/kyc/front.txt', 'back' => 'demo/kyc/back.txt'], 'status' => KycVerification::STATUS_APPROVED]);
        Storage::disk('local')->put('demo/kyc/passport.txt', 'Demo KYC placeholder.');
        Storage::disk('local')->put('demo/kyc/front.txt', 'Demo KYC placeholder.');
        Storage::disk('local')->put('demo/kyc/back.txt', 'Demo KYC placeholder.');
    }

    private function seedContent(): void
    {
        foreach ([
            ['about', 'About Diget', 'A creator-first storefront for useful digital products.', 'Diget connects independent creators with people looking for practical digital products.'],
            ['terms', 'Terms of Use', 'The rules for using the Diget platform.', 'This local demo page describes the terms for browsing, selling, and purchasing demo products.'],
            ['privacy', 'Privacy Policy', 'How the demo platform handles account information.', 'This local demo page contains privacy guidance for the Diget experience.'],
            ['refund-policy', 'Refund Policy', 'A clear demo refund policy for buyers and creators.', 'Demo purchases may be reviewed through the support workspace.'],
            ['seller-guidelines', 'Seller Guidelines', 'Practical guidance for publishing quality products.', 'Creators should provide accurate descriptions, working files, and clear documentation.'],
        ] as [$slug, $title, $short, $body]) {
            Page::updateOrCreate(['slug' => $slug], ['title' => $title, 'short_description' => $short, 'body' => '<p>' . $body . '</p>']);
        }

        $blogCategory = BlogCategory::updateOrCreate(['slug' => 'creator-guides'], ['name' => 'Creator Guides']);
        foreach (range(1, 6) as $number) {
            $slug = 'demo-creator-guide-' . $number;
            BlogArticle::updateOrCreate(
                ['slug' => $slug],
                ['title' => 'Demo Creator Guide ' . $number, 'image' => 'demo/items/creator-guide.png', 'short_description' => 'A practical local demo article for Diget creators.', 'body' => 'This is safe local demo content for creators building storefronts and digital products.', 'blog_category_id' => $blogCategory->id]
            );
        }
        Storage::disk('local')->put('demo/items/creator-guide.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

        $plan = Plan::updateOrCreate(['name' => 'Demo Creator Pro'], ['description' => 'Local demo creator subscription.', 'price' => 9.99, 'interval' => Plan::INTERVAL_MONTH, 'author_earning_percentage' => 80, 'downloads' => null, 'custom_features' => ['Demo analytics', 'Priority support'], 'status' => Plan::STATUS_ACTIVE, 'featured' => true, 'sort_id' => 1]);
        Subscription::updateOrCreate(['user_id' => $this->users['abenezerdesign']->id], ['plan_id' => $plan->id, 'total_downloads' => 3, 'expiry_at' => Carbon::now()->addMonth()]);
    }
}
