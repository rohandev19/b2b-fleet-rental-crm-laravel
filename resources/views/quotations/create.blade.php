<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Create Quotation Draft</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Build a draft with backend-calculated totals.</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('quotations.store') }}" x-data="{ rows: [0], next: 1 }" class="space-y-6">
        @csrf

        <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
                <div class="border-b border-neutral-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-neutral-950">Quotation Information</h2>
                </div>

                <div class="grid gap-5 p-5 md:grid-cols-2">
                    <div>
                        <x-input-label for="prospect_id" value="Prospect" />
                        <select id="prospect_id" name="prospect_id" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select prospect</option>
                            @foreach ($prospects as $prospect)
                                <option value="{{ $prospect->id }}" @selected((string) old('prospect_id') === (string) $prospect->id)>{{ $prospect->company_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('prospect_id')" />
                    </div>

                    <div>
                        <x-input-label for="contact_id" value="PIC" />
                        <select id="contact_id" name="contact_id" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">No specific PIC</option>
                            @foreach ($contacts as $contact)
                                <option value="{{ $contact->id }}" @selected((string) old('contact_id') === (string) $contact->id)>{{ $contact->name }} · {{ $contact->prospect?->company_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('contact_id')" />
                    </div>

                    <div>
                        <x-input-label for="quotation_date" value="Quotation Date" />
                        <x-text-input id="quotation_date" name="quotation_date" type="date" class="mt-1 block w-full" :value="old('quotation_date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('quotation_date')" />
                    </div>

                    <div>
                        <x-input-label for="valid_until" value="Valid Until" />
                        <x-text-input id="valid_until" name="valid_until" type="date" class="mt-1 block w-full" :value="old('valid_until', now()->addDays(14)->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('valid_until')" />
                    </div>

                    <div>
                        <x-input-label for="discount_amount" value="Quotation Discount" />
                        <x-text-input id="discount_amount" name="discount_amount" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('discount_amount', 0)" />
                        <x-input-error class="mt-2" :messages="$errors->get('discount_amount')" />
                    </div>

                    <div>
                        <x-input-label for="tax_percent" value="Tax Percent" />
                        <x-text-input id="tax_percent" name="tax_percent" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" :value="old('tax_percent', 11)" />
                        <x-input-error class="mt-2" :messages="$errors->get('tax_percent')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="terms_and_conditions" value="Terms and Conditions" />
                        <textarea id="terms_and_conditions" name="terms_and_conditions" rows="4" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('terms_and_conditions', "Prices are valid until the date stated above.\nFinal contract terms may be adjusted after customer confirmation.") }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('terms_and_conditions')" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="internal_notes" value="Internal Notes" />
                        <textarea id="internal_notes" name="internal_notes" rows="3" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('internal_notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('internal_notes')" />
                    </div>
                </div>
            </section>

            <aside class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-neutral-950">Draft Rules</h2>
                <div class="mt-4 space-y-3 text-sm text-neutral-600">
                    <p>Quotation number is generated automatically.</p>
                    <p>Subtotal, tax, and grand total are recalculated on the server when saved.</p>
                    <p>Valid until must be at least 7 days after quotation date.</p>
                </div>
            </aside>
        </div>

        <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Quotation Items</h2>
                <button type="button" class="rounded-lg border border-neutral-200 px-3 py-1.5 text-sm font-medium text-neutral-700 transition hover:bg-neutral-50" @click="rows.push(next++)">Add item</button>
            </div>

            <div class="space-y-4 p-5">
                <template x-for="row in rows" :key="row">
                    <div class="rounded-lg border border-neutral-200 p-4">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
                            <div class="xl:col-span-2">
                                <x-input-label value="Vehicle" />
                                <select :name="`items[${row}][vehicle_id]`" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select vehicle</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->brand }} {{ $vehicle->model }} · Rp{{ number_format((float) $vehicle->base_monthly_price, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="xl:col-span-2">
                                <x-input-label value="Package" />
                                <select :name="`items[${row}][package_id]`" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">No package</option>
                                    @foreach ($rentalPackages as $package)
                                        <option value="{{ $package->id }}">{{ $package->name }} · {{ $package->duration_months }} months</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <x-input-label value="Qty" />
                                <input :name="`items[${row}][quantity]`" type="number" min="1" value="1" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <x-input-label value="Duration" />
                                <input :name="`items[${row}][duration_months]`" type="number" min="1" value="12" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div class="xl:col-span-2">
                                <x-input-label value="Monthly Price" />
                                <input :name="`items[${row}][monthly_price]`" type="number" min="1" step="0.01" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <x-input-label value="Discount %" />
                                <input :name="`items[${row}][discount_percent]`" type="number" min="0" max="100" step="0.01" value="0" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div class="flex items-end">
                                <button type="button" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50" @click="rows = rows.filter((value) => value !== row)" :disabled="rows.length === 1">Remove</button>
                            </div>
                        </div>
                    </div>
                </template>

                <x-input-error class="mt-2" :messages="$errors->get('items')" />
                @foreach ($errors->get('items.*') as $messages)
                    <x-input-error class="mt-2" :messages="$messages" />
                @endforeach
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('quotations.index') }}" class="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-white">Cancel</a>
            <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">Save draft</button>
        </div>
    </form>
</x-app-layout>
