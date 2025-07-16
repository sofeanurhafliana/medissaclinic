<!DOCTYPE html>
<html>
<head>
    <title>Medissa Clinic</title>
    
    <!-- Boxicons CDN -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Load Vite CSS -->
    @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/css/auth.css', 'resources/css/dashboard.css','resources/css/doctor.css', 'resources/css/settings.css'])

    <!-- Additional page-specific styles -->
    @stack('styles')
</head>
<body>

    @if (session('status'))
        <div class="flash-message">
            {{ session('status') }}
        </div>
    @endif

    <div class="page-content">
        {{ $slot }}
    </div>

</body>
</html>
