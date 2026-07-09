<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Prospect\StoreProspectRequest;
use App\Http\Requests\Prospect\UpdateProspectRequest;
use App\Models\Prospect;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProspectController extends Controller
{
    public function index(Request $request): View
    {
        $prospects = Prospect::query()
            ->with('assignedSales')
            ->withCount('contacts')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('company_name', 'like', "%{$search}%")
                        ->orWhere('industry', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($query) => $query->where('priority', $request->string('priority')))
            ->when($request->filled('assigned_sales_id'), fn ($query) => $query->where('assigned_sales_id', $request->integer('assigned_sales_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('prospects.index', [
            'prospects' => $prospects,
            'salesUsers' => $this->salesUsers(),
            'filters' => $request->only(['search', 'status', 'priority', 'assigned_sales_id']),
        ]);
    }

    public function create(): View
    {
        return view('prospects.create', [
            'salesUsers' => $this->salesUsers(),
        ]);
    }

    public function store(StoreProspectRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->user()->hasRole(UserRole::Sales) && empty($data['assigned_sales_id'])) {
            $data['assigned_sales_id'] = $request->user()->id;
        }

        $prospect = Prospect::query()->create($data);

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Prospect created successfully.');
    }

    public function show(Prospect $prospect): View
    {
        $prospect->load([
            'assignedSales',
            'contacts' => fn ($query) => $query->latest(),
            'followUpActivities' => fn ($query) => $query->with(['contact', 'user'])->latest('activity_date'),
        ]);

        return view('prospects.show', [
            'prospect' => $prospect,
        ]);
    }

    public function edit(Prospect $prospect): View
    {
        return view('prospects.edit', [
            'prospect' => $prospect,
            'salesUsers' => $this->salesUsers(),
        ]);
    }

    public function update(UpdateProspectRequest $request, Prospect $prospect): RedirectResponse
    {
        $prospect->update($request->validated());

        return redirect()
            ->route('prospects.show', $prospect)
            ->with('status', 'Prospect updated successfully.');
    }

    public function destroy(Prospect $prospect): RedirectResponse
    {
        $prospect->delete();

        return redirect()
            ->route('prospects.index')
            ->with('status', 'Prospect deleted successfully.');
    }

    private function salesUsers()
    {
        return User::query()
            ->where('role', UserRole::Sales)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
