@extends('layouts.app')

@section('content')
    <div class="m-auto">

        <!-- Successful Payment Icon -->
        <img class="w-1/4 m-auto" src="{{ asset('/images/street-vendor.png') }}">

        <!-- Instructions -->
        <div class="text-center">
            <h3 class="md:text-2xl text-base text-gray-900 font-semibold text-center">Perfect Pay</h3>
            <p class="text-gray-600 my-2">Get more from your favourite local stores and pay on the spot using <span class="font-bold">Perfect Pay</span></p>
            <p>Checkout the <span class="font-bold">{{ @Config::get('name', 'Perfect Order Mobile App'); }}</span> 😁👌</p>
        </div>

    </div>
@endsection
