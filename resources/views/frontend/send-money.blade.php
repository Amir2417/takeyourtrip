@extends('frontend.layouts.master')

@section('content')
<section class="contact-section ptb-150">
    <div class="container">
        <div class="row">
            <div class="col-xl-6">
                <div class="dash-payment-item-wrapper">
                    <div class="dash-payment-item active">
                        <div class="dash-payment-title-area">
                            <span class="dash-payment-badge">!</span>
                            <h5 class="title">{{ __(@$page_title) }}</h5>
                        </div>
                        <div class="dash-payment-body">
                            <form class="card-form" action="{{ setRoute('send.money.confirmed') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-xl-12 col-lg-12 form-group text-center">
                                        <div class="exchange-area">
                                            <code class="d-block text-center"><span class="fees-show">--</span> <span class="limit-show">--</span></code>
                                        </div>
                                    </div>
                                    <div class="col-xxl-6 col-xl-12 col-lg-6 form-group">
                                        <label>{{ __("Amount") }}<span>*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form--control amount" required placeholder="Enter Amount" name="amount" value="{{ old("amount") }}">
                                            <select class="form--control nice-select currency" name="currency">
                                                <option value="{{ get_default_currency_code() }}">{{ get_default_currency_code() }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xxl-6 col-xl-12 col-lg-6 form-group paste-wrapper">
                                        <label>{{ __("Receiver Email Address") }} ({{ __("User") }})<span class="text--base">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text copytext">{{ __("Email") }}</span>
                                            </div>
                                            <input type="email" name="email" class="form--control receiver-email" id="username" placeholder="Enter Email" value="{{ old('email') }}" />
                                        </div>
                                        <button type="button" class="paste-badge scan"  data-toggle="tooltip" title="Scan QR"><i class="fas fa-camera"></i></button>
                                        <label class="exist text-start"></label>
                                    </div>
                                    <div class="col-xxl-6 col-xl-12 col-lg-6 form-group paste-wrapper">
                                        <label>{{ __("Sender Email Address to receive invoice (Optional)") }}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ __("Email") }}</span>
                                            </div>
                                            <input type="email" name="sender_email" class="form--control sender-email" placeholder="Enter Email"/>
                                        </div>
                                    </div>
                                    <div class="col-xxl-6 col-xl-12 col-lg-6 form-group paste-wrapper">
                                        <label>{{ __("Select Gateway") }}</label>
                                        <div class="input-group">
                                            <select class="select2-basic" name="payment_method">
                                                    <option value="{{ $google_pay_gateway->id  }}">{{ $google_pay_gateway->name ?? '' }}</option>
                                            </select>
                                        </div>
                                    </div>
    
                                    {{-- <div class="payment-area d-flex justify-content-between mb-5 align-items-center">
                                        @if ($os == 'windows' || $os == 'androidos')
                                            <div class="google-payment" id="google-pay-button">
                                                <button type="submit" class="btn" onclick="setPaymentMethod({{ $google_pay_gateway->id }})">
                                                    <input type="hidden" class="payment-method" name="payment_method">
                                                    <img src="{{ get_image(@$google_pay_gateway->image,'send-money-gateway') }}">
                                                </button>
                                            </div>
                                            <div class="or-area">
                                                <span>Or</span>
                                            </div>
                                            <div class="google-payment">
                                                <button type="submit" class="btn" onclick="setPaymentMethod(3)">
                                                    <input type="hidden" class="payment-method" name="payment_method">
                                                    <img src="{{ get_image(@$paypal_gateway->image,'send-money-gateway') }}">
                                                </button>
                                            </div>
                                        @else
                                            <div class="apple-payment">
                                                <button type="submit" class="btn" onclick="setPaymentMethod(2)">
                                                    <input type="hidden" class="payment-method" name="payment_method">
                                                    <img src="{{ asset('public/backend/images/payment-gateways/seeder/apple-pay.png') }}">
                                                </button>
                                            </div>
                                            <div class="or-area">
                                                <span>Or</span>
                                            </div>
                                            <div class="google-payment">
                                                <button type="submit" class="btn" onclick="setPaymentMethod(3)">
                                                    <input type="hidden" class="payment-method" name="payment_method">
                                                    <img src="{{ get_image(@$paypal_gateway->image,'send-money-gateway') }}">
                                                </button>
                                            </div>
                                        @endif
    
                                        
                                    </div> --}}
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
                            <h5 class="title">{{__("Send Money Preview")}}</h5>
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
    </div>
