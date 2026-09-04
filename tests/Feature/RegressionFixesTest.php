<?php

namespace Tests\Feature;

use App\Models\Gig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegressionFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_seller_cannot_open_the_hire_form_for_their_own_gig(): void
    {
        $seller = User::query()->create([
            'name' => 'Test Seller',
            'email' => 'seller@example.com',
            'password' => 'password123',
            'role' => 'seller',
        ]);

        $gig = Gig::query()->create([
            'user_id' => $seller->id,
            'title' => 'Own Gig',
            'description' => 'A test gig owned by the signed-in seller.',
            'category' => 'Programming & Tech',
            'price' => 20,
            'delivery_time' => 3,
            'status' => 'active',
            'max_weekly_orders' => 5,
            'is_accepting_orders' => true,
        ]);

        $this->actingAs($seller)
            ->get(route('hire-requests.create', $gig))
            ->assertForbidden();
    }

    public function test_uploaded_public_media_can_be_served_without_a_storage_link(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gigs/cover.jpg', 'test-image');

        $this->get(route('media.show', ['path' => 'gigs/cover.jpg']))
            ->assertOk();
    }

    public function test_media_route_rejects_files_outside_approved_upload_directories(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('private/secret.txt', 'not-public');

        $this->get(route('media.show', ['path' => 'private/secret.txt']))
            ->assertNotFound();
    }

    public function test_pagination_uses_compact_bootstrap_links_without_svg_arrows(): void
    {
        $paginator = new LengthAwarePaginator(
            range(1, 10),
            20,
            10,
            1,
            ['path' => route('gigs.marketplace')]
        );

        $html = $paginator->links()->toHtml();

        $this->assertStringContainsString('Previous', $html);
        $this->assertStringContainsString('Next', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }
}
