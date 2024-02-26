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
    ], 'active' => __("All Banks")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("All Banks") }}</h5>
                @include('admin.components.link.custom',[
                    'text'          => __("Add Bank"),
                    'class'         => 'btn btn--base',
                    'href'          => setRoute('admin.bank.create'),
                ])
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ __("Bank Name") }}</th>
                            <th>{{ __("Status") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($banks as $item)
                            <tr data-item="{{ $item }}">
                                <td><ul class="user-list">
                                    <li><img src="{{ get_image($item->image ?? '','bank') ?? ''}}" alt="" srcset=""></li>
                                </ul></td>
                                <td>{{ $item->bank_name }}</td>
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
                                        'href'          => setRoute('admin.bank.edit',$item->slug),
                                        'class'         => "edit-modal-button",
                                        'permission'    => "admin.bank.edit",
                                    ])
                                    <button class="btn btn--base btn--danger delete-modal-button" ><i class="las la-trash-alt"></i></button>
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
        $(".delete-modal-button").click(function(){
            var oldData     = JSON.parse($(this).parents("tr").attr("data-item"));
            var actionRoute = "{{ setRoute('admin.bank.delete') }}";
            var target      = oldData.id;
            var message     = `{{ __("Are you sure to") }} <strong>{{ __("delete") }}</strong> {{ __("this Bank?") }}`;

            openDeleteModal(actionRoute,target,message);

        });

        $(document).ready(function(){
            // Switcher
            switcherAjax("{{ setRoute('admin.bank.status.update') }}");
        })
    </script>
@endpush