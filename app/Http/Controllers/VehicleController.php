<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = Vehicle::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('vehicle_type'), fn ($query) => $query->where('vehicle_type', $request->string('vehicle_type')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->toString() === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('vehicles.index', [
            'vehicles' => $vehicles,
            'filters' => $request->only(['search', 'vehicle_type', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        Vehicle::query()->create($request->safe()->merge([
            'is_active' => $request->boolean('is_active', true),
        ])->all());

        return redirect()
            ->route('vehicles.index')
            ->with('status', 'Vehicle created successfully.');
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', [
            'vehicle' => $vehicle,
        ]);
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update($request->safe()->merge([
            'is_active' => $request->boolean('is_active'),
        ])->all());

        return redirect()
            ->route('vehicles.index')
            ->with('status', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        return $this->toggleActive($vehicle);
    }

    public function toggleActive(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->update([
            'is_active' => ! $vehicle->is_active,
        ]);

        return back()->with('status', $vehicle->is_active ? 'Vehicle activated successfully.' : 'Vehicle archived successfully.');
    }
}
