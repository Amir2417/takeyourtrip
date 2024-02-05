@extends('user.layouts.master')

@push('css')

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
    <div class="row mb-20-none">
        <div class="col-xl-12 col-lg-12 mb-20">
            <div class="custom-card mt-10">
                <div class="card-body">
                    @if (isset($bank_account_pending))
                    <div class="row"> 
                        <div class="col-xl-12 col-lg-12 col-md-12 form-group">
                            <p>{{ __("Your bank account details are under approval process, please wait for us to finalize the process and inform you via email.") }}</p>
                        </div>
                    </div>
                    @elseif (isset($bank_account_approved))
                    
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