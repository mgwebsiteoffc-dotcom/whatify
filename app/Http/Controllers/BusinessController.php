<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function edit()
    {
        $business = auth()->user()->business;
        $industries = config('whatify.industries');
        return view('settings.business', compact('business', 'industries'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'display_name' => 'nullable|string|max:255',
            'industry' => 'required|string',
            'website' => 'nullable|url',
            'description' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'gstin' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        auth()->user()->business->update($validated);

        return back()->with('success', 'Business profile updated.');
    }
}