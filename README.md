# KataKata — Gamified UNEB Revision

> "UNEB decides Ugandan futures. KataKata makes revision addictive — Duolingo for Uganda's exams."

Built for **Cursor Hackathon Kampala** · Solo project · AI for Local Impact track

---

## What it does

KataKata is an AI-powered, gamified revision app for Uganda's UNEB O-Level and A-Level exams. Students answer UNEB Uganda syllabus questions, earn XP, build streaks, level up, compete on a leaderboard — and get a full UNEB Readiness Score from timed mock exams. Works offline. Installs on Android/iOS as a PWA.

---

## Features

### Core Quiz Engine
- **O-Level & A-Level** — separate subject tracks per exam level
- **6 O-Level subjects** — Biology, History, Geography, Science, Mathematics, English/Literature
- **7 A-Level subjects** — Biology, Chemistry, Physics, History, Geography, Mathematics, Economics
- **3 difficulty modes** — Easy (+30 XP), Medium (+50 XP), Hard (+80 XP)
- **A-Level XP bonus** — +20 XP per correct answer
- **Streak multiplier** — longer streaks earn more XP per question
- **Level system** — S1 → S6 as XP grows, with animated level-up badge

### AI & Explanations
- **Hardcoded UNEB explanations** — every question has a full English explanation, no API required
- **Luganda toggle** — switch explanations between English and Luganda instantly
- **HuggingFace AI** — optional upgrade via free `google/flan-t5-large` API for AI-generated explanations
- **Adaptive difficulty engine** — AI adjusts difficulty in real time:
  - 3 consecutive wrong answers → drops to easier difficulty
  - 5 consecutive correct answers → levels up to harder difficulty
  - Live notification shown when difficulty adjusts

### Mock Exam Mode
- **40-question timed mock** — simulates real UNEB exam conditions
- **60-minute countdown** — timer turns red in final 5 minutes
- **UNEB Readiness Score** — overall percentage with grade emoji (🏆 🎓 📚 💪 📖)
- **Per-subject breakdown** — progress bars showing readiness per subject
- **Focus area recommendation** — identifies weakest subject automatically
- **Confetti celebration** — triggered on 70%+ score

### Progress & Mastery Dashboard
- **Subject mastery tracking** — per-subject, per-level, per-difficulty progress bars
- **Overall accuracy stat** — total correct / total answered across all sessions
- **Level XP progress bar** — shows XP toward next level (S1–S6)
- **AI recommendation** — surfaces weakest subject with direct link to revise it
- **localStorage persistence** — progress saved across sessions, no account needed

### Gamification
- XP bar fill animation
- Streak counter 🔥
- Level badges (S1 → S6 themed)
- **Confetti on level-up and round completion**
- Seeded leaderboard with Ugandan student names

### PWA (Progressive Web App)
- **Installable** — add to Android/iOS home screen from browser
- **Works offline** — service worker caches pages and serves fallback questions
- **App icon** — green/yellow KataKata branding

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 8 (PHP 7.4) |
| Frontend | Blade + Tailwind CSS (CDN) |
| Gamification | Vanilla JS + canvas-confetti |
| Questions | UNEB Uganda syllabus question bank (`data/questions.json`) |
| Explanations | Hardcoded English + Luganda (zero API dependency) |
| AI explanations (optional) | HuggingFace Inference API — `google/flan-t5-large` (free) |
| State & progress | `localStorage` (no database required) |
| Offline support | Service Worker + Web App Manifest |

---

## Getting Started

### Requirements
- PHP 7.4+
- Composer

### Installation

```bash
git clone https://github.com/johnsilasokello/KataKata.git
cd KataKata
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Visit: `http://127.0.0.1:8000`

### Optional: AI Explanations via HuggingFace

