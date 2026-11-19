<?php

namespace App\Modules\Platform\Services;

use InvalidArgumentException;

class TenantPackageCatalog
{
    /**
     * @var array<string, array{name: string, price: string, amount: int, description: string, employee_limit: int|null}>
     */
    private const PACKAGES = [
        'starter' => [
            'name' => 'Starter Tenant',
            'price' => 'Rp 149rb / bln',
            'amount' => 149000,
            'description' => 'Maksimal 2 karyawan/mekanik aktif.',
            'employee_limit' => 2,
        ],
        'pro' => [
            'name' => 'Pro Tenant',
            'price' => 'Rp 249rb / bln',
            'amount' => 249000,
            'description' => 'Maksimal 10 karyawan/mekanik aktif.',
            'employee_limit' => 10,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 'Rp 499rb / bln',
            'amount' => 499000,
            'description' => 'Karyawan/mekanik aktif tanpa batas.',
            'employee_limit' => null,
        ],
    ];

    /**
     * @return array<string, array{name: string, price: string, amount: int, description: string, employee_limit: int|null}>
     */
    public function all(): array
    {
        return self::PACKAGES;
    }

    public function employeeLimit(mixed $package): ?int
    {
        if (! is_string($package) || ! array_key_exists($package, self::PACKAGES)) {
            throw new InvalidArgumentException('Tenant package is not supported.');
        }

        return self::PACKAGES[$package]['employee_limit'];
    }
}
