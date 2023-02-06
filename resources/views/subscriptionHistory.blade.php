@extends('layouts.master')
@section('body')
    <div class="container">
        <div class="row">
            <div class="col-6 my-4">
                <h4>Current Plan : {{ $profile->currentSubscription->slug }}</h4>
            </div>
            <div class="col-6 my-4">
                <h4>
                    <a href="{{ url('/subscriptions') }}" class="text-success float-right mx-2">Change Plan</a>
                    {{-- {{dd($profile)}} --}}
                    @if ($profile->currentSubDetail()->slug !== 'basic-retail-monthly')
                        @if ($profile->checkIfCancellationIsSubmitted($profile->current_business_subscription_id) == false)
                            <form action="{{ url('/cancelCurrentSubscription') }}" method="post">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <button type="submit" class="text-danger btn btn-sm float-right">Cancel
                                    Current Subscription</button>
                            </form>
                        @endif
                    @endif
                </h4>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">

                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Status</th>
                            <th scope="col">Type</th>
                            <th scope="col">Start Date</th>
                            <th scope="col">End Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($subscriptions->count() > 0)
                            @foreach ($subscriptions as $sub)
                                <tr>
                                    <th scope="row">{{ $loop->iteration }}</th>
                                    @if ($profile->checkIfCancellationIsSubmitted($sub->id) == true)
                                        <td>{{ $sub->status == 'completed' ? 'completed' : 'canceled' }} <br>
                                            <span class="text-danger">
                                                @if ($sub->subscriptionCancel)
                                                    [Canceled on:
                                                    {{ $sub->subscriptionCancel->created_at->format('M-d-Y') }}] <br>
                                                    @if ($sub->status == 'ongoing')
                                                        [Downgrade date:
                                                        {{ \Carbon\Carbon::parse($sub->activate_datetime)->addMonth(1)->format('M-d-Y') }}
                                                    @endif
                                                @endif
                                            </span>
                                        </td>
                                    @elseif ($sub->status == 'ongoing')
                                        <td class="text-success">
                                            {{ $sub->status }}
                                        </td>
                                    @else
                                        <td>
                                            {{ $sub->status }}
                                            @if ($profile->checkIfCancellationIsSubmitted($sub->id) == true)
                                                [Canceled on:
                                                {{ $sub->created_at->format('M-d-Y') }}] <br>
                                            @endif
                                        </td>
                                    @endif
                                    <td>{{ $sub->subscription->slug }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sub->activate_datetime)->format('M-d-Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($sub->expire_datetime)->format('M-d-Y') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <th scope="row"></th>
                                <td></td>
                                <td>
                                    <h4>No Subscriptions in the history <a href="{{ url('/subscriptions') }}">goto
                                            subscriptions</a>
                                    </h4>
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
