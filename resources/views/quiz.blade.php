@extends('layouts.app')

@section('content')
<div id="app" data-subject="{{ $subject }}" data-level="{{ $level }}" data-difficulty="{{ $difficulty }}" data-xp="{{ $xpPerQ }}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-2xl font-black text-green-700">{{ $subjectName }}</h2>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $level === 'a' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">
                    {{ strtoupper($level) }}-Level
                </span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full
                    {{ $difficulty === 'easy' ? 'bg-green-50 text-green-600' : ($difficulty === 'hard' ? 'bg-red-50 text-red-600' : 'bg-yellow-50 text-yellow-600') }}">
                    {{ ucfirst($difficulty) }}
                </span>
            </div>
            <p class="text-xs text-gray-400">+{{ $xpPerQ }} XP per correct answer</p>
        </div>
        <div class="flex gap-4 items-center">
            <div class="text-center">
                <div class="text-2xl font-black text-orange-500">🔥 <span id="streak-count">0</span></div>
                <div class="text-xs text-gray-400">streak</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-black text-green-600" id="xp-display">0</div>
                <div class="text-xs text-gray-400">XP</div>
            </div>
            <div class="text-center">
                <div class="level-badge text-sm font-bold bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full" id="level-badge">S1</div>
                <div class="text-xs text-gray-400">level</div>
            </div>
        </div>
    </div>

    {{-- XP Bar --}}
    <div class="bg-gray-200 rounded-full h-3 mb-5 overflow-hidden">
        <div id="xp-bar" class="xp-bar bg-gradient-to-r from-green-500 to-yellow-400 h-3 rounded-full" style="width:0%"></div>
    </div>

    {{-- Question card --}}
    <div id="question-card" class="bg-white rounded-2xl shadow-lg p-6 mb-4 min-h-48">
        <div id="loading" class="flex flex-col items-center justify-center h-32 gap-3">
            <div class="w-8 h-8 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
            <p class="text-gray-400 text-sm">Loading question...</p>
        </div>
        <div id="question-content" class="hidden">
            <div class="flex items-start gap-2 mb-5">
                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-semibold mt-0.5" id="q-number">Q1</span>
                <p id="question-text" class="text-lg font-semibold text-gray-800 leading-relaxed"></p>
            </div>
            <div id="options-grid" class="grid grid-cols-1 gap-3"></div>
        </div>
    </div>

    {{-- Feedback / Explanation --}}
    <div id="feedback-card" class="hidden bg-white rounded-2xl shadow-md p-5 mb-4 border-l-4">
        <div class="flex items-center gap-2 mb-2">
            <span id="feedback-icon" class="text-2xl"></span>
            <span id="feedback-title" class="font-bold text-lg"></span>
        </div>
        <p id="explanation-text" class="text-gray-600 text-sm leading-relaxed"></p>
        <div class="flex gap-3 mt-4">
            <button onclick="nextQuestion()"
                class="bg-green-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-green-700 transition-colors text-sm">
                Next Question →
            </button>
            <button onclick="toggleLuganda()" id="luganda-btn"
                class="border border-green-300 text-green-700 px-4 py-2 rounded-full font-semibold hover:bg-green-50 transition-colors text-sm">
                🇺🇬 Luganda
            </button>
        </div>
    </div>

    {{-- Score summary --}}
    <div id="score-summary" class="hidden bg-white rounded-2xl shadow-lg p-6 text-center">
        <div class="text-5xl mb-3">🎉</div>
        <h3 class="text-2xl font-black text-green-700 mb-1">Round Complete!</h3>
        <p class="text-gray-500 mb-4">You answered <span id="summary-correct" class="font-bold text-green-600"></span> / 10 correctly</p>
        <div class="flex gap-3 justify-center">
            <button onclick="restartQuiz()" class="bg-green-600 text-white px-6 py-2.5 rounded-full font-semibold hover:bg-green-700">
                Play Again
            </button>
            <a href="{{ route('home') }}" class="border-2 border-gray-200 text-gray-600 px-6 py-2.5 rounded-full font-semibold hover:bg-gray-50">
                Change Subject
            </a>
            <a href="{{ route('leaderboard') }}" class="border-2 border-yellow-400 text-yellow-700 px-6 py-2.5 rounded-full font-semibold hover:bg-yellow-50">
                🏆 Leaderboard
            </a>
        </div>
    </div>

    <div class="text-center text-xs text-gray-400 mt-2">
        Question <span id="q-counter">1</span> of 10
    </div>
</div>

<script>
const el      = document.getElementById('app').dataset;
const SUBJECT = el.subject;
const LEVEL   = el.level;
const DIFF    = el.difficulty;
const XP_PER  = parseInt(el.xp);
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;

let state = JSON.parse(localStorage.getItem('katakataState') || '{}');
state = Object.assign({ xp: 0, streak: 0, level: 'S1', qNum: 1, correct: 0 }, state);

let currentQ    = null;
let answered    = false;
let lugandaMode = false;

const LEVELS       = ['S1','S2','S3','S4','S5','S6'];
const XP_PER_LEVEL = 500;

function xpToLevel(xp) {
    return LEVELS[Math.min(Math.floor(xp / XP_PER_LEVEL), LEVELS.length - 1)];
}
function xpProgress(xp) {
    return ((xp % XP_PER_LEVEL) / XP_PER_LEVEL) * 100;
}
function saveState() { localStorage.setItem('katakataState', JSON.stringify(state)); }

function updateUI() {
    document.getElementById('xp-display').textContent   = state.xp;
    document.getElementById('streak-count').textContent = state.streak;
    document.getElementById('level-badge').textContent  = state.level;
    document.getElementById('xp-bar').style.width       = xpProgress(state.xp) + '%';
    document.getElementById('q-counter').textContent    = state.qNum;
}

