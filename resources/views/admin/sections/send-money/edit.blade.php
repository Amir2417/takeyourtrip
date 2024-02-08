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
    ], 'active' => __("Send Money Gateway Edit")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("Send Money Gateway Edit") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" action="{{ setRoute('admin.send.money.gateway.update',$data->slug) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method("PUT")
                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label>{{ __("Name*") }}</label>
                        <div class="input-group append">
                            <span class="input-group-text"><i class="las la-key"></i></span>
                            <input type="text" class="form--control" name="name" value="{{ @$data->name }}">
                        </div>
                    </div>
                    @if ($data->slug == 'google-pay')
                    <div class="col-xl-12 col-lg-12 form-group">
                        <div class="row" >
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 form-group">
                                <label>{{ __("Gateway Name*") }}</label>
                                <div class="input-group append">
                                    <span class="input-group-text"><i class="las la-key"></i></span>
                                    <input type="text" class="form--control" name="gateway" value="{{ @$data->credentials->gateway }}">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 form-group">
                                <label>{{ __("Stripe Version*") }}</label>
                                <div class="input-group append">
                                    <span class="input-group-text"><i class="las la-hashtag"></i></span>
                                    <input type="text" class="form--control" name="stripe_version" value="{{ @$data->credentials->stripe_version }}">
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 form-group">
                                <label>{{ __("Stripe Publishable Key*") }}</label>
                                <div class="input-group append">
                                    <span class="input-group-text"><i class="las la-link"></i></span>
                                    <input type="text" class="form--control" name="stripe_publishable_key" value="{{ @$data->credentials->stripe_publishable_key }}">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 form-group">
                                <label>{{ __("Merchant Name*") }}</label>
                                <div class="input-group append">
                                    <span class="input-group-text"><i class="las la-link"></i></span>
                                    <input type="text" class="form--control" name="merchant_name" value="{{ @$data->credentials->merchant_name }}">
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 form-group">
                                <label>{{ __("Merchant ID*") }}</label>
                                <div class="input-group append">
                                    <span class="input-group-text"><i class="las la-link"></i></span>
                                    <input type="text" class="form--control" name="merchant_id" value="{{ @$data->credentials->merchant_id }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label for="card-image">{{ __("Image") }}</label>
                        <div class="col-12 col-sm-6 m-auto">
                            @include('admin.components.form.input-file',[
                                'label'         => false,
                                'class'         => "file-holder m-auto",
                                'old_files_path'    => files_asset_path('send-money-gateway'),
                                'name'          => "image",
                                'old_files'         => old('image',@$data->image)
                            ])
                        </div>
                    </div>

                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'text'          => "Update",
                            'permission'    => "admin.send.money.gateway.update"
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
