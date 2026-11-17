<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_email_can_reset_password_with_a_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'old-password',
            'role' => 'Pelanggan',
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'));
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('AMAHAY')
            ->assertSee('Kirim tautan reset');
        $this->post(route('password.email'), ['email' => 'RESET@EXAMPLE.COM'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Jika alamat email terdaftar, tautan untuk mengatur ulang password akan dikirim.');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('AMAHAY')
            ->assertSee('Buat Password Baru');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'RESET@EXAMPLE.COM',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Password berhasil diperbarui. Silakan masuk.');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Password berhasil diperbarui. Silakan masuk.');
        $this->post(route('login.proses'), [
            'email' => 'RESET@EXAMPLE.COM',
            'password' => 'new-password',
            'role' => 'user',
        ])->assertRedirect(route('user.menu'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_phone_only_account_does_not_send_a_reset_link_to_its_placeholder_email(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'phone-08123456789@accounts.amahay.invalid',
        ]);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'Jika alamat email terdaftar, tautan untuk mengatur ulang password akan dikirim.');

        Notification::assertNothingSent();
    }
}
