@extends('user.layouts.master')

@push('css')
<script src="https://pay.google.com/gp/p/js/pay.js"></script>
@endpush

@section('breadcrumb')
    @include('user.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("user.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none">
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __(@$page_title) }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form" action="{{ setRoute('user.wallet.to.bank.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        @if(isset($bank))
                                        <label for="">{{ __("Bank Details") }}</label>
                                        <div class="bank-list-area">
                                            <div class="bank-list-wrapper">
                                                <input type="hidden" name="bank_account" value="{{ $bank->id }}">
                                                <div class="bank-list-thumb">
                                                    <img class="image-resize" src="{{ get_image($bank->bank->image,'bank') }}" alt="">
                                                </div>
                                                <ul class="bank-account-list">
                                                    @php
                                                        $files      = [];
                                                        $text       = [];
                
                                                        foreach ($bank->credentials ?? [] as $item) {
                                                            if ($item->type == 'file') {
                                                                $files[]      = $item;
                                                            }else{
                                                                $text[]         = $item;
                                                            }
                                                        }
                                                    @endphp
                
                                                    @foreach ($text ?? [] as $item)
                                                        <li class="d-block">{{ $item->label }} : <span>{{ $item->value }}</span></li>
                                                    @endforeach
                                                    @foreach ($files ?? [] as $item)
                                                        <li>{{ $item->label }} : <img class="image-resize" src="{{ get_image($item->value,'kyc-files') }}" alt=""></li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                        @else
                                            <p>{{ __("Your don't have any bank account, so you can not transfer.") }}</p>
                                        
                                        @endif
                                    </div>
                                </div>
                                <div class="col-xxl-6 col-xl-12 col-lg-6 form-group">
                                    <label>{{ __("Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input amount" required placeholder="{{__('enter Amount')}}" name="amount" value="{{ old("amount") }}">
                                        <select class="form--control nice-select currency" name="currency">
                                            <option value="{{ get_default_currency_code() }}">{{ get_default_currency_code() }}</option>
                                        </select>
                                    </div>
                                    <code class="d-block mt-10 text-end text--warning balance-show">{{ __("Available Balance") }} {{ authWalletBalance() }} {{ get_default_currency_code() }}</code>
                                </div>

                                <div class="col-xl-12 col-lg-12">
                                    <button type="submit" class="btn--base w-100 btn-loading transfer">{{ __("Confirm Send") }} <i class="fas fa-paper-plane ms-1"></i></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{__("Wallet To Bank Preview")}}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-coins"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Entered Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fw-bold request-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Transfer Fee") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fees">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Recipient Received") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="recipient-get">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{__("Total Payable")}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="last payable-total text-warning">--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title ">{{__("Wallet To Bank Transfer")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('user.transactions.index','transfer-money') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            {{-- @include('user.components.transaction-log',compact("transactions")) --}}
        </div>
    </div>
</div>
</div>

@endsection

@push('script')
<script>
    var minAmount           = "{{ $bank->bank->min_limit }}";
    var maxAmount           = "{{ $bank->bank->max_limit }}";
    var fixedCharge         = "{{ $bank->bank->fixed_charge }}";
    var percentCharge       = "{{ $bank->bank->percent_charge }}";
    var rate                = "{{ $bank->bank->rate }}";
    var baseCurrency        = "{{ get_default_currency_code() }}";
    var baseCurrencyRate    = "{{ get_default_currency_rate() }}";

    function limitCalc(minAmount,maxAmount,fixedCharge,percentCharge,rate,baseCurrency,baseCurrencyRate){
        var amount      = $('.amount').val();
        var exchangeRate = parseFloat(rate) / parseFloat(baseCurrencyRate);
        var convertRate  = parseFloat(baseCurrencyRate) / parseFloat(rate);
        var minLimit     = parseFloat(minAmount) * convertRate;
        var maxLimit     = parseFloat(maxAmount) * convertRate;
        var fixedCharge  = parseFloat()
        
    }
    

</script>
@endpush
