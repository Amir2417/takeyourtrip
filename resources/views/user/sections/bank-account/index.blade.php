@extends('user.layouts.master')

@push('css')
    <style>
        .image-resize{
            width: 35px;
        }
    </style>
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
    <div class="custom-card mt-10">
            @if (isset($bank_account_pending))
            <div class="card-body">
                <div class="row"> 
                    <div class="col-xl-12 col-lg-12 col-md-12 form-group">
                        <h5 >{{ __("Your bank account details are under approval process, please wait for us to finalize the process and inform you via email.") }}</h5>
                    </div>
                </div>
            </div>
            @elseif (isset($bank_account_approved))
            <div class="bank-account-list-area">
                <div class="bank-account-list-wrapper">
                    
                        <div class="bank-account-list-thumb">
                            <img class="image-resize" src="{{ get_image($bank_account_approved->bank->image,'bank') }}" alt="">
                        </div>
                        <ul class="bank-account-list">
                            @php
                                $files      = [];
                                $text       = [];

                                foreach ($bank_account_approved->credentials ?? [] as $item) {
                                    if ($item->type == 'file') {
                                        $files[]      = $item;
                                    }else{
                                        $text[]         = $item;
                                    }
                                }
                                usort($text, function ($a, $b) {
                                    $order = ['full name in Emirates ID', 'IBAN', 'Swift Code'];
                                    return array_search($a->label, $order) - array_search($b->label, $order);
                                });
                            @endphp
                            @foreach ($text ?? [] as $item)
                                @if ($item->label == 'full name in Emirates ID')
                                    @php
                                        $label = "Name";
                                    @endphp
                                    <li class="d-block"><span>{{ $label }}</span>: {{ $item->value }}</li>
                                @elseif ($item->label == "IBAN" || $item->label == "Swift Code")
                                    <li class="d-block"><span>{{ $item->label }} ends with</span> : **{{ substr($item->value, -4) }}</li>
                                @else
                                <li class="d-block"><span>{{ $item->label }}</span> : {{ $item->value }}</li>
                                @endif
                            @endforeach
                            @foreach ($files ?? [] as $item)
                                <li>{{ $item->label }} : <img class="image-resize" src="{{ get_image($item->value,'kyc-files') }}" alt=""></li>
                            @endforeach
                        </ul>
                        
                    <button type="button" class="remove-btn" data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $bank_account_approved->id }}">
                        <i class="las la-trash"></i>
                    </button>
                </div>
            </div>
            
            {{-- modal --}}
            <div class="modal fade" id="deleteModal-{{ $bank_account_approved->id }}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                    <p class="title">{{ __("Are you sure to delete this Bank Account? If you delete this bank account and add another one,you should wait for bank account approval.") }}</p>  
                    </div>
                    <div class="modal-footer justify-content-between border-0">
                        <button type="button" class="btn--base bg-danger" data-bs-dismiss="modal">{{ __("Close") }}</button>
                        <form action="{{ setRoute('user.bank.account.delete',$bank_account_approved->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn--base">{{ __("Confirm") }}</button>
                        </form>
                    </div>
                </div>
                </div>
            </div>

            @elseif(isset($bank_account_reject))
            <div class="card-body">
                <div class="row"> 
                    <div class="col-xl-12 col-lg-12 col-md-12 form-group">
                        <h5>{{ __("Your bank account is rejected due to ") }} "{{ $bank_account_reject->reject_reason }}". {{ __("Create a new Bank Account") }}</h5>
                    </div>
                </div>

                <form class="card-form add-recipient-item" action="{{ setRoute('user.bank.account.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="trx-inputs bt-view" style="display: block;">
                        <div class="row"> 
                            <div class="col-xl-12 col-lg-12 col-md-12 form-group">
                                <label>{{ __("Select Bank") }}<span>*</span></label>
                                <select class="form--control select2-basic" name="bank">
                                    <option selected disabled>{{ __("Select Bank") }}</option>
                                    @foreach ($banks as $item)
                                        <option 
                                            value="{{ $item->id }}"
                                            data-input_fields="{{ $item }}"
                                            >{{ $item->bank_name }} </option>
                                    @endforeach 
                                </select>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 form-group bank-dynamic-fields">
                                
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12">
                        <button type="submit" class="btn--base w-100">{{ __("Add Bank Account") }}</button>
                    </div>
                </form>
            </div>
            @else
            <div class="card-body">
                <form class="card-form add-recipient-item" action="{{ setRoute('user.bank.account.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="trx-inputs bt-view" style="display: block;">
                        <div class="row"> 
                            <div class="col-xl-12 col-lg-12 col-md-12 form-group">
                                <label>{{ __("Select Bank") }}<span>*</span></label>
                                <select class="form--control select2-basic" name="bank">
                                    <option selected disabled>{{ __("Select Bank") }}</option>
                                    @foreach ($banks as $item)
                                        <option 
                                            value="{{ $item->id }}"
                                            data-input_fields="{{ $item }}"
                                            >{{ $item->bank_name }} </option>
                                    @endforeach 
                                </select>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 form-group bank-dynamic-fields">
                                
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12">
                        <button type="submit" class="btn--base w-100">{{ __("Confirm") }}</button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>


@endsection

@push('script')
    <script>
        $('select[name=bank]').on('change',function(){
            var inputFields     = $("select[name=bank] :selected").attr("data-input_fields");
            var jsonParseData   = JSON.parse(inputFields);
            $('.bank-dynamic-fields').text('');
            var dynamicFields   = '';
            if(jsonParseData.input_fields.length > 0){
                $.each(jsonParseData.input_fields, function (key, value) {
                    var selectedAttribute = (value.required == true) ? '*' : '';  
                    var requiredAttribute = (value.required == true) ? 'required' : '';  
                    var labelText         = (value.label == "IBAN" || value.label == "full name in Emirates ID") ? 'your' : '';
                    dynamicFields += `<label>Enter ${labelText} ${value.label}<span>${selectedAttribute}</span></label>
                        <div class="input-group">
                            <input type="${value.type}" class="form--control" ${requiredAttribute} name="${value.name}" placeholder="Enter ${value.label}">
                        </div>`;
                });
                $('.bank-dynamic-fields').append(dynamicFields);
            }
        });

    </script>
@endpush