@extends('admin.layouts.master')

@push('css')
@endpush

@section('page-title')
    @include('admin.components.page-title', ['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb', [
        'breadcrumbs' => [
            [
                'name' => __('Dashboard'),
                'url' => setRoute('admin.dashboard'),
            ],
        ],
        'active' => __('Merchant Care'),
    ])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("Email To Merchants") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" action="{{ setRoute('admin.merchants.email.merchants.send') }}" method="post">
                @csrf
                <div class="row mb-10-none">
                    <div class="col-xl-6 col-lg-6 form-group">
                        <label>{{ __("Merchant") }}*</label>
                        <select class="form--control nice-select" name="user_type">
                            <option selected disabled>{{ __("Select Merchants") }}</option>
                            <option value="all">{{ __("All Merchants") }}</option>
                            <option value="active">{{ __("Active Merchants") }}</option>
                            {{-- <option value="sma_verified">{{ __("SMS Unverified") }}</option> --}}
                            <option value="kyc_verified">{{ __("Kyc Unverified") }}</option>
                            <option value="banned">{{ __("Banned Merchants") }}</option>
                        </select>
                    </div>
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input',[
                            'label'         => __("Subject")."*",
                            'name'          => 'subject',
                            'value'         => old('subject'),
                        ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.input-text-rich',[
                             'label'         => __("Details")."*",
                            'name'          => 'message',
                            'value'         => old('message'),
                            'placeholder'   => __( "Write Here..."),
                        ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'permission'    => "admin.agents.email.users.send",
                            'text'          => __("Send Email"),
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
@endpush
