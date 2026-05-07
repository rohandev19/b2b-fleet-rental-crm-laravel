<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-lg font-semibold text-neutral-950">Edit Follow-up</h1>
            <p class="mt-0.5 text-sm text-neutral-500">{{ $prospect->company_name }}</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('prospects.follow-ups.update', [$prospect, $activity]) }}" class="max-w-3xl">
        @csrf
        @method('PUT')

        <section class="rounded-lg border border-neutral-200 bg-white shadow-sm">
            <div class="border-b border-neutral-200 px-5 py-4">
                <h2 class="text-base font-semibold text-neutral-950">Activity Details</h2>
            </div>

            <div class="grid gap-5 p-5 md:grid-cols-2">
                <div>
                    <x-input-label for="activity_type" value="Activity Type" />
                    <select id="activity_type" name="activity_type" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @foreach (\App\Models\FollowUpActivity::TYPES as $type)
                            <option value="{{ $type }}" @selected(old('activity_type', $activity->activity_type) === $type)>{{ str($type)->replace('_', ' ')->headline() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('activity_type')" />
                </div>

                <div>
                    <x-input-label for="contact_id" value="Related PIC" />
                    <select id="contact_id" name="contact_id" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">No specific PIC</option>
                        @foreach ($prospect->contacts as $contact)
                            <option value="{{ $contact->id }}" @selected((string) old('contact_id', $activity->contact_id) === (string) $contact->id)>{{ $contact->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('contact_id')" />
                </div>

                <div>
                    <x-input-label for="activity_date" value="Activity Date" />
                    <x-text-input id="activity_date" name="activity_date" type="datetime-local" class="mt-1 block w-full" :value="old('activity_date', $activity->activity_date->format('Y-m-d\\TH:i'))" required />
                    <x-input-error class="mt-2" :messages="$errors->get('activity_date')" />
                </div>

                <div>
                    <x-input-label for="next_follow_up_at" value="Next Follow-up" />
                    <x-text-input id="next_follow_up_at" name="next_follow_up_at" type="datetime-local" class="mt-1 block w-full" :value="old('next_follow_up_at', optional($activity->next_follow_up_at)->format('Y-m-d\\TH:i'))" />
                    <x-input-error class="mt-2" :messages="$errors->get('next_follow_up_at')" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="summary" value="Summary" />
                    <x-text-input id="summary" name="summary" type="text" class="mt-1 block w-full" :value="old('summary', $activity->summary)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('summary')" />
                </div>

                <div class="md:col-span-2">
                    <x-input-label for="detail" value="Detail" />
                    <textarea id="detail" name="detail" rows="4" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('detail', $activity->detail) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('detail')" />
                </div>

                <div>
                    <x-input-label for="outcome" value="Outcome" />
                    <select id="outcome" name="outcome" class="mt-1 block w-full rounded-md border-neutral-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">No outcome yet</option>
                        @foreach (\App\Models\FollowUpActivity::OUTCOMES as $outcome)
                            <option value="{{ $outcome }}" @selected(old('outcome', $activity->outcome) === $outcome)>{{ str($outcome)->replace('_', ' ')->headline() }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('outcome')" />
                </div>
            </div>
        </section>

        <div class="mt-5 flex justify-end gap-3">
            <a href="{{ route('prospects.show', $prospect) }}" class="rounded-lg border border-neutral-200 px-4 py-2 text-sm font-medium text-neutral-700 transition hover:bg-white">Cancel</a>
            <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-neutral-800">Save follow-up</button>
        </div>
    </form>
</x-app-layout>
