@csrf

@if ($method !== 'POST')
    @method($method)
@endif

@php($vehicle = $vehicle ?? null)

<div class="grid gap-6 lg:grid-cols-[1fr_320px]">
    <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
        <div class="border-b border-neutral-200 px-5 py-4">
            <h2 class="text-base font-semibold text-neutral-950">Vehicle Details</h2>
        </div>

        <div class="grid gap-5 p-5 md:grid-cols-2">
            <div>
                <x-input-label for="brand" value="Brand" />
                <x-text-input id="brand" name="brand" type="text" class="mt-1 block w-full" :value="old('brand', $vehicle->brand ?? '')" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('brand')" />
            </div>

            <div>
                <x-input-label for="model" value="Model" />
                <x-text-input id="model" name="model" type="text" class="mt-1 block w-full" :value="old('model', $vehicle->model ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('model')" />
            </div>

            <div>
                <x-input-label for="vehicle_type" value="Vehicle Type" />
                <select id="vehicle_type" name="vehicle_type" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach (\App\Models\Vehicle::TYPES as $type)
                        <option value="{{ $type }}" @selected(old('vehicle_type', $vehicle->vehicle_type ?? 'mpv') === $type)>{{ str($type)->headline() }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('vehicle_type')" />
            </div>

            <div>
                <x-input-label for="transmission" value="Transmission" />
                <select id="transmission" name="transmission" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach (\App\Models\Vehicle::TRANSMISSIONS as $transmission)
                        <option value="{{ $transmission }}" @selected(old('transmission', $vehicle->transmission ?? 'automatic') === $transmission)>{{ str($transmission)->headline() }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('transmission')" />
            </div>

            <div>
                <x-input-label for="fuel_type" value="Fuel Type" />
                <select id="fuel_type" name="fuel_type" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @foreach (\App\Models\Vehicle::FUEL_TYPES as $fuelType)
                        <option value="{{ $fuelType }}" @selected(old('fuel_type', $vehicle->fuel_type ?? 'gasoline') === $fuelType)>{{ str($fuelType)->headline() }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('fuel_type')" />
            </div>

            <div>
                <x-input-label for="seat_capacity" value="Seat Capacity" />
                <x-text-input id="seat_capacity" name="seat_capacity" type="number" min="1" class="mt-1 block w-full" :value="old('seat_capacity', $vehicle->seat_capacity ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('seat_capacity')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="base_monthly_price" value="Base Monthly Price" />
                <x-text-input id="base_monthly_price" name="base_monthly_price" type="number" min="1" step="0.01" class="mt-1 block w-full" :value="old('base_monthly_price', $vehicle->base_monthly_price ?? '')" required />
                <x-input-error class="mt-2" :messages="$errors->get('base_monthly_price')" />
            </div>
        </div>
    </section>

    <aside class="space-y-4">
        <section class="rounded-lg border border-neutral-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-neutral-950">Availability</h2>
            <label class="mt-4 flex items-start gap-3">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="mt-1 rounded border-neutral-300 text-neutral-950 shadow-sm focus:ring-neutral-800" @checked(old('is_active', $vehicle->is_active ?? true))>
                <span>
                    <span class="block text-sm font-medium text-neutral-900">Active vehicle</span>
                    <span class="mt-1 block text-sm text-neutral-500">Inactive vehicles cannot be selected in new quotations later.</span>
                </span>
            </label>
            <x-input-error class="mt-2" :messages="$errors->get('is_active')" />
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('vehicles.index') }}" class="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-white">
                Cancel
            </a>
            <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">
                {{ $submitLabel }}
            </button>
        </div>
    </aside>
</div>
