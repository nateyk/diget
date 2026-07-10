<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('payment_gateways')->where('alias', 'chapa')->exists()) {
            DB::table('payment_gateways')->insert([
                'sort_id' => ((int) DB::table('payment_gateways')->max('sort_id')) + 1,
                'name' => 'Chapa',
                'alias' => 'chapa',
                'logo' => 'images/payment-gateways/chapa.png',
                'fees' => 0,
                'charge_currency' => 'ETB',
                'charge_rate' => 1,
                'credentials' => json_encode([
                    'secret_key' => null,
                    'webhook_secret' => null,
                ]),
                'parameters' => json_encode([
                    [
                        'type' => 'route',
                        'label' => 'Webhook Endpoint',
                        'content' => 'payments/webhooks/chapa',
                    ],
                ]),
                'is_manual' => 0,
                'instructions' => null,
                'mode' => 'sandbox',
                'status' => 0,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('payment_gateways')->where('alias', 'chapa')->delete();
    }
};
