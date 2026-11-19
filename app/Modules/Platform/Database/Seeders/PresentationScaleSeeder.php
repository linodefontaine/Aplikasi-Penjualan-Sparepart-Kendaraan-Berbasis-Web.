<?php

namespace App\Modules\Platform\Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Platform\Services\TenantPackageCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Stancl\Tenancy\Database\Models\Domain;

class PresentationScaleSeeder extends Seeder
{
    /**
     * @var list<array{name: string, email: string}>
     */
    private const SUPER_ADMINS = [
        ['name' => 'Farrelino Putra Setiawan', 'email' => 'farrelino.putra@amahay.id'],
        ['name' => 'Arvanda Pratama', 'email' => 'arvanda.pratama@amahay.id'],
        ['name' => 'Nadia Kusuma Wardani', 'email' => 'nadia.kusuma@amahay.id'],
        ['name' => 'Rizky Ramadhan', 'email' => 'rizky.ramadhan@amahay.id'],
        ['name' => 'Dimas Saputra', 'email' => 'dimas.saputra@amahay.id'],
    ];

    /**
     * @var list<array{
     *     code: string,
     *     owner_name: string,
     *     owner_email: string,
     *     owner_phone: string,
     *     tenant_name: string,
     *     subdomain: string,
     *     package: string,
     *     status: string,
     *     city: string,
     *     plate_region: string,
     *     mechanics: list<string>,
     *     cashier: string,
     *     customers: list<string>,
     *     transaction_count: int
     * }>
     */
    private const WORKSHOPS = [
        [
            'code' => 'SRM',
            'owner_name' => 'Budi Santoso',
            'owner_email' => 'budi.santoso.motor@gmail.com',
            'owner_phone' => '081290000101',
            'tenant_name' => 'Bengkel Sumber Rejeki Motor',
            'subdomain' => 'sumberrejeki',
            'package' => 'enterprise',
            'status' => 'active',
            'city' => 'Banyuwangi',
            'plate_region' => 'P',
            'mechanics' => ['Wawan Setiawan', 'Arif Kurnia'],
            'cashier' => 'Dewi Puspitasari',
            'customers' => ['Citra Lestari', 'Fajar Nugroho', 'Siti Rahmawati'],
            'transaction_count' => 5,
        ],
        [
            'code' => 'WAG',
            'owner_name' => 'Hendra Wijaya',
            'owner_email' => 'hendra.wijaya.auto@gmail.com',
            'owner_phone' => '081290000102',
            'tenant_name' => 'Wijaya Auto Garage',
            'subdomain' => 'wijayaauto',
            'package' => 'pro',
            'status' => 'active',
            'city' => 'Jakarta',
            'plate_region' => 'B',
            'mechanics' => ['Agus Permadi', 'Rizal Firmansyah'],
            'cashier' => 'Nita Permata',
            'customers' => ['Bayu Pratama', 'Tia Anggraini', 'Dimas Nugraha'],
            'transaction_count' => 4,
        ],
        [
            'code' => 'JAA',
            'owner_name' => 'Agus Setiawan',
            'owner_email' => 'agus.bengkeljaya@gmail.com',
            'owner_phone' => '081290000103',
            'tenant_name' => 'Bengkel Jaya Abadi',
            'subdomain' => 'jayaabadi',
            'package' => 'pro',
            'status' => 'active',
            'city' => 'Bandung',
            'plate_region' => 'B',
            'mechanics' => ['Rangga Maulana', 'Deni Firmansyah'],
            'cashier' => 'Lilis Kurniasih',
            'customers' => ['Rina Marlina', 'Yusuf Hidayat', 'Maya Safitri'],
            'transaction_count' => 3,
        ],
        [
            'code' => 'PMS',
            'owner_name' => 'Eko Prasetyo',
            'owner_email' => 'eko.prasetyo.servis@gmail.com',
            'owner_phone' => '081290000104',
            'tenant_name' => 'Prasetyo Motor Service',
            'subdomain' => 'prasetyomotor',
            'package' => 'starter',
            'status' => 'active',
            'city' => 'Yogyakarta',
            'plate_region' => 'B',
            'mechanics' => ['Bambang Riyanto', 'Feri Kurniawan'],
            'cashier' => 'Ratna Wulandari',
            'customers' => ['Andika Saputra', 'Putri Maharani', 'Rizal Akbar'],
            'transaction_count' => 2,
        ],
        [
            'code' => 'BTT',
            'owner_name' => 'Slamet Riyadi',
            'owner_email' => 'slamet.riyadi.garage@gmail.com',
            'owner_phone' => '081290000105',
            'tenant_name' => 'Bintang Timur Bengkel',
            'subdomain' => 'bintangtimur',
            'package' => 'enterprise',
            'status' => 'active',
            'city' => 'Banyuwangi',
            'plate_region' => 'P',
            'mechanics' => ['Teguh Prakoso', 'Ari Wibowo'],
            'cashier' => 'Mila Oktaviani',
            'customers' => ['Rudi Hermawan', 'Dian Pertiwi', 'Ilham Maulana'],
            'transaction_count' => 6,
        ],
        [
            'code' => 'KSS',
            'owner_name' => 'Dedi Kurniawan',
            'owner_email' => 'dedi.kurniawan.part@gmail.com',
            'owner_phone' => '081290000106',
            'tenant_name' => 'Kurniawan Sparepart & Servis',
            'subdomain' => 'kurniawanmotor',
            'package' => 'pro',
            'status' => 'active',
            'city' => 'Jakarta',
            'plate_region' => 'B',
            'mechanics' => ['Yudi Hartono', 'Fajar Setiawan'],
            'cashier' => 'Sari Mulyani',
            'customers' => ['Naufal Ramadhan', 'Wulan Sari', 'Bima Prakoso'],
            'transaction_count' => 5,
        ],
        [
            'code' => 'MBM',
            'owner_name' => 'Wahyu Hidayat',
            'owner_email' => 'wahyu.hidayat.bengkel@gmail.com',
            'owner_phone' => '081290000107',
            'tenant_name' => 'Maju Bersama Motor',
            'subdomain' => 'majubersama',
            'package' => 'starter',
            'status' => 'active',
            'city' => 'Jember',
            'plate_region' => 'P',
            'mechanics' => ['Edi Susanto', 'Roni Kurnia'],
            'cashier' => 'Novi Andriani',
            'customers' => ['Gilang Permana', 'Ayu Lestari', 'Doni Saputro'],
            'transaction_count' => 3,
        ],
        [
            'code' => 'POC',
            'owner_name' => 'Andi Permana',
            'owner_email' => 'andi.permana.auto@gmail.com',
            'owner_phone' => '081290000108',
            'tenant_name' => 'Permana Oto Care',
            'subdomain' => 'permanaoto',
            'package' => 'pro',
            'status' => 'active',
            'city' => 'Bandung',
            'plate_region' => 'B',
            'mechanics' => ['Satria Nugraha', 'Dedi Irawan'],
            'cashier' => 'Intan Permatasari',
            'customers' => ['Rafi Akbar', 'Nanda Kusuma', 'Yuni Astuti'],
            'transaction_count' => 4,
        ],
        [
            'code' => 'SMB',
            'owner_name' => 'Rudi Hartono',
            'owner_email' => 'rudi.hartono.servis@gmail.com',
            'owner_phone' => '081290000109',
            'tenant_name' => 'Sentosa Motor Banyuwangi',
            'subdomain' => 'sentosamotor',
            'package' => 'starter',
            'status' => 'pending_payment',
            'city' => 'Banyuwangi',
            'plate_region' => 'P',
            'mechanics' => ['Heri Kurniawan', 'Eko Purnomo'],
            'cashier' => 'Rina Aprilia',
            'customers' => ['Fikri Ramadhan', 'Dwi Anggraini', 'Salsa Putri'],
            'transaction_count' => 2,
        ],
        [
            'code' => 'BMM',
            'owner_name' => 'Taufik Hidayat',
            'owner_email' => 'taufik.garage.id@gmail.com',
            'owner_phone' => '081290000110',
            'tenant_name' => 'Berkah Mandiri Motor',
            'subdomain' => 'berkahmandiri',
            'package' => 'pro',
            'status' => 'pending_payment',
            'city' => 'Jakarta',
            'plate_region' => 'B',
            'mechanics' => ['Doni Firmansyah', 'Rizky Maulana'],
            'cashier' => 'Fitri Handayani',
            'customers' => ['Tegar Pratama', 'Desi Lestari', 'Rangga Saputra'],
            'transaction_count' => 3,
        ],
    ];

