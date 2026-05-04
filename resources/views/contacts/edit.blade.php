<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Edit PIC</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $prospect->company_name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('prospects.contacts.update', [$prospect, $contact]) }}" class="max-w-3xl">
        @csrf
        @method('PUT')

        <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Contact Details</h2>
            </div>

            <div class="grid gap-5 p-5 md:grid-cols-2">
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $contact->name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>
                <div>
                    <x-input-label for="position" value="Position" />
                    <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position', $contact->position)" />
                    <x-input-error class="mt-2" :messages="$errors->get('position')" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $contact->email)" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="phone" value="Phone" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $contact->phone)" />
                    <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="linkedin_url" value="LinkedIn URL" />
                    <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="mt-1 block w-full" :value="old('linkedin_url', $contact->linkedin_url)" />
                    <x-input-error class="mt-2" :messages="$errors->get('linkedin_url')" />
                </div>
                <div class="md:col-span-2">
                    <x-input-label for="notes" value="Notes" />
                    <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $contact->notes) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                </div>
                <label class="md:col-span-2 flex items-start gap-3">
                    <input type="hidden" name="is_primary" value="0">
                    <input type="checkbox" name="is_primary" value="1" class="mt-1 rounded border-neutral-300 text-neutral-950 shadow-sm focus:ring-neutral-800" @checked(old('is_primary', $contact->is_primary))>
                    <span class="text-sm font-medium text-neutral-800">Set as primary PIC</span>
                </label>
            </div>
        </section>

        <div class="mt-5 flex justify-end gap-3">
            <a href="{{ route('prospects.show', $prospect) }}" class="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-white">Cancel</a>
            <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">Save contact</button>
        </div>
    </form>
</x-app-layout>
