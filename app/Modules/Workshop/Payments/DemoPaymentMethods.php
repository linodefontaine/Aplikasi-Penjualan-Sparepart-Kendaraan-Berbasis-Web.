<?php

namespace App\Modules\Workshop\Payments;

final class DemoPaymentMethods
{
    public const ONLINE_METHODS = [
        'qris',
        'va_bca',
        'va_bri',
        'va_bni',
        'va_mandiri',
        'ewallet',
    ];

    public const METHODS = [
        ...self::ONLINE_METHODS,
        'cash',
    ];

    public const LABELS = [
        'qris' => 'QRIS',
        'va_bca' => 'Virtual Account BCA',
        'va_bri' => 'Virtual Account BRI',
        'va_bni' => 'Virtual Account BNI',
        'va_mandiri' => 'Virtual Account Mandiri',
        'ewallet' => 'E-wallet',
        'cash' => 'Tunai / Kasir',
    ];

    private const BANK_PREFIXES = [
        'va_bca' => '3901',
        'va_bri' => '2621',
        'va_bni' => '988',
        'va_mandiri' => '70012',
    ];

    public static function virtualAccountNumber(string $tenantKey, string $invoice, string $method): ?string
    {
        $prefix = self::BANK_PREFIXES[$method] ?? null;
        if ($prefix === null) {
            return null;
        }

        $suffix = hexdec(substr(hash('sha256', "{$tenantKey}|{$invoice}|{$method}"), 0, 10));

        return $prefix.str_pad((string) ($suffix % 100000000000), 11, '0', STR_PAD_LEFT);
    }

    public static function label(string $method): string
    {
        return self::LABELS[$method] ?? $method;
    }
}
