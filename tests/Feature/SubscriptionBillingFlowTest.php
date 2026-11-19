<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;
use Tests\TestCase;

class SubscriptionBillingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_simulate_payment_and_view_an_active_subscription_and_invoice_history(): void
    {
        $this->travelTo(new \DateTimeImmutable('2026-11-18T10:00:00+00:00'));
        $subscription = $this->createPendingSubscription('SUB-FLOW-001');
        $this->actingAs($subscription['owner']);

        $this->get(route('tenant.checkout.show', ['invoice' => $subscription['invoice']]))
            ->assertOk()
            ->assertSee('Bengkel Tagihan')
            ->assertSee('Starter Tenant')
            ->assertSee('Rp 149.000')
            ->assertSee('SUB-FLOW-001')
            ->assertSee('QRIS')
            ->assertSee('Virtual Account');
        $this->post(route('tenant.checkout.simulate', ['invoice' => $subscription['invoice']]), [
            'payment_method' => 'virtual_account',
        ])->assertRedirect(route('owner.home'));
        $this->post(route('tenant.checkout.simulate', ['invoice' => $subscription['invoice']]), [
            'payment_method' => 'qris',
        ])->assertRedirect(route('owner.home'));

        $this->assertDatabaseHas('subscription_invoices', [
            'invoice_number' => 'SUB-FLOW-001',
            'payment_method' => 'virtual_account',
            'status' => 'paid',
            'amount' => 149000,
            'subscription_starts_at' => '2026-11-18 17:00:00',
            'subscription_ends_at' => '2026-12-18 17:00:00',
        ]);
        $this->assertSame('active', $subscription['tenant']->fresh()->getAttribute('status'));
        $this->assertSame('2026-12-18', CarbonImmutable::parse(
            $subscription['tenant']->fresh()->getAttribute('subscription_ends_at'),
        )->toDateString());

        $this->get(route('owner.subscription'))
            ->assertOk()
            ->assertSee('Langganan & Tagihan')
            ->assertSee('Starter Tenant')
            ->assertSee('Berlaku sampai')
            ->assertSee('SUB-FLOW-001')
            ->assertSee('Lunas')
            ->assertSee('Virtual Account');
    }

    public function test_invalid_payment_method_does_not_pay_or_activate_the_tenant(): void
    {
        $subscription = $this->createPendingSubscription('SUB-INVALID-001');

        $this->actingAs($subscription['owner'])
            ->from(route('tenant.checkout.show', ['invoice' => $subscription['invoice']]))
            ->post(route('tenant.checkout.simulate', ['invoice' => $subscription['invoice']]), [
                'payment_method' => 'bank-transfer',
            ])
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseHas('subscription_invoices', [
            'invoice_number' => 'SUB-INVALID-001',
            'payment_method' => null,
            'status' => 'pending',
        ]);
        $this->assertSame('pending_payment', $subscription['tenant']->fresh()->getAttribute('status'));
    }

    public function test_subscription_invoice_is_hidden_from_another_owner_with_a_404(): void
    {
        $subscription = $this->createPendingSubscription('SUB-PRIVATE-001');
        $otherSubscription = $this->createPendingSubscription('SUB-PRIVATE-002', 'Bengkel Lain');

        $this->actingAs($otherSubscription['owner'])
            ->get(route('tenant.checkout.show', ['invoice' => $subscription['invoice']]))
            ->assertNotFound();
        $this->post(route('tenant.checkout.simulate', ['invoice' => $subscription['invoice']]), [
            'payment_method' => 'qris',
        ])->assertNotFound();

        $this->assertDatabaseHas('subscription_invoices', [
            'invoice_number' => 'SUB-PRIVATE-001',
            'status' => 'pending',
        ]);
        $this->assertSame('pending_payment', $subscription['tenant']->fresh()->getAttribute('status'));
    }

    public function test_guests_and_non_owners_cannot_confirm_subscription_payment(): void
    {
        $subscription = $this->createPendingSubscription('SUB-AUTH-001');

        $this->post(route('tenant.checkout.simulate', ['invoice' => $subscription['invoice']]), [
            'payment_method' => 'qris',
        ])->assertRedirect(route('login'));

        $customer = User::factory()->create(['role' => 'Pelanggan']);
        $this->actingAs($customer)
            ->post(route('tenant.checkout.simulate', ['invoice' => $subscription['invoice']]), [
                'payment_method' => 'qris',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('subscription_invoices', [
            'invoice_number' => 'SUB-AUTH-001',
            'status' => 'pending',
        ]);
    }

    /**
     * @return array{tenant: Tenant, owner: User, invoice: string}
     */
    private function createPendingSubscription(string $invoice, string $tenantName = 'Bengkel Tagihan'): array
    {
        Event::fake([TenantCreated::class]);
        $tenant = Tenant::create([
            'tenant_name' => $tenantName,
            'package' => 'starter',
            'status' => 'pending_payment',
        ]);
        $owner = User::factory()->create([
            'role' => 'Owner',
            'tenant_id' => $tenant->getTenantKey(),
        ]);
        DB::table('subscription_invoices')->insert([
            'tenant_id' => $tenant->getTenantKey(),
            'invoice_number' => $invoice,
            'package' => 'starter',
            'package_name' => 'Starter Tenant',
            'amount' => 149000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'tenant' => $tenant,
            'owner' => $owner,
            'invoice' => $invoice,
        ];
    }
}
