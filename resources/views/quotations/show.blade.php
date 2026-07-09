<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">{{ $quotation->quotation_number }}</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $quotation->prospect?->company_name }} · {{ str($quotation->status)->headline() }}</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="flex justify-between gap-3">
            <a href="{{ route('quotations.index') }}" class="text-sm font-medium text-neutral-600 hover:text-neutral-950">Back to quotations</a>

            <div class="flex flex-wrap justify-end gap-2">
                @if (auth()->user()->hasRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Sales) && in_array($quotation->status, ['draft', 'rejected', 'revised'], true))
                    <form method="POST" action="{{ route('quotations.submit', $quotation) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">Submit for approval</button>
                    </form>
                @endif

                @if (auth()->user()->hasRole(\App\Enums\UserRole::Manager) && $quotation->status === 'submitted')
                    <form method="POST" action="{{ route('quotations.approve', $quotation) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">Approve</button>
                    </form>
                @endif
            </div>
        </div>

        @if ($errors->has('status') || $errors->has('approved_by'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                {{ $errors->first('status') ?: $errors->first('approved_by') }}
            </div>
        @endif

        <section class="grid gap-4 md:grid-cols-4">
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Status</div>
                <div class="mt-3"><span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ str($quotation->status)->headline() }}</span></div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Quotation Date</div>
                <div class="mt-3 text-base font-semibold text-neutral-950">{{ $quotation->quotation_date->format('d M Y') }}</div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Valid Until</div>
                <div class="mt-3 text-base font-semibold text-neutral-950">{{ $quotation->valid_until->format('d M Y') }}</div>
            </div>
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-neutral-500">Grand Total</div>
                <div class="mt-3 text-xl font-semibold text-neutral-950">Rp{{ number_format((float) $quotation->grand_total, 0, ',', '.') }}</div>
            </div>
        </section>

        @if (auth()->user()->hasRole(\App\Enums\UserRole::Manager) && $quotation->status === 'submitted')
            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Reject Quotation</h2>
                </div>
                <form method="POST" action="{{ route('quotations.reject', $quotation) }}" class="space-y-4 p-5">
                    @csrf
                    <div>
                        <x-input-label for="rejection_reason" value="Rejection Reason" />
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('rejection_reason') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('rejection_reason')" />
                    </div>
                    <button type="submit" class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50">Reject quotation</button>
                </form>
            </section>
        @endif

        <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Customer</h2>
            </div>
            <dl class="grid gap-5 p-5 md:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Prospect</dt>
                    <dd class="mt-1 text-sm text-neutral-950">{{ $quotation->prospect?->company_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">PIC</dt>
                    <dd class="mt-1 text-sm text-neutral-950">{{ $quotation->contact?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-neutral-500">Sales</dt>
                    <dd class="mt-1 text-sm text-neutral-950">{{ $quotation->sales?->name }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 text-left text-sm">
                    <thead class="bg-neutral-50 text-xs uppercase tracking-normal text-neutral-500">
                        <tr>
                            <th class="px-5 py-3 font-semibold">Vehicle</th>
                            <th class="px-5 py-3 font-semibold">Package</th>
                            <th class="px-5 py-3 font-semibold">Qty</th>
                            <th class="px-5 py-3 font-semibold">Duration</th>
                            <th class="px-5 py-3 font-semibold">Monthly</th>
                            <th class="px-5 py-3 font-semibold">Discount</th>
                            <th class="px-5 py-3 text-right font-semibold">Line Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach ($quotation->items as $item)
                            <tr>
                                <td class="px-5 py-4 text-neutral-950">{{ $item->vehicle?->brand }} {{ $item->vehicle?->model }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $item->package?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $item->quantity }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ $item->duration_months }} months</td>
                                <td class="px-5 py-4 text-neutral-700">Rp{{ number_format((float) $item->monthly_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-neutral-700">{{ number_format((float) $item->discount_percent, 2) }}%</td>
                                <td class="px-5 py-4 text-right font-medium text-neutral-950">Rp{{ number_format((float) $item->line_total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-neutral-950">Terms</h2>
                <p class="mt-3 whitespace-pre-line text-sm text-neutral-700">{{ $quotation->terms_and_conditions ?: '-' }}</p>
                @if ($quotation->internal_notes)
                    <h3 class="mt-5 text-sm font-semibold text-neutral-950">Internal Notes</h3>
                    <p class="mt-2 whitespace-pre-line text-sm text-neutral-700">{{ $quotation->internal_notes }}</p>
                @endif
            </div>

            <div class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-neutral-500">Subtotal</span><span class="font-medium text-neutral-950">Rp{{ number_format((float) $quotation->subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500">Discount</span><span class="font-medium text-neutral-950">Rp{{ number_format((float) $quotation->discount_amount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between"><span class="text-neutral-500">Tax {{ number_format((float) $quotation->tax_percent, 2) }}%</span><span class="font-medium text-neutral-950">Rp{{ number_format((float) $quotation->tax_amount, 0, ',', '.') }}</span></div>
                    <div class="border-t border-neutral-200 pt-3">
                        <div class="flex justify-between text-base"><span class="font-semibold text-neutral-950">Grand Total</span><span class="font-semibold text-neutral-950">Rp{{ number_format((float) $quotation->grand_total, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Approval History</h2>
            </div>
            <div class="divide-y divide-neutral-100">
                @forelse ($quotation->approvals as $approval)
                    <div class="px-5 py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="font-medium text-neutral-950">{{ str($approval->action)->headline() }} by {{ $approval->user?->name ?? 'Unknown' }}</div>
                                <div class="mt-1 text-sm text-neutral-500">{{ str($approval->from_status ?: '-')->headline() }} to {{ str($approval->to_status)->headline() }} · {{ $approval->created_at->format('d M Y H:i') }}</div>
                                @if ($approval->reason)
                                    <p class="mt-2 whitespace-pre-line text-sm text-neutral-700">{{ $approval->reason }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-neutral-500">No approval activity yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