Get a free API key at [huggingface.co](https://huggingface.co) → Settings → Access Tokens.

Add to `.env`:
```
HUGGINGFACE_API_TOKEN=hf_your_token_here
```

Without a token the app uses built-in UNEB explanations — everything works fully offline.

---

## App Routes

| Route | Description |
|-------|-------------|
| `/` | Subject picker — choose O/A level, difficulty, subject |
| `/quiz` | Quiz engine with XP, streaks, adaptive difficulty, Luganda toggle |
| `/mock` | 40-question timed mock exam with readiness score |
| `/progress` | Subject mastery dashboard + AI recommendation |
| `/leaderboard` | Student leaderboard |

---

## Project Structure

```
app/Http/Controllers/QuizController.php   # All routes, question serving, mock exam, AI explanation
data/
  questions.json     # Full UNEB Uganda syllabus question bank
                     # O-Level: Biology, History, Geography, Science, Math, English
                     # A-Level: Biology, Chemistry, Physics, History, Geography, Math, Economics
                     # Each with Easy / Medium / Hard + English & Luganda explanations
  leaderboard.json   # Seeded leaderboard with Ugandan names
public/
  manifest.json      # PWA manifest
  sw.js              # Service worker for offline support
  icon-192.png       # PWA icon
  icon-512.png       # PWA icon
resources/views/
  home.blade.php          # Subject picker (O/A toggle + difficulty selector)
  quiz.blade.php          # Quiz engine (questions, XP, streaks, adaptive difficulty, confetti)
  mock.blade.php          # Mock exam (40Q timer, readiness score, subject breakdown)
  progress.blade.php      # Mastery dashboard (per-subject bars, accuracy, recommendation)
  leaderboard.blade.php
  layouts/app.blade.php   # PWA meta tags + SW registration
routes/web.php
```

---

## How it works

1. Student picks **exam level** (O or A), **difficulty**, and **subject**
2. UNEB Uganda syllabus question served with 4 options
3. Student answers → instant feedback + hardcoded explanation (English or Luganda)
4. AI **adaptive engine** monitors performance — drops/raises difficulty automatically
5. Correct answer → XP earned, streak incremented, mastery recorded
6. Level-up → confetti 🎉
7. After 10 questions → round summary with XP earned
8. **Mock exam** → 40 questions, 60-min timer → UNEB Readiness Score per subject
9. **Progress dashboard** → see weakest subject, get directed revision recommendation

---

## UNEB Syllabus Coverage

Questions mapped to actual UNEB Uganda curriculum topics:

| Subject | Sample topics covered |
|---------|----------------------|
| O Biology | Cell structure, osmosis, photosynthesis equation, genetics, food chains, HIV |
| O History | 1900 Buganda Agreement, Berlin Conference, Idi Amin, NRM 1986, colonial policies |
| O Geography | Lake Victoria, Rwenzori Mountains, population distribution, soil erosion, Jinja |
| O Science | Newton's laws, pH scale, energy transformations, Nalubaale Dam hydroelectricity |
| O Math | Algebra, simultaneous equations, circle geometry, logarithms, VAT in UGX |
| O English | Figures of speech, passive voice, comprehension inference, essay structure |
| A Biology | ATP synthesis, Hardy-Weinberg, meiosis vs mitosis, chemiosmotic theory |
| A Chemistry | Hess's Law, Le Chatelier, SN1/SN2 mechanisms, Born-Haber cycle |
| A Physics | de Broglie hypothesis, SHM, Doppler effect, binding energy |
| A History | NRM assessment, Cold War in Uganda, colonial economic policies |
| A Geography | Demographic transition, Albertine oil, plate tectonics in East Africa |
| A Math | Differentiation, integration, differential equations, de Moivre's theorem |
| A Economics | PED and Uganda tax policy, multiplier effect, game theory, Gini coefficient |

---

## Impact

Uganda has over **1 million UNEB candidates** annually. Most revise from printed past papers with no feedback, no adaptation, no way to track weak areas. KataKata gives every student an intelligent, gamified tutor in their pocket — mapped to the exact Uganda national curriculum, working offline on the cheapest Android phone.

---

## License

MIT
