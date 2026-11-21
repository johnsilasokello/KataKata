@extends('layouts.app')

@section('content')
<div class="text-center mb-10">
    <h1 class="text-5xl font-black text-green-700 mb-2">Kata<span class="text-yellow-500">Kata</span></h1>
    <p class="text-xl text-gray-600 font-medium">Master your UNEB exams. Level up. Beat the leaderboard.</p>
    <p class="text-sm text-gray-400 mt-1">AI-powered questions • XP & streaks • Luganda explanations</p>
</div>

<div class="grid grid-cols-2 gap-5">
    @foreach($categories as $key => $cat)
    <a href="{{ route('quiz', ['subject' => $key]) }}"
       class="group bg-white rounded-2xl p-6 shadow-md border-2 border-transparent hover:border-yellow-400 hover:shadow-xl transition-all cursor-pointer">
        <div class="text-4xl mb-3">
            @if($key === 'biology') 🧬
            @elseif($key === 'history') 📜
            @elseif($key === 'geography') 🗺️
            @else ⚗️
            @endif
        </div>
        <h3 class="text-xl font-bold text-gray-800 group-hover:text-green-700">{{ $cat['name'] }}</h3>
        <p class="text-sm text-gray-400 mt-1">UNEB O-level / A-level</p>
        <div class="mt-4 inline-block bg-green-600 text-white text-sm px-4 py-1.5 rounded-full font-semibold group-hover:bg-yellow-500 transition-colors">
            Start Quiz →
        </div>
    </a>
    @endforeach
</div>

<div class="mt-8 bg-white rounded-2xl p-5 shadow-md border border-green-100">
    <h3 class="font-bold text-gray-700 mb-3">🏆 Top Players</h3>
    @php
        $leaders = json_decode(file_get_contents(base_path('data/leaderboard.json')), true);
    @endphp
    <div class="space-y-2">
        @foreach(array_slice($leaders, 0, 3) as $i => $p)
        <div class="flex items-center justify-between text-sm">
            <span class="font-semibold text-gray-700">
                {{ ['🥇','🥈','🥉'][$i] }} {{ $p['name'] }}
            </span>
            <span class="text-green-600 font-bold">{{ number_format($p['xp']) }} XP</span>
        </div>
        @endforeach
    </div>
    <a href="{{ route('leaderboard') }}" class="text-xs text-yellow-600 font-semibold mt-2 inline-block hover:underline">View full leaderboard →</a>
</div>
@endsection
