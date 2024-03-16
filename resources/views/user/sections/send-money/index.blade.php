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
                        <div class="row justify-content-center">
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
                            <div class="col-xxl-12 col-xl-12 col-lg-12 form-group paste-wrapper">
                                <label>{{ __("Sender Email Address to receive invoice (Optional)") }}</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">{{ __("Email") }}</span>
                                    </div>
                                    <input type="email" name="sender_email" class="form--control sender-email" placeholder="Enter Email" value="{{ auth()->user()->email }}" />
                                </div>
                            </div>
                            
                            @if ($os == 'windows' || $os == 'androidos')
                            <div class="col-lg-7 text-center pay-btn-wrapper">
                                <button class="pay-button w-100" id="google-pay-button"><input type="hidden" class="payment-method" name="payment_method" value="{{ $google_pay_gateway->id }}">{{ __("Pay With") }} <img src="{{ get_image($google_pay_gateway->image ,'send-money-gateway') }}" alt=""></button>
                                <span class="divider-badge">or</span>
                                <button class="pay-button round w-100" id="paypal-button"><input type="hidden" class="paypal-payment-method" name="payment_method" value="{{ $paypal_gateway->id }}"><img src="{{ asset('public/backend/images/send-money-gateways/seeder/paypal.webp') }}" alt=""></button>
                            </div>
                            @else
                            <div class="col-lg-7 text-center pay-btn-wrapper">
                                <button class="pay-button w-100" id="apple-pay-button"><input type="hidden" class="payment-method" name="payment_method" value="">{{ __("Pay With") }} <img src="{{ asset('public/backend/images/send-money-gateways/seeder/apple-pay.png') }}" alt=""></button>
                                <span class="divider-badge">or</span>
                                <button class="pay-button round w-100" id="paypal-button"><input type="hidden" class="paypal-payment-method" name="payment_method" value="{{ $paypal_gateway->id }}"><img src="{{ asset('public/backend/images/send-money-gateways/seeder/paypal.webp') }}" alt=""></button>
                            </div>
                            @endif
                        </div>
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
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title ">{{__("Send Money Log")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('user.transactions.index','transfer-money') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('user.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
            <div class="modal-body text-center">
                <video id="preview" class="p-1 border" style="width:300px;"></video>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">@lang('close')</button>
            </div>
      </div>
    </div>
</div>

@endsection

@push('script')
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
    function setPaymentMethod(method) {
        $('.payment-method').val(method);
        
    }
</script>
<script>
//  'use strict'
    (function ($) {
        $('.scan').click(function(){
            var scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5, mirror: false });
            scanner.addListener('scan',function(content){
                var route = '{{url('user/qr/scan/')}}'+'/'+content
                $.get(route, function( data ) {
                    if(data.error){
                        throwMessage('error',[data.error]);
                    } else {
                        $("#username").val(data);
                        $("#username").focus()
                    }
                    $('#scanModal').modal('hide')
                });
            });

            Instascan.Camera.getCameras().then(function (cameras){
                if(cameras.length>0){
                    $('#scanModal').modal('show')
                        scanner.start(cameras[0]);
                } else{
                //    alert('No cameras found.');
                    throwMessage('error',["No camera found "]);
                }
            }).catch(function(e){
                // alert('No cameras found.');
                throwMessage('error',["No camera found "]);
            });
        });
        $('.checkUser').on('keyup',function(e){
            var url = '{{ route('user.send.money.check.exist') }}';
            var value = $(this).val();
            var token = '{{ csrf_token() }}';
            if ($(this).attr('name') == 'email') {
                var data = {email:value,_token:token}

            }
            $.post(url,data,function(response) {
                if(response.own){
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').addClass('text--danger').text(response.own);
                    $('.transfer').attr('disabled',true)
                    return false
                }
                if(response['data'] != null){
                    if($('.exist').hasClass('text--danger')){
                        $('.exist').removeClass('text--danger');
                    }
                    $('.exist').text(`Valid user for transaction.`).addClass('text--success');
                    $('.transfer').attr('disabled',false)
                } else {
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').text('User doesn\'t  exists.').addClass('text--danger');
                    $('.transfer').attr('disabled',true)
                    return false
                }

            });
        });
    })(jQuery);
</script>
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

<script>
    var handlePaymentRoute = "{{ setRoute('user.send.money.handle.payment.confirm') }}";
    var stripeUrl = "{{ setRoute('user.send.money.stripe.payment.gateway') }}";

    $('#google-pay-button').on('click',function(){
        var amount          = $('.amount').val();
        var receiverEmail   = $('.receiver-email').val();
        var senderEmail     = $('.sender-email').val();
        var paymentMethod   = $('.payment-method').val();
        var currency        = $('.currency').val();

        handlePaymentRouteUrl(handlePaymentRoute,amount,receiverEmail,senderEmail,paymentMethod,currency);


        
    });

    //send money using paypal
    $('#paypal-button').on('click',function(){
        var amount          = $('.amount').val();
        var receiverEmail   = $('.receiver-email').val();
        var senderEmail     = $('.sender-email').val();
        var paymentMethod   = $('.paypal-payment-method').val();
        var currency        = $('.currency').val();
        handlePaymentRouteUrl(handlePaymentRoute,amount,receiverEmail,senderEmail,paymentMethod,currency);
    });

    //function for handle payment route
    function handlePaymentRouteUrl(handlePaymentRoute,amount,receiverEmail,senderEmail,paymentMethod,currency){
        $.post(handlePaymentRoute,{amount:amount,receiverEmail:receiverEmail,senderEmail:senderEmail,paymentMethod:paymentMethod,currency:currency,_token:"{{ csrf_token() }}"},function(response){
            if(response.type == 'success'){
                window.location.href = "{{ route('user.send.money.redirect.url', ['identifier' => ':identifier']) }}".replace(':identifier', response.data.data.identifier);
                
            }else {
                throwMessage(response.type,response.message);
            }

        });
    }

    $('#apple-pay-button').on('click',function(){
        var errorMessage = "Apple Pay is not available right now. Please try again later.";
        throwMessage('error',[errorMessage]);
    });
    

</script>


@endpush
