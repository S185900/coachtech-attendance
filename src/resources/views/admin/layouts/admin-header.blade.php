<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech-attendance</title>
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin-header.css')}}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap" rel="stylesheet">

    @yield('css')
</head>
<!-- 管理者ログイン後ヘッダー -->
<body>
    <header class="header">
        <a href="/" class="header-logo">
            <img class="header-logo-img" src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
        </a>

        <input type="checkbox" id="menu-toggle" class="menu-checkbox">

        <label for="menu-toggle" class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </label>

        <nav class="header-nav">
            <ul class="header-nav-list">
                <li class="header-nav-item"><a href="#" class="header-nav-link">勤怠一覧</a></li>
                <li class="header-nav-item"><a href="#" class="header-nav-link">スタッフ一覧</a></li>
                <li class="header-nav-item"><a href="#" class="header-nav-link">申請一覧</a></li>
                <li class="header-nav-item">
                    <form action="/admin/logout" method="POST">
                        @csrf
                        <button type="submit" class="header-nav-link logout-button">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>
    <main class="main-content">
        @yield('content')
    </main>
</body>
</html>