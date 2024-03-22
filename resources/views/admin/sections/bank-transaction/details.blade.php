@extends('admin.layouts.master')

@push('css')

    <style>
        .fileholder {
            min-height: 374px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 330px !important;
        }
        .image-resize{
            width: 35px;
        }
    </style>
@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ],
        
    ], 'active' => __($page_title)])
@endsection

@section('content')
<div class="row mb-30-none">
    
    <div class="col-lg-6 mb-30">
        <div class="transaction-area">
            <h4 class="title mb-0"><i class="fas fa-user text--base me-2"></i>{{ __("Sender Information") }}</h4>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("User Name") }} <span>{{ $data->details->data->user_info->user_name ?? 'N/A'  }}</span> </li>
                        <li>{{ __("Email") }} <span class="text-lowercase">{{ $data->details->data->user_info->email ?? 'N/A'  }}</span> </li>
                        <li>{{ __("Bank Name") }} <span class="text-lowercase">{{ $data->details->data->bank->bank_name ?? 'N/A'  }}</span> </li>
                        @foreach ($data->details->data->bank->credentials  as $item)
                            
                        <li>{{ __($item->label) }} <span class="text-lowercase">{{ $item->value ?? 'N/A'  }}</span> </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-30">
        <div class="transaction-area">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="title"><i class="fas fa-user text--base me-2"></i>{{ __("Payment Information") }}</h4>
                @if ($data->status  == payment_gateway_const()::STATUSPENDING)
                    <div class="d-flex">
                        @include('admin.components.link.status-update',[
                            'text'          => __("Complete"),
                            'href'          => "#confirm",
                            'class'         => "modal-btn",
                        ])
                        @include('admin.components.link.status-update',[
                            'text'          => __("Reject"),
                            'href'          => "#reject",
                            'class'         => "modal-btn ms-1",
                        ])
                    </div>
                @elseif($data->status  == payment_gateway_const()::STATUSSUCCESS)
                    <button class="btn--base">{{ __("Complete") }}</button>
                @elseif($data->status  == payment_gateway_const()::STATUSREJECTED)
                    <button class="btn--base">{{ __("Rejected") }}</button>
                @endif
                
            </div>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("TRANSACTION ID	") }} <span>{{ $data->trx_id ?? ''  }}</span> </li>
                        <li>{{ __("Request Amount	") }} <span>{{ get_amount(@$data->request_amount,@$data->details->data->default_currency)  }}</span> </li>
                        <li>{{ __("Total Charge	") }} <span>{{ get_amount(@$data->details->data->total_charge,@$data->details->data->default_currency)  }}</span> </li>
                        <li>{{ __("Payable Amount") }} <span>{{ get_amount(@$data->details->data->total_payable,@$data->details->data->default_currency) }}</span> </li>
                        <li>{{ __("Exchange Rate") }} <span>1 {{ @$data->details->data->default_currency }} = {{ get_amount(@$data->details->data->exchange_rate,@$data->details->data->bank_currency) }}</span> </li>
                        <li>{{ __("Receive Amount") }} <span>{{ get_amount(@$data->details->data->receive_money,@$data->details->data->bank_currency) }}</span> </li>
                        @if ($data->status == payment_gateway_const()::STATUSREJECTED)
                        <li>{{ __("Reject Reason") }} <span>{{ $data->reject_reason ?? 'N/A' }}</span></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
</div>

{{-- confirm modal --}}
<div id="confirm" class="mfp-hide large">
    <div class="modal-data">
        <div class="modal-header px-0">
            <h5 class="modal-title">{{ __("Transaction ID") }} : {{ $data->trx_id }}</h5>
        </div>
        <div class="modal-form-data">
            <form class="modal-form" method="POST" action="{{ setRoute('admin.bank.transaction.status.update',$data->trx_id) }}">
                @csrf
                <div class="row mb-10-none">
                    <h6>{{ __("Are you sure to Complete this Transaction?") }}</h6>
                    <input type="hidden" name="status" value="{{ payment_gateway_const()::STATUSSUCCESS }}">
                    <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                        <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base">{{ __("Confirm") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- reject modal --}}
<div id="reject" class="mfp-hide large">
    <div class="modal-data">
        <div class="modal-header px-0">
            <h5 class="modal-title">{{ __("Transaction ID") }} : {{ $data->trx_id }}</h5>
        </div>
        <div class="modal-form-data">
            <form class="modal-form" method="POST" action="{{ setRoute('admin.bank.transaction.status.update.reject',$data->trx_id) }}">
                @csrf
                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.textarea',[
                            'label'         => __('Reject Reason'),
                            'name'          => 'reject_reason',
                        ])
                    </div>
                    <input type="hidden" name="status" value="{{ payment_gateway_const()::STATUSREJECTED }}">
                    <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                        <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base">{{ __("Confirm") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
