<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="KataKata">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <title>KataKata — UNEB Revision</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .xp-bar { transition: width 0.6s cubic-bezier(.4,0,.2,1); }
        .option-btn { transition: all 0.2s; }
        .option-btn:hover { transform: translateY(-2px); }
        .level-badge { animation: pop 0.4s ease; }
        @keyframes pop { 0%{transform:scale(0.5);opacity:0} 80%{transform:scale(1.1)} 100%{transform:scale(1);opacity:1} }
        .progress-bar { transition: width 0.8s cubic-bezier(.4,0,.2,1); }
    </style>
</head>
<body class="bg-gradient-to-br from-yellow-50 to-green-50 min-h-screen">
    <nav class="bg-white shadow-sm border-b-4 border-yellow-400 px-4 py-3 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <span class="text-2xl font-black text-green-700">Kata<span class="text-yellow-500">Kata</span></span>
            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold">UNEB</span>
        </a>
        <div class="flex gap-3 text-sm font-semibold">
            <a href="{{ route('home') }}" class="text-gray-600 hover:text-green-700">Subjects</a>
            <a href="{{ route('mock') }}" class="text-gray-600 hover:text-blue-600">📝 Mock</a>
            <a href="{{ route('progress') }}" class="text-gray-600 hover:text-purple-600">📊 Progress</a>
            <a href="{{ route('leaderboard') }}" class="text-gray-600 hover:text-yellow-600">🏆</a>
        </div>
    </nav>
    <main class="max-w-3xl mx-auto px-4 py-8">
        @yield('content')
    </main>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>
</html>
