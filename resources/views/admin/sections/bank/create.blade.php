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
    ], 'active' => __("Bank Create")])
@endsection

@section('content')
<div class="custom-card">
    <div class="card-header">
        <h6 class="title">{{ __($page_title) }}</h6>
    </div>
    <div class="card-body">
        <form class="card-form" action="{{ setRoute('admin.bank.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="row justify-content-center mb-10-none">
                <div class="col-xl-12 col-lg-12 form-group">
                    <label>{{ __("Image") }}</label>
                    <div class="col-12 col-sm-3 m-auto">
                        @include('admin.components.form.input-file',[
                            'label'         => false,
                            'class'         => "file-holder",
                            'name'          => "image",
                            'value'         => old("image"),
                        ])
                    </div>
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input',[
                        'label'     => __("Bank Name")."*",
                        'name'      => "bank_name",
                        'value'     => old("bank_name"),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input',[
                        'label'     => __("Bank Name")."*",
                        'name'      => "bank_name",
                        'value'     => old("bank_name"),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input-text-rich',[
                        'label'     => __("Instruction"),
                        'name'      => "desc",
                        'value'     => old("desc"),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.payment-gateway.manual.input-field-generator')
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.button.form-btn',[
                        'class'         => "w-100 btn-loading",
                        'text'          => __("Submit"),
                        'permission'    => "admin.bank.store"
                    ])
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
