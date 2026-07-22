<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('username_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('old_username', 50)->unique();
            $table->string('new_username', 50)->index();
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('source', 30)->default('account');
            $table->timestamp('changed_at')->index();
            $table->timestamps();
        });

        if (Schema::hasTable('settings') && !DB::table('settings')->where('key', 'username')->exists()) {
            DB::table('settings')->insert([
                'key' => 'username',
                'value' => json_encode(['blacklisted_usernames' => '']),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('username_histories');
    }
};
