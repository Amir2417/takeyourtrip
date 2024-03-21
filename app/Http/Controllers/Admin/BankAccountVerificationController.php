<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Helpers\Response;
use App\Constants\BankAccountConst;
use App\Models\Admin\BasicSettings;
use App\Http\Controllers\Controller;
use App\Notifications\Admin\BankAccountApproveNotification;
use App\Notifications\Admin\BankAccountRejectNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class BankAccountVerificationController extends Controller
{
    /**
     * Method for view pending bank account page
     */
    public function pending(){
        $page_title     = "Pending Bank Accounts";
        $data           = BankAccount::with(['user','bank'])->where('status',BankAccountConst::PENDING)->get();
        
        return view('admin.sections.bank-account.pending',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for view bank account details page
     * @param $id
     * @param \Illuminate\Http\Request $request
     */
    public function details($id){
        $page_title     = "Bank Account Details";
        $data           = BankAccount::with(['user','bank'])->where('id',$id)->first();
        if(!$data) return back()->with(['error' => ['Sorry! Data not found.']]);

        return view('admin.sections.bank-account.details',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for approve bank account
     * @param $id
     * @param \Illuminate\Http\Request $request
     */
    public function statusApproved(Request $request,$id){
        $basic_settings     = BasicSettings::first();
        $validator = Validator::make($request->all(),[
            'status'            => 'required|integer',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated      = $validator->validate();
        $bank_data      = BankAccount::with(['user','bank'])->where('id',$id)->first();
        
        $form_data = [
            'data'        => $bank_data,
            'status'      => 'Approved',
        ];
        try{
            $bank_data->update([
                'status'    => $validated['status'],
            ]);
            if($basic_settings->email_notification == true){
                Notification::route('mail',$bank_data->user->email)->notify(new BankAccountApproveNotification($form_data));
            }
        }catch(Exception $e){
            dd($e->getMessage());
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Bank Account Approved Successfully.']]);
    }
    /**
     * Method for reject bank account 
     * @param $id
     * @param \Illuminate\Http\Request $request
     */
    public function statusReject(Request $request,$id){
        $basic_settings     = BasicSettings::first();
        $validator = Validator::make($request->all(),[
            'reject_reason'     => 'required',
            'status'            => 'required|integer',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated      = $validator->validate();
        $bank_data      = BankAccount::with(['user','bank'])->where('id',$id)->first();
        
        $form_data = [
            'data'          => $bank_data,
            'status'        => 'Rejected',
            'reject_reason' => $validated['reject_reason']
        ];
        try{
            $bank_data->update([
                'status'            => $validated['status'],
                'reject_reason'     => $validated['reject_reason']
            ]);
            if($basic_settings->email_notification == true){
                Notification::route('mail',$bank_data->user->email)->notify(new BankAccountRejectNotification($form_data));
            }
        }catch(Exception $e){
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Bank Account Approved Successfully.']]);
    }
    /**
     * Method for view approve bank account page
     */
    public function approve(){
        $page_title     = "Approved Bank Accounts";
        $data           = BankAccount::with(['user','bank'])->where('status',BankAccountConst::APPROVED)->get();
        
        return view('admin.sections.bank-account.approved',compact(
            'page_title',
            'data'
        ));
    }
    /**
     * Method for view reject bank account page
     */
    public function reject(){
        $page_title     = "Rejected Bank Accounts";
        $data           = BankAccount::with(['user','bank'])->where('status',BankAccountConst::REJECTED)->get();
        
        return view('admin.sections.bank-account.reject',compact(
            'page_title',
            'data'
        ));
    }
}
