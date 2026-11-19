<?php

namespace App\Modules\Workshop\Payments\Contracts;

use App\Modules\Workshop\Payments\Data\PaymentChargeRequest;
use App\Modules\Workshop\Payments\Data\QrisCharge;

interface PaymentGateway
{
    public function createQrisCharge(PaymentChargeRequest $request): QrisCharge;
}
