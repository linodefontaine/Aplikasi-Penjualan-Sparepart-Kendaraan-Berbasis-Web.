<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WorkshopSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::query()
            ->whereHas('owner')
            ->get()
            ->filter(fn (Tenant $tenant): bool => $tenant->isActive());

        if ($tenants->isEmpty()) {
            throw new RuntimeException('Tidak ada tenant aktif dengan akun Owner untuk diisi data contoh.');
        }

        foreach ($tenants as $tenant) {
            $tenant->run(fn (): mixed => $this->seedTenant());
        }
    }

    private function seedTenant(): void
    {
        DB::transaction(function (): void {
            $parts = [
                ['sku' => 'OLI-MPX2', 'name' => 'Oli Mesin MPX2 0.8L', 'category' => 'Oli Mesin', 'unit' => 'botol', 'stock' => 12, 'minimum_stock' => 3, 'location' => 'A-01', 'purchase_price' => 42000, 'price' => 58000],
                ['sku' => 'KAMPAS-DEPAN', 'name' => 'Kampas Rem Depan', 'category' => 'Sistem Rem', 'unit' => 'set', 'stock' => 2, 'minimum_stock' => 3, 'location' => 'B-02', 'purchase_price' => 52000, 'price' => 85000],
                ['sku' => 'BUSI-NGK', 'name' => 'Busi NGK CPR6EA-9', 'category' => 'Pengapian', 'unit' => 'pcs', 'stock' => 0, 'minimum_stock' => 2, 'location' => 'C-01', 'purchase_price' => 16000, 'price' => 28000],
                ['sku' => 'FILTER-UDARA', 'name' => 'Filter Udara Vario 160', 'category' => 'Filter', 'unit' => 'pcs', 'stock' => 8, 'minimum_stock' => 2, 'location' => 'C-02', 'purchase_price' => 52000, 'price' => 75000],
                ['sku' => 'VBELT-BEAT', 'name' => 'V-Belt Honda BeAT', 'category' => 'Transmisi', 'unit' => 'pcs', 'stock' => 6, 'minimum_stock' => 2, 'location' => 'D-01', 'purchase_price' => 95000, 'price' => 135000],
                ['sku' => 'AKI-GTZ5S', 'name' => 'Aki Kering GTZ5S', 'category' => 'Kelistrikan', 'unit' => 'pcs', 'stock' => 7, 'minimum_stock' => 2, 'location' => 'E-01', 'purchase_price' => 145000, 'price' => 185000],
                ['sku' => 'OLI-GARDAN', 'name' => 'Oli Gardan 120ml', 'category' => 'Oli Mesin', 'unit' => 'tube', 'stock' => 10, 'minimum_stock' => 3, 'location' => 'A-02', 'purchase_price' => 12000, 'price' => 20000],
                ['sku' => 'LAMPU-LED', 'name' => 'Lampu Depan LED Motor', 'category' => 'Kelistrikan', 'unit' => 'pcs', 'stock' => 5, 'minimum_stock' => 2, 'location' => 'E-02', 'purchase_price' => 68000, 'price' => 98000],
                ['sku' => 'KAMPAS-BELAKANG', 'name' => 'Kampas Rem Belakang', 'category' => 'Sistem Rem', 'unit' => 'set', 'stock' => 6, 'minimum_stock' => 2, 'location' => 'B-03', 'purchase_price' => 38000, 'price' => 65000],
                ['sku' => 'RANTAI-GEAR', 'name' => 'Set Rantai dan Gear', 'category' => 'Transmisi', 'unit' => 'set', 'stock' => 4, 'minimum_stock' => 2, 'location' => 'D-02', 'purchase_price' => 125000, 'price' => 175000],
            ];
            $partIds = [];

            foreach ($parts as $part) {
                DB::table('spare_parts')->updateOrInsert(
                    ['sku' => $part['sku']],
                    [...$part, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()],
                );
                $partIds[$part['sku']] = DB::table('spare_parts')->where('sku', $part['sku'])->value('id');
            }

            $employees = [
                ['employee_code' => 'MEK-01', 'name' => 'Anton Pratama', 'phone' => '081200000101', 'position' => 'mechanic', 'specialization' => 'Servis mesin'],
                ['employee_code' => 'MEK-02', 'name' => 'Budi Santoso', 'phone' => '081200000102', 'position' => 'mechanic', 'specialization' => 'Sistem rem dan transmisi'],
                ['employee_code' => 'KSR-01', 'name' => 'Siti Rahma', 'phone' => '081200000103', 'position' => 'cashier', 'specialization' => null],
            ];
            $employeeIds = [];

            foreach ($employees as $employee) {
                DB::table('employees')->updateOrInsert(
                    ['employee_code' => $employee['employee_code']],
                    [...$employee, 'status' => 'active', 'updated_at' => now(), 'created_at' => now()],
                );
                $employeeIds[$employee['employee_code']] = DB::table('employees')
                    ->where('employee_code', $employee['employee_code'])
                    ->value('id');
            }

            $customers = [
                ['name' => 'Rizky Maulana', 'phone' => '081200000201', 'email' => 'rizky24@gmail.com', 'address' => 'Jl. Melati No. 12'],
                ['name' => 'Dewi Anggraini', 'phone' => '081200000202', 'email' => 'dewi24@gmail.com', 'address' => 'Jl. Kenanga No. 8'],
                ['name' => 'Fajar Nugraha', 'phone' => '081200000203', 'email' => 'fajar24@gmail.com', 'address' => 'Jl. Mawar No. 21'],
            ];
            $customerIds = [];

            foreach ($customers as $customer) {
                DB::table('customers')->updateOrInsert(
                    ['phone' => $customer['phone']],
                    [...$customer, 'updated_at' => now(), 'created_at' => now()],
                );
                $customerIds[$customer['phone']] = DB::table('customers')
                    ->where('phone', $customer['phone'])
                    ->value('id');
            }

            $vehicles = [
                ['customer_phone' => '081200000201', 'license_plate' => 'D 1234 AMH', 'brand' => 'Honda', 'model' => 'Vario 160', 'year' => 2023, 'odometer' => 12500],
                ['customer_phone' => '081200000202', 'license_plate' => 'D 5678 XZ', 'brand' => 'Yamaha', 'model' => 'Mio M3', 'year' => 2021, 'odometer' => 23800],
                ['customer_phone' => '081200000203', 'license_plate' => 'B 3210 WQ', 'brand' => 'Honda', 'model' => 'BeAT', 'year' => 2022, 'odometer' => 18700],
            ];
            $vehicleIds = [];

            foreach ($vehicles as $vehicle) {
                DB::table('vehicles')->updateOrInsert(
                    ['license_plate' => $vehicle['license_plate']],
                    [
                        'brand' => $vehicle['brand'],
                        'model' => $vehicle['model'],
                        'year' => $vehicle['year'],
                        'odometer' => $vehicle['odometer'],
                        'customer_id' => $customerIds[$vehicle['customer_phone']],
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
                $vehicleIds[$vehicle['license_plate']] = DB::table('vehicles')
                    ->where('license_plate', $vehicle['license_plate'])
                    ->value('id');
            }

            $services = [
                ['service_code' => 'SAMPLE-SRV-OLI', 'name' => 'Servis Ganti Oli', 'description' => 'Penggantian oli mesin dan pemeriksaan ringan.', 'estimated_duration_minutes' => 30, 'price' => 35000],
                ['service_code' => 'SAMPLE-SRV-REM', 'name' => 'Servis Sistem Rem', 'description' => 'Pemeriksaan dan pemasangan kampas rem.', 'estimated_duration_minutes' => 45, 'price' => 45000],
                ['service_code' => 'SAMPLE-SRV-TUNE', 'name' => 'Tune Up Ringan', 'description' => 'Pemeriksaan busi, filter udara, dan penyetelan mesin.', 'estimated_duration_minutes' => 60, 'price' => 75000],
                ['service_code' => 'SAMPLE-SRV-AKI', 'name' => 'Jasa Penggantian Aki', 'description' => 'Pemeriksaan kelistrikan dan pemasangan aki.', 'estimated_duration_minutes' => 20, 'price' => 25000],
            ];
            $serviceIds = [];

            foreach ($services as $service) {
                DB::table('service_catalog')->updateOrInsert(
                    ['service_code' => $service['service_code']],
                    [...$service, 'is_active' => true, 'updated_at' => now(), 'created_at' => now()],
                );
                $serviceIds[$service['service_code']] = DB::table('service_catalog')
                    ->where('service_code', $service['service_code'])
                    ->value('id');
            }

            $orders = [
                ['number' => 'SAMPLE-WO-001', 'invoice' => 'INV-001', 'customer' => '081200000201', 'vehicle' => 'D 1234 AMH', 'mechanic' => 'MEK-01', 'service' => 'SRV-OLI', 'parts' => ['OLI-MPX2' => 1], 'days_ago' => 1],
                ['number' => 'SAMPLE-WO-002', 'invoice' => 'INV-002', 'customer' => '081200000202', 'vehicle' => 'D 5678 XZ', 'mechanic' => 'MEK-02', 'service' => 'SRV-REM', 'parts' => ['KAMPAS-DEPAN' => 1], 'days_ago' => 2],
                ['number' => 'SAMPLE-WO-003', 'invoice' => 'INV-003', 'customer' => '081200000203', 'vehicle' => 'B 3210 WQ', 'mechanic' => 'MEK-01', 'service' => 'SRV-TUNE', 'parts' => ['BUSI-NGK' => 1, 'FILTER-UDARA' => 1], 'days_ago' => 3],
                ['number' => 'SAMPLE-WO-004', 'invoice' => 'INV-004', 'customer' => '081200000201', 'vehicle' => 'D 1234 AMH', 'mechanic' => 'MEK-02', 'service' => 'SRV-AKI', 'parts' => ['AKI-GTZ5S' => 1], 'days_ago' => 5],
            ];

            foreach ($orders as $order) {
                $completedAt = now()->subDays($order['days_ago']);
                $customerId = $customerIds[$order['customer']];
                $vehicleId = $vehicleIds[$order['vehicle']];
                $service = collect($services)->firstWhere('service_code', $order['service']);
                $total = (float) $service['price'];

                foreach ($order['parts'] as $sku => $quantity) {
                    $part = collect($parts)->firstWhere('sku', $sku);
                    $total += $part['price'] * $quantity;
                }

                DB::table('work_orders')->updateOrInsert(
                    ['work_order_number' => $order['number']],
                    [
                        'customer_id' => $customerId,
                        'vehicle_id' => $vehicleId,
                        'mechanic_id' => $employeeIds[$order['mechanic']],
                        'status' => 'completed',
                        'complaint' => 'Servis berkala dan pemeriksaan kendaraan.',
                        'diagnosis' => 'Kendaraan siap digunakan setelah servis.',
                        'odometer' => DB::table('vehicles')->where('id', $vehicleId)->value('odometer'),
                        'started_at' => $completedAt->copy()->subHour(),
                        'completed_at' => $completedAt,
                        'updated_at' => now(),
                        'created_at' => $completedAt,
                    ],
                );
                $workOrderId = DB::table('work_orders')
                    ->where('work_order_number', $order['number'])
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
                    'updated_at' => $completedAt,
                ]);

                DB::table('sales')->updateOrInsert(
                    ['invoice_number' => $order['invoice']],
                    [
                        'work_order_id' => $workOrderId,
                        'customer_id' => $customerId,
                        'cashier_id' => $employeeIds['SAMPLE-KSR-01'],
                        'status' => 'completed',
                        'subtotal' => $total,
                        'discount' => 0,
                        'tax' => 0,
                        'total' => $total,
                        'amount_paid' => $total,
                        'change_due' => 0,
                        'sold_at' => $completedAt,
                        'notes' => 'Transaksi contoh dari WorkshopSampleDataSeeder.',
                        'updated_at' => now(),
                        'created_at' => $completedAt,
                    ],
                );
                $saleId = DB::table('sales')->where('invoice_number', $order['invoice'])->value('id');
                DB::table('sale_items')->where('sale_id', $saleId)->delete();
                DB::table('payments')->where('sale_id', $saleId)->delete();

                DB::table('sale_items')->insert([
                    [
                        'sale_id' => $saleId,
                        'service_id' => $serviceIds[$service['service_code']],
                        'item_name' => $service['name'],
                        'sku' => $service['service_code'],
                        'quantity' => 1,
                        'unit_price' => $service['price'],
                        'discount' => 0,
                        'subtotal' => $service['price'],
                        'created_at' => $completedAt,
                        'updated_at' => $completedAt,
                    ],
                ]);

                foreach ($order['parts'] as $sku => $quantity) {
                    $part = collect($parts)->firstWhere('sku', $sku);
                    $partId = $partIds[$sku];
                    $subtotal = $part['price'] * $quantity;

                    DB::table('work_order_parts')->insert([
                        'work_order_id' => $workOrderId,
                        'spare_part_id' => $partId,
                        'sku' => $sku,
                        'part_name' => $part['name'],
                        'quantity' => $quantity,
                        'unit_price' => $part['price'],
                        'subtotal' => $subtotal,
                        'created_at' => $completedAt,
                        'updated_at' => $completedAt,
                    ]);
                    DB::table('sale_items')->insert([
                        'sale_id' => $saleId,
                        'spare_part_id' => $partId,
                        'item_name' => $part['name'],
                        'sku' => $sku,
                        'quantity' => $quantity,
                        'unit_price' => $part['price'],
                        'discount' => 0,
                        'subtotal' => $subtotal,
                        'created_at' => $completedAt,
                        'updated_at' => $completedAt,
                    ]);
                }

                DB::table('payments')->insert([
                    'sale_id' => $saleId,
                    'payment_method' => 'cash',
                    'amount' => $total,
                    'status' => 'completed',
                    'reference_number' => $order['invoice'],
                    'paid_at' => $completedAt,
                    'notes' => 'Pembayaran contoh.',
                    'created_at' => $completedAt,
                    'updated_at' => $completedAt,
                ]);
            }
        });
    }
}
