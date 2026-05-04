<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreProspectContactRequest;
use App\Http\Requests\Contact\UpdateProspectContactRequest;
use App\Models\Prospect;
use App\Models\ProspectContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProspectContactController extends Controller
{
    public function store(StoreProspectContactRequest $request, Prospect $prospect): RedirectResponse
    {
        DB::transaction(function () use ($request, $prospect) {
            if ($request->boolean('is_primary')) {
                $prospect->contacts()->update(['is_primary' => false]);
            }

            $prospect->contacts()->create($request->safe()->merge([
                'is_primary' => $request->boolean('is_primary'),
            ])->all());
        });

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Contact added successfully.');
    }

    public function edit(Prospect $prospect, ProspectContact $contact): View
    {
        return view('contacts.edit', [
            'prospect' => $prospect,
            'contact' => $contact,
        ]);
    }

    public function update(UpdateProspectContactRequest $request, Prospect $prospect, ProspectContact $contact): RedirectResponse
    {
        DB::transaction(function () use ($request, $prospect, $contact) {
            if ($request->boolean('is_primary')) {
                $prospect->contacts()
                    ->whereKeyNot($contact->getKey())
                    ->update(['is_primary' => false]);
            }

            $contact->update($request->safe()->merge([
                'is_primary' => $request->boolean('is_primary'),
            ])->all());
        });

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Contact updated successfully.');
    }

    public function destroy(Prospect $prospect, ProspectContact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Contact deleted successfully.');
    }
}
