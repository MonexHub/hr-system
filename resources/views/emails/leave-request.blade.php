@component('mail::message')
    # {{ $title }}

    {!! $content !!}

    @if(isset($details))
        @component('mail::panel')
            {{ $details }}
        @endcomponent
    @endif

    @if(isset($actionText))
        @component('mail::button', ['url' => $actionUrl, 'color' => $actionColor ?? 'primary'])
            {{ $actionText }}
        @endcomponent
    @endif

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent
