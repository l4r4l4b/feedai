<?php

use App\Models\User;
use App\Models\Vendor;
use App\Services\StripeConnect;
use Mockery\MockInterface;
use Stripe\AccountLink;

it('syncs account status on return and redirects to settings', function () {
    $user = User::factory()->vendor()->create();
    Vendor::factory()->for($user)->create(['stripe_account_id' => 'acct_test_x']);

    $this->mock(StripeConnect::class, function (MockInterface $mock) {
        $mock->shouldReceive('syncAccountStatus')->once();
    });

    $this->actingAs($user)
        ->get('/dashboard/payments/stripe/return')
        ->assertRedirect(route('dashboard.payments.settings'));
});

it('regenerates the onboarding link on refresh and redirects to Stripe', function () {
    $user = User::factory()->vendor()->create();
    Vendor::factory()->for($user)->create(['stripe_account_id' => 'acct_test_y']);

    $link = AccountLink::constructFrom([
        'object' => 'account_link',
        'url' => 'https://connect.stripe.com/setup/test_refresh',
        'expires_at' => time() + 300,
    ]);

    $this->mock(StripeConnect::class, function (MockInterface $mock) use ($link) {
        $mock->shouldReceive('createOnboardingLink')->once()->andReturn($link);
    });

    $this->actingAs($user)
        ->get('/dashboard/payments/stripe/refresh')
        ->assertRedirect('https://connect.stripe.com/setup/test_refresh');
});

it('blocks guests from both stripe handlers', function () {
    $this->get('/dashboard/payments/stripe/return')->assertRedirect('/login');
    $this->get('/dashboard/payments/stripe/refresh')->assertRedirect('/login');
});
