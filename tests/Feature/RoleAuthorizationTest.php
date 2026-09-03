<?php

namespace Tests\Feature;

use App\Models\Gig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_browse_but_cannot_use_account_actions(): void
    {
        $seller = $this->user('seller', 'seller@example.com');
        $gig = $this->gig($seller);

        $this->get(route('gigs.marketplace'))->assertOk();
        $this->get(route('gigs.show', $gig))->assertOk();
        $this->get(route('sellers.profile', $seller))->assertOk();

        $this->get(route('gigs.create'))->assertRedirect(route('login'));
        $this->get(route('hire-requests.create', $gig))->assertRedirect(route('login'));
        $this->get(route('orders.index'))->assertRedirect(route('login'));
        $this->post(route('gigs.store'))->assertRedirect(route('login'));
        $this->post(route('hire-requests.store', $gig))->assertRedirect(route('login'));
        $this->post(route('wishlist.store', $gig))->assertRedirect(route('login'));
    }

    public function test_a_buyer_cannot_create_edit_or_delete_gigs(): void
    {
        $seller = $this->user('seller', 'seller@example.com');
        $buyer = $this->user('buyer', 'buyer@example.com');
        $gig = $this->gig($seller);

        $this->actingAs($buyer)->get(route('gigs.create'))->assertForbidden();
        $this->actingAs($buyer)->post(route('gigs.store'))->assertForbidden();
        $this->actingAs($buyer)->get(route('gigs.edit', $gig))->assertForbidden();
        $this->actingAs($buyer)->put(route('gigs.update', $gig))->assertForbidden();
        $this->actingAs($buyer)->delete(route('gigs.destroy', $gig))->assertForbidden();

        $this->assertDatabaseHas('gigs', ['id' => $gig->id]);
    }

    public function test_a_seller_can_manage_only_their_own_gigs(): void
    {
        $owner = $this->user('seller', 'owner@example.com');
        $otherSeller = $this->user('seller', 'other@example.com');
        $gig = $this->gig($owner);

        $this->actingAs($owner)->get(route('gigs.edit', $gig))->assertOk();
        $this->actingAs($otherSeller)->get(route('gigs.edit', $gig))->assertForbidden();
        $this->actingAs($otherSeller)->delete(route('gigs.destroy', $gig))->assertForbidden();

        $this->assertDatabaseHas('gigs', ['id' => $gig->id]);
    }

    public function test_only_a_buyer_can_open_the_hire_request_form(): void
    {
        $seller = $this->user('seller', 'seller@example.com');
        $buyer = $this->user('buyer', 'buyer@example.com');
        $gig = $this->gig($seller);

        $this->actingAs($buyer)->get(route('hire-requests.create', $gig))->assertOk();
        $this->actingAs($seller)->get(route('hire-requests.create', $gig))->assertForbidden();
    }

    private function user(string $role, string $email): User
    {
        return User::query()->create([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    private function gig(User $seller): Gig
    {
        return Gig::query()->create([
            'user_id' => $seller->id,
            'title' => 'Authorization Test Gig',
            'description' => 'A gig used to verify role and ownership protection.',
            'category' => 'Programming & Tech',
            'price' => 25,
            'delivery_time' => 3,
            'status' => 'active',
            'max_weekly_orders' => 5,
            'is_accepting_orders' => true,
        ]);
    }
}
