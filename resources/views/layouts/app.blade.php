<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'NS & Beauty' }}</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    @yield('styles')
</head>

<body>

<div class="dashboard">
    @include('partials.sidebar')

    <main class="main">
        @yield('content')
    </main>
</div>

@yield('scripts')

</body>
</html>