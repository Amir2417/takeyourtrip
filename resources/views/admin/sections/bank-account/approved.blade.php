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
    ], 'active' => __("Approved Account")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("Approved Account") }}</h5>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>{{ __("User Name") }}</th>
                            <th>{{ __("Bank Name") }}</th>
                            <th>{{ __("Status") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                            <tr>
                                <td><span>{{ $item->user->fullname ?? '' }}</span></td>
                                <td><span>{{ $item->bank->bank_name ?? '' }}</span></td>
                                
                                <td>
                                   
                                    @if ($item->status == bank_account_const()::PENDING)
                                        <span class="badge badge--primary">{{ __("Pending") }}</span>
                                    @elseif ($item->status == bank_account_const()::APPROVED)
                                        <span class="badge badge--success">{{ __("Approved") }}</span>
                                    @else
                                        <span class="badge badge--danger">{{ __("Rejected") }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ setRoute('admin.bank.account.details',$item->id) }}" class="btn btn--base btn--primary"><i class="las la-info-circle"></i></a>
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 6])
                        @endforelse
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
