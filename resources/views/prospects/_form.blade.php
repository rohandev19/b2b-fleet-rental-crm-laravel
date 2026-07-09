@csrf

@if ($method !== 'POST')
    @method($method)
@endif

@php
    $prospect = $prospect ?? null;
    $canAssign = auth()->user()->hasRole(\App\Enums\UserRole::Admin);
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_360px]">
    <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
        <div class="border-b border-neutral-200 px-5 py-4">
            <h2 class="text-base font-semibold text-neutral-950">Company Prospect</h2>
        </div>

        <div class="grid gap-5 p-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <x-input-label for="company_name" value="Company Name" />
                <x-text-input id="company_name" name="company_name" type="text" class="mt-1 block w-full" :value="old('company_name', $prospect->company_name ?? '')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('company_name')" />
            </div>

            <div>
                <x-input-label for="industry" value="Industry" />
                <x-text-input id="industry" name="industry" type="text" class="mt-1 block w-full" :value="old('industry', $prospect->industry ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('industry')" />
            </div>

            <div>
                <x-input-label for="company_size" value="Company Size" />
                <select id="company_size" name="company_size" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach (\App\Models\Prospect::COMPANY_SIZES as $size)
                        <option value="{{ $size }}" @selected(old('company_size', $prospect->company_size ?? 'medium') === $size)>{{ str($size)->headline() }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('company_size')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="address" value="Address" />
                <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $prospect->address ?? '') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div>
                <x-input-label for="city" value="City" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $prospect->city ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>

            <div>
                <x-input-label for="province" value="Province" />
                <x-text-input id="province" name="province" type="text" class="mt-1 block w-full" :value="old('province', $prospect->province ?? '')" />
                <x-input-error class="mt-2" :messages="$errors->get('province')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="website" value="Website" />
                <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website', $prospect->website ?? '')" placeholder="https://example.com" />
                <x-input-error class="mt-2" :messages="$errors->get('website')" />
            </div>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Pipeline</h2>
            </div>

            <div class="space-y-5 p-5">
                <div>
                    <x-input-label for="source" value="Source" />
                    <select id="source" name="source" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (\App\Models\Prospect::SOURCES as $source)
                            <option value="{{ $source }}" @selected(old('source', $prospect->source ?? 'manual') === $source)>{{ str($source)->replace('_', ' ')->headline() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('source')" />
                </div>

                <div>
                    <x-input-label for="status" value="Status" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (\App\Models\Prospect::STATUSES as $status)
                            @if ($status !== 'won')
                                <option value="{{ $status }}" @selected(old('status', $prospect->status ?? 'new') === $status)>{{ str($status)->headline() }}</option>
                            @endif
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('status')" />
                </div>

                <div>
                    <x-input-label for="priority" value="Priority" />
                    <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (\App\Models\Prospect::PRIORITIES as $priority)
                            <option value="{{ $priority }}" @selected(old('priority', $prospect->priority ?? 'medium') === $priority)>{{ str($priority)->headline() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('priority')" />
                </div>

                <div>
                    <x-input-label for="estimated_vehicle_need" value="Estimated Vehicle Need" />
                    <x-text-input id="estimated_vehicle_need" name="estimated_vehicle_need" type="number" min="0" class="mt-1 block w-full" :value="old('estimated_vehicle_need', $prospect->estimated_vehicle_need ?? '')" />
                    <x-input-error class="mt-2" :messages="$errors->get('estimated_vehicle_need')" />
                </div>

                <div>
                    <x-input-label for="next_follow_up_at" value="Next Follow-up" />
                    <x-text-input id="next_follow_up_at" name="next_follow_up_at" type="datetime-local" class="mt-1 block w-full" :value="old('next_follow_up_at', optional($prospect?->next_follow_up_at)->format('Y-m-d\\TH:i'))" />
                    <x-input-error class="mt-2" :messages="$errors->get('next_follow_up_at')" />
                </div>

                <div>
                    <x-input-label for="lost_reason" value="Lost Reason" />
                    <textarea id="lost_reason" name="lost_reason" rows="3" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('lost_reason', $prospect->lost_reason ?? '') }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('lost_reason')" />
                </div>

                @if ($canAssign)
                    <div>
                        <x-input-label for="assigned_sales_id" value="Assigned Sales" />
                        <select id="assigned_sales_id" name="assigned_sales_id" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Unassigned</option>
                            @foreach ($salesUsers as $sales)
                                <option value="{{ $sales->id }}" @selected((string) old('assigned_sales_id', $prospect->assigned_sales_id ?? '') === (string) $sales->id)>{{ $sales->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('assigned_sales_id')" />
                    </div>
                @else
                    <input type="hidden" name="assigned_sales_id" value="{{ old('assigned_sales_id', $prospect->assigned_sales_id ?? auth()->id()) }}">
                @endif
            </div>
        </section>

        <section class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
            <x-input-label for="notes" value="Notes" />
            <textarea id="notes" name="notes" rows="5" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $prospect->notes ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ $prospect ? route('prospects.show', $prospect) : route('prospects.index') }}" class="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-white">
                Cancel
            </a>
            <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                {{ $submitLabel }}
            </button>
        </div>
    </aside>
</div>
