<?php

namespace Tests\Feature;

use App\Models\Gig;
use App\Models\GigAddon;
use App\Models\GigTier;
use App\Models\HireRequest;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerAnalyticsAndServiceOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_dashboard_shows_order_and_earnings_analytics_and_exports_csv(): void
    {
        [$seller, $buyer, $gig] = $this->marketplaceUsersAndGig();

        Order::query()->create([
            'gig_id' => $gig->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'agreed_price' => 40,
            'status' => 'in_progress',
            'due_date' => now()->addDays(3),
        ]);
        Order::query()->create([
            'gig_id' => $gig->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'agreed_price' => 75,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->actingAs($seller)
            ->get(route('gigs.index'))
            ->assertOk()
            ->assertSee('Active Orders')
            ->assertSee('Completed')
            ->assertSee('Completion Rate')
            ->assertSee('$75.00')
            ->assertSee('Earnings CSV');

        $export = $this->actingAs($seller)->get(route('seller.analytics.export'));

        $export->assertOk();
        $this->assertStringContainsString(
            'attachment;',
            (string) $export->headers->get('content-disposition')
        );
        $this->assertStringContainsString('text/csv', (string) $export->headers->get('content-type'));
    }

    public function test_seller_can_create_a_gig_with_tiers_and_paid_addons(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $response = $this->actingAs($seller)->post(route('gigs.store'), [
            'title' => 'Laravel debugging service',
            'category' => 'Programming & Tech',
            'price' => 20,
            'delivery_time' => 4,
            'max_weekly_orders' => 5,
            'is_accepting_orders' => 1,
            'description' => 'I will diagnose and fix a Laravel application issue.',
            'tiers' => [
                'basic' => [
                    'enabled' => 1,
                    'title' => 'Quick Fix',
                    'description' => 'One focused bug fix.',
                    'price' => 25,
                    'delivery_time' => 3,
                    'revisions' => 1,
                ],
                'standard' => [
                    'enabled' => 1,
                    'title' => 'Full Debug',
                    'description' => 'Debugging plus regression checks.',
                    'price' => 50,
                    'delivery_time' => 5,
                    'revisions' => 2,
                ],
                'premium' => ['enabled' => 0],
            ],
            'addons' => [
                [
                    'name' => 'Rush delivery',
                    'description' => 'Move the request to the front of the queue.',
                    'price' => 15,
                    'extra_days' => 0,
                ],
            ],
        ]);

        $response->assertRedirect(route('gigs.index'));
        $gig = Gig::query()->where('user_id', $seller->id)->firstOrFail();

        $this->assertDatabaseCount('gig_tiers', 2);
        $this->assertDatabaseHas('gig_tiers', [
            'gig_id' => $gig->id,
            'name' => 'standard',
            'price' => 50,
        ]);
        $this->assertDatabaseHas('gig_addons', [
            'gig_id' => $gig->id,
            'name' => 'Rush delivery',
            'price' => 15,
        ]);
    }

    public function test_selected_package_and_addons_are_snapshotted_into_the_accepted_order(): void
    {
        [$seller, $buyer, $gig] = $this->marketplaceUsersAndGig();
        $tier = GigTier::query()->create([
            'gig_id' => $gig->id,
            'name' => 'standard',
            'title' => 'Standard Package',
            'description' => 'The standard scope.',
            'price' => 50,
            'delivery_time' => 5,
            'revisions' => 2,
            'sort_order' => 2,
        ]);
        $addon = GigAddon::query()->create([
            'gig_id' => $gig->id,
            'name' => 'Source files',
            'description' => 'Editable project files.',
            'price' => 10,
            'extra_days' => 0,
            'sort_order' => 1,
        ]);

        $this->actingAs($buyer)
            ->post(route('hire-requests.store', $gig), [
                'tier_id' => $tier->id,
                'addon_ids' => [$addon->id],
                'message' => 'Please complete this service with the source files.',
                'proposed_deadline' => now()->addDays(7)->format('Y-m-d'),
            ])
            ->assertRedirect(route('gigs.show', $gig));

        $hireRequest = HireRequest::query()->firstOrFail();
        $this->assertSame('60.00', $hireRequest->quoted_price);
        $this->assertSame('Standard Package', $hireRequest->selected_tier['title']);
        $this->assertSame('Source files', $hireRequest->selected_addons[0]['name']);

        $this->actingAs($seller)
            ->patch(route('hire-requests.accept', $hireRequest))
            ->assertRedirect();

        $order = Order::query()->where('hire_request_id', $hireRequest->id)->firstOrFail();
        $this->assertSame('60.00', $order->agreed_price);
        $this->assertSame('Standard Package', $order->selected_tier['title']);
        $this->assertSame('Source files', $order->selected_addons[0]['name']);
    }

    public function test_completed_order_uses_clickable_stars_instead_of_a_rating_dropdown(): void
    {
        [$seller, $buyer, $gig] = $this->marketplaceUsersAndGig();
        $order = Order::query()->create([
            'gig_id' => $gig->id,
            'seller_id' => $seller->id,
            'buyer_id' => $buyer->id,
            'agreed_price' => 30,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->actingAs($buyer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('class="star-rating"', false)
            ->assertSee('name="rating"', false)
            ->assertDontSee('Select 1-5 stars');

        $this->actingAs($buyer)
            ->post(route('orders.rating.store', $order), ['rating' => 4])
            ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseHas('ratings', [
            'order_id' => $order->id,
            'rating' => 4,
        ]);
    }

    private function marketplaceUsersAndGig(): array
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $gig = Gig::query()->create([
            'user_id' => $seller->id,
            'title' => 'Web application service',
            'description' => 'A complete student marketplace development service.',
            'price' => 30,
            'category' => 'Programming & Tech',
            'delivery_time' => 5,
            'status' => 'active',
            'max_weekly_orders' => 5,
            'is_accepting_orders' => true,
        ]);

        return [$seller, $buyer, $gig];
    }
}
