<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\Admin\Bank;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Constants\BankAccountConst;
use App\Http\Controllers\Controller;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Validator;

class BankAccountController extends Controller
{
    use ControlDynamicInputFields;
    /**
     * Method for view bank account page
     * @return view
     */
    public function index(){
        $page_title                 = __("Bank Account");
        $bank_account_approved      = BankAccount::auth()->with(['bank'])->where('status',BankAccountConst::APPROVED)->first();
        $bank_account_pending       = BankAccount::auth()->with(['bank'])->where('status',BankAccountConst::PENDING)->first();
        $bank_account_reject        = BankAccount::auth()->with(['bank'])->where('status',BankAccountConst::REJECTED)->first();
        $banks                      = Bank::where('status',true)->orderBy('id','desc')->get();

        return view('user.sections.bank-account.index',compact(
            'page_title',
            'bank_account_approved',
            'bank_account_pending',
            'bank_account_reject',
            'banks'
        ));
    }
    /**
     * Method for store bank account information
     */
    public function store(Request $request){
        $bank_fields                = Bank::where('id',$request->bank)->first()->input_fields ?? [];
        $validation_rules           = $this->generateValidationRules($bank_fields);

        $validated                  = Validator::make($request->all(),$validation_rules)->validate();
        $get_values                 = $this->placeValueWithFields($bank_fields,$validated);
        $validated['user_id']       = auth()->user()->id;
        $validated['bank_id']       = $request->bank;
        $validated['credentials']   = $get_values;
        try{
            BankAccount::create($validated);
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Bank Account created successfully.']]);
    }
    /**
     * Method for bank account delete
     */
    public function delete($id){
        $bank_account   = BankAccount::auth()->where('id',$id)->first();
        if(!$bank_account) return back()->with(['error' => ['Sorry! Data not found.']]);
        try{
            $bank_account->delete();
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Bank account deleted successfully.']]);
    }
}
