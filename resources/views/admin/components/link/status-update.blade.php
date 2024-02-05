
@isset($permission)
    @if (admin_permission_by_name($permission))
        <a href="{{ $href ?? "" }}" class="btn--base {{ $class ?? "" }}"> {{ __($text ?? "") }}</a>
    @endif
@else
    <a href="{{ $href ?? "" }}" class="btn--base {{ $class ?? "" }}"> {{ __($text ?? "") }}</a>
@endisset
