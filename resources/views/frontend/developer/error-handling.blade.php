@extends('frontend.layouts.developer_master')

@php
    $lang = selectedLang();
@endphp

@section('content')
<div class="developer-body-wrapper">
    <div class="developer-main-wrapper">
        <h1 class="heading-title mb-20">Error Handling</h1>
        <p>In case of an error, the API will return an error response containing a specific error code <strong>400, 403 Failed</strong> and a user-friendly message. Refer to our API documentation for a comprehensive list of error codes and their descriptions.</p>
    </div>
    <div class="page-change-area">
        <div class="navigation-wrapper">
            <a href="{{ setRoute("developer.response.code") }}" class="left"><i class="las la-arrow-left me-1"></i> Response Codes</a>
            <a href="{{ setRoute("developer.best.practices") }}" class="right">Best Practices <i class="las la-arrow-right ms-1"></i></a>
        </div>
    </div>
</div>
@endsection


@push("script")

@endpush
