<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pay with {{ $data->data->currency }}</title>
    <script src="https://pay.google.com/gp/p/js/pay.js"></script>
</head>
<body>
    

    <script src="{{ asset('public/frontend/') }}/js/jquery-3.5.1.min.js"></script>

    <script>
        const paymentDataRequest = {
            apiVersion: 2,
            apiVersionMinor: 0,
            allowedPaymentMethods: [{
                type: 'CARD',
                parameters: {
                    allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                    allowedCardNetworks: ['VISA', 'MASTERCARD']
                },
                tokenizationSpecification: {
                    type: 'PAYMENT_GATEWAY',
                    parameters: {
                        gateway: "{{ $payment_gateway->credentials->gateway }}",
                        "stripe:version": "{{ $payment_gateway->credentials->stripe_version }}",
                        "stripe:publishableKey":"{{ $payment_gateway->credentials->stripe_publishable_key }}"
                    }
                }
            }],
            merchantInfo: {
                merchantId: "{{ $payment_gateway->credentials->merchant_id }}",
                merchantName: "{{ $payment_gateway->credentials->merchant_name }}"
            },
            transactionInfo: {
                totalPriceStatus: 'FINAL',
                totalPriceLabel: 'Total',
                totalPrice: "{{ $data->data->payable }}",
                currencyCode: "{{ $data->data->currency }}",
                countryCode: 'US'
            }
        };
       
        const paymentsClient = new google.payments.api.PaymentsClient({
            environment: "{{ $payment_gateway->credentials->mode }}"
        });

        var stripeRoute = "{{ setRoute('user.send.money.stripe.payment.gateway') }}"
        var identifier  = "{{ $data->identifier }}";
        var csrfToken   = $('meta[name="csrf-token"]').attr('content');
        const paymentDataRequestWithParameters = Object.assign({},paymentDataRequest);
        paymentDataRequestWithParameters.transactionInfo.totalPrice = "{{ $data->data->payable }}";
        paymentsClient.loadPaymentData(paymentDataRequestWithParameters)
        .then((paymentData) => {
            var paymentDataToken = JSON.parse(paymentData.paymentMethodData.tokenizationData.token);
            console.log(paymentDataToken.id);
            $.post(stripeRoute,{paymentToken:paymentDataToken.id,identifier:identifier,_token:"{{ csrf_token() }}"},function(response){
                console.log(response);
                window.location.href = response.data.data;
        });
            
            
        })
        
    </script>
    
</body>
</html>