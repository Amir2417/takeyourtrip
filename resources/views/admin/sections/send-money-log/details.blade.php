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
                        <li>{{ __("User Name") }} <span>{{ $data->user->username ?? 'N/A'  }}</span> </li>
                        <li>{{ __("Email") }} <span class="text-lowercase">{{ $data->user->email ?? 'N/A'  }}</span> </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-30">
        <div class="transaction-area">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="title"><i class="fas fa-user text--base me-2"></i>{{ __("Receiver Information") }}</h4>
            </div>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("Receiver Email	") }} <span>{{ $data->details->data->receiver_email ?? ''  }}</span> </li>
                        <li>{{ __("Receiver Total Balance	") }} <span>{{ get_amount($data->details->recipient_balance,@$data->details->data->currency)  }}</span> </li>                       
                        
                        
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-30">
        <div class="transaction-area">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="title"><i class="fas fa-user text--base me-2"></i>{{ __("Payment Information") }}</h4>
            </div>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("Method	") }} <span>{{ @$data->send_money_gateway->name  }}</span> </li>
                        <li>{{ __("Request Amount	") }} <span>{{ get_amount(@$data->request_amount,@$data->details->data->currency)  }}</span> </li>
                        <li>{{ __("Total Charge	") }} <span>{{ get_amount(@$data->details->data->total_charge,@$data->details->data->currency)  }}</span> </li>
                        <li>{{ __("Payable Amount") }} <span>{{ get_amount(@$data->details->data->payable,@$data->details->data->currency) }}</span> </li>
                        <li>{{ __("Receiver Get") }} <span>{{ get_amount(@$data->details->recipient_amount,@$data->details->data->currency) }}</span> </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
</div>




@endsection
