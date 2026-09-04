<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Serve project uploads without relying on a Windows public/storage link.
     */
    public function show(Request $request): StreamedResponse
    {
        $path = ltrim(str_replace('\\', '/', (string) $request->query('path')), '/');

        abort_if($path === '' || str_contains($path, '..'), 404);

        $allowedDirectory = collect(['gigs/', 'portfolio/', 'deliverables/'])
            ->contains(fn (string $directory): bool => str_starts_with($path, $directory));

        abort_unless($allowedDirectory && Storage::disk('public')->exists($path), 404);

        if (str_starts_with($path, 'deliverables/')) {
            $deliverable = Deliverable::query()
                ->with('order')
                ->where('file_path', $path)
                ->firstOrFail();

            abort_unless(
                Auth::check()
                    && in_array(
                        (int) Auth::id(),
                        [(int) $deliverable->order->seller_id, (int) $deliverable->order->buyer_id],
                        true
                    ),
                403,
                'You may open only deliverables from your own orders.'
            );
        }

        return Storage::disk('public')->response(
            $path,
            null,
            ['Cache-Control' => 'public, max-age=3600']
        );
    }
}
