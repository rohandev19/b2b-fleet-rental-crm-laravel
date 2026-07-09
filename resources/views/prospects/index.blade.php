<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Prospects</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Track company leads, sales owner, PIC readiness, and pipeline status.</p>
        </div>
    </x-slot>

    @php
        $statusClasses = [
            'new' => 'bg-sky-50 text-sky-700',
            'contacted' => 'bg-indigo-50 text-indigo-700',
            'meeting' => 'bg-violet-50 text-violet-700',
            'quotation' => 'bg-amber-50 text-amber-700',
            'negotiation' => 'bg-orange-50 text-orange-700',
            'won' => 'bg-emerald-50 text-emerald-700',
            'lost' => 'bg-red-50 text-red-700',
        ];
    @endphp

    <div class="space-y-5">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <form method="GET" action="{{ route('prospects.index') }}" class="grid gap-3 md:grid-cols-[minmax(220px,1fr)_160px_160px_180px_auto]">
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Search company, industry, city"
                    class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >

                <select name="status" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All status</option>
                    @foreach (\App\Models\Prospect::STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->headline() }}</option>
                    @endforeach
                </select>

                <select name="priority" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All priority</option>
                    @foreach (\App\Models\Prospect::PRIORITIES as $priority)
                        <option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ str($priority)->headline() }}</option>
                    @endforeach
                </select>

                <select name="assigned_sales_id" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All sales</option>
                    @foreach ($salesUsers as $sales)
                        <option value="{{ $sales->id }}" @selected((string) ($filters['assigned_sales_id'] ?? '') === (string) $sales->id)>{{ $sales->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="rounded-lg border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50">
                    Filter
                </button>
            </form>

            @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Sales))
                <a href="{{ route('prospects.create') }}" class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                    Add prospect
                </a>
            @endif
        </div>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Company</th>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Priority</th>
                            <th class="px-5 py-3 font-semibold">Sales</th>
                            <th class="px-5 py-3 font-semibold">PIC</th>
                            <th class="px-5 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($prospects as $prospect)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $prospect->company_name }}</div>
                                    <div class="mt-0.5 text-neutral-500">{{ $prospect->industry ?: 'No industry' }}{{ $prospect->city ? ' · '.$prospect->city : '' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', $statusClasses[$prospect->status] ?? 'bg-neutral-100 text-neutral-700'])>
                                        {{ str($prospect->status)->headline() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-semibold text-neutral-700">{{ str($prospect->priority)->headline() }}</span>
                                </td>
                                <td class="px-5 py-4 text-neutral-700">{{ $prospect->assignedSales?->name ?? 'Unassigned' }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $prospect->contacts_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('prospects.show', $prospect) }}" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">View</a>
                                        @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Sales))
                                            <a href="{{ route('prospects.edit', $prospect) }}" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">Edit</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="font-medium text-neutral-950">No prospects found.</div>
                                    <div class="mt-1 text-sm text-neutral-500">Start adding company prospects to build the sales pipeline.</div>
                                    @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Sales))
                                        <a href="{{ route('prospects.create') }}" class="mt-4 inline-flex rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white">Add prospect</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($prospects->hasPages())
                <div class="border-t border-neutral-200 px-5 py-4">
                    {{ $prospects->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
