@extends('layouts.master')
@section('body')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>
                    Buy subscription
                </h2>
            </div>
            <div class="col-md-12">
                <h4>Current Plan : {{ $profile->currentSubscription->slug }}</h4>
            </div>
        </div>
        <div class="row">
            {{-- {{dd($subscriptions->pluck('slug'))}} --}}
            @foreach ($subscriptions as $sub)
                <div class="p-2 m-2 col-md-3 text-center" style="border: 1px solid blue">
                    <p class="text-danger">{{ $sub->slug }}</p>
                    @if ($sub->billing_cycle == 'monthly')
                        <p>Term Price: ${{ number_format($sub->amount), 2 }} CAD</p>
                    @else
                        <p>Term Price: ${{ number_format($sub->amount/6), 2 }} CAD</p>
                    @endif
                    <p>Term: {{ $sub->billing_cycle }}</p>
                    <p>--Features--</p>
                    <p>--Features--</p>

                    @if ($profile->checkIfThisSubscriptionIsCurrentSubscription($sub->id))
                        <span class="btn btn-sm btn-primary">Current Subscription</span>
                    @else
                        @if (!in_array($sub->slug, ['basic-retail-monthly']))
                            <a href="{{ url('/payment/' . $sub->slug . '') }}" class="btn btn-sm btn-success">Buy Now</a>
                        @endif
                    @endif
                </div>
            @endforeach
        </div>

    </div>
@endsection
