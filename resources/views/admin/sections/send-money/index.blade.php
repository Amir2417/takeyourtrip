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
    ], 'active' => __("Send Money Gateway")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("Send Money Gateway") }}</h5>
                
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ __("Name") }}</th>
                            <th>{{ __("Status") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($send_money ?? [] as $item)
                            <tr data-item="{{ $item }}">
                                <td>
                                    <ul class="user-list">
                                        <li><img src="{{ get_image($item->image,'send-money-gateway') }}" alt="image"></li>
                                    </ul>
                                </td>
                                <td>{{ $item->name }}</td>
                                <td>
                                    @include('admin.components.form.switcher',[
                                        'name'        => 'status',
                                        'value'       => $item->status,
                                        'options'     => [__('Enable') => 1, __('Disable') => 0],
                                        'onload'      => true,
                                        'data_target' => $item->id,
                                    ])
                                    
                                </td>
                                <td>
                                    @include('admin.components.link.edit-default',[
                                        'href'          => setRoute('admin.send.money.gateway.edit',$item->slug),
                                        'class'         => "edit-modal-button",
                                        'permission'    => "admin.send.money.gateway.edit",
                                    ])
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 3])
                        @endforelse
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        $(document).ready(function(){
            // Switcher
            switcherAjax("{{ setRoute('admin.send.money.gateway.status.update') }}");
        })
    </script>
@endpush
