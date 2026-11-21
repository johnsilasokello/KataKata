@extends('layouts.app')

@section('content')
<div id="mock-app">

    {{-- Setup screen --}}
    <div id="setup-screen">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-black text-blue-700 mb-2">📝 UNEB Mock Exam</h2>
            <p class="text-gray-500">Simulate exam conditions. 40 questions. 60-minute timer.</p>
            <p class="text-sm text-yellow-600 font-semibold mt-1">Get your UNEB Readiness Score at the end.</p>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
            <h3 class="font-bold text-gray-700 mb-4">Select Exam Level</h3>
            <div class="flex gap-3">
                <button onclick="setMockLevel('o')" id="mock-btn-o"
                    class="flex-1 py-3 rounded-xl font-bold text-sm bg-green-600 text-white border-2 border-green-600">
                    O-Level
                </button>
                <button onclick="setMockLevel('a')" id="mock-btn-a"
                    class="flex-1 py-3 rounded-xl font-bold text-sm border-2 border-gray-200 text-gray-500">
                    A-Level
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
            <h3 class="font-bold text-gray-700 mb-2">Mock Exam Info</h3>
            <ul class="space-y-2 text-sm text-gray-600">
                <li>✅ 40 questions drawn from all subjects</li>
                <li>✅ Mix of easy, medium and hard questions</li>
                <li>⏱️ 60-minute countdown timer</li>
                <li>📊 Per-subject readiness score at end</li>
                <li>🏆 Top score saved to leaderboard</li>
            </ul>
        </div>

        <button onclick="startMock()"
            class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black text-lg hover:bg-blue-700 transition-colors">
            Start Mock Exam →
        </button>
    </div>

    {{-- Exam screen --}}
    <div id="exam-screen" class="hidden">
        {{-- Timer + progress --}}
        <div class="flex items-center justify-between mb-4 bg-white rounded-xl px-4 py-3 shadow-sm">
            <div class="flex items-center gap-2">
                <span class="text-2xl">⏱️</span>
                <span id="timer-display" class="text-2xl font-black text-blue-700">60:00</span>
            </div>
            <div class="text-sm text-gray-500">
                Q <span id="mock-q-num">1</span> / <span id="mock-q-total">40</span>
            </div>
            <div class="text-sm font-semibold text-green-600">
                ✅ <span id="mock-correct-count">0</span> correct
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="bg-gray-200 rounded-full h-2 mb-5 overflow-hidden">
            <div id="mock-progress-bar" class="progress-bar bg-blue-500 h-2 rounded-full" style="width:0%"></div>
        </div>

        {{-- Question --}}
        <div id="mock-question-card" class="bg-white rounded-2xl shadow-lg p-6 mb-4">
            <div id="mock-loading" class="flex justify-center py-10">
                <div class="w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
            <div id="mock-question-content" class="hidden">
                <div class="flex gap-2 mb-4">
                    <span id="mock-subject-badge" class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-semibold"></span>
                    <span id="mock-diff-badge" class="text-xs px-2 py-0.5 rounded-full font-semibold"></span>
                </div>
                <p id="mock-question-text" class="text-lg font-semibold text-gray-800 leading-relaxed mb-5"></p>
                <div id="mock-options-grid" class="grid grid-cols-1 gap-3"></div>
            </div>
        </div>

        {{-- Feedback (brief) --}}
        <div id="mock-feedback" class="hidden bg-white rounded-xl px-4 py-3 mb-4 border-l-4 text-sm font-semibold"></div>
    </div>

    {{-- Results screen --}}
    <div id="results-screen" class="hidden">
        <div class="text-center mb-6">
            <div class="text-5xl mb-3" id="result-emoji">🎓</div>
            <h2 class="text-3xl font-black text-blue-700 mb-1">UNEB Readiness Report</h2>
            <div class="text-5xl font-black text-green-600 my-3" id="overall-score">0%</div>
            <p class="text-gray-500 text-sm" id="result-message"></p>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-5 mb-5">
            <h3 class="font-bold text-gray-700 mb-4">Subject Breakdown</h3>
            <div id="subject-breakdown" class="space-y-3"></div>
        </div>

        <div class="bg-yellow-50 rounded-2xl border border-yellow-200 p-4 mb-5">
            <h3 class="font-bold text-yellow-800 mb-1">🎯 Focus Area</h3>
            <p id="focus-area" class="text-yellow-700 text-sm"></p>
        </div>

        <div class="flex gap-3">
            <button onclick="resetMock()" class="flex-1 bg-blue-600 text-white py-3 rounded-full font-bold hover:bg-blue-700">
                Try Again
            </button>
            <a href="{{ route('progress') }}" class="flex-1 text-center border-2 border-purple-400 text-purple-700 py-3 rounded-full font-bold hover:bg-purple-50">
                📊 My Progress
            </a>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let mockLevel    = 'o';
