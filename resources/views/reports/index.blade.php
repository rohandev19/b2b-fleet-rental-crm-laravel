<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Reports</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Pipeline, quotation, and sales performance</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <section class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('reports.index') }}" class="grid gap-4 lg:grid-cols-[1fr_1fr_auto_auto] lg:items-end">
                <div>
                    <x-input-label for="start_date" value="Start Date" />
                    <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="$startDate->toDateString()" />
                </div>
                <div>
                    <x-input-label for="end_date" value="End Date" />
                    <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="$endDate->toDateString()" />
                </div>
                <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-neutral-800">Apply</button>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('reports.exports.prospects', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]) }}" class="rounded-lg border border-neutral-300 px-4 py-2.5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-50">
                        Export Prospects
                    </a>
                    <a href="{{ route('reports.exports.quotations', ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()]) }}" class="rounded-lg border border-neutral-300 px-4 py-2.5 text-sm font-semibold text-neutral-700 transition hover:bg-neutral-50">
                        Export Quotations
                    </a>
                </div>
            </form>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">New Prospects</div>
                <div class="mt-4 text-3xl font-semibold text-neutral-950">{{ $summary['new_prospects'] }}</div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Quotations</div>
                <div class="mt-4 text-3xl font-semibold text-neutral-950">{{ $summary['quotations'] }}</div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Approved Value</div>
                <div class="mt-4 text-3xl font-semibold text-neutral-950">{{ $summary['approved_value'] }}</div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Follow-ups</div>
                <div class="mt-4 text-3xl font-semibold text-neutral-950">{{ $summary['follow_ups'] }}</div>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Prospect Funnel</h2>
                </div>
                <div class="grid gap-3 p-5 sm:grid-cols-2">
                    @foreach ($prospectFunnel as $stage)
                        <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-3">
                            <span class="text-sm font-medium text-neutral-700">{{ $stage['label'] }}</span>
                            <span class="text-lg font-semibold text-neutral-950">{{ $stage['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Follow-up Outcomes</h2>
                </div>
                <div class="grid gap-3 p-5 sm:grid-cols-2">
                    @foreach ($followUpOutcomes as $outcome)
                        <div class="flex items-center justify-between rounded-lg border border-neutral-200 bg-neutral-50 px-4 py-3">
                            <span class="text-sm font-medium text-neutral-700">{{ $outcome['label'] }}</span>
                            <span class="text-lg font-semibold text-neutral-950">{{ $outcome['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Quotation Status</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Status</th>
                            <th class="px-5 py-3 font-semibold">Count</th>
                            <th class="px-5 py-3 text-right font-semibold">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($quotationStatus as $status)
                            <tr>
                                <td class="px-5 py-4 font-medium text-neutral-950">{{ $status['label'] }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $status['count'] }}</td>
                                <td class="px-5 py-4 text-right font-medium text-neutral-950">{{ $status['value'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Sales Performance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Sales</th>
                            <th class="px-5 py-3 font-semibold">Prospects</th>
                            <th class="px-5 py-3 font-semibold">Quotations</th>
                            <th class="px-5 py-3 text-right font-semibold">Approved Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse ($salesPerformance as $sales)
                            <tr>
                                <td class="px-5 py-4 font-medium text-neutral-950">{{ $sales['name'] }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $sales['prospects'] }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $sales['quotations'] }}</td>
                                <td class="px-5 py-4 text-right font-medium text-neutral-950">{{ $sales['approved_value'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-neutral-500">No sales users yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
