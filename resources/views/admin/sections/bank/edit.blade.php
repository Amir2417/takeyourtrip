@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("Edit Bank Method")])
@endsection

@section('content')
<form action="{{ setRoute('admin.bank.update',$bank->slug) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ $page_title }}</h6>
        </div>
        <div class="card-body">
            <div class="row mb-10-none">
                <div class="col-xl-3 col-lg-3 form-group">
                    @include('admin.components.form.input-file',[
                        'label'             => false,
                        'class'             => "file-holder",
                        'name'              => "image",
                        'old_files_path'    => files_asset_path("bank"),
                        'old_files'         => old("image",$bank->image),
                        'value'             => $bank->image
                    ])
                </div>
                <div class="col-xl-9 col-lg-9">
                    <div class="form-group">
                        @include('admin.components.form.input',[
                            'label'         => __("Bank Name*"),
                            'name'          => "bank_name",
                            'value'         => old('bank_name',$bank->bank_name),
                        ])
                    </div>
                    <div class="form-group">
                        @include('admin.components.form.input',[
                            'label'         => __("Currency Name*"),
                            'name'          => "currency_name",
                            'value'         => old("currency_name",$bank->currency_name),
                        ])
                    </div>
                    <div class="form-group">
                        @include('admin.components.form.input',[
                        'label'     =>__( "Currency Code")."*",
                            'name'          => "currency_code",
                            'value'         => old('currency_code',$bank->currency_code),
                        ])
                    </div>
                    <div class="form-group">
                        @include('admin.components.form.input',[
                           'label'     =>__( "Currency Symbol")."*",
                            'name'          => "currency_symbol",
                            'value'         => old('currency_symbol',$bank->currency_symbol),
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="custom-card mt-15">
        <div class="card-body">
            <div class="row">
                <div class="col-xl-4 col-lg-4 mb-10">
                    <div class="custom-inner-card">
                        <div class="card-inner-header">
                            <h5 class="title">{{ __("Amount Limit") }}</h5>
                        </div>
                        <div class="card-inner-body">
                            <div class="row">
                                <div class="col-xl-6 col-lg-6">
                                    <div class="form-group">
                                        @include('admin.components.form.input-amount',[
                                              'label'         => __("Minimum")."*",
                                            'name'          => "min_limit",
                                            'value'         => old("min_limit",$bank->min_limit),
                                            'currency'      => $bank->currency_code,
                                        ])
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="form-group">
                                        @include('admin.components.form.input-amount',[
                                             'label'         => __("Maximum")."*",
                                            'name'          => "max_limit",
                                            'value'         => old("max_limit",$bank->max_limit),
                                            'currency'      => $bank->currency_code,
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 mb-10">
                    <div class="custom-inner-card">
                        <div class="card-inner-header">
                            <h5 class="title">{{ __("Charges") }}</h5>
                        </div>
                        <div class="card-inner-body">
                            <div class="row">
                                <div class="col-xl-6 col-lg-6">
                                    <div class="form-group">
                                        @include('admin.components.form.input-amount',[
                                            'label'         => __("Fixed")."*",
                                            'name'          => "fixed_charge",
                                            'value'         => old("fixed_charge",$bank->fixed_charge),
                                            'currency'      => $bank->currency_code,
                                        ])
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6">
                                    <div class="form-group">
                                        @include('admin.components.form.input-amount',[
                                            'label'         => __("Percent")."*",
                                            'name'          => "percent_charge",
                                            'value'         => old("percent_charge",$bank->percent_charge),
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 mb-10">
                    <div class="custom-inner-card">
                        <div class="card-inner-header">
                            <h5 class="title">{{ __("Rate") }}</h5>
                        </div>
                        <div class="card-inner-body">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12">
                                    <div class="form-group">
                                        <label>{{ __("Rate") }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text append ">1 &nbsp; <span class="default-currency">{{ get_default_currency_code($default_currency) }}</span>&nbsp; = </span>
                                            <input type="te" class="form--control" value="{{ old("rate",$bank->rate) }}" name="rate" placeholder="Type Here...">
                                            <span class="input-group-text currency">{{ $bank->currency_code }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input-text-rich',[
                       'label'     =>__( "Instructions")."*",
                        'name'      => "desc",
                        'value'     => old("desc",$bank->desc),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    <div class="custom-inner-card input-field-generator" data-source="manual_gateway_input_fields">
                        <div class="card-inner-header">
                            <h6 class="title">{{ __("Collect Data") }}</h6>
                            <button type="button" class="btn--base add-row-btn"><i class="fas fa-plus"></i> {{ __("Add") }}</button>
                        </div>
                        <div class="card-inner-body">
                            <div class="results">
                                @foreach ($bank->input_fields as $item)
                                    <div class="row add-row-wrapper align-items-end">
                                        <div class="col-xl-3 col-lg-3 form-group">
                                            @include('admin.components.form.input',[
                                                'label'     =>__( "Field Name*"),
                                                'name'      => "label[]",
                                                'attribute' => "required",
                                                'value'     => $item->label,
                                            ])
                                        </div>
                                        <div class="col-xl-2 col-lg-2 form-group">
                                            @php
                                                $selectOptions = ['text' => "Input Text", 'file' => "File", 'textarea' => "Textarea"];
                                            @endphp
                                            <label>{{ __("Field Types*") }}</label>
                                            <select class="form--control nice-select field-input-type" name="input_type[]" data-old="{{ $item->type }}">
                                                @foreach ($selectOptions as $key => $value)
                                                    <option value="{{ $key }}" {{ ($key == $item->type) ? "selected" : "" }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="field_type_input col-lg-4 col-xl-4">
                                            @if ($item->type == "file")
                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6 form-group">
                                                        @include('admin.components.form.input',[
                                                            'label'         => __("Max File Size (mb)*"),
                                                            'name'          => "file_max_size[]",
                                                            'type'          => "number",
                                                            'attribute'     => "required",
                                                            'value'         => old('file_max_size[]',$item->validation->max),
                                                            'placeholder'   => __("ex").": 10",
                                                        ])
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6 form-group">
                                                        @include('admin.components.form.input',[
                                                            'label'         => __("File Extension*"),
                                                            'name'          => "file_extensions[]",
                                                            'attribute'     => "required",
                                                            'value'         => old('file_extensions[]',implode(",",$item->validation->mimes)),
                                                            'placeholder'   => __("ex").": jpg, png, pdf",
                                                        ])
                                                    </div>
                                                </div>
                                            @else
                                                <div class="row">
                                                    <div class="col-xl-6 col-lg-6 form-group">
                                                        @include('admin.components.form.input',[
                                                            'label'         => __("Min Character*"),
                                                            'name'          => "min_char[]",
                                                            'type'          => "number",
                                                            'attribute'     => "required",
                                                            'value'         => old('min_char[]',$item->validation->min),
                                                            'placeholder'   => __("ex").": 6",
                                                        ])
                                                    </div>
                                                    <div class="col-xl-6 col-lg-6 form-group">
                                                        @include('admin.components.form.input',[
                                                            'label'         => __("Max Character*"),
                                                            'name'          => "max_char[]",
                                                            'type'          => "number",
                                                            'attribute'     => "required",
                                                            'value'         => old('max_char[]',$item->validation->max),
                                                            'placeholder'   => __("ex").": 16",
                                                        ])
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="col-xl-2 col-lg-2 form-group">
                                            @include('admin.components.form.switcher',[
                                                'label'     => __("Field Necessity*"),
                                                'name'      => "field_necessity[]",
                                                'options'   => ['Required' => 1,'Optional' => 0],
                                                'value'     => old("field_necessity[]",$item->required),
                                            ])
                                        </div>
                                        <div class="col-xl-1 col-lg-1 form-group">
                                            <button type="button" class="custom-btn btn--base btn--danger row-cross-btn w-100 btn-loading"><i class="las la-times"></i></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-10-none">
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.button.form-btn',[
                        'class'         => "w-100",
                        'text'          => __("update"),
                        'permission'    => "admin.payment.gateway.update",
                    ])
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

