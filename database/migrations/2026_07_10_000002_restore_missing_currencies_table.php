<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('symbol');
                $table->tinyInteger('position')->default(1)->comment('1:Before price 2:After price');
                $table->decimal('rate', 28, 9);
                $table->string('icon');
                $table->bigInteger('sort_id')->default(0);
                $table->timestamps();
            });
        }

        if (! DB::table('currencies')->where('code', 'USD')->exists()) {
            DB::table('currencies')->insert([
                'code' => 'USD',
                'symbol' => '$',
                'position' => 1,
                'rate' => 1,
                'icon' => 'images/currencies/usd.png',
                'sort_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // This migration repairs a baseline installation issue. Do not drop
        // a currencies table that may contain merchant-managed data.
    }
};