</section>
@endsection
@push('script')
<script>
    var defualCurrency = "{{ get_default_currency_code() }}";
    var defualCurrencyRate = "{{ get_default_currency_rate() }}";

       $(document).ready(function(){

           getLimit();
           getFees();
           getPreview();
       });
       $("input[name=amount]").keyup(function(){
            getFees();
            getPreview();
       });
       function getLimit() {
           var currencyCode = acceptVar().currencyCode;
           var currencyRate = acceptVar().currencyRate;

           var min_limit = acceptVar().currencyMinAmount;
           var max_limit =acceptVar().currencyMaxAmount;
           if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
               var min_limit_calc = parseFloat(min_limit/currencyRate).toFixed(2);
               var max_limit_clac = parseFloat(max_limit/currencyRate).toFixed(2);
               $('.limit-show').html("Limit " + min_limit_calc + " " + currencyCode + " - " + max_limit_clac + " " + currencyCode);

               return {
                   minLimit:min_limit_calc,
                   maxLimit:max_limit_clac,
               };
           }else {
               $('.limit-show').html("--");
               return {
                   minLimit:0,
                   maxLimit:0,
               };
           }
       }
       function acceptVar() {
           var selectedVal = $("select[name=currency] :selected");
           var currencyCode = $("select[name=currency] :selected").val();
           var currencyRate = defualCurrencyRate;
           var currencyMinAmount ="{{getAmount($sendMoneyCharge->min_limit)}}"
           var currencyMaxAmount = "{{getAmount($sendMoneyCharge->max_limit)}}"
           var currencyFixedCharge = "{{getAmount($sendMoneyCharge->fixed_charge)}}"
           var currencyPercentCharge = "{{getAmount($sendMoneyCharge->percent_charge)}}"

           return {
               currencyCode:currencyCode,
               currencyRate:currencyRate,
               currencyMinAmount:currencyMinAmount,
               currencyMaxAmount:currencyMaxAmount,
               currencyFixedCharge:currencyFixedCharge,
               currencyPercentCharge:currencyPercentCharge,
               selectedVal:selectedVal,

           };
       }
       function feesCalculation() {
           var currencyCode = acceptVar().currencyCode;
           var currencyRate = acceptVar().currencyRate;
           var sender_amount = $("input[name=amount]").val();
           sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

           var fixed_charge = acceptVar().currencyFixedCharge;
           var percent_charge = acceptVar().currencyPercentCharge;
           if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
               // Process Calculation
               var fixed_charge_calc = parseFloat(currencyRate * fixed_charge);
               var percent_charge_calc = parseFloat(currencyRate)*(parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
               var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
               total_charge = parseFloat(total_charge).toFixed(2);
               // return total_charge;
               return {
                   total: total_charge,
                   fixed: fixed_charge_calc,
                   percent: percent_charge,
               };
           } else {
               // return "--";
               return false;
           }
       }

       function getFees() {
           var currencyCode = acceptVar().currencyCode;
           var percent = acceptVar().currencyPercentCharge;
           var charges = feesCalculation();
           if (charges == false) {
               return false;
           }
           $(".fees-show").html("Transfer Fee: " + parseFloat(charges.fixed).toFixed(2) + " " + currencyCode + " + " + parseFloat(charges.percent).toFixed(2) + "%  ");
       }
       function getPreview() {
               var senderAmount = $("input[name=amount]").val();
               var sender_currency = acceptVar().currencyCode;
               var sender_currency_rate = acceptVar().currencyRate;
               senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;
               // Sending Amount
               $('.request-amount').text(senderAmount + " " + defualCurrency);

               // Fees
               var charges = feesCalculation();
               var total_charge = 0;
               if(senderAmount == 0){
                   total_charge = 0;
               }else{
                   total_charge = charges.total;
               }

               $('.fees').text(total_charge + " " + sender_currency);
               // // recipient received
               var recipient = parseFloat(senderAmount) * parseFloat(sender_currency_rate)
               var recipient_get = 0;
               if(senderAmount == 0){
                    recipient_get = 0;
               }else{
                    recipient_get =  parseFloat(recipient);
               }
               $('.recipient-get').text(parseFloat(recipient_get).toFixed(2) + " " + sender_currency);

                // Pay In Total
               var totalPay = parseFloat(senderAmount) * parseFloat(sender_currency_rate)
               var pay_in_total = 0;
               if(senderAmount == 0){
                    pay_in_total = 0;
               }else{
                    pay_in_total =  parseFloat(totalPay) + parseFloat(charges.total);
               }
               $('.payable-total').text(parseFloat(pay_in_total).toFixed(2) + " " + sender_currency);

       }

</script>
@endpush

