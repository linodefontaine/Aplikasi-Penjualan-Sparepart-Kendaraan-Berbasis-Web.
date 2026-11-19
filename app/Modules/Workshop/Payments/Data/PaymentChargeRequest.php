<?php

namespace App\Modules\Workshop\Payments\Data;

use Carbon\CarbonImmutable;

final readonly class PaymentChargeRequest
{
    public function __construct(
        public string $paymentReference,
        public string $idempotencyKey,
        public int $amount,
        public CarbonImmutable $expiresAt,
    ) {}
}
