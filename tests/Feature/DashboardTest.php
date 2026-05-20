<?php

namespace Tests\Feature;

use App\Models\OnboardingSession;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_vendor_with_live_feed_can_visit_the_dashboard(): void
    {
        $vendor = Vendor::factory()->live()->create();
        $this->actingAs($vendor->user);

        $this->get(route('dashboard'))->assertOk();
    }

    public function test_authenticated_vendor_with_draft_feed_is_redirected_to_onboarding(): void
    {
        $vendor = Vendor::factory()->create(['status' => 'draft']);
        OnboardingSession::factory()->create(['vendor_id' => $vendor->id]);
        $this->actingAs($vendor->user);

        $this->get(route('dashboard'))->assertRedirect(route('onboarding'));
    }

    public function test_user_without_vendor_gets_404_on_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertNotFound();
    }
}
