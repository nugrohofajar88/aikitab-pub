@extends('layouts.app')

@section('content')
    <style>
        @media print {
            @page { margin: 10mm; }
            body { font-size: 12px; }
        }
    </style>

    <div x-data="bookViewer({
        processedPages: {{ $book->pages->pluck('page_number')->values()->toJson() }},
    })" x-init="init()">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-neutral-900">{{ $book->title }}</h1>
            @if ($book->author)
                <p class="text-sm text-neutral-500">{{ $book->author }}</p>
            @endif
        </div>

        @if ($book->pages->isNotEmpty())
            <div class="mb-6 flex flex-wrap gap-2 print:hidden">
                <button type="button" @click="mode = 'arab'"
                    :class="mode === 'arab' ? 'bg-emerald-600 text-white' : 'bg-white text-neutral-600 border border-neutral-300'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium">Arab</button>
                <button type="button" @click="mode = 'perkata'"
                    :class="mode === 'perkata' ? 'bg-emerald-600 text-white' : 'bg-white text-neutral-600 border border-neutral-300'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium">Arab + Terjemah Per Kata</button>
                <button type="button" @click="mode = 'kalimat'"
                    :class="mode === 'kalimat' ? 'bg-emerald-600 text-white' : 'bg-white text-neutral-600 border border-neutral-300'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium">Arab + Terjemah Kalimat</button>
                <button type="button" @click="mode = 'lengkap'"
                    :class="mode === 'lengkap' ? 'bg-emerald-600 text-white' : 'bg-white text-neutral-600 border border-neutral-300'"
                    class="rounded-full px-4 py-1.5 text-sm font-medium">Lengkap</button>
            </div>

            <div class="mb-4 flex items-center justify-between rounded-xl border border-neutral-200 bg-white px-4 py-2 print:hidden">
                <button type="button" @click="prevPage()" :disabled="!hasPrev()"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium text-neutral-600 hover:bg-neutral-100 disabled:opacity-30 disabled:hover:bg-transparent">
                    &larr; Sebelumnya
                </button>
                <span class="text-sm font-medium text-neutral-700">Halaman <span x-text="currentPage"></span></span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="window.print()" title="Print halaman ini"
                        class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-neutral-600 hover:bg-neutral-100">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                            <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0 1 18 8.653v4.097A2.25 2.25 0 0 1 15.75 15h-.241l.305 3.05a.75.75 0 0 1-.746.826H4.932a.75.75 0 0 1-.746-.826L4.491 15H4.25A2.25 2.25 0 0 1 2 12.75V8.653c0-1.082.784-2.005 1.874-2.198.374-.056.75-.107 1.126-.153V2.75Zm8.5 3.19V2.75a.25.25 0 0 0-.25-.25h-6.5a.25.25 0 0 0-.25.25v3.19a41.703 41.703 0 0 1 7 0ZM6.006 15l-.29 2.9h8.568l-.29-2.9H6.006Z" clip-rule="evenodd" />
                        </svg>
                        Print
                    </button>
                    <button type="button" @click="nextPage()" :disabled="!hasNext()"
                        class="rounded-lg px-3 py-1.5 text-sm font-medium text-neutral-600 hover:bg-neutral-100 disabled:opacity-30 disabled:hover:bg-transparent">
                        Berikutnya &rarr;
                    </button>
                </div>
            </div>

            <div class="space-y-8">
                @foreach ($book->pages as $page)
                    <div x-show="currentPage === {{ $page->page_number }}" id="page-{{ $page->page_number }}" class="rounded-xl border border-neutral-200 bg-white p-6 scroll-mt-4 print:rounded-none print:border-0 print:p-0">
                        <p class="mb-4 text-xs font-medium uppercase tracking-wide text-neutral-400 print:mb-2">
                            Halaman {{ $page->page_number }}
                        </p>

                        <div class="space-y-6 print:space-y-2">
                            @foreach ($page->paragraphs as $paragraph)
                                @foreach (data_get($paragraph->content_json, 'sentences', []) as $sentence)
                                    @php
                                        $kalimatCopyText = trim(($sentence['arabic'] ?? '')."\n\n".($sentence['translation'] ?? ''));
                                    @endphp
                                    <div class="mb-5 border-b border-neutral-100 pb-5 last:mb-0 last:border-0 last:pb-0 print:mb-2 print:border-0 print:pb-2">
                                        <template x-if="mode === 'arab' || mode === 'kalimat'">
                                            <p class="font-arabic text-right text-3xl leading-loose print:text-xl print:leading-snug" dir="rtl">
                                                {{ $sentence['arabic'] ?? '' }}
                                            </p>
                                        </template>

                                        <template x-if="mode === 'perkata' || mode === 'lengkap'">
                                            <div class="flex flex-wrap items-start gap-x-4 gap-y-3 print:gap-x-2 print:gap-y-1" dir="rtl">
                                                @foreach (data_get($sentence, 'words', []) as $word)
                                                    <div class="group relative flex flex-col items-center text-center">
                                                        <span @class(['font-arabic text-2xl leading-loose print:text-base print:leading-snug', 'cursor-help' => !empty($word['grammar'])])>{{ $word['arabic'] ?? '' }}</span>
                                                        <span class="mt-1 text-xs text-neutral-500" dir="ltr">{{ $word['translation'] ?? '' }}</span>

                                                        @if (!empty($word['grammar']))
                                                            <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 hidden w-52 -translate-x-1/2 rounded-lg bg-neutral-800 px-3 py-2 text-xs leading-relaxed text-white group-hover:block" dir="rtl">
                                                                {{ $word['grammar'] }}
                                                                <span class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-neutral-800"></span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </template>

                                        <template x-if="mode === 'kalimat' || mode === 'lengkap'">
                                            <div>
                                                <p class="mt-3 text-sm italic text-neutral-600 print:mt-1 print:text-xs">
                                                    {{ $sentence['translation'] ?? '' }}
                                                </p>
                                                <div x-data="{ copied: false }" class="mt-1 flex justify-end print:hidden" dir="ltr">
                                                    <button type="button" title="Salin"
                                                        @click="navigator.clipboard.writeText(@js($kalimatCopyText)); copied = true; setTimeout(() => copied = false, 1500)"
                                                        class="rounded-md p-1.5 text-neutral-400 hover:bg-neutral-100 hover:text-neutral-600">
                                                        @include('books.partials.copy-icon')
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-neutral-400">Kitab ini belum punya konten.</p>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function bookViewer({ processedPages }) {
        return {
            mode: 'lengkap',
            processedPages: processedPages ?? [],
            currentPage: (processedPages && processedPages.length) ? processedPages[0] : null,
            init() {},
            goToPage(n) {
                this.currentPage = n;
                this.$nextTick(() => {
                    document.getElementById('page-' + n)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            },
            hasPrev() {
                return this.processedPages.indexOf(this.currentPage) > 0;
            },
            hasNext() {
                return this.processedPages.indexOf(this.currentPage) < this.processedPages.length - 1;
            },
            prevPage() {
                const i = this.processedPages.indexOf(this.currentPage);
                if (i > 0) this.goToPage(this.processedPages[i - 1]);
            },
            nextPage() {
                const i = this.processedPages.indexOf(this.currentPage);
                if (i < this.processedPages.length - 1) this.goToPage(this.processedPages[i + 1]);
            },
        };
    }
</script>
@endpush
