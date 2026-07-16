<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('levels') && ! $this->hasForeignKey('users', 'level_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('level_id')->references('id')->on('levels')->nullOnDelete();
            });
        }

        if (Schema::hasTable('withdrawal_methods') && ! $this->hasForeignKey('users', 'withdrawal_method_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('withdrawal_method_id')->references('id')->on('withdrawal_methods')->nullOnDelete();
            });
        }

        if (Schema::hasTable('support_periods') && ! $this->hasForeignKey('cart_items', 'support_period_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreign('support_period_id')->references('id')->on('support_periods')->nullOnDelete();
            });
        }

        if (Schema::hasTable('plans') && ! $this->hasForeignKey('transactions', 'plan_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if ($this->hasForeignKey('users', 'level_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['level_id']);
            });
        }

        if ($this->hasForeignKey('users', 'withdrawal_method_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['withdrawal_method_id']);
            });
        }

        if ($this->hasForeignKey('cart_items', 'support_period_id')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropForeign(['support_period_id']);
            });
        }

        if ($this->hasForeignKey('transactions', 'plan_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['plan_id']);
            });
        }
    }

    private function hasForeignKey(string $table, string $column): bool
    {
        return collect(Schema::getForeignKeys($table))
            ->contains(fn (array $foreignKey) => in_array($column, $foreignKey['columns'] ?? [], true));
    }
};
