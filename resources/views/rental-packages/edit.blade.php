<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Edit Rental Package</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $rentalPackage->name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('rental-packages.update', $rentalPackage) }}">
        @include('rental-packages._form', [
            'method' => 'PUT',
            'submitLabel' => 'Save package',
        ])
    </form>
</x-app-layout>