async function loadQuestion() {
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('question-content').classList.add('hidden');
    document.getElementById('feedback-card').classList.add('hidden');
    document.getElementById('score-summary').classList.add('hidden');
    document.getElementById('question-card').classList.remove('hidden');
    answered = false;

    try {
        const res = await fetch('{{ route("api.question") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ subject: SUBJECT, level: LEVEL, difficulty: DIFF }),
        });
        currentQ = await res.json();
    } catch(e) { return; }

    document.getElementById('loading').classList.add('hidden');
    document.getElementById('question-content').classList.remove('hidden');
    document.getElementById('question-text').textContent = currentQ.question;
    document.getElementById('q-number').textContent = 'Q' + state.qNum;

    const grid = document.getElementById('options-grid');
    grid.innerHTML = '';
    currentQ.options.forEach((opt, i) => {
        const btn = document.createElement('button');
        btn.className = 'option-btn w-full text-left bg-gray-50 hover:bg-green-50 border-2 border-gray-200 hover:border-green-400 rounded-xl px-4 py-3 font-medium text-gray-700 transition-all';
        btn.textContent = String.fromCharCode(65 + i) + '. ' + opt;
        btn.onclick = () => selectAnswer(i, btn);
        grid.appendChild(btn);
    });
    updateUI();
}

async function selectAnswer(index, btn) {
    if (answered) return;
    answered = true;

    const correct = index === currentQ.answerIndex;
    document.querySelectorAll('.option-btn').forEach((b, i) => {
        b.disabled = true;
        if (i === currentQ.answerIndex) {
            b.className = b.className.replace('bg-gray-50 hover:bg-green-50 border-gray-200 hover:border-green-400', 'bg-green-100 border-green-500 text-green-800');
        }
    });
    if (!correct) {
        btn.className = btn.className.replace('bg-gray-50 hover:bg-green-50 border-gray-200 hover:border-green-400', 'bg-red-100 border-red-500 text-red-800');
    }

    if (correct) {
        const earned = XP_PER + (state.streak * 5);
        state.xp     += earned;
        state.streak += 1;
        state.correct += 1;
        const newLevel = xpToLevel(state.xp);
        if (newLevel !== state.level) { state.level = newLevel; triggerLevelUp(); }
        if (state.streak >= 3 && state.streak % 3 === 0) smallConfetti();
    } else {
        state.streak = 0;
    }

    saveState();
    updateUI();
    await showExplanation(correct);
}

async function showExplanation(correct) {
    const card = document.getElementById('feedback-card');
    card.classList.remove('hidden', 'border-green-500', 'border-red-400');
    card.classList.add(correct ? 'border-green-500' : 'border-red-400');
    document.getElementById('feedback-icon').textContent  = correct ? '✅' : '❌';
    const earned = correct ? XP_PER + ((state.streak - 1) * 5) : 0;
    document.getElementById('feedback-title').textContent = correct ? `Correct! +${earned} XP` : 'Not quite...';
    document.getElementById('explanation-text').textContent = 'Loading explanation...';

    try {
        const res = await fetch('{{ route("api.explain") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                question: currentQ.question,
                answer:   currentQ.options[currentQ.answerIndex],
                subject:  SUBJECT,
                level:    LEVEL,
                luganda:  lugandaMode,
            }),
        });
        const data = await res.json();
        document.getElementById('explanation-text').textContent = data.explanation;
    } catch(e) {
        document.getElementById('explanation-text').textContent = 'Correct answer: ' + currentQ.options[currentQ.answerIndex];
    }
}

function nextQuestion() {
    state.qNum++;
    if (state.qNum > 10) { showSummary(); return; }
    saveState();
    loadQuestion();
}

function showSummary() {
    document.getElementById('question-card').classList.add('hidden');
    document.getElementById('feedback-card').classList.add('hidden');
    document.getElementById('score-summary').classList.remove('hidden');
    document.getElementById('summary-correct').textContent = state.correct;
    bigConfetti();
    state.qNum    = 1;
    state.correct = 0;
    saveState();
}

function restartQuiz() {
    document.getElementById('question-card').classList.remove('hidden');
    loadQuestion();
}

async function toggleLuganda() {
    lugandaMode = !lugandaMode;
    document.getElementById('luganda-btn').textContent = lugandaMode ? '🇬🇧 English' : '🇺🇬 Luganda';
    if (currentQ && answered) {
        document.getElementById('explanation-text').textContent = 'Loading...';
        try {
            const res = await fetch('{{ route("api.explain") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({
                    question: currentQ.question,
                    answer:   currentQ.options[currentQ.answerIndex],
                    subject:  SUBJECT, level: LEVEL, luganda: lugandaMode,
                }),
            });
            const data = await res.json();
            document.getElementById('explanation-text').textContent = data.explanation;
        } catch(e) {}
    }
}

function triggerLevelUp() {
    bigConfetti();
    const badge = document.getElementById('level-badge');
    badge.classList.remove('level-badge');
    void badge.offsetWidth;
    badge.classList.add('level-badge');
    badge.style.background = '#facc15';
}

function smallConfetti() {
    confetti({ particleCount: 60, spread: 50, origin: { y: 0.6 } });
}
function bigConfetti() {
    confetti({ particleCount: 150, spread: 90, origin: { y: 0.5 } });
    setTimeout(() => confetti({ particleCount: 100, spread: 70, origin: { x: 0.1, y: 0.5 } }), 300);
    setTimeout(() => confetti({ particleCount: 100, spread: 70, origin: { x: 0.9, y: 0.5 } }), 600);
}

loadQuestion();
</script>
@endsection
