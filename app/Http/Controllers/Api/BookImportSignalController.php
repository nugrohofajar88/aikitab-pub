<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookImportSignalController extends Controller
{
    /**
     * Records that a book-export file has been pushed to this server over
     * FTPS and is ready to be picked up by the `books:process-imports` cron
     * command. Deliberately does not touch the file itself or the Book
     * table — just logs the pending import so it stays fast regardless of
     * how large the underlying file is.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_local_id' => ['required', 'integer'],
            'filename' => ['required', 'string', 'max:255'],
            'request_uuid' => ['nullable', 'uuid'],
        ]);

        $import = BookImport::updateOrCreate(
            ['source_local_id' => $validated['source_local_id'], 'filename' => $validated['filename']],
            [
                'request_uuid' => $validated['request_uuid'] ?? null,
                'status' => 'pending',
                'error_message' => null,
                'processed_at' => null,
            ]
        );

        return response()->json([
            'import_id' => $import->id,
            'message' => 'Sinyal impor diterima, menunggu diproses.',
        ]);
    }
}
