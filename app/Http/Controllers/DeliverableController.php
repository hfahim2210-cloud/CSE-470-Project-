<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeliverableController extends Controller
{
    /**
     * Feature 1: seller submits a final deliverable as a file, a link, or both.
     */
    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->ensureSellerMaySubmit($order);

        $validated = $request->validate([
            'deliverable_file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,zip,png,jpg,jpeg,txt',
                'max:10240',
                'required_without:submission_link',
            ],
            'submission_link' => [
                'nullable',
                'url:http,https',
                'max:1000',
                'required_without:deliverable_file',
            ],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $validated, $order): void {
            $oldDeliverable = $order->deliverable;
            $newFilePath = $oldDeliverable?->file_path;
            $newFileName = $oldDeliverable?->file_name;

            if ($request->hasFile('deliverable_file')) {
                if ($oldDeliverable?->file_path
                    && Storage::disk('public')->exists($oldDeliverable->file_path)) {
                    Storage::disk('public')->delete($oldDeliverable->file_path);
                }

                $uploadedFile = $request->file('deliverable_file');
                $newFilePath = $uploadedFile->store('deliverables', 'public');
                $newFileName = $uploadedFile->getClientOriginalName();
            }

            $order->deliverable()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'file_name' => $newFileName,
                    'file_path' => $newFilePath,
                    'submission_link' => $validated['submission_link'] ?? null,
                    'note' => $validated['note'] ?? null,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'approved_at' => null,
                ]
            );

            $order->update([
                'status' => 'under_review',
                'completed_at' => null,
            ]);
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Final deliverable submitted successfully.');
    }

    /**
     * Feature 2: buyer approves the submitted deliverable.
     */
    public function approve(Order $order): RedirectResponse
    {
        $this->ensureBuyerMayApprove($order);

        DB::transaction(function () use ($order): void {
            $order->deliverable->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Deliverable approved. The order is now completed.');
    }

    private function ensureSellerMaySubmit(Order $order): void
    {
        // Once authentication is merged, only the actual seller may submit.
        if (Auth::check() && Auth::id() !== $order->seller_id) {
            abort(403, 'Only the seller assigned to this order can submit work.');
        }

        if ($order->status === 'completed') {
            throw ValidationException::withMessages([
                'deliverable_file' => 'A completed order cannot receive another deliverable.',
            ]);
        }

        if (! in_array($order->status, ['in_progress', 'revision_requested', 'under_review'], true)) {
            throw ValidationException::withMessages([
                'deliverable_file' => 'This order is not ready for final delivery.',
            ]);
        }
    }

    private function ensureBuyerMayApprove(Order $order): void
    {
        // Once authentication is merged, only the actual buyer may approve.
        if (Auth::check() && Auth::id() !== $order->buyer_id) {
            abort(403, 'Only the buyer assigned to this order can approve work.');
        }

        $order->loadMissing('deliverable');

        if (! $order->deliverable) {
            throw ValidationException::withMessages([
                'approval' => 'No deliverable has been submitted yet.',
            ]);
        }

        if ($order->status !== 'under_review'
            || $order->deliverable->status !== 'submitted') {
            throw ValidationException::withMessages([
                'approval' => 'Only a submitted deliverable under review can be approved.',
            ]);
        }
    }
}
