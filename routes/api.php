<?php

use App\Http\Controllers\Api\BookImportSignalController;
use App\Http\Controllers\Api\BookRequestSyncController;
use App\Http\Controllers\Api\BookSyncController;
use Illuminate\Support\Facades\Route;

// Called only by the local (producer) KitabAI instance — see VerifySyncToken.
Route::middleware('sync.token')->prefix('sync')->group(function () {
    Route::post('/books', [BookSyncController::class, 'store'])->name('api.sync.books.store');
    Route::post('/books/import-signal', [BookImportSignalController::class, 'store'])->name('api.sync.books.import-signal');

    Route::get('/requests/pending', [BookRequestSyncController::class, 'pending'])->name('api.sync.requests.pending');
    Route::post('/requests/{bookRequest}/claim', [BookRequestSyncController::class, 'claim'])->name('api.sync.requests.claim');
    Route::get('/requests/{bookRequest}/download', [BookRequestSyncController::class, 'download'])->name('api.sync.requests.download');
    Route::post('/requests/by-uuid/{uuid}/reject', [BookRequestSyncController::class, 'reject'])->name('api.sync.requests.reject');
});
