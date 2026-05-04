<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Edit Prospect</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $prospect->company_name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('prospects.update', $prospect) }}">
        @include('prospects._form', [
            'method' => 'PUT',
            'submitLabel' => 'Save prospect',
        ])
    </form>
</x-app-layout>
