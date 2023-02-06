@extends('layouts.master')
@section('body')
    <div class="row">
        <div class="col-md-12">
            <h4>Add Card</h4>
        </div>
        <div class="col-md-4">
            <form action="{{ url('/addCard') }}" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                <p>Card Number <input type="text" name="cardNo" placeholder="Card number" value="4111111111111111" />
                </p>
                <p>Exp Month<input name="cardExpMonth" type="text" placeholder="Exp Month" /></p>
                <p>Exp Year<input name="cardExpYear" type="text" placeholder="Exp Year" /></p>
                <button type="submit">Add Card</button>
            </form>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    Cards' List
                </div>
            </div>
            <div class="row">
                @foreach ($cardInfoFromPsi->Account->CardInfo as $card)
                    <div class="col-4" style="border:1px solid green">
                        <p>Serial No: {{ $card->SerialNo }} | {{ $card->CardNumber }}</p>
                        <p>Expiry Month: {{ $card->CardExpMonth }}</p>
                        <p>Expiry Year: {{ $card->CardExpYear }}</p>
                        @if ($profile->checkIfCardIsDefault($card->SerialNo) == false)
                            <form method="post" action="{{ url('/makeCardAsDefault') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <input type="hidden" name="serial_no" value="{{ $card->SerialNo }}">
                                <p><button type="submit" class="btn btn-sm btn-dark">Make Card Default</button></p>
                            </form>
                        @else
                            <p class="text-success">Default Card</p>
                        @endif
                        @if ($profile->checkIfCardIsBackUpPayment($card->SerialNo) == false)
                            <form method="post" action="{{ url('/makeCardAsBackUpPayment') }}">
                                <input type="hidden" name="serial_no" value="{{ $card->SerialNo }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                                <p><button type="submit" class="btn btn-sm btn-dark">Set as Backup Payment</button>
                                </p>
                            </form>
                        @else
                            <p class="text-success">Backup Payment</p>
                        @endif
                    </div>
                @endforeach
            </div>

        </div>
    </div>
@endsection
