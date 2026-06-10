<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login — Todak Attendace')</title>
    <style>
        :root {
            --primary: #0f4c81;
            --primary-dark: #0b2742;
            --accent: #1d9bf0;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #dbe4f0;
            --bg: #f8fbff;
            --white: #ffffff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--ink);
        }

        button,
        input {
            font: inherit;
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
