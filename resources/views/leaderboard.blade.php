@extends('layouts.app')

@section('content')
<div class="text-center mb-8">
    <h2 class="text-3xl font-black text-green-700">🏆 Leaderboard</h2>
    <p class="text-gray-500 text-sm mt-1">Top students across all subjects</p>
</div>

<div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    @foreach($leaders as $i => $player)
    <div class="flex items-center px-6 py-4 {{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} {{ $i < 3 ? 'border-l-4 '.(['border-yellow-400','border-gray-400','border-orange-400'][$i]) : '' }}">
        <span class="w-8 font-black text-lg text-gray-400">
            @if($i === 0) 🥇
            @elseif($i === 1) 🥈
            @elseif($i === 2) 🥉
            @else {{ $i + 1 }}
            @endif
        </span>
        <span class="flex-1 font-semibold text-gray-800">{{ $player['name'] }}</span>
        <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-0.5 rounded-full mr-4">{{ $player['level'] }}</span>
        <span class="text-orange-500 font-semibold text-sm mr-4">🔥 {{ $player['streak'] }}</span>
        <span class="font-black text-green-600">{{ number_format($player['xp']) }} XP</span>
    </div>
    @endforeach
</div>

<div class="mt-6 text-center">
    <a href="{{ route('home') }}" class="inline-block bg-green-600 text-white px-8 py-3 rounded-full font-bold hover:bg-green-700 transition-colors">
        Start Revising →
    </a>
</div>
@endsection
