@extends('layouts.app')

@section('content')
    <div class="m-auto">

        <!-- Payment Success -->
        @if($transaction->isPaid())

            <!-- Successful Payment Icon -->
            <svg class="text-green-600 w-20 h-20 mx-auto my-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
            </svg>

            <img class="w-1/4 m-auto" src="{{ asset('/images/celebration.png') }}">

            <!-- Instructions -->
            <div class="text-center">
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">Yay! Payment Successful</h3>
                <p class="text-gray-600 my-2">Thank you for completing your secure online payment.</p>
                <p>Get your next order on <span class="font-bold">{{ @Config::get('name', 'Perfect Order'); }}</span> 😁👌</p>
            </div>

        <!-- Non-Successful Payment - With DPO Information -->
        @elseif(!empty($transaction->dpo_payment_response))

            <!-- Unsuccessful Payment Icon -->
            <svg class="text-yellow-600 w-20 h-20 mx-auto my-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
            </svg>

            <!-- Instructions -->
            <div class="text-center">
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">{{ $transaction->dpo_payment_response['onVerifyPaymentResponse']['resultExplanation'] }}</h3>
                @if(!empty($transaction->dpo_payment_response['onVerifyPaymentResponse']['transactionCurrency']))
                    <p class="text-gray-600 my-2">Currency: <span class="font-semibold">Currency: {{ $transaction->dpo_payment_response['onVerifyPaymentResponse']['transactionCurrency'] ?? '' }}</p>
                @endif
                @if(!empty($transaction->dpo_payment_response['onVerifyPaymentResponse']['transactionAmount']))
                    <p class="text-gray-600 my-2">Currency: <span class="font-semibold">Amount: {{ $transaction->dpo_payment_response['onVerifyPaymentResponse']['transactionAmount'] ?? '' }}</p>
                @endif
            </div>

        <!-- Non-Successful Payment - Without DPO Information -->
        @else

            <!-- Unsuccessful Payment Icon -->
            <svg class="text-red-600 w-20 h-20 mx-auto my-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z" clip-rule="evenodd" />
            </svg>

            <img class="w-1/4 m-auto" src="{{ asset('/images/sorry.png') }}">

            <!-- Instructions -->
            <div class="text-center">
                <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">Payment Unsuccessful</h3>
                <p class="text-gray-600 my-2">Sorry, we were not able to take your payment 😔</p>
                <p>Please try again or use a different card</p>
            </div>

        @endif

    </div>
@endsection
