<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barrack;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarrackController extends Controller
{
    public function index(): View
    {
        $barracks = Barrack::orderBy('region')->orderBy('name')->paginate(20);
        $regions = config('recruitment.regions', []);

        return view('admin.barracks.index', compact('barracks', 'regions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'region' => 'required|string|max:50',
            'name' => 'required|string|max:200',
            'location' => 'nullable|string|max:200',
            'is_active' => 'nullable|boolean',
        ]);

        Barrack::create([
            'region' => $validated['region'],
            'name' => $validated['name'],
            'location' => $validated['location'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.barracks.index')
            ->with('success', 'Barrack added successfully.');
    }

    public function update(Request $request, Barrack $barrack): RedirectResponse
    {
        $validated = $request->validate([
            'region' => 'required|string|max:50',
            'name' => 'required|string|max:200',
            'location' => 'nullable|string|max:200',
            'is_active' => 'nullable|boolean',
        ]);

        $barrack->update([
            'region' => $validated['region'],
            'name' => $validated['name'],
            'location' => $validated['location'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.barracks.index')
            ->with('success', 'Barrack updated successfully.');
    }

    public function destroy(Barrack $barrack): RedirectResponse
    {
        $barrack->delete();

        return redirect()->route('admin.barracks.index')
            ->with('success', 'Barrack deleted successfully.');
    }
}
