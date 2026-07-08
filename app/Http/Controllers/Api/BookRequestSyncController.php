<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookRequestSyncController extends Controller
{
    public function pending(): JsonResponse
    {
        $requests = BookRequest::where('status', 'pending')
            ->orderBy('created_at')
            ->get()
            ->map(fn (BookRequest $r) => [
                'id' => $r->id,
                'uuid' => $r->uuid,
                'title' => $r->title,
                'author' => $r->author,
                'requester_name' => $r->requester_name,
                'requester_note' => $r->requester_note,
                'original_filename' => $r->original_filename,
                'created_at' => $r->created_at,
            ]);

        return response()->json(['requests' => $requests]);
    }

    public function claim(BookRequest $bookRequest): JsonResponse
    {
        if ($bookRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Request ini sudah diklaim/diproses sebelumnya.',
                'status' => $bookRequest->status,
            ], 409);
        }

        $bookRequest->update([
            'status' => 'claimed',
            'claimed_at' => now(),
        ]);

        return response()->json(['message' => 'Request diklaim.', 'status' => $bookRequest->status]);
    }

    public function download(BookRequest $bookRequest): StreamedResponse
    {
        return Storage::disk('local')->response(
            $bookRequest->file_path,
            $bookRequest->original_filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Called when the local instance gives up on a claimed request (e.g. the
     * imported book failed to process and was deleted) so it doesn't stay
     * stuck showing "sedang diproses" to the visitor forever. Looked up by
     * uuid, not id — that's the only identifier the local Book keeps around.
     */
    public function reject(string $uuid): JsonResponse
    {
        $bookRequest = BookRequest::where('uuid', $uuid)->firstOrFail();
        $bookRequest->update(['status' => 'rejected']);

        return response()->json(['message' => 'Request ditandai gagal/ditolak.', 'status' => $bookRequest->status]);
    }
}
