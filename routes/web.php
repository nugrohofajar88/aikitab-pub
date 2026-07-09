<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookRequestController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/books');

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/by-source/{sourceLocalId}', [BookController::class, 'showBySource'])->name('books.show-by-source');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

Route::get('/request', [BookRequestController::class, 'create'])->name('requests.create');
Route::post('/request', [BookRequestController::class, 'store'])->name('requests.store');
Route::get('/request/{uuid}', [BookRequestController::class, 'status'])->name('requests.status');

Route::get('/status', [StatusController::class, 'index'])->name('status.index');
