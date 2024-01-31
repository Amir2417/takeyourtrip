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
                    <form class="card-form add-recipient-item" action="{{ setRoute('user.bank.account.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="trx-inputs bt-view" style="display: block;">
                            <div class="row"> 
                                <div class="col-xl-12 col-lg-12 col-md-12 form-group">
                                    <label>{{ __("Select Bank") }}<span>*</span></label>
                                    <select class="form--control select2-basic" name="bank">
                                        <option selected disabled>{{ __("Select Bank") }}</option>
                                        @foreach ($banks as $item)
                                            <option value="{{ $item->id }}">{{ $item->bank_name }} </option>
                                        @endforeach 
                                    </select>
                                </div>
                                <div class="col-xl-12 col-lg-12 col-md-12 form-group bank-dynamic-fields">
                                    {{-- <label>{{ __("Select Bank") }}<span>*</span></label>
                                    <select class="form--control select2-basic" name="bank">
                                        <option selected disabled>{{ __("Select Bank") }}</option>
                                        @foreach ($banks as $item)
                                            <option value="{{ $item->id }}">{{ $item->bank_name }} </option>
                                        @endforeach 
                                    </select> --}}
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-12 col-lg-12">
                            <button type="submit" class="btn--base w-100">{{ __("Confirm") }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        $('select[name=bank]').on('change',function(){
            var value       = $(this).val();
            console.log(value);
        });
    </script>
@endpush