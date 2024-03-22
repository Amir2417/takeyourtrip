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
    ], 'active' => __($page_title)])
@endsection

@section('content')
<form action="{{ setRoute('admin.bank.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('admin.components.bank-method.gateway-header',['title' => __($page_title)])
    <div class="custom-card mt-15">
        <div class="card-body">
            <div class="row">
                @include('admin.components.payment-gateway.manual.charges')
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.form.input-text-rich',[
                         'label'     =>__( "Instructions")."*",
                        'name'      => "desc",
                        'value'     => old("desc"),
                    ])
                </div>
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.payment-gateway.manual.input-field-generator')
                </div>
            </div>
            <div class="row mb-10-none">
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.button.form-btn',[
                        'class'         => "w-100 btn-loading",
                        'text'          => "Add",
                        'permission'    => "admin.payment.gateway.store",
                    ])
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
