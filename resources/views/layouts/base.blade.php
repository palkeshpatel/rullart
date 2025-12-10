<!DOCTYPE html>
<html lang="en" @yield('html_attribute')>

<head>

    <meta charset="utf-8">
    <title>{{ $title }} | Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Admin Dashboard">
    <meta name="keywords" content="admin dashboard">

    <!-- App favicon -->
    <link rel="shortcut icon" href="/images/favicon.ico">        

    @include('layouts.partials/head-css')

</head>

<body @yield('body_attribute')>

    @yield('content')

    @include('layouts.partials/footer-scripts')

</body>

</html>