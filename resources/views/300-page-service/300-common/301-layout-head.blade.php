<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Plobin' }} - 서비스</title>

    @include('300-page-service.300-common.302-layout-css-imports')
    @include('300-page-service.300-common.303-layout-js-imports')

    @livewireStyles
</head>
