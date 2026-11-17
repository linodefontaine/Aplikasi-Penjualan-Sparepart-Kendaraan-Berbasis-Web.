<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class LandingPageStatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_displays_zero_statistics_when_no_tenants_exist(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Data contoh')
            ->assertSee('Belum ada tenant terdaftar; statistik dimulai dari 0.')
            ->assertSeeTextInOrder([
                'Total Kendaraan',
                '0',
                'Transaksi Selesai',
                '0',
                'Sparepart Terjual',
                '0',
                'Pendapatan',
                'Rp 0',
            ])
            ->assertDontSee('Data aktual belum tersedia')
            ->assertViewHas('statistics', fn (array $statistics): bool => $statistics === [
                'totalVehicles' => 0,
                'completedTransactions' => 0,
                'sparePartsSold' => 0.0,
                'revenue' => 0.0,
                'tenantCount' => 0,
                'availableTenantCount' => 0,
                'unavailableTenantCount' => 0,
                'description' => 'Belum ada tenant terdaftar; statistik dimulai dari 0.',
            ]);
    }

    public function test_landing_page_displays_zero_statistics_for_an_empty_tenant_database(): void
    {
        $tenant = Tenant::create([
            'tenant_name' => 'Bengkel Tanpa Data',
            'package' => 'starter',
        ]);

        try {
            $this->get(route('home'))
                ->assertOk()
                ->assertSeeTextInOrder([
                    'Total Kendaraan',
                    '0',
                    'Transaksi Selesai',
                    '0',
                    'Sparepart Terjual',
                    '0',
                    'Pendapatan',
                    'Rp 0',
                ])
                ->assertSee('Akumulasi dari 1 tenant terdaftar.')
                ->assertViewHas('statistics', fn (array $statistics): bool => $statistics === [
                    'totalVehicles' => 0,
                    'completedTransactions' => 0,
                    'sparePartsSold' => 0.0,
                    'revenue' => 0.0,
                    'tenantCount' => 1,
                    'availableTenantCount' => 1,
                    'unavailableTenantCount' => 0,
                    'description' => 'Akumulasi dari 1 tenant terdaftar.',
                ]);
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }

            DB::purge('tenant');
            $tenant->delete();
        }
    }

    public function test_landing_page_aggregates_completed_transaction_statistics_across_tenants(): void
    {
        $firstTenant = Tenant::create([
            'tenant_name' => 'Bengkel Statistik Satu',
            'package' => 'starter',
        ]);
        $secondTenant = Tenant::create([
            'tenant_name' => 'Bengkel Statistik Dua',
            'package' => 'pro',
        ]);

        try {
            $this->seedTenantData($firstTenant, 2, [
                ['status' => 'completed', 'total' => 125000, 'sparePartsSold' => 3],
                ['status' => 'cancelled', 'total' => 75000, 'sparePartsSold' => 5],
            ]);
            $this->seedTenantData($secondTenant, 1, [
                ['status' => 'completed', 'total' => 200000, 'sparePartsSold' => 4],
                ['status' => 'pending', 'total' => 100000, 'sparePartsSold' => 100],
            ]);

            $this->get(route('home'))
                ->assertOk()
                ->assertSeeTextInOrder([
                    'Total Kendaraan',
                    '3',
                    'Transaksi Selesai',
                    '2',
                    'Sparepart Terjual',
                    '7',
                    'Pendapatan',
                    'Rp 325.000',
                ])
                ->assertSee('Akumulasi dari 2 tenant terdaftar.')
                ->assertDontSee('Data aktual belum tersedia')
                ->assertViewHas('statistics', fn (array $statistics): bool => $statistics === [
                    'totalVehicles' => 3,
                    'completedTransactions' => 2,
                    'sparePartsSold' => 7.0,
                    'revenue' => 325000.0,
                    'tenantCount' => 2,
                    'availableTenantCount' => 2,
                    'unavailableTenantCount' => 0,
                    'description' => 'Akumulasi dari 2 tenant terdaftar.',
                ]);
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }

            DB::purge('tenant');
            $firstTenant->delete();
            $secondTenant->delete();
        }
    }

    /**
     * @param  list<array{status: string, total: int, sparePartsSold: int}>  $sales
     */
    private function seedTenantData(Tenant $tenant, int $vehicleCount, array $sales): void
    {
        $tenantSlug = Str::slug($tenant->getAttribute('tenant_name'));

        $tenant->run(function () use ($tenantSlug, $vehicleCount, $sales): void {
            $now = now();
            $customerId = DB::table('customers')->insertGetId([
                'name' => 'Pelanggan '.$tenantSlug,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            for ($index = 1; $index <= $vehicleCount; $index++) {
                DB::table('vehicles')->insert([
                    'customer_id' => $customerId,
                    'license_plate' => strtoupper($tenantSlug.'-'.$index),
                    'brand' => 'Honda',
                    'model' => 'Beat',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $sparePartId = DB::table('spare_parts')->insertGetId([
                'sku' => strtoupper($tenantSlug.'-PART'),
                'name' => 'Kampas Rem '.$tenantSlug,
                'price' => 50000,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($sales as $index => $sale) {
                $saleId = DB::table('sales')->insertGetId([
                    'invoice_number' => strtoupper('INV-'.$tenantSlug.'-'.$index),
                    'status' => $sale['status'],
                    'total' => $sale['total'],
                    'sold_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('sale_items')->insert([
                    'sale_id' => $saleId,
                    'spare_part_id' => $sparePartId,
                    'item_name' => 'Kampas Rem '.$tenantSlug,
                    'sku' => strtoupper($tenantSlug.'-PART'),
                    'quantity' => $sale['sparePartsSold'],
                    'unit_price' => 50000,
                    'subtotal' => $sale['sparePartsSold'] * 50000,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }
}
