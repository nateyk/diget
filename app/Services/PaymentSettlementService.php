<?php

namespace App\Services;

use App\Events\TransactionPaid;
use App\Listeners\ProcessPaidTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentSettlementService
{
    public function __construct(private readonly ProcessPaidTransaction $fulfilment)
    {
    }

    /**
     * Settle and fulfil a verified payment exactly once.
     *
     * Provider adapters must supply values obtained from the provider API or
     * authenticated callback. Browser input is never used for these values.
     */
    public function settle(Transaction $transaction, array $provider): Transaction
    {
        return DB::transaction(function () use ($transaction, $provider) {
            $locked = Transaction::query()->whereKey($transaction->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->fulfilled_at || !$locked->isUnpaid()) {
                return $locked;
            }

            $providerId = (string) ($provider['id'] ?? '');
            if ($providerId === '') {
                throw new InvalidArgumentException('A provider payment identifier is required.');
            }

            if (isset($provider['gateway_id']) && (int) $locked->payment_gateway_id !== (int) $provider['gateway_id']) {
                throw new InvalidArgumentException('The payment gateway does not match the transaction.');
            }

            if (array_key_exists('amount', $provider)
                && array_key_exists('expected_amount', $provider)
                && !$this->sameMoney($provider['amount'], $provider['expected_amount'])) {
                throw new InvalidArgumentException('The provider amount does not match the transaction.');
            }

            if (isset($provider['currency'], $provider['expected_currency'])
                && strtoupper((string) $provider['currency']) !== strtoupper((string) $provider['expected_currency'])) {
                throw new InvalidArgumentException('The provider currency does not match the transaction.');
            }

            $locked->payment_id = $providerId;
            $locked->payer_id = $provider['payer_id'] ?? $locked->payer_id;
            $locked->payer_email = $provider['payer_email'] ?? $locked->payer_email;
            $locked->status = Transaction::STATUS_PAID;
            $locked->save();

            // Fulfilment remains inside this transaction. Any exception rolls
            // back the paid transition and all wallet/earning records.
            $this->fulfilment->fulfil($locked);

            $locked->fulfilled_at = now();
            $locked->save();

            return $locked;
        }, 3);
    }

    private function sameMoney(mixed $actual, mixed $expected): bool
    {
        return abs((float) $actual - (float) $expected) < 0.00001;
    }
}
