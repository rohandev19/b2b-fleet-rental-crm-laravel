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
                <div class="grid gap-3 p-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($pipelineSnapshot as $stage)
                        <div class="rounded-lg border border-neutral-200 bg-neutral-50 p-4">
                            <div class="text-sm font-medium text-neutral-600">{{ $stage['stage'] }}</div>
                            <div class="mt-3 text-2xl font-semibold text-neutral-950">{{ $stage['count'] }}</div>
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

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Recent Quotations</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                        <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Quotation</th>
                                <th class="px-5 py-3 font-semibold">Customer</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                                <th class="px-5 py-3 text-right font-semibold">Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse ($recentQuotations as $quotation)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-neutral-950">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="hover:underline">{{ $quotation->quotation_number }}</a>
                                    </td>
                                    <td class="px-5 py-4 text-neutral-700">{{ $quotation->prospect?->company_name ?? '-' }}</td>
                                    <td class="px-5 py-4 text-neutral-700">{{ str($quotation->status)->headline() }}</td>
                                    <td class="px-5 py-4 text-right font-medium text-neutral-950">Rp{{ number_format((float) $quotation->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-neutral-500">No quotations yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Overdue Follow-ups</h2>
                </div>
                <div class="divide-y divide-neutral-100">
                    @forelse ($overdueFollowUps as $activity)
                        <div class="px-5 py-4">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="font-medium text-neutral-950">{{ $activity->prospect?->company_name ?? 'Unknown prospect' }}</div>
                                    <div class="mt-1 text-sm text-neutral-500">{{ $activity->summary }} - {{ $activity->user?->name ?? '-' }}</div>
                                </div>
                                <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">
                                    {{ $activity->next_follow_up_at?->format('d M Y') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-neutral-500">No overdue follow-ups.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
