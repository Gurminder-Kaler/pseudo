@extends('layouts.master')
@section('body')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2>
                    Do Payment
                </h2>
            </div>
        </div>
        <div class="row">
            @php
                $amt = 0;
                $tax = 0;
                $billingCycle = $subscription->billing_cycle;
                if ($billingCycle == 'half-yearly') {
                    $amt = number_format($subscription->amount / 6, 2);
                    $endTime = now()
                        ->addMonths(6)
                        ->format('Y-m-d');
                } else {
                    $amt = number_format($subscription->amount, 2);
                    $endTime = now()
                        ->addMonths(1)
                        ->format('Y-m-d');
                }
                $tax = number_format(0.13 * $amt, 2);
                $credit = $profile->calculateLeftOutCreditFromLastSub();
            @endphp
            <div class="p-5 col-6">
                <h4 class="text-success">Purchasing: </h4>
                <p class="text-danger"> {{ $subscription->slug }}</p>
                <hr>
                <p class="text-primary"> Sub Total : ${{ $amt }} CAD</p>
                <p class="text-primary"> Tax : ${{ $tax }} CAD</p>
                <p class="text-primary"> Credit from previous  {{$profile->lastSub()->subscription->slug}} subscription: ${{ $credit }} CAD</p>
                <p class="text-primary"> Total To Be Paid for this term: ${{ ($amt + $tax) - $credit }} CAD</p>
                <p class="text-primary"> Total To Be Paid for next term onwards: ${{ ($amt + $tax)}} CAD</p>
            </div>
            <div class="col-6 p-5">
                <form action="{{ url('/doPayment') }}" method="post">
                    <button type="submit" class="my-2 btn btn-sm btn-primary" href="javascript:void(0)" id="proceed">Proceed </button>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <input type="hidden" value="{{ $subscription->slug }}" name="slug" />
                    <div class="row">
                        @foreach ($cardInfoFromPsi->Account->CardInfo as $card)
                            <div class="col-6" style="border:1px solid green">
                                <input @if ($loop->iteration == 1) checked @endif type="radio"
                                    name="selectedSerialNo" value="{{ $card->SerialNo }}" />
                                <p>Serial No: {{ $card->SerialNo }} | {{ $card->CardNumber }}</p>
                                <p>Expiry Month: {{ $card->CardExpMonth }}</p>
                                <p>Expiry Year: {{ $card->CardExpYear }}</p>
                            </div>
                        @endforeach
                    </div>

                </form>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('js')
@endsection
