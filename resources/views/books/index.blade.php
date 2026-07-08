@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-neutral-900">Daftar Kitab</h1>
        <a href="{{ route('requests.create') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800">
            Kitab yang dicari belum ada? Minta di sini &rarr;
        </a>
    </div>

    @if ($books->isEmpty())
        <div class="rounded-xl border border-neutral-200 bg-white p-8 text-center">
            <p class="text-sm text-neutral-500">Belum ada kitab yang dipublikasikan.</p>
            <a href="{{ route('requests.create') }}" class="mt-3 inline-block text-sm font-medium text-emerald-700 hover:text-emerald-800">
                Minta kitab pertama &rarr;
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($books as $book)
                <a href="{{ route('books.show', $book) }}"
                    class="block rounded-xl border border-neutral-200 bg-white p-4 hover:border-emerald-300">
                    <h3 class="font-medium text-neutral-900">{{ $book->title }}</h3>
                    @if ($book->author)
                        <p class="text-sm text-neutral-500">{{ $book->author }}</p>
                    @endif
                    <p class="mt-1 text-xs text-neutral-400">{{ $book->total_pages }} halaman</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