    /**
     * @var list<array{name: string, category: string, unit: string, purchase_price: int, price: int}>
     */
    private const PARTS = [
        ['name' => 'Oli Yamalube Super Sport 10W-40', 'category' => 'Oli Mesin', 'unit' => 'botol', 'purchase_price' => 52000, 'price' => 68000],
        ['name' => 'Oli Honda MPX2 0.8 L', 'category' => 'Oli Mesin', 'unit' => 'botol', 'purchase_price' => 41000, 'price' => 57000],
        ['name' => 'Kampas Rem Depan Honda', 'category' => 'Sistem Rem', 'unit' => 'set', 'purchase_price' => 48000, 'price' => 78000],
        ['name' => 'Busi NGK CPR6EA-9', 'category' => 'Pengapian', 'unit' => 'pcs', 'purchase_price' => 15000, 'price' => 26000],
        ['name' => 'Filter Udara Honda BeAT', 'category' => 'Filter Udara', 'unit' => 'pcs', 'purchase_price' => 45000, 'price' => 69000],
        ['name' => 'V-Belt Yamaha Mio', 'category' => 'Transmisi', 'unit' => 'pcs', 'purchase_price' => 96000, 'price' => 135000],
        ['name' => 'Aki Yuasa YTZ5S', 'category' => 'Kelistrikan', 'unit' => 'pcs', 'purchase_price' => 155000, 'price' => 198000],
    ];