let mockQuestions = [];
let mockIndex    = 0;
let mockCorrect  = 0;
let subjectStats = {};
let timerInterval;
let secondsLeft  = 3600; // 60 min

function setMockLevel(l) {
    mockLevel = l;
    document.getElementById('mock-btn-o').className = l === 'o'
        ? 'flex-1 py-3 rounded-xl font-bold text-sm bg-green-600 text-white border-2 border-green-600'
        : 'flex-1 py-3 rounded-xl font-bold text-sm border-2 border-gray-200 text-gray-500';
    document.getElementById('mock-btn-a').className = l === 'a'
        ? 'flex-1 py-3 rounded-xl font-bold text-sm bg-yellow-500 text-white border-2 border-yellow-500'
        : 'flex-1 py-3 rounded-xl font-bold text-sm border-2 border-gray-200 text-gray-500';
}

async function startMock() {
    document.getElementById('setup-screen').classList.add('hidden');
    document.getElementById('exam-screen').classList.remove('hidden');
    document.getElementById('mock-loading').classList.remove('hidden');
    document.getElementById('mock-question-content').classList.add('hidden');

    const res = await fetch('{{ route("api.mock") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ level: mockLevel, count: 40 }),
    });
    const data = await res.json();
    mockQuestions = data.questions;
    document.getElementById('mock-q-total').textContent = mockQuestions.length;
    mockIndex   = 0;
    mockCorrect = 0;
    subjectStats = {};
    secondsLeft = 3600;

    startTimer();
    renderMockQuestion();
}

function startTimer() {
    updateTimerDisplay();
    timerInterval = setInterval(() => {
        secondsLeft--;
        updateTimerDisplay();
        if (secondsLeft <= 300) {
            document.getElementById('timer-display').classList.add('text-red-600');
            document.getElementById('timer-display').classList.remove('text-blue-700');
        }
        if (secondsLeft <= 0) {
            clearInterval(timerInterval);
            showMockResults();
        }
    }, 1000);
}

function updateTimerDisplay() {
    const m = Math.floor(secondsLeft / 60).toString().padStart(2, '0');
    const s = (secondsLeft % 60).toString().padStart(2, '0');
    document.getElementById('timer-display').textContent = `${m}:${s}`;
}

function renderMockQuestion() {
    document.getElementById('mock-feedback').classList.add('hidden');
    if (mockIndex >= mockQuestions.length) { showMockResults(); return; }

    const q = mockQuestions[mockIndex];
    document.getElementById('mock-loading').classList.add('hidden');
    document.getElementById('mock-question-content').classList.remove('hidden');
    document.getElementById('mock-q-num').textContent = mockIndex + 1;
    document.getElementById('mock-progress-bar').style.width = ((mockIndex / mockQuestions.length) * 100) + '%';

    const subjectNames = {
        biology:'Biology', history:'History', geography:'Geography',
        science:'Science', math:'Mathematics', english:'English',
        chemistry:'Chemistry', physics:'Physics', economics:'Economics'
    };
    document.getElementById('mock-subject-badge').textContent = subjectNames[q.subject] || q.subject;

    const diffColors = { easy: 'bg-green-50 text-green-600', medium: 'bg-yellow-50 text-yellow-600', hard: 'bg-red-50 text-red-600' };
    const diffBadge = document.getElementById('mock-diff-badge');
    diffBadge.textContent = q.difficulty ? q.difficulty.charAt(0).toUpperCase() + q.difficulty.slice(1) : 'Medium';
    diffBadge.className   = `text-xs px-2 py-0.5 rounded-full font-semibold ${diffColors[q.difficulty] || diffColors.medium}`;

    document.getElementById('mock-question-text').textContent = q.question;

    const grid = document.getElementById('mock-options-grid');
    grid.innerHTML = '';
    q.options.forEach((opt, i) => {
        const btn = document.createElement('button');
        btn.className = 'option-btn w-full text-left bg-gray-50 hover:bg-blue-50 border-2 border-gray-200 hover:border-blue-400 rounded-xl px-4 py-3 font-medium text-gray-700 transition-all';
        btn.textContent = String.fromCharCode(65 + i) + '. ' + opt;
        btn.onclick = () => selectMockAnswer(i);
        grid.appendChild(btn);
    });
}

