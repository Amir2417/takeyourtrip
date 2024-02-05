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
        
    ], 'active' => __("Bank Account Details")])
@endsection

@section('content')
<div class="row mb-30-none">
    
    <div class="col-lg-6 mb-30">
        <div class="transaction-area">
            <h4 class="title mb-0"><i class="fas fa-user text--base me-2"></i>{{ __("User Information") }}</h4>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("User Name") }} <span>{{ $data->user->fullname ?? 'N/A'  }}</span> </li>
                        <li>{{ __("Email") }} <span class="text-lowercase">{{ $data->user->email ?? 'N/A'  }}</span> </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-30">
        <div class="transaction-area">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="title"><i class="fas fa-user text--base me-2"></i>{{ __("Bank Information") }}</h4>
                @if ($data->status  == bank_account_const()::PENDING)
                    <div class="d-flex">
                        @include('admin.components.link.status-update',[
                            'text'          => __("Approved"),
                            'href'          => "#confirm",
                            'class'         => "modal-btn",
                        ])
                        @include('admin.components.link.status-update',[
                            'text'          => __("Reject"),
                            'href'          => "#reject",
                            'class'         => "modal-btn ms-1",
                        ])
                    </div>
                @elseif($data->status  == bank_account_const()::APPROVED)
                    <button class="btn--base">{{ __("APPROVED") }}</button>
                @elseif($data->status  == bank_account_const()::REJECTED)
                    <button class="btn--base">{{ __("Rejected") }}</button>
                @endif
                
            </div>
            <div class="content pt-0">
                <div class="list-wrapper">
                    <ul class="list">
                        <li>{{ __("Bank Name") }} <span>{{ $data->bank->bank_name ?? ''  }}</span> </li>
                        @foreach ($data->credentials ?? [] as $item)
                            @if ($item->type == 'file')
                                <li>{{ $item->label }}<span><img class="image-resize" src="{{ get_image($item->value,'kyc-files') }}" alt=""></span></li>
                            @elseif ($item->type == 'text')
                                <li>{{ $item->label }} <span>{{ $item->value }}</span> </li>
                            @endif
                        @endforeach
                        @if ($data->status == bank_account_const()::REJECTED)
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
            <h5 class="modal-title">{{ __("Bank Name") }} : {{ $data->bank->bank_name }}</h5>
        </div>
        <div class="modal-form-data">
            <form class="modal-form" method="POST" action="{{ setRoute('admin.bank.account.status.approve',$data->id) }}">
                @csrf
                <div class="row mb-10-none">
                    <h6>{{ __("Are you sure to APPROVED this Bank?") }}</h6>
                    <input type="hidden" name="status" value="{{ bank_account_const()::APPROVED }}">
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
            <h5 class="modal-title">{{ __("Bank Name") }} : {{ $data->bank->bank_name }}</h5>
        </div>
        <div class="modal-form-data">
            <form class="modal-form" method="POST" action="{{ setRoute('admin.bank.account.status.reject',$data->id) }}">
                @csrf
                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.textarea',[
                            'label'         => __('Reject Reason'),
                            'name'          => 'reject_reason',
                        ])
                    </div>
                    <input type="hidden" name="status" value="{{ bank_account_const()::REJECTED }}">
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
