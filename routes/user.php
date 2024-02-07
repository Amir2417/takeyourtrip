<?php

use App\Http\Controllers\User\RequestMoneyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GlobalController;
use App\Providers\Admin\BasicSettingsProvider;
use App\Http\Controllers\User\WalletController;
use Pusher\PushNotifications\PushNotifications;
use App\Http\Controllers\User\BillPayController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\AddMoneyController;
use App\Http\Controllers\User\BankAccountController;
use App\Http\Controllers\User\MoneyOutController;
use App\Http\Controllers\User\SecurityController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\MakePaymentController;
use App\Http\Controllers\User\RemitanceController;
use App\Http\Controllers\User\SendMoneyController;
use App\Http\Controllers\User\ReceipientController;
use App\Http\Controllers\User\MobileTopupController;
use App\Http\Controllers\User\PaymentLinkController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\VirtualcardController;
use App\Http\Controllers\User\ReceiveMoneyController;
use App\Http\Controllers\User\StripeVirtualController;
use App\Http\Controllers\User\StrowalletVirtualController;
use App\Http\Controllers\User\SudoVirtualCardController;
use App\Http\Controllers\User\SupportTicketController;

Route::prefix("user")->name("user.")->group(function(){
    Route::post("info",[GlobalController::class,'userInfo'])->name('info');
    Route::controller(DashboardController::class)->group(function(){
        Route::get('dashboard','index')->name('dashboard');
        Route::get('qr/scan/{qr_code}','qrScan')->name('qr.scan');
        Route::get('merchant/qr/scan/{qr_code}','merchantQrScan')->name('merchant.qr.scan');
        Route::post('logout','logout')->name('logout');
        Route::delete('delete/account','deleteAccount')->name('delete.account')->middleware('app.mode');
    });

    //profile
    Route::controller(ProfileController::class)->prefix("profile")->name("profile.")->middleware('app.mode')->group(function(){
        Route::get('/','index')->name('index');
        Route::put('password/update','passwordUpdate')->name('password.update');
        Route::put('update','update')->name('update');
    });
     //Request Money
    Route::middleware('module:request-money')->group(function(){
        Route::controller(RequestMoneyController::class)->prefix("request-money")->name("request.money.")->group(function(){
            Route::get('/','index')->name('index');
            Route::post('submit','submit')->name('submit')->middleware("kyc.verification.guard");
            Route::post('user/exist','checkUser')->name('check.exist');
            Route::prefix("logs")->name("log.")->group(function(){
                Route::get('list','logLists')->name('list');
                Route::post('approve','approved')->name('approve')->middleware("kyc.verification.guard");
                Route::post('reject','rejected')->name('reject')->middleware("kyc.verification.guard");
            });
        });
    });
    //bank account
    Route::controller(BankAccountController::class)->prefix('bank-account')->name('bank.account.')->group(function(){
        Route::get('/','index')->name('index');
        Route::get('create','create')->name('create');
        Route::post('store','store')->name('store');
        Route::post('delete/{id}','delete')->name('delete');
    });
    //Send Money
    Route::middleware('module:send-money')->group(function(){
        Route::controller(SendMoneyController::class)->prefix('send-money')->name('send.money.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('confirmed','confirmed')->name('confirmed');
            Route::post('user/exist','checkUser')->name('check.exist');
            Route::post('/handle-payment-confirmation','handlePaymentConfirmation')->name('handle.payment.confirm');
        });
    });
     //Receive Money
    Route::middleware('module:receive-money')->group(function(){
        Route::controller(ReceiveMoneyController::class)->prefix('receive-money')->name('receive.money.')->group(function(){
            Route::get('/','index')->name('index');
        });
    });
    //Pay Link
    Route::middleware('module:pay-link')->group(function(){
        Route::controller(PaymentLinkController::class)->prefix('payment-link')->name('payment-link.')->group(function(){
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store')->middleware('kyc.verification.guard');
            Route::get('/edit/{id}', 'edit')->name('edit');
            Route::post('/update', 'update')->name('update')->middleware('kyc.verification.guard');
            Route::get('/share/{id}', 'share')->name('share');
            Route::delete('delete', 'delete')->name('delete')->middleware('kyc.verification.guard');
            Route::post('/status', 'status')->name('status')->middleware('kyc.verification.guard');
        });
    });
    Route::controller(WalletController::class)->prefix("wallets")->name("wallets.")->group(function(){
        Route::get("/","index")->name("index");
        Route::post("balance","balance")->name("balance");
    });
    //add money
    Route::middleware('module:add-money')->group(function(){
        Route::controller(AddMoneyController::class)->prefix("add-money")->name("add.money.")->group(function(){
            Route::get('/','index')->name("index");
            Route::post('submit','submit')->name('submit');
            Route::get('success/response/{gateway}','success')->name('payment.success');
            Route::get("cancel/response/{gateway}",'cancel')->name('payment.cancel');
            //manual gateway
            Route::get('manual/payment','manualPayment')->name('manual.payment');
            Route::post('manual/payment/confirmed','manualPaymentConfirmed')->name('manual.payment.confirmed');
            //manual gateway
            Route::get('flutterwave/callback', 'flutterwaveCallback')->name('flutterwave.callback');
            //Razorpay
            Route::get('razor/payment/{trx_id}', 'razorPayment')->name('razor.payment');
            Route::get('razor/callback', 'razorCallback')->name('razor.callback');
            Route::get('razor-pay/cancel/{trx_id}', 'razorCancel')->name('razor.cancel');
            //Stripe
            Route::get('stripe/payment/success/{trx}','stripePaymentSuccess')->name('stripe.payment.success');
            //coingate
            Route::match(['get','post'],'coingate/success/response/{gateway}','coinGateSuccess')->name('coingate.payment.success');
            Route::match(['get','post'],"coingate/cancel/response/{gateway}",'coinGateCancel')->name('coingate.payment.cancel');

            //crypto
            Route::prefix('payment')->name('payment.')->group(function() {
                Route::get('crypto/address/{trx_id}','cryptoPaymentAddress')->name('crypto.address');
                Route::post('crypto/confirm/{trx_id}','cryptoPaymentConfirm')->name('crypto.confirm');
            });

        });
    });
    //withdraw money
    Route::middleware('module:withdraw-money')->group(function(){
        Route::controller(MoneyOutController::class)->prefix('withdraw')->name('money.out.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('insert','paymentInsert')->name('insert');
            Route::get('preview','preview')->name('preview');
            Route::post('confirm','confirmMoneyOut')->name('confirm');

            //check bank validation
            Route::post('check/flutterwave/bank','checkBanks')->name('check.flutterwave.bank');
            //automatic withdraw confirmed
            Route::post('automatic/confirmed','confirmMoneyOutAutomatic')->name('confirm.automatic');

        });
    });
    Route::middleware('module:virtual-card')->group(function(){
        //virtual card flutterwave
        Route::middleware('virtual_card_method:flutterwave')->group(function(){
            Route::controller(VirtualcardController::class)->prefix('virtual-card')->name('virtual.card.')->group(function(){
                Route::get('/','index')->name('index');
                Route::post('create','cardBuy')->name('create')->middleware('kyc.verification.guard');
                Route::post('fund','cardFundConfirm')->name('fund.confirm')->middleware('kyc.verification.guard');
                Route::get('details/{card_id}','cardDetails')->name('details');
                Route::get('transaction/{card_id}','cardTransaction')->name('transaction');
                Route::put('change/status','cardBlockUnBlock')->name('change.status');
                Route::post('make/default/remove/default','makeDefaultOrRemove')->name('make.default.or.remove');
                Route::post('flutter-wave-card-callback','cardCallBack')->name('flutterWave.callBack');
            });
        });
        //virtual card sudo
        Route::middleware('virtual_card_method:sudo')->group(function(){
            Route::controller(SudoVirtualCardController::class)->prefix('sudo-virtual-card')->name('sudo.virtual.card.')->group(function(){
                Route::get('/','index')->name('index');
                Route::post('create','cardBuy')->name('create')->middleware('kyc.verification.guard');
                Route::post('make/default/remove/default','makeDefaultOrRemove')->name('make.default.or.remove');
                Route::get('details/{card_id}','cardDetails')->name('details');
                Route::get('transaction/{card_id}','cardTransaction')->name('transaction');
                Route::put('change/status','cardBlockUnBlock')->name('change.status');
            });
        });
        //virtual card stripe
        Route::middleware('virtual_card_method:stripe')->group(function(){
            Route::controller(StripeVirtualController::class)->prefix('stripe-virtual-card')->name('stripe.virtual.card.')->group(function(){
                Route::get('/','index')->name('index');
                Route::post('create','cardBuy')->name('create')->middleware('kyc.verification.guard');
                Route::get('details/{card_id}','cardDetails')->name('details');
                Route::get('transaction/{card_id}','cardTransaction')->name('transaction');
                Route::put('change/status','cardBlockUnBlock')->name('change.status');
                Route::post('get/sensitive/data','getSensitiveData')->name('sensitive.data');
                Route::post('make/default/remove/default','makeDefaultOrRemove')->name('make.default.or.remove');
            });
        });
        //virtual card strowallet
        Route::middleware('virtual_card_method:strowallet')->group(function(){
            Route::controller(StrowalletVirtualController::class)->prefix('strowallet-virtual-card')->name('strowallet.virtual.card.')->group(function(){
                Route::get('/','index')->name('index');
                Route::post('create','cardBuy')->name('create')->middleware('kyc.verification.guard');
                Route::post('fund','cardFundConfirm')->name('fund')->middleware('kyc.verification.guard');
                Route::get('details/{card_id}','cardDetails')->name('details');
                Route::get('transaction/{card_id}','cardTransaction')->name('transaction');
                Route::put('change/status','cardBlockUnBlock')->name('change.status');
                Route::post('make/default/remove/default','makeDefaultOrRemove')->name('make.default.or.remove');
            });
        });
    });
    //bill pay
    Route::middleware('module:bill-pay')->group(function(){
        Route::controller(BillPayController::class)->prefix('bill-pay')->name('bill.pay.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('insert','payConfirm')->name('confirm');
        });
    });
    //Mobile TopUp
    Route::middleware('module:mobile-top-up')->group(function(){
        Route::controller(MobileTopupController::class)->prefix('mobile-topup')->name('mobile.topup.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('insert','payConfirm')->name('confirm');
        });
    });
    //Recipient
    Route::controller(ReceipientController::class)->prefix('recipient')->name('receipient.')->group(function(){
        Route::get('/','index')->name('index');
        Route::get('/add','addReceipient')->name('add');
        Route::post('/add','storeReceipient');
        Route::get('edit/{id}','editReceipient')->name('edit');
        Route::put('update','updateReceipient')->name('update');
        Route::delete('delete','deleteReceipient')->name('delete');
        Route::post('find/user','checkUser')->name('check.user');
        Route::post('get/create-input','getTrxTypeInputs')->name('create.get.input');
        Route::post('get/edit-input','getTrxTypeInputsEdit')->name('edit.get.input');
        Route::get('send/remittance/{id}','sendRemittance')->name('send.remittance');
    });
    //Remittance
    Route::middleware('module:remittance-money')->group(function(){
        Route::controller(RemitanceController::class)->prefix('remittance')->name('remittance.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('get/token','getToken')->name('get.token');
            Route::post('confirmed','confirmed')->name('confirmed');
            //for filters
            Route::post('get/recipient/country','getRecipientByCountry')->name('get.recipient.country');
            Route::post('get/recipient/transaction/type','getRecipientByTransType')->name('get.recipient.transtype');
        });
    });
    //make payment
    Route::middleware('module:make-payment')->group(function(){
        Route::controller(MakePaymentController::class)->prefix('make-payment')->name('make.payment.')->group(function(){
            Route::get('/','index')->name('index');
            Route::post('confirmed','confirmed')->name('confirmed');
            Route::post('merchant/exist','checkUser')->name('check.exist');
        });
    });
    //transactions
    Route::controller(TransactionController::class)->prefix("transactions")->name("transactions.")->group(function(){
        Route::get('/{slug?}','index')->name('index')->whereIn('slug',['add-money','withdraw','transfer-money','money-exchange','bill-pay','mobile-topup','virtual-card','remittance','make-payment','merchant-payment']);
        // Route::get('log/{slug?}','log')->name('log')->whereIn('slug',['add-money','money-out','transfer-money']);
        Route::post('search','search')->name('search');
    });
    //google-2fa
    Route::controller(SecurityController::class)->prefix("security")->name('security.')->group(function(){
        Route::get('google/2fa','google2FA')->name('google.2fa');
        Route::post('google/2fa/status/update','google2FAStatusUpdate')->name('google.2fa.status.update')->middleware('app.mode');
    });
    //support tickets
    Route::controller(SupportTicketController::class)->prefix("support/ticket")->name("support.ticket.")->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('conversation/{encrypt_id}','conversation')->name('conversation');
        Route::post('message/send','messageSend')->name('messaage.send');
    });

});
Route::get('user/pusher/beams-auth', function (Request $request) {
    if(Auth::check() == false) {
        return response(['Inconsistent request'], 401);
    }
    $userID = Auth::user()->id;

    $basic_settings = BasicSettingsProvider::get();
    if(!$basic_settings) {
        return response('Basic setting not found!', 404);
    }

    $notification_config = $basic_settings->push_notification_config;

    if(!$notification_config) {
        return response('Notification configuration not found!', 404);
    }

    $instance_id    = $notification_config->instance_id ?? null;
    $primary_key    = $notification_config->primary_key ?? null;
    if($instance_id == null || $primary_key == null) {
        return response('Sorry! You have to configure first to send push notification.', 404);
    }
    $beamsClient = new PushNotifications(
        array(
            "instanceId" => $notification_config->instance_id,
            "secretKey" => $notification_config->primary_key,
        )
    );
    $publisherUserId = "user-".$userID;
    try{
        $beamsToken = $beamsClient->generateToken($publisherUserId);
    }catch(Exception $e) {
        return response(['Server Error. Faild to generate beams token.'], 500);
    }

    return response()->json($beamsToken);
})->name('user.pusher.beams.auth');
