<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookSyncController extends Controller
{
    /**
     * Full-replace sync: the local instance sends the complete finished
     * content for one book. We upsert by source_local_id and just delete +
     * recreate its pages/paragraphs — much simpler than diffing, and this
     * endpoint is only ever called with fully-processed content anyway.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source_local_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'total_pages' => ['required', 'integer', 'min:0'],
            'request_uuid' => ['nullable', 'uuid'],
            // "present" not "required" — a book where every paragraph failed
            // to process legitimately has zero pages worth publishing.
            'pages' => ['present', 'array'],
            'pages.*.page_number' => ['required', 'integer', 'min:1'],
            'pages.*.paragraphs' => ['required', 'array'],
            'pages.*.paragraphs.*.paragraph_number' => ['required', 'integer', 'min:1'],
            'pages.*.paragraphs.*.harakat_text' => ['nullable', 'string'],
            'pages.*.paragraphs.*.content_json' => ['required', 'array'],
        ]);

        $book = DB::transaction(function () use ($validated) {
            $book = Book::updateOrCreate(
                ['source_local_id' => $validated['source_local_id']],
                [
                    'title' => $validated['title'],
                    'author' => $validated['author'] ?? null,
                    'total_pages' => $validated['total_pages'],
                    'published_at' => now(),
                ]
            );

            // Full replace — simplest correct sync, avoids diffing logic.
            $book->paragraphs()->delete();
            $book->pages()->delete();

            foreach ($validated['pages'] as $pageData) {
                $page = $book->pages()->create([
                    'page_number' => $pageData['page_number'],
                ]);

                foreach ($pageData['paragraphs'] as $paragraphData) {
                    $page->paragraphs()->create([
                        'book_id' => $book->id,
                        'paragraph_number' => $paragraphData['paragraph_number'],
                        'harakat_text' => $paragraphData['harakat_text'] ?? null,
                        'content_json' => $paragraphData['content_json'],
                    ]);
                }
            }

            if (! empty($validated['request_uuid'])) {
                BookRequest::where('uuid', $validated['request_uuid'])->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'book_id' => $book->id,
                ]);
            }

            return $book;
        });

        return response()->json([
            'book_id' => $book->id,
            'message' => 'Kitab berhasil disinkronkan.',
        ]);
    }
}
