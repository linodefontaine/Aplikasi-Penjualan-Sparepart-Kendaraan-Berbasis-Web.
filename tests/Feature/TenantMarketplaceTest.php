<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_order_from_a_tenant_catalog_without_immediate_stock_deduction(): void
    {
        $tenant = $this->createTenantForMainAccount('081298765431');
        $owner = auth()->user();
        $this->configureMarketplace($tenant);
        $partId = $this->createPart($tenant, 3);
        $customer = User::factory()->create([
            'role' => 'Pelanggan',
            'phone' => '081234567801',
        ]);

        try {
            $this->actingAs($customer)
                ->get(route('user.bengkel.katalog', ['id' => $tenant->getTenantKey()]))
                ->assertOk()
                ->assertSee('Kampas Rem Marketplace')
                ->assertSee('Tersedia 3');

            $response = $this->post(route('user.bengkel.pesanan.store', ['id' => $tenant->getTenantKey()]), [
                'items' => [
                    ['part_id' => $partId, 'quantity' => 2],
                ],
                'payment_method' => 'bank_transfer',
                'fulfillment_method' => 'shipping',
                'delivery_address' => 'Jl. Merdeka No. 10',
            ]);
            $response->assertRedirect();
            $invoice = basename((string) parse_url($response->headers->get('Location'), PHP_URL_PATH));

            $tenant->run(function () use ($customer, $partId, $invoice): void {
                $this->assertDatabaseHas('spare_parts', [
                    'id' => $partId,
                    'stock' => 3,
                ]);
                $this->assertDatabaseHas('sales', [
                    'invoice_number' => $invoice,
                    'status' => 'pending',
                    'total' => 155000,
                    'fulfillment_method' => 'shipping',
                    'delivery_address' => 'Jl. Merdeka No. 10',
                ]);
                $this->assertDatabaseHas('payments', [
                    'payment_method' => 'bank_transfer',
                    'amount' => 155000,
                    'status' => 'pending',
                ]);
                $this->assertDatabaseHas('customers', [
                    'central_user_id' => $customer->id,
                    'name' => $customer->name,
                ]);
            });

            $this->actingAs($owner)
                ->get(route('owner.kasir'))
                ->assertOk()
                ->assertSee($invoice)
                ->assertSee('Kampas Rem Marketplace')
                ->assertSee('Konfirmasi pembayaran');

            $this->actingAs($customer)->get(route('user.bengkel.pembayaran', [
                'id' => $tenant->getTenantKey(),
                'invoice' => $invoice,
            ]))
                ->assertOk()
                ->assertSee($invoice)
                ->assertSee('Transfer ke rekening marketplace.')
                ->assertSee('Website ini belum menggunakan payment gateway.');

            $otherCustomer = User::factory()->create(['role' => 'Pelanggan']);
            $this->actingAs($otherCustomer)
                ->get(route('user.bengkel.pembayaran', [
                    'id' => $tenant->getTenantKey(),
                    'invoice' => $invoice,
                ]))
                ->assertNotFound();

            $this->actingAs($customer)
                ->post(route('user.bengkel.pesanan.store', ['id' => $tenant->getTenantKey()]), [
                    'items' => [
                        ['part_id' => $partId, 'quantity' => 2],
                    ],
                    'payment_method' => 'bank_transfer',
                    'fulfillment_method' => 'pickup',
                ])
                ->assertSessionHasErrors('items');

            $this->actingAs($owner)
                ->post(route('owner.kasir.checkout'), [
                    'checkout_idempotency_key' => (string) Str::uuid(),
                    'parts' => [$partId],
                    'part_quantities' => [$partId => 2],
                    'payment_method' => 'cash',
                    'amount_paid' => 150000,
                ])
                ->assertSessionHasErrors('parts');
        } finally {
            tenancy()->end();
            DB::purge('tenant');
            $tenant->delete();
        }
    }

    public function test_owner_confirmation_deducts_stock_and_completes_the_payment(): void
    {
        $tenant = $this->createTenantForMainAccount('081298765432');
        $owner = auth()->user();
        $this->configureMarketplace($tenant);
        $partId = $this->createPart($tenant, 4);
        $customer = User::factory()->create(['role' => 'Pelanggan']);

        try {
            $response = $this->actingAs($customer)->post(
                route('user.bengkel.pesanan.store', ['id' => $tenant->getTenantKey()]),
                [
                    'items' => [
                        ['part_id' => $partId, 'quantity' => 2],
                    ],
                    'payment_method' => 'qris',
                    'fulfillment_method' => 'pickup',
                ],
            );
            $invoice = basename((string) parse_url($response->headers->get('Location'), PHP_URL_PATH));
            $saleId = $tenant->run(fn (): int => (int) DB::table('sales')
                ->where('invoice_number', $invoice)
                ->value('id'));

            $this->actingAs($owner)
                ->post(route('owner.kasir.pesanan.confirm', ['sale' => $saleId]))
                ->assertRedirect(route('owner.kasir'))
                ->assertSessionHas('success', 'Pembayaran dikonfirmasi dan stok telah diperbarui.');

            $tenant->run(function () use ($partId, $saleId): void {
                $this->assertDatabaseHas('spare_parts', [
                    'id' => $partId,
                    'stock' => 2,
                ]);
                $this->assertDatabaseHas('sales', [
                    'id' => $saleId,
                    'status' => 'completed',
                    'amount_paid' => 150000,
                ]);
                $this->assertDatabaseHas('payments', [
                    'sale_id' => $saleId,
                    'status' => 'completed',
                ]);
                $this->assertDatabaseHas('inventory_movements', [
                    'spare_part_id' => $partId,
                    'movement_type' => 'sale',
                    'quantity' => 2,
                    'reference_type' => 'sale',
                    'reference_id' => $saleId,
                ]);
            });

            $this->post(route('owner.kasir.pesanan.confirm', ['sale' => $saleId]))
                ->assertNotFound();
        } finally {
            tenancy()->end();
            DB::purge('tenant');
            $tenant->delete();
        }
    }

    public function test_confirmation_keeps_stock_and_payment_pending_when_physical_stock_is_insufficient(): void
    {
        $tenant = $this->createTenantForMainAccount('081298765433');
        $owner = auth()->user();
        $this->configureMarketplace($tenant);
        $partId = $this->createPart($tenant, 3);
        $customer = User::factory()->create(['role' => 'Pelanggan']);

        try {
            $response = $this->actingAs($customer)->post(
                route('user.bengkel.pesanan.store', ['id' => $tenant->getTenantKey()]),
                [
                    'items' => [
                        ['part_id' => $partId, 'quantity' => 2],
                    ],
                    'payment_method' => 'cash',
                    'fulfillment_method' => 'pickup',
                ],
            );
            $invoice = basename((string) parse_url($response->headers->get('Location'), PHP_URL_PATH));
            $saleId = $tenant->run(function () use ($invoice, $partId): int {
                DB::table('spare_parts')->where('id', $partId)->update(['stock' => 1]);

                return (int) DB::table('sales')->where('invoice_number', $invoice)->value('id');
            });
            $this->actingAs($owner)
                ->post(route('owner.kasir.pesanan.confirm', ['sale' => $saleId]))
                ->assertSessionHasErrors('payment');

            $tenant->run(function () use ($partId, $saleId): void {
                $this->assertDatabaseHas('spare_parts', [
                    'id' => $partId,
                    'stock' => 1,
                ]);
                $this->assertDatabaseHas('sales', [
                    'id' => $saleId,
                    'status' => 'pending',
                ]);
                $this->assertDatabaseHas('payments', [
                    'sale_id' => $saleId,
                    'status' => 'pending',
                ]);
                $this->assertDatabaseMissing('inventory_movements', [
                    'reference_type' => 'sale',
                    'reference_id' => $saleId,
                ]);
            });
        } finally {
            tenancy()->end();
            DB::purge('tenant');
            $tenant->delete();
        }
    }

    public function test_owner_can_save_marketplace_settings_but_cashier_cannot_edit_them(): void
    {
        $tenant = $this->createTenantForMainAccount('081298765434');
        $cashier = User::factory()->create([
            'role' => 'Kasir',
            'tenant_id' => $tenant->getTenantKey(),
        ]);

        try {
            $this->get(route('owner.settings'))->assertOk();
            $this->put(route('owner.settings.update'), [
                'address' => 'Jl. Bengkel No. 1',
                'payment_methods' => ['bank_transfer', 'qris'],
                'payment_instructions' => [
                    'bank_transfer' => 'Transfer ke rekening bengkel.',
                    'qris' => 'Bayar dengan QRIS yang ditampilkan di kasir.',
                ],
                'fulfillment_modes' => ['pickup', 'delivery'],
                'fulfillment_fees' => [
                    'delivery' => 10000,
                    'shipping' => 0,
                ],
            ])
                ->assertRedirect(route('owner.settings'))
                ->assertSessionHas('success');

            $savedTenant = Tenant::query()->findOrFail($tenant->getTenantKey());
            $this->assertSame('Jl. Bengkel No. 1', $savedTenant->getAttribute('address'));
            $this->assertSame(
                ['bank_transfer', 'qris'],
                array_column($savedTenant->getAttribute('payment_methods'), 'key'),
            );
            $this->assertSame(['pickup', 'delivery'], $savedTenant->getAttribute('fulfillment_modes'));
            $this->assertSame(10000, $savedTenant->getAttribute('fulfillment_fees')['delivery']);

            $this->put(route('owner.settings.update'), [
                'address' => 'Alamat yang tidak boleh tersimpan',
                'payment_methods' => ['qris'],
                'payment_instructions' => ['qris' => ' '],
                'fulfillment_modes' => ['pickup'],
            ])->assertSessionHasErrors('payment_instructions.qris');
            $this->assertSame('Jl. Bengkel No. 1', Tenant::query()
                ->findOrFail($tenant->getTenantKey())
                ->getAttribute('address'));

            $this->actingAs($cashier)
                ->get(route('owner.settings'))
                ->assertForbidden();
        } finally {
            tenancy()->end();
            DB::purge('tenant');
            $tenant->delete();
        }
    }

    private function configureMarketplace(Tenant $tenant): void
    {
        $tenant->setAttribute('address', 'Jl. Bengkel Marketplace No. 1');
        $tenant->setAttribute('payment_methods', [
            [
                'key' => 'bank_transfer',
                'label' => 'Transfer bank',
                'instructions' => 'Transfer ke rekening marketplace.',
            ],
            [
                'key' => 'qris',
                'label' => 'QRIS manual',
                'instructions' => 'Bayar dengan QRIS di bengkel.',
            ],
            [
                'key' => 'cash',
                'label' => 'Tunai saat ambil',
                'instructions' => 'Bayar tunai saat mengambil barang.',
            ],
        ]);
        $tenant->setAttribute('fulfillment_modes', ['pickup', 'shipping']);
        $tenant->setAttribute('fulfillment_fees', [
            'pickup' => 0,
            'delivery' => 10000,
            'shipping' => 5000,
        ]);
        $tenant->save();
    }

    private function createPart(Tenant $tenant, int $stock): int
    {
        return $tenant->run(fn (): int => DB::table('spare_parts')->insertGetId([
            'sku' => 'MARKETPLACE-PART-001',
            'name' => 'Kampas Rem Marketplace',
            'stock' => $stock,
            'price' => 75000,
            'category' => 'Rem',
            'unit' => 'set',
            'minimum_stock' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function createTenantForMainAccount(string $phone): Tenant
    {
        $digits = preg_replace('/\D+/', '', $phone);
        $suffix = substr($digits, -6);

        $this->post(route('register.proses'), [
            'username' => 'marketowner'.$suffix,
            'name' => 'Pemilik Marketplace '.$suffix,
            'contact' => $phone,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('user.menu'));

        $this->post(route('tenant.store'), [
            'tenant_name' => 'Bengkel Marketplace '.$suffix,
            'subdomain' => 'marketplace-'.$suffix,
            'package' => 'starter',
        ])->assertRedirect(route('owner.home'));

        return Tenant::query()->findOrFail(auth()->user()->tenant_id);
    }
}
