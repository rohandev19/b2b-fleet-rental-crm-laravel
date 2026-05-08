<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentalPackage\StoreRentalPackageRequest;
use App\Http\Requests\RentalPackage\UpdateRentalPackageRequest;
use App\Models\RentalPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RentalPackageController extends Controller
{
    public function index(Request $request): View
    {
        $rentalPackages = RentalPackage::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('rental-packages.index', [
            'rentalPackages' => $rentalPackages,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('rental-packages.create');
    }

    public function store(StoreRentalPackageRequest $request): RedirectResponse
    {
        RentalPackage::query()->create($this->payload($request));

        return redirect()
            ->route('rental-packages.index')
            ->with('status', 'Rental package created successfully.');
    }

    public function edit(RentalPackage $rentalPackage): View
    {
        return view('rental-packages.edit', [
            'rentalPackage' => $rentalPackage,
        ]);
    }

    public function update(UpdateRentalPackageRequest $request, RentalPackage $rentalPackage): RedirectResponse
    {
        $rentalPackage->update($this->payload($request));

        return redirect()
            ->route('rental-packages.index')
            ->with('status', 'Rental package updated successfully.');
    }

    public function destroy(RentalPackage $rentalPackage): RedirectResponse
    {
        return $this->toggleActive($rentalPackage);
    }

    public function toggleActive(RentalPackage $rentalPackage): RedirectResponse
    {
        $rentalPackage->update([
            'is_active' => ! $rentalPackage->is_active,
        ]);

        return back()->with('status', $rentalPackage->is_active ? 'Rental package activated successfully.' : 'Rental package archived successfully.');
    }

    private function payload(Request $request): array
    {
        return $request->safe()->merge([
            'includes_driver' => $request->boolean('includes_driver'),
            'includes_maintenance' => $request->boolean('includes_maintenance'),
            'includes_insurance' => $request->boolean('includes_insurance'),
            'is_active' => $request->boolean('is_active', true),
        ])->all();
    }
}
