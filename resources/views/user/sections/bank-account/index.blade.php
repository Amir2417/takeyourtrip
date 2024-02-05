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
    <div class="row justify-content-center mb-20-none">
        <div class="col-xl-12 col-lg-12 mb-20">
            <div class="custom-card mt-10">
                    @if (isset($bank_account_pending))
                    <div class="card-body">
                        <div class="row"> 
                            <div class="col-xl-12 col-lg-12 col-md-12 form-group">
                                <p>{{ __("Your bank account details are under approval process, please wait for us to finalize the process and inform you via email.") }}</p>
                            </div>
                        </div>
                    </div>
                    @elseif (isset($bank_account_approved))
                    <div class="bank-account-list-area">
                        <div class="bank-account-list-wrapper">
                            @php
                                $fileData = [];
                                $textData = [];

                                foreach ($bank_account_approved->credentials as $item) {
                                    if ($item->type === 'file') {
                                        $fileData[] = $item;
                                    } else {
                                        $textData[] = $item;
                                    }
                                }
                            @endphp
                                @foreach ($fileData ?? [] as $item)
                                    <div class="bank-account-list-thumb">
                                        <img class="image-resize" src="{{ get_image($item->value,'kyc-files') }}" alt="">
                                    </div>
                                @endforeach
                                <ul class="bank-account-list">
                                    @foreach ($textData ?? [] as $item)
                                        <li class="d-block">{{ $item->label }} : <span>{{ $item->value }}</span></li>
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
                    dynamicFields += `<label>${value.label}<span>${selectedAttribute}</span></label>
                        <div class="input-group">
                            <input type="${value.type}" class="form--control" ${requiredAttribute} name="${value.name}" placeholder="Enter ${value.label}">
                        </div>`;
                });
                $('.bank-dynamic-fields').append(dynamicFields);
            }
        });

    </script>
@endpush