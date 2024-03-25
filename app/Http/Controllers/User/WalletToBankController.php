<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\UserWallet;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Admin\Currency;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\User\WalletToBank\CreateEmailNotification;

class WalletToBankController extends Controller
{
    protected  $trx_id;

    public function __construct()
    {
        $this->trx_id = 'WTB'.getTrxNum();
    }
    /**
     * Method for wallet to bank transfer page
     * @return view
     */
    public function index(){
        $page_title     = "Wallet To Bank Transfer";
        $bank           = BankAccount::auth()->with(['bank'])->where('status',bank_account_const()::APPROVED)->first();
        $transactions   = Transaction::auth()->walletToBank()->latest()->take(10)->get();
        
        return view('user.sections.wallet-to-bank.index',compact(
            'page_title',
            'bank',
            'transactions'
        ));
    }
    /**
     * Method for store wallet to bank transfer info
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request){
        $basic_setting      = BasicSettings::first();
        $validator          = Validator::make($request->all(),[
            'bank_account'  => 'required',
            'amount'        => 'required',
            'currency'      => 'required'
        ]);
        if($validator->fails()){
            return back()->withErrors($validator)->withInput($request->all());
        }
        $validated      = $validator->validate();
        $amount         = floatval($request->amount);
        $user_wallet    = UserWallet::where('user_id',auth()->user()->id)->first();
        if(!$user_wallet) return back()->with(['error' => ['Wallet not found!']]);
        $bank_account   = BankAccount::auth()->with(['bank'])->where('id',$request->bank_account)->where('status',bank_account_const()::APPROVED)->first();
        if(!$bank_account) return back()->with(['error' => ['Bank account not found.']]);

        $currency   = Currency::default();
        
        $exchange_rate  = $bank_account->bank->rate / $currency->rate;
        $convert_rate   = $currency->rate / $bank_account->bank->rate;
        $min_limit      = $bank_account->bank->min_limit * $convert_rate;
        $max_limit      = $bank_account->bank->max_limit * $convert_rate;
        $fixed_charge   = $bank_account->bank->fixed_charge * $convert_rate;
        $percent_charge = ($amount / 100) * $bank_account->bank->percent_charge;
        $total_charge   = $fixed_charge + $percent_charge;
        $total_payable  = floatval($amount) + floatval($total_charge);
        $receive_money  = floatval($amount) * floatval($exchange_rate);
        $user           = auth()->user();

        if($total_payable < $min_limit || $total_payable > $max_limit){
            return back()->with(['error' => ['Follow the transaction limit.']]);
        }

        if($total_payable > $user_wallet->balance){
            return back()->with(['error' => ['Sorry! Insufficient balance.']]);
        }
        $trx_id = $this->trx_id;
        $bank_name = $bank_account->bank->bank_name;
        $credentials = $bank_account->credentials;
        
        $data = [
            'bank'              => [
                'id'            => $bank_account->bank->id,
                'slug'          => $bank_account->bank->slug,
                'bank_name'     => $bank_account->bank->bank_name,
                'bank_account'  => $bank_account->id,
                'credentials'   => $credentials
            ],
            'user_wallet'       => $user_wallet->id, 
            'user_info'         => [
                'user_name'     => auth()->user()->fullname,
                'email'         => auth()->user()->email,
            ],
            'request_amount'    => $amount,      
            'exchange_rate'     => floatval($exchange_rate),
            'convert_rate'      => floatval($convert_rate),
            'min_limit'         => floatval($min_limit),
            'fixed_charge'      => floatval($fixed_charge),
            'percent_charge'    => floatval($percent_charge),
            'total_charge'      => floatval($total_charge),
            'total_payable'     => floatval($total_payable),
            'receive_money'     => floatval($receive_money),
            'default_currency'  => $currency->code,
            'bank_currency'     => $bank_account->bank->currency_code,
        ];
        try{
            
            $sender = $this->insertRecord($trx_id,$data,$user_wallet,$amount,$total_payable);
            $this->insertCharges($bank_name,$data,$sender);
            if($basic_setting->email_notification == true){
                Notification::route("mail",auth()->user()->email)->notify(new CreateEmailNotification($user,$data,$trx_id));
            }
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Wallet To Bank Transfer successful.']]);
    }

    public function insertRecord($trx_id,$data,$user_wallet,$amount,$total_payable) {
        $trx_id = $trx_id;
        $user   = auth()->user();
        $details =[
            'data' => $data,
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $user_wallet->id,
                'payment_gateway_currency_id'   => null,
                'send_money_gateway_id'         => null,
                'type'                          => PaymentGatewayConst::WALLETTOBANK,
                'trx_id'                        => $trx_id,
                'request_amount'                => floatval($amount),
                'payable'                       => floatval($total_payable),
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::WALLETTOBANK," ")),
                'details'                       => json_encode($details),
                'attribute'                     => PaymentGatewayConst::SEND,
                'status'                        => 2,
                'created_at'                    => now(),
            ]);
            $this->updateWalletBalance($data);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }

    function updateWalletBalance($data){
        $user_wallet = UserWallet::where('user_id',auth()->user()->id)->first();
        if(!$user_wallet) return back()->with(['error' => ['Wallet not found.']]);
        
        $balance = floatval($user_wallet->balance) - floatval($data['total_payable']);
        $user_wallet->update([
            'balance'   => $balance,
        ]);
    }
    
    /**
     * Method for insert sender charges
     */
    public function insertCharges($bank_name,$data,$id) {
        $user = auth()->user();
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $data['percent_charge'],
                'fixed_charge'      =>$data['fixed_charge'],
                'total_charge'      =>$data['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         => __("Wallet To Bank"),
                'message'       => __('Wallet To Bank ')." ".$bank_name." ".__('Transfer Successful'),
                'image'         =>  get_image($user->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::WALLETTOBANK,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);

            
            DB::commit();

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
}
