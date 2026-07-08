<?php

namespace App\Http\Controllers;

use App\Models\Book;
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
}
