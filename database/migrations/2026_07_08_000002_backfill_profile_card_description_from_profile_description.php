<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('profile_card_description')
            ->whereNotNull('profile_description')
            ->orderBy('id')
            ->select(['id', 'profile_description'])
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    $description = trim(strip_tags((string) $user->profile_description));

                    if ($description === '') {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'profile_card_description' => Str::words($description, 100, ''),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('users')->update([
            'profile_card_description' => null,
        ]);
    }
};
