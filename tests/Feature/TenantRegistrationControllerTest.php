<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Stancl\Tenancy\Events\TenantCreated;
use Tests\TestCase;

class TenantRegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public static function packagePrices(): array
    {
        return [
            'starter' => ['starter', 149000],
            'pro' => ['pro', 249000],
            'enterprise' => ['enterprise', 499000],
        ];
    }

    public function test_main_account_registration_does_not_create_a_tenant(): void
    {
        $this->post(route('register.proses'), [
            'username' => 'akunutama',
            'name' => 'Pengguna Utama',
            'contact' => 'utama@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('user.menu'));

        $this->assertAuthenticated();
        $this->assertDatabaseCount('tenants', 0);
        $this->assertDatabaseHas('users', [
            'username' => 'akunutama',
            'role' => 'Pelanggan',
            'tenant_id' => null,
        ]);
        $this->get(route('user.menu'))
            ->assertOk()
            ->assertSee(route('tenant.create'));
    }

    public function test_authenticated_main_account_can_create_a_tenant_and_becomes_its_owner(): void
    {
        Event::fake([TenantCreated::class]);
        $customer = User::factory()->create([
            'name' => 'Pemilik Akun',
            'phone' => '08123456789',
            'role' => 'Pelanggan',
            'password' => 'password123',
        ]);
        $this->actingAs($customer);

        $this->get(route('tenant.create'))
            ->assertOk()
            ->assertSee('Daftarkan Tenant Bengkel')
            ->assertSee('Pemilik Akun')
            ->assertSeeInOrder([
                'Starter Tenant — Rp 149rb / bln',
                'Pro Tenant — Rp 249rb / bln',
                'Enterprise — Rp 499rb / bln',
            ])
            ->assertSee('Paket dibayar per bulan dan aktif setelah pembayaran dikonfirmasi.')
            ->assertSee('Maksimal 2 karyawan/mekanik aktif.')
            ->assertSee('Maksimal 10 karyawan/mekanik aktif.')
            ->assertSee('Karyawan/mekanik aktif tanpa batas.');
        $registrationResponse = $this->post(route('tenant.store'), [
            'tenant_name' => 'Bengkel Utama',
            'subdomain' => 'bengkel-utama',
            'package' => 'starter',
        ]);
        $invoice = basename((string) parse_url($registrationResponse->headers->get('Location'), PHP_URL_PATH));
        $registrationResponse->assertRedirect(route('tenant.checkout.show', ['invoice' => $invoice]));
        $this->get(route('tenant.checkout.show', ['invoice' => $invoice]))
            ->assertOk()
            ->assertSee('Bengkel Utama')
            ->assertSee('Starter Tenant')
            ->assertSee('Rp 149.000')
            ->assertSee($invoice)
            ->assertSee('QRIS')
            ->assertSee('Virtual Account');

        $tenant = Tenant::query()->firstOrFail();
        $this->assertSame('starter', $tenant->getAttribute('package'));
        $this->assertSame('pending_payment', $tenant->getAttribute('status'));
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'role' => 'Owner',
            'tenant_id' => $tenant->getTenantKey(),
        ]);
        $this->assertDatabaseHas('domains', [
            'domain' => 'bengkel-utama.yuma.yume.tech',
            'tenant_id' => $tenant->getTenantKey(),
        ]);
        $this->get(route('tenant.create'))
            ->assertRedirect(route('tenant.checkout.show', ['invoice' => $invoice]));
        $this->post(route('tenant.checkout.simulate', ['invoice' => $invoice]), [
            'payment_method' => 'qris',
        ])->assertRedirect(route('owner.home'));
        $this->assertDatabaseHas('subscription_invoices', [
            'invoice_number' => $invoice,
            'payment_method' => 'qris',
            'status' => 'paid',
            'amount' => 149000,
        ]);
        $this->assertTrue($tenant->fresh()->isActive());
        $this->assertAuthenticatedAs($customer->fresh());
        $this->get(route('tenant.create'))->assertRedirect(route('owner.home'));
        $this->post(route('tenant.store'), [
            'tenant_name' => 'Bengkel Kedua',
            'subdomain' => 'bengkel-kedua',
            'package' => 'starter',
        ])->assertRedirect(route('owner.home'));
        $this->assertDatabaseCount('tenants', 1);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->post(route('login.proses'), [
            'email' => $customer->email,
            'password' => 'password123',
            'role' => 'user',
        ])->assertRedirect(route('user.menu'));
        $this->assertAuthenticatedAs($customer->fresh());
        Event::assertDispatched(TenantCreated::class);
    }

    public function test_guest_cannot_open_or_submit_tenant_creation(): void
    {
        $this->get(route('tenant.create'))->assertRedirect(route('login'));
        $this->post(route('tenant.store'), [
            'tenant_name' => 'Bengkel Tamu',
            'subdomain' => 'bengkel-tamu',
            'package' => 'starter',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('tenants', 0);
    }

    public function test_tenant_domain_uses_the_current_host_when_no_base_domain_is_configured(): void
    {
        config(['tenancy.base_domain' => null]);
        Event::fake([TenantCreated::class]);
        $customer = User::factory()->create(['role' => 'Pelanggan']);

        $response = $this->actingAs($customer)->post(route('tenant.store'), [
            'tenant_name' => 'Bengkel Host',
            'subdomain' => 'bengkel-host',
            'package' => 'starter',
        ]);
        $invoice = basename((string) parse_url($response->headers->get('Location'), PHP_URL_PATH));

        $response->assertRedirect(route('tenant.checkout.show', ['invoice' => $invoice]));
        $this->assertDatabaseHas('domains', [
            'domain' => 'bengkel-host.yuma.yume.tech',
            'tenant_id' => $customer->fresh()->tenant_id,
        ]);
    }

    #[DataProvider('packagePrices')]
    public function test_registration_creates_an_invoice_for_the_selected_package(string $package, int $amount): void
    {
        Event::fake([TenantCreated::class]);
        $customer = User::factory()->create(['role' => 'Pelanggan']);

        $response = $this->actingAs($customer)->post(route('tenant.store'), [
            'tenant_name' => 'Bengkel Paket '.ucfirst($package),
            'subdomain' => 'bengkel-'.$package,
            'package' => $package,
        ]);
        $invoice = basename((string) parse_url($response->headers->get('Location'), PHP_URL_PATH));

        $response->assertRedirect(route('tenant.checkout.show', ['invoice' => $invoice]));
        $this->assertDatabaseHas('subscription_invoices', [
            'invoice_number' => $invoice,
            'package' => $package,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    public function test_existing_tenant_owner_is_redirected_to_the_dashboard_instead_of_creating_another_tenant(): void
    {
        Event::fake([TenantCreated::class]);
        $tenant = Tenant::create([
            'tenant_name' => 'Toko Sudah Ada',
            'package' => 'starter',
        ]);
        $owner = User::factory()->create([
            'role' => 'Owner',
            'tenant_id' => $tenant->getTenantKey(),
        ]);

        $this->actingAs($owner);
        $this->get(route('tenant.create'))->assertRedirect(route('owner.home'));
        $this->post(route('tenant.store'), [
            'tenant_name' => 'Bengkel Kedua',
            'subdomain' => 'bengkel-kedua',
            'package' => 'starter',
        ])
            ->assertRedirect(route('owner.home'));

        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'role' => 'Owner',
            'tenant_id' => $tenant->getTenantKey(),
        ]);
    }

    public function test_duplicate_subdomain_does_not_promote_the_main_account(): void
    {
        Event::fake([TenantCreated::class]);
        $existingTenant = Tenant::create([
            'tenant_name' => 'Tenant Awal',
            'package' => 'starter',
        ]);
        $existingTenant->createDomain('bengkel-terpakai.yuma.yume.tech');
        $customer = User::factory()->create(['role' => 'Pelanggan']);

        $this->actingAs($customer)
            ->post(route('tenant.store'), [
                'tenant_name' => 'Tenant Baru',
                'subdomain' => 'bengkel-terpakai',
                'package' => 'starter',
            ])
            ->assertSessionHasErrors('subdomain');

        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'role' => 'Pelanggan',
            'tenant_id' => null,
        ]);
    }
}
