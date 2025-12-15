<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech-attendance</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    <link rel="stylesheet" href="{{ asset('css/guest.css')}}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">

    @yield('css')
</head>
<!-- ゲスト用ヘッダー -->
<body>
    <div class="guest-layout">
        <header class="header" role="banner">
            <a href="/" class="header-logo">
                <h1 class="header-logo-visually-hidden">
                COACHTECH
                </h1>
                <img class="header-logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
            </a>
        </header>
        <main class="main-content">
            @yield('content')
        </main>
    </div>
</body>
</html>