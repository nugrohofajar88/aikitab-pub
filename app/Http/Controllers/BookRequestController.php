<?php

namespace App\Http\Controllers;

use App\Models\BookRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookRequestController extends Controller
{
    public function create(): View
    {
        return view('requests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'requester_name' => ['nullable', 'string', 'max:255'],
            'requester_note' => ['nullable', 'string', 'max:1000'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $path = $request->file('pdf')->store('requests');

        $bookRequest = BookRequest::create([
            'title' => $validated['title'],
            'author' => $validated['author'] ?? null,
            'requester_name' => $validated['requester_name'] ?? null,
            'requester_note' => $validated['requester_note'] ?? null,
            'original_filename' => $request->file('pdf')->getClientOriginalName(),
            'file_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('requests.status', $bookRequest->uuid)
            ->with('status', 'Permintaan kamu sudah terkirim. Simpan link halaman ini untuk memantau statusnya.');
    }

    public function status(string $uuid): View
    {
        $bookRequest = BookRequest::where('uuid', $uuid)->firstOrFail();

        return view('requests.status', compact('bookRequest'));
    }
}
