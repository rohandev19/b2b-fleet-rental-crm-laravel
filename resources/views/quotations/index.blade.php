<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Quotations</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Create and track fleet rental quotation drafts.</p>
        </div>
    </x-slot>

    <div class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="GET" action="{{ route('quotations.index') }}" class="grid gap-3 sm:grid-cols-[minmax(240px,1fr)_160px_auto]">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search quotation or prospect" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select name="status" class="rounded-lg border-neutral-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All status</option>
                    @foreach (\App\Models\Quotation::STATUSES as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str($status)->headline() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border border-neutral-200 bg-white px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50">Filter</button>
            </form>

            @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Sales))
                <a href="{{ route('quotations.create') }}" class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                    Create quotation
                </a>
            @endif
        </div>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Quotation</th>
                            <th class="px-5 py-3 font-semibold">Prospect</th>
                            <th class="px-5 py-3 font-semibold">Sales</th>
                            <th class="px-5 py-3 font-semibold">Valid Until</th>
                            <th class="px-5 py-3 font-semibold">Total</th>
                            <th class="px-5 py-3 text-right font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($quotations as $quotation)
                            <tr class="hover:bg-neutral-50">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-neutral-950">{{ $quotation->quotation_number }}</div>
                                    <div class="mt-0.5">
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ str($quotation->status)->headline() }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-neutral-700">{{ $quotation->prospect?->company_name ?? '-' }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $quotation->sales?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $quotation->valid_until->format('d M Y') }}</td>
                                <td class="px-5 py-4 font-medium text-neutral-950">Rp{{ number_format((float) $quotation->grand_total, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('quotations.show', $quotation) }}" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-white">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <div class="font-medium text-neutral-950">No quotations found.</div>
                                    <div class="mt-1 text-sm text-neutral-500">Create a draft quotation from an active prospect.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($quotations->hasPages())
                <div class="border-t border-neutral-200 px-5 py-4">
                    {{ $quotations->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
