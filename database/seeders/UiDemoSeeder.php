<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Follower;
use App\Models\Item;
use App\Models\ItemChangeLog;
use App\Models\ItemDiscount;
use App\Models\ItemReview;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\UploadedFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UiDemoSeeder extends Seeder
{
    /** @var array<string, Category> */
    private array $categories = [];

    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, Item> */
    private array $items = [];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('UI demo seeding is disabled in production.');
        }

        $this->writeAssets();

        DB::transaction(function (): void {
            $this->seedRuntimePrerequisites();
            $this->seedCategories();
            $this->seedUsers();
            $this->seedProducts();
            $this->seedVerifiedReviewScenario();
            $this->seedSocialActivity();
        });

        $this->clearPublicCaches();

        $this->command?->info('Local UI demo records are ready: 3 creators, 10 products, and 5 categories.');
    }

    private function seedRuntimePrerequisites(): void
    {
        DB::table('storage_providers')->insertOrIgnore(
            [
                'name' => 'Local',
                'alias' => 'local',
                'processor' => 'App\\Http\\Controllers\\Storage\\LocalController',
                'credentials' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        foreach ([
            'actions' => [
                'registration' => true,
                'email_verification' => false,
                'force_ssl' => false,
                'become_an_author' => true,
            ],
            'currency' => [
                'code' => 'USD',
                'symbol' => '$',
                'position' => 1,
            ],
            'general' => [
                'site_name' => 'Diget',
                'site_url' => '',
                'date_format' => '10',
                'timezone' => 'UTC',
                'contact_email' => null,
            ],
            'item' => [
                'reviews_status' => true,
                'comments_status' => false,
                'changelogs_status' => false,
                'buy_now_button' => true,
                'free_item_option' => true,
                'free_item_total_downloads' => true,
                'free_items_require_login' => false,
                'external_file_link_option' => true,
                'discount_status' => true,
            ],
            'profile' => [
                'default_avatar' => 'images/profiles/default/fymG7nwhBiXI12c_1733601562.png',
                'default_cover' => 'images/profiles/default/bjhPVvmXixCNqAH_1733601554.png',
            ],
            'seo' => [
                'title' => 'Diget',
                'description' => 'Creator storefronts for digital products.',
                'keywords' => 'creator, storefront, digital products',
            ],
            'social_links' => [],
            'links' => [],
            'smtp' => ['status' => true],
        ] as $key => $value) {
            DB::table('settings')->insertOrIgnore([
                'key' => $key,
                'value' => json_encode($value, JSON_UNESCAPED_UNICODE),
            ]);
        }
    }

    private function seedCategories(): void
    {
        foreach ([
            ['ui-demo-templates', 'Templates', 'Polished website and portfolio templates for independent creators.'],
            ['ui-demo-code', 'Code', 'Production-minded starter kits, components, and integrations.'],
            ['ui-demo-design', 'Design', 'Reusable interface kits and visual design systems.'],
            ['ui-demo-business', 'Business', 'Practical documents and tools for small teams.'],
            ['ui-demo-marketing', 'Marketing', 'Ready-to-publish social and launch materials.'],
        ] as $index => [$slug, $name, $description]) {
            $this->categories[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'title' => $name,
                    'description' => $description,
                    'file_type' => 1,
                    'main_file_types' => 'txt,zip,pdf',
                    'sort_id' => 900 + $index,
                ],
            );
        }
    }

    private function seedUsers(): void
    {
        $password = Hash::make('DigetUiDemoOnly!2026');

        foreach ([
            [
                'email' => 'creator.design@diget.test',
                'username' => 'selamstudio',
                'firstname' => 'Selam',
                'lastname' => 'Studio',
                'is_author' => User::AUTHOR,
                'is_featured_author' => User::FEATURED_AUTHOR,
                'profile_heading' => 'Visual systems for growing brands',
                'profile_card_description' => 'Practical templates and design assets for creators who want a confident launch.',
                'profile_description' => '<p>Selam Studio creates thoughtful visual systems for small businesses and independent creators. Every product is designed to be easy to adapt, publish, and reuse.</p>',
                'profile_social_links' => ['website' => 'https://selamstudio.example.test', 'instagram' => '@selamstudio', 'linkedin' => 'selamstudio'],
            ],
            [
                'email' => 'creator.code@diget.test',
                'username' => 'nahomdeveloper',
                'firstname' => 'Nahom',
                'lastname' => 'Developer',
                'is_author' => User::AUTHOR,
                'is_featured_author' => User::NOT_FEATURED_AUTHOR,
                'profile_heading' => 'Developer tools that stay out of your way',
                'profile_card_description' => 'Starter kits and components for shipping dependable web products faster.',
                'profile_description' => '<p>Nahom builds carefully structured starter kits for Laravel and Livewire teams. The focus is clear code, practical documentation, and useful defaults.</p>',
                'profile_social_links' => ['website' => 'https://nahomdeveloper.example.test', 'github' => 'nahomdeveloper', 'linkedin' => 'nahomdeveloper'],
            ],
            [
                'email' => 'creator.business@diget.test',
                'username' => 'abenezerdesign',
                'firstname' => 'Abenezer',
                'lastname' => 'Design',
                'is_author' => User::AUTHOR,
                'is_featured_author' => User::NOT_FEATURED_AUTHOR,
                'profile_heading' => 'Independent creator',
                'profile_card_description' => 'A new storefront for useful creator resources and client-ready templates.',
                'profile_description' => '<p>Abenezer Design is preparing a first collection of creator resources. Follow the storefront to see new work as it is published.</p>',
                'profile_social_links' => ['website' => 'https://abenezerdesign.example.test'],
            ],
            [
                'email' => 'buyer.demo@diget.test',
                'username' => 'buyerdemo',
                'firstname' => 'Demo',
                'lastname' => 'Buyer',
                'is_author' => User::NOT_AUTHOR,
                'is_featured_author' => User::NOT_FEATURED_AUTHOR,
            ],
            [
                'email' => 'reviewer.one@diget.test',
                'username' => 'reviewerone',
                'firstname' => 'Mekdes',
                'lastname' => 'Demo',
                'is_author' => User::NOT_AUTHOR,
                'is_featured_author' => User::NOT_FEATURED_AUTHOR,
            ],
            [
                'email' => 'reviewer.two@diget.test',
                'username' => 'reviewertwo',
                'firstname' => 'Biruk',
                'lastname' => 'Demo',
                'is_author' => User::NOT_AUTHOR,
                'is_featured_author' => User::NOT_FEATURED_AUTHOR,
            ],
        ] as $definition) {
            $username = $definition['username'];
            $isCreator = $definition['is_author'] === User::AUTHOR;

            $user = User::updateOrCreate(
                ['email' => $definition['email']],
                [
                    'firstname' => $definition['firstname'],
                    'lastname' => $definition['lastname'],
                    'username' => $username,
                    'password' => $password,
                    'address' => ['country' => 'ET'],
                    'is_author' => $definition['is_author'],
                    'is_featured_author' => $definition['is_featured_author'],
                    'exclusivity' => $isCreator ? User::AUTHOR_NON_EXCLUSIVE : null,
                    'avatar' => $isCreator ? $this->assetPath("avatars/{$username}.svg") : null,
                    'profile_cover' => $isCreator ? $this->assetPath("covers/{$username}.svg") : null,
                    'profile_heading' => $definition['profile_heading'] ?? null,
                    'profile_card_description' => $definition['profile_card_description'] ?? null,
                    'profile_description' => $definition['profile_description'] ?? null,
                    'profile_contact_email' => $isCreator ? $definition['email'] : null,
                    'profile_social_links' => $definition['profile_social_links'] ?? null,
                    'kyc_status' => $isCreator ? User::KYC_STATUS_VERIFIED : User::KYC_STATUS_UNVERIFIED,
                    'status' => User::STATUS_ACTIVE,
                ],
            );

            $user->forceFill(['email_verified_at' => now()])->save();
            $this->users[$username] = $user->fresh();
        }
    }

    private function seedProducts(): void
    {
        $catalog = [
            ['laravel-saas-starter-kit', 'Laravel SaaS Starter Kit', 'ui-demo-code', 'nahomdeveloper', 790, false, null, true, true],
            ['livewire-dashboard-components', 'Livewire Dashboard Components', 'ui-demo-code', 'nahomdeveloper', 490, false, null, false, false],
            ['chapa-payment-integration-starter', 'Chapa Payment Integration Starter', 'ui-demo-code', 'nahomdeveloper', 350, false, null, false, false],
            ['modern-portfolio-template', 'Modern Portfolio Template', 'ui-demo-templates', 'selamstudio', 420, false, 315, false, true],
            ['mobile-banking-ui-kit', 'Mobile Banking UI Kit', 'ui-demo-design', 'selamstudio', 520, false, null, false, false],
            ['social-media-design-bundle', 'Social Media Design Bundle', 'ui-demo-marketing', 'selamstudio', 240, false, 168, false, false],
            ['ethiopian-business-invoice-pack', 'Ethiopian Business Invoice Pack', 'ui-demo-business', 'selamstudio', 180, false, null, false, false],
            ['small-business-accounting-template', 'Small Business Accounting Template', 'ui-demo-business', 'selamstudio', 0, true, null, false, false],
            ['restaurant-website-template', 'Restaurant Website Template', 'ui-demo-templates', 'selamstudio', 0, true, null, false, false],
            ['brand-launch-checklist', 'Brand Launch Checklist', 'ui-demo-marketing', 'selamstudio', 120, false, null, false, false],
        ];

        foreach ($catalog as $index => [$slug, $name, $categorySlug, $username, $price, $isFree, $discountedPrice, $isBestSelling, $isFeatured]) {
            $category = $this->categories[$categorySlug];
            $item = Item::updateOrCreate(
                ['slug' => $slug],
                [
                    'author_id' => $this->users[$username]->id,
                    'name' => $name,
                    'description' => $this->productDescription($name),
                    'category_id' => $category->id,
                    'sub_category_id' => null,
                    'version' => $slug === 'laravel-saas-starter-kit' ? '1.1.0' : '1.0.0',
                    'demo_link' => null,
                    'tags' => 'creator storefront, digital product, ui demo',
                    'thumbnail' => $this->assetPath("products/{$slug}.svg"),
                    'preview_type' => Item::PREVIEW_FILE_TYPE_IMAGE,
                    'preview_image' => $this->assetPath("products/{$slug}.svg"),
                    'main_file' => "ui-demo/products/{$slug}.txt",
                    'screenshots' => [$this->assetPath("products/{$slug}.svg")],
                    'regular_price' => $price,
                    'extended_price' => $isFree ? 0 : $price * 2,
                    'is_supported' => Item::NOT_SUPPORTED,
                    'support_instructions' => null,
                    'status' => Item::STATUS_APPROVED,
                    'total_sales' => 0,
                    'total_sales_amount' => 0,
                    'total_earnings' => 0,
                    'total_reviews' => 0,
                    'avg_reviews' => 0,
                    'total_comments' => 0,
                    'total_views' => 0,
                    'current_month_views' => 0,
                    'free_downloads' => 0,
                    'purchasing_status' => Item::PURCHASING_STATUS_ENABLED,
                    'is_premium' => Item::NOT_PREMIUM,
                    'is_free' => $isFree ? Item::FREE : Item::NOT_FREE,
                    'is_trending' => $index === 0 ? Item::TRENDING : Item::NOT_TRENDING,
                    'is_best_selling' => $isBestSelling ? Item::BEST_SELLING : Item::NOT_BEST_SELLING,
                    'is_on_discount' => $discountedPrice ? Item::DISCOUNT_ON : Item::DISCOUNT_OFF,
                    'is_featured' => $isFeatured ? Item::FEATURED : Item::NOT_FEATURED,
                    'was_featured' => $isFeatured ? Item::FEATURED : Item::NOT_FEATURED,
                    'last_update_at' => $slug === 'laravel-saas-starter-kit' ? Carbon::now()->subDays(3) : null,
                    'last_discount_at' => $discountedPrice ? Carbon::now()->subDay() : null,
                    'price_updated_at' => Carbon::now()->subDays($index + 1),
                ],
            );

            $this->items[$slug] = $item->fresh();
            $this->writeDownloadFile($item);

            UploadedFile::updateOrCreate(
                [
                    'author_id' => $item->author_id,
                    'category_id' => $item->category_id,
                    'name' => "{$slug}.txt",
                ],
                [
                    'mime_type' => 'text/plain',
                    'extension' => 'txt',
                    'size' => strlen($this->downloadContents($item)),
                    'path' => $item->main_file,
                    'expiry_at' => Carbon::now()->addYear(),
                ],
            );

            if ($discountedPrice) {
                ItemDiscount::updateOrCreate(
                    ['item_id' => $item->id],
                    [
                        'regular_percentage' => (int) round((1 - ($discountedPrice / $price)) * 100),
                        'regular_price' => $discountedPrice,
                        'extended_percentage' => null,
                        'extended_price' => null,
                        'starting_at' => Carbon::now()->subDay(),
                        'ending_at' => Carbon::now()->addDays(14),
                        'status' => ItemDiscount::STATUS_ACTIVE,
                    ],
                );
            }
        }

        $updatedItem = $this->items['laravel-saas-starter-kit'];
        ItemChangeLog::updateOrCreate(
            ['item_id' => $updatedItem->id, 'version' => '1.1.0'],
            ['body' => '<p>Updated the account setup flow and added clearer documentation for the starter workspace.</p>'],
        );
    }

    private function seedVerifiedReviewScenario(): void
    {
        $item = $this->items['laravel-saas-starter-kit'];
        $buyers = ['buyerdemo', 'reviewerone', 'reviewertwo'];
        $reviews = [
            ['A clear starting point', 'The setup is easy to follow and the product structure is practical for a small SaaS project.', 5],
            ['Thoughtful defaults', 'The components feel deliberate and the documentation makes it simple to adapt the starter kit.', 5],
            ['Useful for a first release', 'A solid foundation for getting a creator product online without unnecessary complexity.', 4],
        ];

        foreach ($buyers as $index => $username) {
            $buyer = $this->users[$username];
            $paymentId = sprintf('UI-DEMO-PURCHASE-%02d', $index + 1);
            $price = $item->price->regular;

            $transaction = Transaction::updateOrCreate(
                ['payment_id' => $paymentId],
                [
                    'user_id' => $buyer->id,
                    'amount' => $price,
                    'fees' => 0,
                    'tax' => null,
                    'total' => $price,
                    'payer_id' => "ui-demo-{$buyer->username}",
                    'payer_email' => $buyer->email,
                    'type' => Transaction::TYPE_PURCHASE,
                    'status' => Transaction::STATUS_PAID,
                    'fulfilled_at' => Carbon::now()->subDays($index + 2),
                ],
            );

            $sale = Sale::updateOrCreate(
                ['author_id' => $item->author_id, 'user_id' => $buyer->id, 'item_id' => $item->id],
                [
                    'license_type' => Sale::LICENSE_TYPE_REGULAR,
                    'price' => $price,
                    'buyer_fee' => 0,
                    'author_fee' => 0,
                    'author_earning' => $price,
                    'country' => 'ET',
                    'status' => Sale::STATUS_ACTIVE,
                ],
            );

            $purchase = Purchase::updateOrCreate(
                ['code' => sprintf('UI-DEMO-LICENSE-%02d', $index + 1)],
                [
                    'user_id' => $buyer->id,
                    'author_id' => $item->author_id,
                    'sale_id' => $sale->id,
                    'item_id' => $item->id,
                    'license_type' => Purchase::LICENSE_TYPE_REGULAR,
                    'support_expiry_at' => null,
                    'is_downloaded' => Purchase::NOT_DOWNLOADED,
                    'status' => Purchase::STATUS_ACTIVE,
                ],
            );

            $transaction->forceFill(['purchase_id' => $purchase->id])->save();

            TransactionItem::updateOrCreate(
                ['transaction_id' => $transaction->id, 'item_id' => $item->id],
                [
                    'license_type' => TransactionItem::LICENSE_TYPE_REGULAR,
                    'price' => $price,
                    'quantity' => 1,
                    'total' => $price,
                    'support' => null,
                ],
            );

            [$subject, $body, $stars] = $reviews[$index];
            ItemReview::updateOrCreate(
                ['user_id' => $buyer->id, 'item_id' => $item->id],
                [
                    'author_id' => $item->author_id,
                    'stars' => $stars,
                    'subject' => $subject,
                    'body' => $body,
                ],
            );
        }

        $item->forceFill([
            'total_sales' => Sale::where('item_id', $item->id)->active()->count(),
            'total_sales_amount' => Sale::where('item_id', $item->id)->active()->sum('price'),
            'total_earnings' => Sale::where('item_id', $item->id)->active()->sum('author_earning'),
        ])->save();

        $author = $this->users['nahomdeveloper'];
        $author->forceFill([
            'total_sales' => Sale::where('author_id', $author->id)->active()->count(),
            'total_sales_amount' => Sale::where('author_id', $author->id)->active()->sum('price'),
        ])->save();
    }

    private function seedSocialActivity(): void
    {
        Follower::firstOrCreate([
            'follower_id' => $this->users['buyerdemo']->id,
            'following_id' => $this->users['selamstudio']->id,
        ]);

        Favorite::firstOrCreate([
            'user_id' => $this->users['buyerdemo']->id,
            'item_id' => $this->items['modern-portfolio-template']->id,
        ]);
    }

    private function writeAssets(): void
    {
        foreach (['selamstudio', 'nahomdeveloper', 'abenezerdesign'] as $username) {
            $this->writeSvg("avatars/{$username}.svg", $this->avatarSvg($username));
            $this->writeSvg("covers/{$username}.svg", $this->coverSvg($username));
        }

        foreach ([
            'laravel-saas-starter-kit',
            'livewire-dashboard-components',
            'chapa-payment-integration-starter',
            'modern-portfolio-template',
            'mobile-banking-ui-kit',
            'social-media-design-bundle',
            'ethiopian-business-invoice-pack',
            'small-business-accounting-template',
            'restaurant-website-template',
            'brand-launch-checklist',
        ] as $slug) {
            $this->writeSvg("products/{$slug}.svg", $this->productSvg(str_replace('-', ' ', $slug)));
        }
    }

    private function writeDownloadFile(Item $item): void
    {
        Storage::disk('local')->put($item->main_file, $this->downloadContents($item));
    }

    private function downloadContents(Item $item): string
    {
        return "Diget local UI demo product\n\n{$item->name}\nThis is a non-executable local demo download used for visual QA.\n";
    }

    private function writeSvg(string $relativePath, string $contents): void
    {
        $path = public_path('images/ui-demo/' . $relativePath);
        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);
    }

    private function assetPath(string $relativePath): string
    {
        return 'images/ui-demo/' . $relativePath;
    }

    private function productDescription(string $name): string
    {
        return "<p>{$name} is a local UI demo product with a focused structure, clear documentation, and practical files for creators and small teams.</p><h3>What's included</h3><p>Editable source files, a concise setup guide, and a sensible starting point for your next release.</p>";
    }

    private function avatarSvg(string $username): string
    {
        $initials = strtoupper(substr($username, 0, 2));

        return $this->svg("<circle cx=\"240\" cy=\"240\" r=\"240\" fill=\"#ff5a45\"/><text x=\"240\" y=\"270\" text-anchor=\"middle\" font-family=\"Arial, sans-serif\" font-size=\"150\" font-weight=\"700\" fill=\"#ffffff\">{$initials}</text>", 480, 480);
    }

    private function coverSvg(string $username): string
    {
        $label = htmlspecialchars(str_replace('studio', ' studio', $username), ENT_QUOTES, 'UTF-8');

        return $this->svg("<rect width=\"1440\" height=\"520\" fill=\"#101828\"/><rect x=\"70\" y=\"72\" width=\"590\" height=\"330\" rx=\"22\" fill=\"#ff5a45\" opacity=\".94\"/><circle cx=\"1180\" cy=\"176\" r=\"120\" fill=\"#ffffff\" opacity=\".14\"/><text x=\"112\" y=\"210\" font-family=\"Arial, sans-serif\" font-size=\"34\" font-weight=\"700\" fill=\"#ffffff\">DIGET CREATOR</text><text x=\"112\" y=\"278\" font-family=\"Arial, sans-serif\" font-size=\"54\" font-weight=\"700\" fill=\"#ffffff\">{$label}</text>", 1440, 520);
    }

    private function productSvg(string $label): string
    {
        $safeLabel = htmlspecialchars(ucwords($label), ENT_QUOTES, 'UTF-8');

        return $this->svg("<rect width=\"1200\" height=\"700\" fill=\"#101828\"/><rect x=\"64\" y=\"64\" width=\"1072\" height=\"572\" rx=\"28\" fill=\"#ffffff\" opacity=\".08\"/><rect x=\"100\" y=\"118\" width=\"140\" height=\"14\" rx=\"7\" fill=\"#ff5a45\"/><rect x=\"100\" y=\"180\" width=\"450\" height=\"36\" rx=\"18\" fill=\"#ffffff\" opacity=\".95\"/><rect x=\"100\" y=\"238\" width=\"340\" height=\"22\" rx=\"11\" fill=\"#98a2b3\"/><rect x=\"100\" y=\"348\" width=\"1000\" height=\"188\" rx=\"22\" fill=\"#ff5a45\" opacity=\".9\"/><text x=\"120\" y=\"472\" font-family=\"Arial, sans-serif\" font-size=\"42\" font-weight=\"700\" fill=\"#ffffff\">{$safeLabel}</text>", 1200, 700);
    }

    private function svg(string $body, int $width, int $height): string
    {
        return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$width} {$height}\" role=\"img\" aria-label=\"Diget UI demo artwork\">{$body}</svg>";
    }

    private function clearPublicCaches(): void
    {
        foreach ([
            'home_trending_items_cache',
            'home_best_selling_items_cache',
            'home_sale_items_cache',
            'home_free_items_cache',
            'home_featured_items_cache',
            'home_latest_items_cache',
            'home_latest_items_categories_cache',
            'home_featured_author_cache',
        ] as $key) {
            Cache::forget($key);
        }
    }
}
