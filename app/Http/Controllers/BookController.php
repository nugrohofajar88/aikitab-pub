<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(): View
    {
        $books = Book::query()->latest('published_at')->get();

        return view('books.index', compact('books'));
    }

    public function show(Book $book): View
    {
        $book->load('pages.paragraphs');

        return view('books.show', compact('book'));
    }

    /**
     * The producer app doesn't know a book's id here until it's actually
     * synced (publishing is now async — see ProcessBookImports), so its
     * "lihat di situs publik" link points here by source_local_id instead.
     * 404s if the import hasn't landed yet; the producer UI already tells
     * the user to expect a short delay after publishing.
     */
    public function showBySource(int $sourceLocalId): RedirectResponse
    {
        $book = Book::where('source_local_id', $sourceLocalId)->firstOrFail();

        return redirect()->route('books.show', $book);
    }
}
