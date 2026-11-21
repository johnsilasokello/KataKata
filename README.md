# KataKata — Gamified UNEB Revision

> "UNEB decides Ugandan futures. KataKata makes revision addictive — Duolingo for Uganda's exams."

Built for **Cursor Hackathon Kampala** · Solo project · 75-min build window

---

## What it does

KataKata is an AI-powered, gamified revision app for Uganda's UNEB O-Level and A-Level exams. Students answer UNEB syllabus questions, earn XP, build streaks, level up, and compete on a leaderboard — all with Luganda explanation support.

---

## Features

- **O-Level & A-Level** — separate subject tracks per exam level
- **6 O-Level subjects** — Biology, History, Geography, Science, Mathematics, English
- **7 A-Level subjects** — Biology, Chemistry, Physics, History, Geography, Mathematics, Economics
- **3 difficulty modes** — Easy (30 XP), Medium (50 XP), Hard (80 XP)
- **A-Level XP bonus** — +20 XP per correct answer for harder content
- **Streak multiplier** — longer streaks = more XP per question
- **Level system** — S1 → S6 as XP grows
- **Confetti celebrations** — on level-up and round completion
- **Luganda toggle** — switch explanations between English and Luganda
- **AI explanations** — powered by HuggingFace free API (optional)
- **Offline fallback** — full UNEB question bank works with no internet
- **Leaderboard** — compete with other students

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 8 (PHP 7.4) |
| Frontend | Blade + Tailwind CSS (CDN) |
| Gamification | Vanilla JS + canvas-confetti |
| Questions | UNEB Uganda syllabus question bank (`data/questions.json`) |
| AI explanations | HuggingFace Inference API — `google/flan-t5-large` (free, optional) |
| State | `localStorage` (no database required) |

---

## Getting Started

### Requirements
- PHP 7.4+
- Composer
- XAMPP or any local server

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

### Optional: AI Explanations

Get a free API key at [huggingface.co](https://huggingface.co) → Settings → Access Tokens.

Add to `.env`:
```
HUGGINGFACE_API_TOKEN=hf_your_token_here
```

Without a token, the app uses built-in fallback explanations — everything still works.

---

## Project Structure

```
app/Http/Controllers/QuizController.php   # Routes, question serving, AI explanation
data/
  questions.json     # Full UNEB Uganda syllabus question bank (O & A level)
  leaderboard.json   # Seeded leaderboard with Ugandan names
resources/views/
  home.blade.php     # Subject picker with O/A level toggle + difficulty selector
  quiz.blade.php     # Quiz engine — questions, XP, streaks, confetti, Luganda toggle
  leaderboard.blade.php
  layouts/app.blade.php
routes/web.php
```

---

## How it works

1. Student picks **exam level** (O or A), **subject**, and **difficulty**
2. Question served from UNEB Uganda syllabus question bank
3. Student answers → instant feedback + AI explanation (English or Luganda)
4. Correct answer → XP earned, streak incremented, level checked
5. Level-up → confetti 🎉
6. After 10 questions → round summary + leaderboard

---

## Impact

Uganda has over **1 million UNEB candidates** annually. Most revise from printed past papers with no feedback. KataKata gives every student an intelligent, gamified tutor in their pocket — mapped to the exact Uganda national curriculum.

---

## Built with

- [Laravel](https://laravel.com)
- [Tailwind CSS](https://tailwindcss.com)
- [canvas-confetti](https://github.com/catdad/canvas-confetti)
- [HuggingFace Inference API](https://huggingface.co/inference-api)

---

## License

MIT
