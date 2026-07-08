<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookImportProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookSyncController extends Controller
{
    /**
     * Full-replace sync: the local instance sends the complete finished
     * content for one book directly as a JSON body. Fine for small/medium
     * books; large ones are pushed via the file-based import instead (see
     * BookImportSignalController + ProcessBookImports) to avoid the request
     * body ever getting big enough to hit temp-file issues.
     */
    public function store(Request $request, BookImportProcessor $processor): JsonResponse
    {
        $validated = $processor->validate($request->all());
        $book = $processor->upsert($validated);

        return response()->json([
            'book_id' => $book->id,
            'message' => 'Kitab berhasil disinkronkan.',
        ]);
    }
}
