@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-lg">
        <h1 class="mb-1 text-xl font-bold text-neutral-900">Minta Kitab Baru</h1>
        <p class="mb-6 text-sm text-neutral-500">
            Tidak menemukan kitab yang kamu cari? Upload PDF-nya di sini, nanti akan diproses dan ditambahkan ke daftar.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data" class="space-y-4 rounded-xl border border-neutral-200 bg-white p-5">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Judul Kitab</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Penulis (opsional)</label>
                <input type="text" name="author" value="{{ old('author') }}"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">File PDF</label>
                <input type="file" name="pdf" accept="application/pdf" required
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-emerald-700">
                <p class="mt-1 text-xs text-neutral-400">Maks. 50MB.</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Nama kamu (opsional)</label>
                <input type="text" name="requester_name" value="{{ old('requester_name') }}"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-neutral-700">Catatan (opsional)</label>
                <textarea name="requester_note" rows="2"
                    class="w-full rounded-lg border border-neutral-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">{{ old('requester_note') }}</textarea>
            </div>
            <button type="submit"
                class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Kirim Permintaan
            </button>
        </form>
    </div>
@endsection
