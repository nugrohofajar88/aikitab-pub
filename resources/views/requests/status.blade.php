@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="mb-1 text-xl font-bold text-neutral-900">Status Permintaan</h1>
        <p class="mb-6 text-sm text-neutral-500">Simpan link halaman ini untuk memantau statusnya kapan saja.</p>

        <div class="rounded-xl border border-neutral-200 bg-white p-5">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 class="font-medium text-neutral-900">{{ $bookRequest->title }}</h2>
                    @if ($bookRequest->author)
                        <p class="text-sm text-neutral-500">{{ $bookRequest->author }}</p>
                    @endif
                </div>

                @php
                    $badge = match ($bookRequest->status) {
                        'pending' => ['Menunggu diproses', 'bg-neutral-100 text-neutral-600'],
                        'claimed', 'processing' => ['Sedang diproses', 'bg-amber-100 text-amber-700'],
                        'completed' => ['Selesai', 'bg-emerald-100 text-emerald-700'],
                        'rejected' => ['Ditolak', 'bg-red-100 text-red-700'],
                        default => [$bookRequest->status, 'bg-neutral-100 text-neutral-600'],
                    };
                @endphp
                <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $badge[1] }}">
                    {{ $badge[0] }}
                </span>
            </div>

            @if ($bookRequest->status === 'pending')
                <p class="text-sm text-neutral-500">Permintaan kamu sudah masuk antrean, menunggu diambil untuk diproses.</p>
            @elseif (in_array($bookRequest->status, ['claimed', 'processing']))
                <p class="text-sm text-neutral-500">Kitab kamu sedang dalam proses pembacaan &amp; penerjemahan AI. Ini bisa memakan waktu, silakan cek lagi nanti.</p>
            @elseif ($bookRequest->status === 'completed' && $bookRequest->book)
                <p class="mb-3 text-sm text-neutral-500">Kitab kamu sudah selesai diproses dan siap dibaca.</p>
                <a href="{{ route('books.show', $bookRequest->book) }}"
                    class="inline-block rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Baca Kitab &rarr;
                </a>
            @elseif ($bookRequest->status === 'rejected')
                <p class="text-sm text-neutral-500">Maaf, permintaan ini tidak bisa diproses.</p>
            @endif

            <p class="mt-4 text-xs text-neutral-400">Diminta pada {{ $bookRequest->created_at->format('d M Y H:i') }}</p>
        </div>
    </div>
@endsection
