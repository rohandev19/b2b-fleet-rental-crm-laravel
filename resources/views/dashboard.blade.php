<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Dashboard</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $role->label() }} workspace</p>
        </div>
    </x-slot>

    @php
        $toneClasses = [
            'sky' => 'border-sky-200 bg-sky-50 text-sky-700',
            'emerald' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'amber' => 'border-amber-200 bg-amber-50 text-amber-700',
            'slate' => 'border-slate-200 bg-slate-50 text-slate-700',
        ];
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($metrics as $metric)
                <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-neutral-500">{{ $metric['label'] }}</p>
                        <span @class(['rounded-full border px-2 py-0.5 text-xs font-semibold', $toneClasses[$metric['tone']]])>
                            {{ $metric['trend'] }}
                        </span>
                    </div>
                    <div class="mt-4 text-3xl font-semibold tracking-normal text-neutral-950">{{ $metric['value'] }}</div>
                </div>
            @endforeach
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Pipeline Snapshot</h2>
                </div>
                <div class="grid gap-3 p-5 md:grid-cols-4">
                    @foreach (['New', 'Contacted', 'Quotation', 'Won'] as $stage)
                        <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                            <div class="text-sm font-medium text-neutral-600">{{ $stage }}</div>
                            <div class="mt-3 text-2xl font-semibold text-neutral-950">0</div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Work Queue</h2>
                </div>
                <div class="divide-y divide-neutral-100">
                    @foreach ($workQueue as $item)
                        <div class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="min-w-0 text-sm font-medium text-neutral-800">{{ $item['title'] }}</div>
                            <span class="shrink-0 rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-600">{{ $item['status'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
