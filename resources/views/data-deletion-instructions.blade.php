@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="mx-auto w-full p-8 lg:w-2/3 lg:p-20">
        <div class="mb-8 text-center">
            <img src="{{ asset('images/logo-black-transparent.png') }}" alt="Perfect Order Logo" class="h-24 mx-auto">
        </div>
        <h1 class="text-3xl font-bold mb-4">Data Deletion Instructions</h1>
        <p class="text-gray-700 leading-relaxed mb-6">
            If you wish to delete your data from Perfect Order, follow these steps.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">Automated Deletion Process</h2>
        <p class="text-gray-700 leading-relaxed">
            Users can delete their stores and data directly via the platform. For account deletion, users must contact support.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">Confirmation</h2>
        <p class="text-gray-700 leading-relaxed">
            Deletions are confirmed via email or SMS once completed.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">Exceptions</h2>
        <p class="text-gray-700 leading-relaxed">
            Data required for legal compliance (e.g., transaction records) cannot be deleted. Data related to unresolved disputes or chargebacks is retained until resolution.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">Timeframe</h2>
        <p class="text-gray-700 leading-relaxed">
            Data is typically deleted within 30 days of a verified request.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">Backup Data</h2>
        <p class="text-gray-700 leading-relaxed">
            Backups are updated during the regular cycle to exclude deleted data within 30 days.
        </p>
    </div>
</div>
@endsection
