@extends('layouts.app')

@section('content')
<div class="text-center mb-8">
    <h1 class="text-5xl font-black text-green-700 mb-2">Kata<span class="text-yellow-500">Kata</span></h1>
    <p class="text-xl text-gray-600 font-medium">Master your UNEB exams. Level up. Beat the leaderboard.</p>
    <p class="text-sm text-gray-400 mt-1">AI-powered questions • XP & streaks • Luganda explanations</p>
</div>

{{-- Level toggle --}}
<div class="flex justify-center mb-6">
    <div class="inline-flex bg-gray-100 rounded-full p-1 gap-1">
        <button onclick="setLevel('o')" id="btn-o"
            class="level-tab px-6 py-2 rounded-full text-sm font-bold transition-all bg-green-600 text-white shadow">
            O-Level
        </button>
        <button onclick="setLevel('a')" id="btn-a"
            class="level-tab px-6 py-2 rounded-full text-sm font-bold transition-all text-gray-500 hover:text-gray-700">
            A-Level
        </button>
    </div>
</div>

{{-- Difficulty selector --}}
<div class="flex justify-center gap-3 mb-7">
    @foreach(['easy' => ['🟢','30 XP'], 'medium' => ['🟡','50 XP'], 'hard' => ['🔴','80 XP']] as $d => $meta)
    <button onclick="setDifficulty('{{ $d }}')" id="btn-diff-{{ $d }}"
        class="diff-tab px-4 py-2 rounded-full border-2 text-sm font-semibold transition-all
               {{ $d === 'medium' ? 'border-yellow-400 bg-yellow-50 text-yellow-700' : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
        {{ $meta[0] }} {{ ucfirst($d) }} <span class="text-xs opacity-70">+{{ $meta[1] }}</span>
    </button>
    @endforeach
</div>

{{-- Subject cards --}}
<div id="subjects-o" class="grid grid-cols-2 gap-4">
    @foreach($subjects['o'] as $key => $sub)
    <a href="#" onclick="startQuiz('{{ $key }}'); return false;"
       class="group bg-white rounded-2xl p-5 shadow-md border-2 border-transparent hover:border-yellow-400 hover:shadow-xl transition-all cursor-pointer">
        <div class="text-4xl mb-2">{{ $sub['icon'] }}</div>
        <h3 class="text-lg font-bold text-gray-800 group-hover:text-green-700">{{ $sub['name'] }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">UNEB O-Level</p>
        <div class="mt-3 inline-block bg-green-600 text-white text-xs px-3 py-1 rounded-full font-semibold group-hover:bg-yellow-500 transition-colors">
            Start →
        </div>
    </a>
    @endforeach
</div>

<div id="subjects-a" class="grid grid-cols-2 gap-4 hidden">
    @foreach($subjects['a'] as $key => $sub)
    <a href="#" onclick="startQuiz('{{ $key }}'); return false;"
       class="group bg-white rounded-2xl p-5 shadow-md border-2 border-transparent hover:border-yellow-400 hover:shadow-xl transition-all cursor-pointer">
        <div class="text-4xl mb-2">{{ $sub['icon'] }}</div>
        <h3 class="text-lg font-bold text-gray-800 group-hover:text-green-700">{{ $sub['name'] }}</h3>
        <p class="text-xs text-gray-400 mt-0.5">UNEB A-Level <span class="text-yellow-500 font-semibold">+20 XP bonus</span></p>
        <div class="mt-3 inline-block bg-green-600 text-white text-xs px-3 py-1 rounded-full font-semibold group-hover:bg-yellow-500 transition-colors">
            Start →
        </div>
    </a>
    @endforeach
</div>

{{-- Mini leaderboard --}}
<div class="mt-8 bg-white rounded-2xl p-5 shadow-md border border-green-100">
    <h3 class="font-bold text-gray-700 mb-3">🏆 Top Players</h3>
    @php $leaders = json_decode(file_get_contents(base_path('data/leaderboard.json')), true); @endphp
    <div class="space-y-2">
        @foreach(array_slice($leaders, 0, 3) as $i => $p)
        <div class="flex items-center justify-between text-sm">
            <span class="font-semibold text-gray-700">{{ ['🥇','🥈','🥉'][$i] }} {{ $p['name'] }}</span>
            <span class="text-green-600 font-bold">{{ number_format($p['xp']) }} XP</span>
        </div>
        @endforeach
    </div>
    <a href="{{ route('leaderboard') }}" class="text-xs text-yellow-600 font-semibold mt-2 inline-block hover:underline">View full leaderboard →</a>
</div>

<script>
let activeLevel = 'o';
let activeDiff  = 'medium';

function setLevel(l) {
    activeLevel = l;
    document.getElementById('subjects-o').classList.toggle('hidden', l !== 'o');
    document.getElementById('subjects-a').classList.toggle('hidden', l !== 'a');
    document.getElementById('btn-o').className = l === 'o'
        ? 'level-tab px-6 py-2 rounded-full text-sm font-bold transition-all bg-green-600 text-white shadow'
        : 'level-tab px-6 py-2 rounded-full text-sm font-bold transition-all text-gray-500 hover:text-gray-700';
    document.getElementById('btn-a').className = l === 'a'
        ? 'level-tab px-6 py-2 rounded-full text-sm font-bold transition-all bg-yellow-500 text-white shadow'
        : 'level-tab px-6 py-2 rounded-full text-sm font-bold transition-all text-gray-500 hover:text-gray-700';
}

function setDifficulty(d) {
    activeDiff = d;
    document.querySelectorAll('.diff-tab').forEach(b => {
        b.className = 'diff-tab px-4 py-2 rounded-full border-2 text-sm font-semibold transition-all border-gray-200 text-gray-500 hover:border-gray-300';
    });
    const active = document.getElementById('btn-diff-' + d);
    const colorMap = { easy: 'border-green-400 bg-green-50 text-green-700', medium: 'border-yellow-400 bg-yellow-50 text-yellow-700', hard: 'border-red-400 bg-red-50 text-red-700' };
    active.className = 'diff-tab px-4 py-2 rounded-full border-2 text-sm font-semibold transition-all ' + colorMap[d];
}

function startQuiz(subject) {
    window.location.href = `/quiz?subject=${subject}&level=${activeLevel}&difficulty=${activeDiff}`;
}
</script>
@endsection