function selectMockAnswer(index) {
    const q       = mockQuestions[mockIndex];
    const correct = index === q.answerIndex;

    document.querySelectorAll('#mock-options-grid .option-btn').forEach((b, i) => {
        b.disabled = true;
        if (i === q.answerIndex) b.className = b.className.replace('bg-gray-50 hover:bg-blue-50 border-gray-200 hover:border-blue-400', 'bg-green-100 border-green-500 text-green-800');
    });
    if (!correct) {
        document.querySelectorAll('#mock-options-grid .option-btn')[index].className =
            document.querySelectorAll('#mock-options-grid .option-btn')[index].className.replace('bg-gray-50 hover:bg-blue-50 border-gray-200 hover:border-blue-400', 'bg-red-100 border-red-500 text-red-800');
    }

    if (!subjectStats[q.subject]) subjectStats[q.subject] = { correct: 0, total: 0 };
    subjectStats[q.subject].total++;
    if (correct) { subjectStats[q.subject].correct++; mockCorrect++; }

    document.getElementById('mock-correct-count').textContent = mockCorrect;

    const fb = document.getElementById('mock-feedback');
    fb.classList.remove('hidden', 'border-green-500', 'border-red-400');
    fb.classList.add(correct ? 'border-green-500' : 'border-red-400');
    fb.className = `bg-white rounded-xl px-4 py-3 mb-4 border-l-4 text-sm font-semibold ${correct ? 'border-green-500 text-green-700' : 'border-red-400 text-red-700'}`;
    const expl = q.explanation || `Correct answer: ${q.options[q.answerIndex]}`;
    fb.textContent = (correct ? '✅ ' : '❌ ') + expl;

    setTimeout(() => {
        mockIndex++;
        if (mockIndex >= mockQuestions.length) { showMockResults(); }
        else { renderMockQuestion(); }
    }, 1800);
}

function showMockResults() {
    clearInterval(timerInterval);
    document.getElementById('exam-screen').classList.add('hidden');
    document.getElementById('results-screen').classList.remove('hidden');

    const total   = mockQuestions.length;
    const pct     = total > 0 ? Math.round((mockCorrect / total) * 100) : 0;
    document.getElementById('overall-score').textContent = pct + '%';

    const emojis = { 90:'🏆', 75:'🎓', 60:'📚', 50:'💪', 0:'📖' };
    const emoji  = Object.entries(emojis).reverse().find(([k]) => pct >= parseInt(k));
    document.getElementById('result-emoji').textContent = emoji ? emoji[1] : '📖';

    const messages = [
        [90, 'Excellent! You are very well prepared for UNEB. Keep it up!'],
        [75, 'Good performance! A little more revision and you are ready.'],
        [60, 'Fair performance. Focus on your weak subjects below.'],
        [50, 'You need more revision. Use KataKata daily to improve.'],
        [0,  'Keep studying! Consistency is the key to UNEB success.'],
    ];
    const msg = messages.find(([k]) => pct >= k);
    document.getElementById('result-message').textContent = msg ? msg[1] : '';

    // Subject breakdown
    const subjectNames = {
        biology:'Biology', history:'History', geography:'Geography',
        science:'Science', math:'Mathematics', english:'English',
        chemistry:'Chemistry', physics:'Physics', economics:'Economics'
    };
    const breakdown = document.getElementById('subject-breakdown');
    breakdown.innerHTML = '';
    let weakestSubject = '', weakestPct = 101;

    Object.entries(subjectStats).sort((a,b) => {
        const pA = a[1].total > 0 ? (a[1].correct/a[1].total) : 0;
        const pB = b[1].total > 0 ? (b[1].correct/b[1].total) : 0;
        return pB - pA;
    }).forEach(([subj, stats]) => {
        const p = stats.total > 0 ? Math.round((stats.correct / stats.total) * 100) : 0;
        if (p < weakestPct) { weakestPct = p; weakestSubject = subj; }
        const color = p >= 70 ? 'bg-green-500' : p >= 50 ? 'bg-yellow-500' : 'bg-red-500';
        breakdown.innerHTML += `
            <div>
                <div class="flex justify-between text-sm font-semibold mb-1">
                    <span>${subjectNames[subj] || subj}</span>
                    <span class="${p >= 70 ? 'text-green-600' : p >= 50 ? 'text-yellow-600' : 'text-red-600'}">${p}% (${stats.correct}/${stats.total})</span>
                </div>
                <div class="bg-gray-100 rounded-full h-3 overflow-hidden">
                    <div class="progress-bar ${color} h-3 rounded-full" style="width:${p}%"></div>
                </div>
            </div>`;
    });

    if (weakestSubject) {
        document.getElementById('focus-area').textContent =
            `Your weakest subject is ${subjectNames[weakestSubject] || weakestSubject} (${weakestPct}%). Focus revision here before your UNEB exam.`;
    }

    if (pct >= 70) bigConfetti();
}

function resetMock() {
    document.getElementById('results-screen').classList.add('hidden');
    document.getElementById('setup-screen').classList.remove('hidden');
}

function bigConfetti() {
    confetti({ particleCount: 150, spread: 90, origin: { y: 0.5 } });
    setTimeout(() => confetti({ particleCount: 100, spread: 70, origin: { x: 0.1, y: 0.5 } }), 300);
    setTimeout(() => confetti({ particleCount: 100, spread: 70, origin: { x: 0.9, y: 0.5 } }), 600);
}
</script>
@endsection
