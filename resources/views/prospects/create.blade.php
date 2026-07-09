<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Create Prospect</h1>
            <p class="mt-0.5 text-sm text-neutral-500">Add a company to the sales pipeline.</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('prospects.store') }}">
        @include('prospects._form', [
            'method' => 'POST',
            'submitLabel' => 'Create prospect',
        ])
    </form>
</x-app-layout>
