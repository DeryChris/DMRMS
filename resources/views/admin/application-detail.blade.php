@extends('layouts.admin')

@section('title', 'Application Detail - DMRMS')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-heading font-bold text-2xl text-gray-800">Application Detail</h1>
            <p class="text-gray-500 text-sm">GAF-2026001 | Submitted June 15, 2026</p>
        </div>
        <div class="flex space-x-3">
            <select class="border border-gray-300 rounded-lg px-4 py-2 text-sm"><option>Update Status</option><option>Approve</option><option>Reject</option><option>Mark as Shortlisted</option><option>Schedule Appointment</option></select>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Personal Information</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Name:</span> <span class="font-medium">John Doe</span></div>
                    <div><span class="text-gray-500">DOB:</span> <span class="font-medium">1998-05-15</span></div>
                    <div><span class="text-gray-500">Gender:</span> <span class="font-medium">Male</span></div>
                    <div><span class="text-gray-500">Marital:</span> <span class="font-medium">Single</span></div>
                    <div><span class="text-gray-500">Phone:</span> <span class="font-medium">+233 123 456 789</span></div>
                    <div><span class="text-gray-500">Email:</span> <span class="font-medium">john@email.com</span></div>
                    <div><span class="text-gray-500">Region:</span> <span class="font-medium">Greater Accra</span></div>
                    <div><span class="text-gray-500">National ID:</span> <span class="font-medium">GHA-000-123456-1</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Education</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Institution:</span> <span class="font-medium">University of Ghana</span></div>
                    <div><span class="text-gray-500">Qualification:</span> <span class="font-medium">BSc Computer Science</span></div>
                    <div><span class="text-gray-500">Year:</span> <span class="font-medium">2020</span></div>
                    <div><span class="text-gray-500">Level:</span> <span class="font-medium">Tertiary</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Documents</h3>
                <div class="space-y-3">
                    @php $docs = ['Birth Certificate' => 'verified', 'National ID' => 'verified', 'WASSCE Certificate' => 'pending', 'Passport' => 'rejected', 'Medical Report' => 'pending']; @endphp
                    @foreach($docs as $name => $status)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <span class="text-sm">{{ $name }}</span>
                        <div class="flex items-center space-x-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ ['verified' => 'bg-green-100 text-green-700', 'pending' => 'bg-yellow-100 text-yellow-700', 'rejected' => 'bg-red-100 text-red-700'][$status] }}">{{ ucfirst($status) }}</span>
                            <button class="text-xs text-green-600 hover:underline font-medium">Verify</button>
                            <button class="text-xs text-red-600 hover:underline font-medium">Reject</button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Eligibility Results</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span>Age</span><span class="text-green-600 font-medium">Pass</span></div>
                    <div class="flex justify-between"><span>Nationality</span><span class="text-green-600 font-medium">Pass</span></div>
                    <div class="flex justify-between"><span>Height</span><span class="text-green-600 font-medium">Pass</span></div>
                    <div class="flex justify-between"><span>Education</span><span class="text-green-600 font-medium">Pass</span></div>
                    <div class="mt-3 pt-3 border-t"><span class="font-semibold text-green-700">Overall: Eligible</span></div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Screening Results</h3>
                <p class="text-sm text-gray-500">Not yet screened</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Appointment</h3>
                <p class="text-sm text-gray-500">Not scheduled</p>
                <button class="mt-3 px-4 py-2 bg-gaf-green text-white rounded-lg text-sm font-medium hover:bg-gaf-dark-green transition w-full">Schedule Appointment</button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Actions</h3>
                <div class="space-y-2">
                    <button class="w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">Approve</button>
                    <button class="w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition">Reject</button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-heading font-semibold text-lg text-gray-800 mb-4">Audit Log</h3>
                <div class="space-y-2 text-xs text-gray-500">
                    <p><span class="text-gray-700">Admin</span> updated status to "Eligible" - 2 hours ago</p>
                    <p><span class="text-gray-700">System</span> verified documents - 1 day ago</p>
                    <p><span class="text-gray-700">Applicant</span> submitted application - 3 days ago</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
