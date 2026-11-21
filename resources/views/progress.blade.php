@extends('layouts.app')

@section('content')
<div class="text-center mb-8">
    <h2 class="text-3xl font-black text-purple-700">📊 My Progress</h2>
    <p class="text-gray-500 text-sm mt-1">Track your mastery across all UNEB subjects</p>
</div>

{{-- Overall stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 shadow-md text-center">
        <div class="text-3xl font-black text-green-600" id="total-xp">0</div>
        <div class="text-xs text-gray-400 mt-1">Total XP</div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow-md text-center">
        <div class="text-3xl font-black text-orange-500" id="best-streak">0</div>
        <div class="text-xs text-gray-400 mt-1">Best Streak 🔥</div>
    </div>
    <div class="bg-white rounded-2xl p-4 shadow-md text-center">
        <div class="text-3xl font-black text-purple-600" id="accuracy-pct">0%</div>
        <div class="text-xs text-gray-400 mt-1">Accuracy</div>
    </div>
</div>

{{-- Level progress --}}
<div class="bg-white rounded-2xl p-5 shadow-md mb-6">
    <div class="flex justify-between items-center mb-2">
        <div class="font-bold text-gray-700">Level Progress</div>
        <div class="text-sm font-black text-yellow-600" id="current-level-display">S1</div>
    </div>
    <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
        <div id="level-progress-bar" class="progress-bar bg-gradient-to-r from-green-500 to-yellow-400 h-4 rounded-full" style="width:0%"></div>
    </div>
    <div class="flex justify-between text-xs text-gray-400 mt-1">
        <span id="level-xp-label">0 XP</span>
        <span id="level-next-label">500 XP to S2</span>
    </div>
</div>

{{-- Level toggle --}}
<div class="flex justify-center mb-5">
    <div class="inline-flex bg-gray-100 rounded-full p-1 gap-1">
        <button onclick="showLevel('o')" id="prog-btn-o"
            class="px-5 py-1.5 rounded-full text-sm font-bold bg-green-600 text-white shadow">O-Level</button>
        <button onclick="showLevel('a')" id="prog-btn-a"
            class="px-5 py-1.5 rounded-full text-sm font-bold text-gray-500">A-Level</button>
    </div>
</div>

{{-- Subject mastery --}}
<div id="mastery-o" class="space-y-4">
    @foreach($subjects['o'] as $key => $sub)
    <div class="bg-white rounded-2xl p-5 shadow-md" id="card-o-{{ $key }}">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">{{ $sub['icon'] }}</span>
                <div>
                    <div class="font-bold text-gray-800">{{ $sub['name'] }}</div>
                    <div class="text-xs text-gray-400">O-Level</div>
                </div>
            </div>
            <div class="text-right">
                <div class="font-black text-lg" id="pct-o-{{ $key }}">—</div>
                <div class="text-xs text-gray-400" id="count-o-{{ $key }}">Not started</div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2">
            @foreach(['easy' => '🟢 Easy', 'medium' => '🟡 Medium', 'hard' => '🔴 Hard'] as $diff => $label)
            <div>
                <div class="text-xs text-gray-500 mb-1">{{ $label }}</div>
                <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="progress-bar h-2 rounded-full {{ $diff === 'easy' ? 'bg-green-400' : ($diff === 'hard' ? 'bg-red-400' : 'bg-yellow-400') }}"
                         id="bar-o-{{ $key }}-{{ $diff }}" style="width:0%"></div>
                </div>
                <div class="text-xs text-gray-400 mt-0.5" id="stat-o-{{ $key }}-{{ $diff }}">0/0</div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<div id="mastery-a" class="space-y-4 hidden">
    @foreach($subjects['a'] as $key => $sub)
    <div class="bg-white rounded-2xl p-5 shadow-md" id="card-a-{{ $key }}">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="text-2xl">{{ $sub['icon'] }}</span>
                <div>
                    <div class="font-bold text-gray-800">{{ $sub['name'] }}</div>
                    <div class="text-xs text-yellow-600 font-semibold">A-Level</div>
                </div>
            </div>
            <div class="text-right">
                <div class="font-black text-lg" id="pct-a-{{ $key }}">—</div>
                <div class="text-xs text-gray-400" id="count-a-{{ $key }}">Not started</div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2">
            @foreach(['easy' => '🟢 Easy', 'medium' => '🟡 Medium', 'hard' => '🔴 Hard'] as $diff => $label)
            <div>
                <div class="text-xs text-gray-500 mb-1">{{ $label }}</div>
                <div class="bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="progress-bar h-2 rounded-full {{ $diff === 'easy' ? 'bg-green-400' : ($diff === 'hard' ? 'bg-red-400' : 'bg-yellow-400') }}"
                         id="bar-a-{{ $key }}-{{ $diff }}" style="width:0%"></div>
                </div>
                <div class="text-xs text-gray-400 mt-0.5" id="stat-a-{{ $key }}-{{ $diff }}">0/0</div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

{{-- Weakest subject recommendation --}}
<div id="recommendation" class="hidden mt-6 bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
    <h3 class="font-bold text-yellow-800 mb-1">🎯 AI Recommendation</h3>
    <p id="rec-text" class="text-yellow-700 text-sm"></p>
    <a id="rec-link" href="#" class="inline-block mt-3 bg-yellow-500 text-white px-4 py-2 rounded-full text-sm font-semibold hover:bg-yellow-600">
        Start revising →
    </a>
</div>

<div class="mt-6 text-center">
    <button onclick="clearProgress()" class="text-xs text-gray-400 hover:text-red-500 underline">Reset all progress</button>
</div>

<script>
const oSubjects = @json(array_keys($subjects['o']));
const aSubjects = @json(array_keys($subjects['a']));

function showLevel(l) {
    document.getElementById('mastery-o').classList.toggle('hidden', l !== 'o');
    document.getElementById('mastery-a').classList.toggle('hidden', l !== 'a');
    document.getElementById('prog-btn-o').className = l === 'o'
        ? 'px-5 py-1.5 rounded-full text-sm font-bold bg-green-600 text-white shadow'
        : 'px-5 py-1.5 rounded-full text-sm font-bold text-gray-500';
    document.getElementById('prog-btn-a').className = l === 'a'
        ? 'px-5 py-1.5 rounded-full text-sm font-bold bg-yellow-500 text-white shadow'
        : 'px-5 py-1.5 rounded-full text-sm font-bold text-gray-500';
}

function loadProgress() {
    const progress  = JSON.parse(localStorage.getItem('katakataProgress') || '{}');
    const gameState = JSON.parse(localStorage.getItem('katakataState') || '{}');

    // Overall stats
    document.getElementById('total-xp').textContent      = gameState.xp || 0;
    document.getElementById('best-streak').textContent   = gameState.streak || 0;
    document.getElementById('current-level-display').textContent = gameState.level || 'S1';

    const xp         = gameState.xp || 0;
    const XP_PER_LVL = 500;
    const levelIdx   = Math.min(Math.floor(xp / XP_PER_LVL), 5);
    const levels     = ['S1','S2','S3','S4','S5','S6'];
    const pctInLevel = ((xp % XP_PER_LVL) / XP_PER_LVL) * 100;
    document.getElementById('level-progress-bar').style.width = pctInLevel + '%';
    document.getElementById('level-xp-label').textContent     = xp + ' XP';
    document.getElementById('level-next-label').textContent   = levelIdx < 5
        ? `${XP_PER_LVL - (xp % XP_PER_LVL)} XP to ${levels[levelIdx + 1]}`
        : 'Max level reached!';

    // Accuracy
    let totalCorrect = 0, totalAnswered = 0;
    Object.values(progress).forEach(v => { totalCorrect += v.correct; totalAnswered += v.total; });
    const acc = totalAnswered > 0 ? Math.round((totalCorrect / totalAnswered) * 100) : 0;
    document.getElementById('accuracy-pct').textContent = acc + '%';

    // Per-subject bars
    let weakestKey = '', weakestPct = 101, weakestLevel = 'o';

    ['o', 'a'].forEach(level => {
        const subjects = level === 'o' ? oSubjects : aSubjects;
        subjects.forEach(subj => {
            let subjectCorrect = 0, subjectTotal = 0;
            ['easy', 'medium', 'hard'].forEach(diff => {
                const key   = `${level}_${subj}_${diff}`;
                const stats = progress[key] || { correct: 0, total: 0 };
                const pct   = stats.total > 0 ? Math.round((stats.correct / stats.total) * 100) : 0;
                const bar   = document.getElementById(`bar-${level}-${subj}-${diff}`);
                const stat  = document.getElementById(`stat-${level}-${subj}-${diff}`);
                if (bar)  bar.style.width  = pct + '%';
                if (stat) stat.textContent = `${stats.correct}/${stats.total}`;
                subjectCorrect += stats.correct;
                subjectTotal   += stats.total;
            });
            const subjPct = subjectTotal > 0 ? Math.round((subjectCorrect / subjectTotal) * 100) : null;
            const pctEl   = document.getElementById(`pct-${level}-${subj}`);
            const countEl = document.getElementById(`count-${level}-${subj}`);
            if (pctEl)  pctEl.textContent   = subjPct !== null ? subjPct + '%' : '—';
            if (pctEl)  pctEl.className      = `font-black text-lg ${subjPct !== null ? (subjPct >= 70 ? 'text-green-600' : subjPct >= 50 ? 'text-yellow-600' : 'text-red-600') : 'text-gray-300'}`;
            if (countEl) countEl.textContent = subjectTotal > 0 ? `${subjectCorrect}/${subjectTotal} answered` : 'Not started';

            if (subjPct !== null && subjPct < weakestPct) {
                weakestPct   = subjPct;
                weakestKey   = subj;
                weakestLevel = level;
            }
        });
    });

    // Recommendation
    if (weakestKey && weakestPct < 80) {
        const subjectNamesMap = {
            biology:'Biology', history:'History', geography:'Geography',
            science:'Science', math:'Mathematics', english:'English',
            chemistry:'Chemistry', physics:'Physics', economics:'Economics'
        };
        document.getElementById('recommendation').classList.remove('hidden');
        document.getElementById('rec-text').textContent =
            `You are scoring ${weakestPct}% in ${subjectNamesMap[weakestKey] || weakestKey} (${weakestLevel.toUpperCase()}-Level). This is your weakest area — revise it now to improve your UNEB readiness.`;
        document.getElementById('rec-link').href =
            `/quiz?subject=${weakestKey}&level=${weakestLevel}&difficulty=medium`;
    }
}

function clearProgress() {
    if (confirm('Reset all progress and XP? This cannot be undone.')) {
        localStorage.removeItem('katakataProgress');
        localStorage.removeItem('katakataState');
        location.reload();
    }
}

loadProgress();
</script>
@endsection
