<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body class="bg-gray-100">

<div class="container mx-auto mt-10">
    @yield('content')
</div>
<script>
    window.userId = @json(auth()->id());
</script>
@yield('scripts')
</body>

</html>
