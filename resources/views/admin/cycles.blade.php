@extends('layouts.admin')

@section('title', 'Recruitment Cycles - Ghana Armed Forces')

@section('content')
<div x-data="{ showModal: false, editing: null }" class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="font-heading font-bold text-2xl text-gray-800">Recruitment Cycles</h1>
        <button @click="showModal = true; editing = null" class="px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition">+ New Cycle</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr><th class="px-6 py-4 text-left font-medium text-gray-700">Name</th><th class="px-6 py-4 text-left font-medium text-gray-700">Code</th><th class="px-6 py-4 text-left font-medium text-gray-700">Start Date</th><th class="px-6 py-4 text-left font-medium text-gray-700">End Date</th><th class="px-6 py-4 text-left font-medium text-gray-700">Vacancies</th><th class="px-6 py-4 text-left font-medium text-gray-700">Status</th><th class="px-6 py-4 text-right font-medium text-gray-700">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @php $cycles = [['2026 Recruitment Cycle', '2026/01', '2026-06-01', '2026-07-31', 5000, 'active'], ['2025 Recruitment Cycle', '2025/02', '2025-06-01', '2025-07-31', 4500, 'closed'], ['2025 Early Intake', '2025/01', '2025-01-15', '2025-03-15', 2000, 'archived']]; @endphp
                @foreach($cycles as $c)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium">{{ $c[0] }}</td>
                    <td class="px-6 py-4">{{ $c[1] }}</td>
                    <td class="px-6 py-4">{{ $c[2] }}</td>
                    <td class="px-6 py-4">{{ $c[3] }}</td>
                    <td class="px-6 py-4">{{ number_format($c[4]) }}</td>
                    <td class="px-6 py-4"><span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['active' => 'bg-green-100 text-green-700', 'closed' => 'bg-red-100 text-red-700', 'archived' => 'bg-gray-100 text-gray-700'][$c[5]] }}">{{ ucfirst($c[5]) }}</span></td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button @click="showModal = true; editing = true" class="text-gaf-khaki hover:underline text-sm font-medium">Edit</button>
                        <button class="text-red-600 hover:underline text-sm font-medium">{{ $c[5] === 'active' ? 'Close' : 'Archive' }}</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.outside="showModal = false">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-lg mx-4 p-8">
            <h2 class="font-heading font-bold text-xl text-gray-800 mb-6" x-text="editing ? 'Edit Cycle' : 'Create New Cycle'"></h2>
            <form class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Cycle Name</label><input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Cycle Code</label><input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label><input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">End Date</label><input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Deadline</label><input type="date" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Vacancies</label><input type="number" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki"></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Requirements (JSON)</label>
                    <textarea rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-gaf-khaki font-mono"></textarea>
                </div>
                <label class="flex items-center space-x-2 text-sm"><input type="checkbox" class="rounded border-gray-300"><span>Enable AI Processing</span></label>
                <div class="flex space-x-3 pt-2">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-gaf-green text-white rounded-lg text-sm font-semibold hover:bg-gaf-dark-green transition" x-text="editing ? 'Update' : 'Create'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
