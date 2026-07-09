@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-neutral-900">Status &amp; Aktivitas</h1>
        <p class="mt-1 text-sm text-neutral-500">Log permintaan kitab dari pengunjung dan sinkronisasi data dari server pemrosesan.</p>
    </div>

    <div class="mb-8">
        <h2 class="mb-3 text-base font-semibold text-neutral-900">Permintaan Kitab</h2>

        @if ($requests->isEmpty())
            <p class="text-sm text-neutral-400">Belum ada permintaan kitab.</p>
        @else
            <div class="overflow-x-auto rounded-xl border border-neutral-200 bg-white">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 text-left text-xs uppercase tracking-wide text-neutral-400">
                            <th class="px-4 py-2 font-medium">Judul</th>
                            <th class="px-4 py-2 font-medium">Status</th>
                            <th class="px-4 py-2 font-medium">Diminta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requests as $req)
                            @php
                                $badge = match ($req->status) {
                                    'pending' => ['Menunggu diproses', 'bg-neutral-100 text-neutral-600'],
                                    'claimed', 'processing' => ['Sedang diproses', 'bg-amber-100 text-amber-700'],
                                    'completed' => ['Selesai', 'bg-emerald-100 text-emerald-700'],
                                    'rejected' => ['Ditolak', 'bg-red-100 text-red-700'],
                                    default => [$req->status, 'bg-neutral-100 text-neutral-600'],
                                };
                            @endphp
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('requests.status', $req->uuid) }}" class="font-medium text-neutral-800 hover:text-emerald-700 hover:underline">
                                        {{ $req->title }}
                                    </a>
                                    @if ($req->author)
                                        <span class="text-neutral-400">— {{ $req->author }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $badge[1] }}">{{ $badge[0] }}</span>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-neutral-400">{{ $req->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>
        <h2 class="mb-3 text-base font-semibold text-neutral-900">Sinkronisasi dari Server Pemrosesan</h2>

        @if ($imports->isEmpty())
            <p class="text-sm text-neutral-400">Belum ada aktivitas sinkronisasi.</p>
        @else
            <div class="overflow-x-auto rounded-xl border border-neutral-200 bg-white">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-neutral-200 text-left text-xs uppercase tracking-wide text-neutral-400">
                            <th class="px-4 py-2 font-medium">File</th>
                            <th class="px-4 py-2 font-medium">Status</th>
                            <th class="px-4 py-2 font-medium">Keterangan</th>
                            <th class="px-4 py-2 font-medium">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($imports as $import)
                            @php
                                $badge = match ($import->status) {
                                    'pending' => ['Menunggu', 'bg-neutral-100 text-neutral-600'],
                                    'processing' => ['Diproses', 'bg-amber-100 text-amber-700'],
                                    'done' => ['Selesai', 'bg-emerald-100 text-emerald-700'],
                                    'failed' => ['Gagal', 'bg-red-100 text-red-700'],
                                    default => [$import->status, 'bg-neutral-100 text-neutral-600'],
                                };
                            @endphp
                            <tr class="border-b border-neutral-100 last:border-0">
                                <td class="px-4 py-2.5 font-mono text-xs text-neutral-600">{{ $import->filename }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-medium {{ $badge[1] }}">{{ $badge[0] }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-xs text-neutral-500">
                                    {{ $import->status === 'failed' ? $import->error_message : '—' }}
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap text-xs text-neutral-400">{{ $import->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
