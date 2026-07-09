@csrf

@if ($method !== 'POST')
    @method($method)
@endif

@php($rentalPackage = $rentalPackage ?? null)

<div class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
        <div class="border-b border-neutral-200 px-5 py-4">
            <h2 class="text-base font-semibold text-neutral-950">Package Details</h2>
        </div>

        <div class="space-y-5 p-5">
            <div>
                <x-input-label for="name" value="Package Name" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $rentalPackage->name ?? '')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="duration_months" value="Duration Months" />
                <x-text-input id="duration_months" name="duration_months" type="number" min="1" class="mt-1 block w-full" :value="old('duration_months', $rentalPackage->duration_months ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('duration_months')" />
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $rentalPackage->description ?? '') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('description')" />
            </div>
        </div>
    </section>

    <aside class="space-y-4">
        <section class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-neutral-950">Includes</h2>
            <div class="mt-4 space-y-3">
                @foreach ([
                    'includes_driver' => 'Driver',
                    'includes_maintenance' => 'Maintenance',
                    'includes_insurance' => 'Insurance',
                ] as $field => $label)
                    <label class="flex items-start gap-3">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" class="mt-1 rounded border-neutral-300 text-neutral-950 shadow-sm focus:ring-neutral-800" @checked(old($field, $rentalPackage->{$field} ?? false))>
                        <span class="text-sm font-medium text-neutral-800">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-neutral-950">Availability</h2>
            <label class="mt-4 flex items-start gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="mt-1 rounded border-neutral-300 text-neutral-950 shadow-sm focus:ring-neutral-800" @checked(old('is_active', $rentalPackage->is_active ?? true))>
                <span>
                    <span class="block text-sm font-medium text-neutral-900">Active package</span>
                    <span class="mt-1 block text-sm text-neutral-500">Inactive packages cannot be selected in new quotations later.</span>
                </span>
            </label>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('rental-packages.index') }}" class="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-white">Cancel</a>
            <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">{{ $submitLabel }}</button>
        </div>
    </aside>
</div>
