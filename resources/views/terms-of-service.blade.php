@extends('layouts.app')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="mx-auto w-full p-8 lg:w-2/3 lg:p-20">
        <div class="mb-8 text-center">
            <img src="{{ asset('images/logo-black-transparent.png') }}" alt="Perfect Order Logo" class="h-24 mx-auto">
        </div>
        <h1 class="text-3xl font-bold mb-4">Terms of Service</h1>
        <p class="text-gray-700 leading-relaxed mb-6">
            By using Perfect Order ("we," "our," or "us"), you agree to these Terms of Service. If you do not agree, please do not use our services.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">1. Scope of Service</h2>
        <p class="text-gray-700 leading-relaxed">
            Perfect Order enables store creation, product management, AI assistant usage, and subscriptions. Services are available on web, mobile, and USSD platforms.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">2. Account Usage</h2>
        <p class="text-gray-700 leading-relaxed">
            Users must provide accurate account and store information. Account sharing or unauthorized use is prohibited. Accounts may be suspended or terminated for policy violations.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">3. Transactions and Payments</h2>
        <p class="text-gray-700 leading-relaxed">
            Supported payment methods include DPO and other integrated gateways. Subscriptions renew automatically unless canceled. Refunds are issued for valid disputes within 7 business days.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">4. Privacy and Data</h2>
        <p class="text-gray-700 leading-relaxed">
            Users acknowledge and agree to the Privacy Policy. Data collection, processing, and usage comply with legal obligations.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">5. Intellectual Property</h2>
        <p class="text-gray-700 leading-relaxed">
            Users retain ownership of content they upload. Unauthorized use of the platform or its features is prohibited.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">6. Limitation of Liability</h2>
        <p class="text-gray-700 leading-relaxed">
            The platform is not liable for service interruptions or errors. Users are responsible for content they upload.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">7. Changes to Terms</h2>
        <p class="text-gray-700 leading-relaxed">
            We will notify users of updates to these Terms or the Privacy Policy.
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">8. Governing Law</h2>
        <p class="text-gray-700 leading-relaxed">
            These terms are governed by the laws of Botswana.
        </p>
    </div>
</div>
@endsection
