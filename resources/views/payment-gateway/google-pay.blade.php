<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pay with {{ $output['currency'] }}</title>
    <script src="https://pay.google.com/gp/p/js/pay.js"></script>
</head>
<body>
    
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
                        gateway: "{{ $output['payment_gateway']->credentials->gateway }}",
                        "stripe:version": "{{ $output['payment_gateway']->credentials->stripe_version }}",
                        "stripe:publishableKey":"{{ $output['payment_gateway']->credentials->stripe_publishable_key }}"
                    }
                }
            }],
            merchantInfo: {
                merchantId: "{{ $output['payment_gateway']->credentials->merchant_id }}",
                merchantName: "{{ $output['payment_gateway']->credentials->merchant_name }}"
            },
            transactionInfo: {
                totalPriceStatus: 'FINAL',
                totalPriceLabel: 'Total',
                totalPrice: "{{ $output['payable'] }}",
                currencyCode: "{{ $output['currency'] }}",
                countryCode: 'US'
            }
        };
       
        const paymentsClient = new google.payments.api.PaymentsClient({
            environment: "{{ $output['payment_gateway']->credentials->mode }}"
        });
        
    </script>
    
</body>
</html>