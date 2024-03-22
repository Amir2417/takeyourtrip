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
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ __($page_title) }}</h5>
            
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("web_trx_id") }}</th>
                        <th>{{ __("Full Name") }}</th>
                        <th>{{ __("Email") }}</th>
                        <th>{{ __("request Amount") }}</th>
                        <th>{{ __("Method") }}</th>
                        <th>{{ __(("Status")) }}</th>
                        <th>{{ __("Time") }}</th>
                        <th>{{ __("Action") }}</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions  as $key => $item)

                        <tr>
                            <td>{{ $item->trx_id }}</td>
                            <td>
                                {{ $item->user->fullname }}
                            </td>
                            <td>
                               {{ $item->user->email ?? '' }}
                            </td>
                            

                            <td>{{ number_format($item->request_amount,2) }} {{ get_default_currency_code() }}</td>
                            <td><span class="text--info">{{ @$item->send_money_gateway->name }}</span></td>
                            <td>
                                <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                            </td>
                            <td>{{ $item->created_at->format('d-m-y h:i:s A') }}</td>
                            <td>
                                @include('admin.components.link.info-default',[
                                    'href'          => setRoute('admin.send.money.log.details', $item->trx_id),
                                    'permission'    => "admin.send.money.logs.details",
                                ])

                            </td>
                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 9])
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ get_paginate($transactions) }}
    </div>
</div>
@endsection