    public function run(TenantPackageCatalog $packageCatalog): void
    {
        $baseDomain = trim((string) config('tenancy.base_domain'));
        if ($baseDomain === '') {
            $baseDomain = (string) parse_url((string) config('app.url'), PHP_URL_HOST);
        }
        if ($baseDomain === '') {
            throw new RuntimeException('Domain tenant belum dapat ditentukan dari konfigurasi tenancy atau APP_URL.');
        }

        $this->seedSuperAdmins();

        $packages = $packageCatalog->all();

        foreach (self::WORKSHOPS as $tenantIndex => $workshop) {
            if (! array_key_exists($workshop['package'], $packages)) {
                throw new RuntimeException("Paket {$workshop['package']} tidak tersedia.");
            }

            $tenant = $this->seedWorkshopAccount($workshop, $baseDomain, $packages[$workshop['package']]);
            $tenant->run(function () use ($workshop, $tenantIndex): void {
                $this->seedWorkshopOperations($workshop, $tenantIndex);
            });
        }

        $this->command?->info('Seed presentasi selesai: 5 Super Admin, 10 Owner, 10 tenant, 8 invoice lunas, dan 2 invoice menunggu pembayaran.');
    }

    private function seedSuperAdmins(): void
    {
        foreach (self::SUPER_ADMINS as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'username' => Str::before($admin['email'], '@'),
                    'name' => $admin['name'],
                    'password' => 'password',
                    'phone' => null,
                    'role' => 'Super Admin',
                    'tenant_id' => null,
                ],
            );
        }
    }

    /**
     * @param  array{
     *     code: string,
     *     owner_name: string,
     *     owner_email: string,
     *     owner_phone: string,
     *     tenant_name: string,
     *     subdomain: string,
     *     package: string,
     *     status: string,
     *     city: string,
     *     plate_region: string,
     *     mechanics: list<string>,
     *     cashier: string,
     *     customers: list<string>,
     *     transaction_count: int
     * }  $workshop
     * @param  array{name: string, price: string, amount: int, description: string, employee_limit: int|null}  $package
     */
    private function seedWorkshopAccount(array $workshop, string $baseDomain, array $package): Tenant
    {
        $domainName = Str::lower($workshop['subdomain'].'.'.$baseDomain);
        $domain = Domain::query()->where('domain', $domainName)->first();
        $tenant = $domain === null
            ? Tenant::query()->where('data->tenant_name', $workshop['tenant_name'])->first()
            : Tenant::query()->findOrFail($domain->tenant_id);

        if ($tenant !== null && $tenant->getAttribute('tenant_name') !== $workshop['tenant_name']) {
            throw new RuntimeException("Domain {$domainName} sudah digunakan tenant lain.");
        }

        if ($tenant === null) {
            $tenant = Tenant::create([
                'tenant_name' => $workshop['tenant_name'],
                'phone' => $workshop['owner_phone'],
                'package' => $workshop['package'],
                'status' => $workshop['status'],
            ]);
        }

        if ($domain !== null && (string) $domain->tenant_id !== (string) $tenant->getTenantKey()) {
            throw new RuntimeException("Domain {$domainName} sudah terhubung ke tenant lain.");
        }

        if ($domain === null) {
            $tenant->createDomain($domainName);
        }

        $invoiceNumber = 'SUB-PRESENTATION-'.Str::upper($workshop['subdomain']);
        $existingInvoice = DB::table('subscription_invoices')
            ->where('invoice_number', $invoiceNumber)
            ->first();
        $now = CarbonImmutable::now();
        $isPaid = $workshop['status'] === 'active';
        $periodStartsAt = null;
        $periodEndsAt = null;
        $paidAt = null;

        if ($isPaid) {
            $periodStartsAt = $existingInvoice?->subscription_starts_at !== null
                ? CarbonImmutable::parse($existingInvoice->subscription_starts_at)
                : $now->subDays(5);
            $periodEndsAt = $existingInvoice?->subscription_ends_at !== null
                ? CarbonImmutable::parse($existingInvoice->subscription_ends_at)
                : $periodStartsAt->addMonthNoOverflow();
            $paidAt = $existingInvoice?->paid_at !== null
                ? CarbonImmutable::parse($existingInvoice->paid_at)
                : $periodStartsAt;
        }

        $owner = User::updateOrCreate(
            ['email' => $workshop['owner_email']],
            [
                'username' => Str::before($workshop['owner_email'], '@'),
                'name' => $workshop['owner_name'],
                'password' => 'password',
                'phone' => $workshop['owner_phone'],
                'role' => 'Owner',
                'tenant_id' => $tenant->getTenantKey(),
            ],
        );

        DB::transaction(function () use (
            $tenant,
            $workshop,
            $package,
            $owner,
            $invoiceNumber,
            $existingInvoice,
            $isPaid,
            $periodStartsAt,
            $periodEndsAt,
            $paidAt,
            $now,
        ): void {
            $tenant->update([
                'tenant_name' => $workshop['tenant_name'],
                'phone' => $workshop['owner_phone'],
                'package' => $workshop['package'],
                'status' => $workshop['status'],
                'subscription_started_at' => $periodStartsAt,
                'subscription_ends_at' => $periodEndsAt,
            ]);

            $owner->update(['tenant_id' => $tenant->getTenantKey()]);

            DB::table('subscription_invoices')->updateOrInsert(
                ['invoice_number' => $invoiceNumber],
                [
                    'tenant_id' => $tenant->getTenantKey(),
                    'package' => $workshop['package'],
                    'package_name' => $package['name'],
                    'amount' => $package['amount'],
                    'payment_method' => $isPaid ? ($existingInvoice?->payment_method ?? 'qris') : null,
                    'status' => $isPaid ? 'paid' : 'pending',
                    'paid_at' => $paidAt,
                    'subscription_starts_at' => $periodStartsAt,
                    'subscription_ends_at' => $periodEndsAt,
                    'created_at' => $existingInvoice?->created_at ?? $now,
                    'updated_at' => $now,
                ],
            );
        });

        return $tenant;
    }

    /**
     * @param  array{
     *     code: string,
     *     owner_name: string,
     *     owner_email: string,
     *     owner_phone: string,
     *     tenant_name: string,
     *     subdomain: string,
     *     package: string,
     *     status: string,
     *     city: string,
     *     plate_region: string,
     *     mechanics: list<string>,
     *     cashier: string,
     *     customers: list<string>,
     *     transaction_count: int
     * }  $workshop
     */
    private function seedWorkshopOperations(array $workshop, int $tenantIndex): void
    {
        DB::transaction(function () use ($workshop, $tenantIndex): void {
            $code = $workshop['code'];
            $now = CarbonImmutable::now();
            $partIds = [];
            $parts = [];

            foreach (self::PARTS as $partIndex => $part) {
                $sku = $code.'-'.['OLI-YAMALUBE', 'OLI-MPX2', 'KAMPAS-REM', 'BUSI-NGK', 'FILTER-UDARA', 'V-BELT', 'AKI-YUASA'][$partIndex];
                $partRecord = [
                    'sku' => $sku,
                    ...$part,
                    'stock' => 5 + (($tenantIndex + $partIndex) % 8),
                    'minimum_stock' => 2 + ($partIndex % 2),
                    'location' => chr(65 + $partIndex).'-01',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                DB::table('spare_parts')->updateOrInsert(['sku' => $sku], $partRecord);
                $partIds[$sku] = (int) DB::table('spare_parts')->where('sku', $sku)->value('id');
                $parts[] = [...$part, 'sku' => $sku];
            }

            $employeeIds = [];
            $employees = [];

            foreach (array_slice($workshop['mechanics'], 0, $workshop['package'] === 'starter' ? 1 : 2) as $mechanicIndex => $mechanicName) {
                $employees[] = [
                    'employee_code' => $code.'-MEK-'.($mechanicIndex === 0 ? 'A' : 'B'),
                    'name' => $mechanicName,
                    'phone' => $this->operationalPhone(200000 + ($tenantIndex * 10) + $mechanicIndex),
                    'position' => 'mechanic',
                    'specialization' => $mechanicIndex === 0 ? 'Servis mesin dan tune up' : 'Sistem rem dan transmisi',
                ];
            }

            $employees[] = [
                'employee_code' => $code.'-KSR-A',
                'name' => $workshop['cashier'],
                'phone' => $this->operationalPhone(200002 + ($tenantIndex * 10)),
                'position' => 'cashier',
                'specialization' => null,
            ];

            foreach ($employees as $employee) {
                DB::table('employees')->updateOrInsert(
                    ['employee_code' => $employee['employee_code']],
                    [...$employee, 'status' => 'active', 'created_at' => $now, 'updated_at' => $now],
                );
                $employeeIds[$employee['employee_code']] = (int) DB::table('employees')
                    ->where('employee_code', $employee['employee_code'])
                    ->value('id');
            }

            $customerIds = [];
            $vehicleIds = [];
            $plateSuffixes = $workshop['plate_region'] === 'P'
                ? ['AB', 'CD', 'EF']
                : ['KLM', 'NPR', 'QRS'];
            $models = [
                ['Honda', 'Vario 160'],
                ['Yamaha', 'NMAX'],
                ['Honda', 'BeAT'],
            ];

            foreach ($workshop['customers'] as $customerIndex => $customerName) {
                $phone = $this->operationalPhone(300000 + ($tenantIndex * 10) + $customerIndex, '0813');
                $customer = [
                    'name' => $customerName,
                    'phone' => $phone,
                    'email' => Str::slug($customerName, '.').'@gmail.com',
                    'address' => 'Jl. Diponegoro No. '.(12 + $customerIndex * 5).', '.$workshop['city'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
                DB::table('customers')->updateOrInsert(['phone' => $phone], $customer);
                $customerIds[$customerIndex] = (int) DB::table('customers')->where('phone', $phone)->value('id');

                $plateNumber = str_pad((string) (1400 + ($tenantIndex * 61) + ($customerIndex * 7)), 4, '0', STR_PAD_LEFT);
                $licensePlate = $workshop['plate_region'].' '.$plateNumber.' '.$plateSuffixes[$customerIndex];
                [$brand, $model] = $models[$customerIndex];
                $vehicle = [
                    'customer_id' => $customerIds[$customerIndex],
                    'license_plate' => $licensePlate,
                    'brand' => $brand,
                    'model' => $model,
                    'year' => 2021 + (($tenantIndex + $customerIndex) % 5),
                    'odometer' => 12500 + ($tenantIndex * 370) + ($customerIndex * 850),
                    'updated_at' => $now,
                    'created_at' => $now,
                ];
                DB::table('vehicles')->updateOrInsert(['license_plate' => $licensePlate], $vehicle);
                $vehicleIds[$customerIndex] = [
                    'id' => (int) DB::table('vehicles')->where('license_plate', $licensePlate)->value('id'),
                    'odometer' => $vehicle['odometer'],
                ];
            }

            $services = [
                [
                    'service_code' => $code.'-SERVIS-OLI',
                    'name' => 'Servis Ganti Oli',
                    'description' => 'Penggantian oli mesin dan pemeriksaan kendaraan.',
                    'estimated_duration_minutes' => 30,
                    'price' => 35000,
                ],
                [
                    'service_code' => $code.'-SERVIS-REM',
                    'name' => 'Servis Kampas Rem',
                    'description' => 'Pemeriksaan dan penggantian kampas rem.',
                    'estimated_duration_minutes' => 45,
                    'price' => 48000,
                ],
                [
                    'service_code' => $code.'-TUNE-UP',
                    'name' => 'Tune Up Ringan',
                    'description' => 'Pemeriksaan busi, filter udara, dan penyetelan mesin.',
                    'estimated_duration_minutes' => 60,
                    'price' => 75000,
                ],
                [
                    'service_code' => $code.'-CEK-AKI',
                    'name' => 'Pemeriksaan dan Penggantian Aki',
                    'description' => 'Pemeriksaan sistem kelistrikan dan kondisi aki.',
                    'estimated_duration_minutes' => 25,
                    'price' => 28000,
                ],
            ];
            $serviceIds = [];

            foreach ($services as $service) {
                DB::table('service_catalog')->updateOrInsert(
                    ['service_code' => $service['service_code']],
                    [...$service, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                );
                $serviceIds[$service['service_code']] = (int) DB::table('service_catalog')
                    ->where('service_code', $service['service_code'])
                    ->value('id');
            }

            foreach (range(0, $workshop['transaction_count'] - 1) as $transactionIndex) {
                $completedAt = $now->subDays($transactionIndex + 2);
                $customerIndex = $transactionIndex % count($workshop['customers']);
                $service = $services[$transactionIndex % count($services)];
                $part = $parts[($transactionIndex + $tenantIndex) % count($parts)];
                $quantity = 1 + ($transactionIndex % 2);
                $partSubtotal = $part['price'] * $quantity;
                $total = $service['price'] + $partSubtotal;
                $sequence = str_pad((string) ($transactionIndex + 1), 3, '0', STR_PAD_LEFT);
                $workOrderNumber = 'WO-'.$code.'-'.$sequence;
                $invoiceNumber = 'POS-'.$code.'-'.$sequence;
                $mechanicCode = $code.'-MEK-'.($workshop['package'] === 'starter' || $transactionIndex % 2 === 0 ? 'A' : 'B');
                $vehicle = $vehicleIds[$customerIndex];

                DB::table('work_orders')->updateOrInsert(
                    ['work_order_number' => $workOrderNumber],
                    [
                        'customer_id' => $customerIds[$customerIndex],
                        'vehicle_id' => $vehicle['id'],
                        'mechanic_id' => $employeeIds[$mechanicCode],
                        'status' => 'completed',
                        'complaint' => 'Servis berkala dan pemeriksaan kendaraan.',
                        'diagnosis' => 'Kendaraan siap digunakan setelah perawatan.',
                        'odometer' => $vehicle['odometer'],
                        'started_at' => $completedAt->subHour(),
                        'completed_at' => $completedAt,
                        'created_at' => $completedAt,
                        'updated_at' => $now,
                    ],
                );
                $workOrderId = (int) DB::table('work_orders')
                    ->where('work_order_number', $workOrderNumber)
                    ->value('id');
                DB::table('work_order_services')->where('work_order_id', $workOrderId)->delete();
                DB::table('work_order_parts')->where('work_order_id', $workOrderId)->delete();
                DB::table('work_order_services')->insert([
                    'work_order_id' => $workOrderId,
                    'service_id' => $serviceIds[$service['service_code']],
                    'service_name' => $service['name'],
                    'quantity' => 1,
                    'unit_price' => $service['price'],
                    'subtotal' => $service['price'],
                    'created_at' => $completedAt,
                    'updated_at' => $now,
                ]);
                DB::table('work_order_parts')->insert([
                    'work_order_id' => $workOrderId,
                    'spare_part_id' => $partIds[$part['sku']],
                    'sku' => $part['sku'],
                    'part_name' => $part['name'],
                    'quantity' => $quantity,
                    'unit_price' => $part['price'],
                    'subtotal' => $partSubtotal,
                    'created_at' => $completedAt,
                    'updated_at' => $now,
                ]);

                DB::table('sales')->updateOrInsert(
                    ['invoice_number' => $invoiceNumber],
                    [
                        'work_order_id' => $workOrderId,
                        'customer_id' => $customerIds[$customerIndex],
                        'cashier_id' => $employeeIds[$code.'-KSR-A'],
                        'status' => 'completed',
                        'subtotal' => $total,
                        'discount' => 0,
                        'tax' => 0,
                        'total' => $total,
                        'amount_paid' => $total,
                        'change_due' => 0,
                        'sold_at' => $completedAt,
                        'notes' => 'Pembayaran servis selesai.',
                        'created_at' => $completedAt,
                        'updated_at' => $now,
                    ],
                );
                $saleId = (int) DB::table('sales')->where('invoice_number', $invoiceNumber)->value('id');
                DB::table('sale_items')->where('sale_id', $saleId)->delete();
                DB::table('payments')->where('sale_id', $saleId)->delete();
                DB::table('sale_items')->insert([
                    [
                        'sale_id' => $saleId,
                        'spare_part_id' => null,
                        'service_id' => $serviceIds[$service['service_code']],
                        'item_name' => $service['name'],
                        'sku' => $service['service_code'],
                        'quantity' => 1,
                        'unit_price' => $service['price'],
                        'discount' => 0,
                        'subtotal' => $service['price'],
                        'created_at' => $completedAt,
                        'updated_at' => $now,
                    ],
                    [
                        'sale_id' => $saleId,
                        'spare_part_id' => $partIds[$part['sku']],
                        'service_id' => null,
                        'item_name' => $part['name'],
                        'sku' => $part['sku'],
                        'quantity' => $quantity,
                        'unit_price' => $part['price'],
                        'discount' => 0,
                        'subtotal' => $partSubtotal,
                        'created_at' => $completedAt,
                        'updated_at' => $now,
                    ],
                ]);
                DB::table('payments')->insert([
                    'sale_id' => $saleId,
                    'payment_method' => $transactionIndex % 2 === 0 ? 'cash' : 'transfer',
                    'amount' => $total,
                    'status' => 'completed',
                    'reference_number' => $invoiceNumber,
                    'paid_at' => $completedAt,
                    'notes' => 'Pembayaran transaksi servis.',
                    'created_at' => $completedAt,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    private function operationalPhone(int $number, string $prefix = '0812'): string
    {
        return $prefix.str_pad((string) $number, 7, '0', STR_PAD_LEFT);
    }
}
