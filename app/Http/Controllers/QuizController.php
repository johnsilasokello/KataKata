<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QuizController extends Controller
{
    private array $subjects = [
        'o' => [
            'biology'   => ['id' => 17, 'name' => 'Biology',         'icon' => '🧬'],
            'history'   => ['id' => 23, 'name' => 'History',         'icon' => '📜'],
            'geography' => ['id' => 22, 'name' => 'Geography',       'icon' => '🗺️'],
            'science'   => ['id' => 17, 'name' => 'General Science', 'icon' => '⚗️'],
            'math'      => ['id' => 19, 'name' => 'Mathematics',     'icon' => '📐'],
            'english'   => ['id' => 10, 'name' => 'English/Lit',     'icon' => '📚'],
        ],
        'a' => [
            'biology'   => ['id' => 17, 'name' => 'Biology',         'icon' => '🧬'],
            'chemistry' => ['id' => 17, 'name' => 'Chemistry',       'icon' => '🔬'],
            'physics'   => ['id' => 17, 'name' => 'Physics',         'icon' => '⚡'],
            'history'   => ['id' => 23, 'name' => 'History',         'icon' => '📜'],
            'geography' => ['id' => 22, 'name' => 'Geography',       'icon' => '🗺️'],
            'math'      => ['id' => 19, 'name' => 'Mathematics',     'icon' => '📐'],
            'economics' => ['id' => 9,  'name' => 'Economics',       'icon' => '📊'],
        ],
    ];

    // XP awarded per correct answer
    private array $xpMap = [
        'easy'   => 30,
        'medium' => 50,
        'hard'   => 80,
    ];

    // A-level bonus on top of difficulty XP
    private int $aLevelBonus = 20;

    public function index()
    {
        return view('home', ['subjects' => $this->subjects]);
    }

    public function quiz(Request $request)
    {
        $level   = in_array($request->get('level'), ['o', 'a']) ? $request->get('level') : 'o';
        $subject = $request->get('subject', 'biology');
        if (!isset($this->subjects[$level][$subject])) {
            $subject = array_key_first($this->subjects[$level]);
        }
        $difficulty = in_array($request->get('difficulty'), ['easy', 'medium', 'hard'])
            ? $request->get('difficulty') : 'medium';

        return view('quiz', [
            'subject'     => $subject,
            'subjectName' => $this->subjects[$level][$subject]['name'],
            'level'       => $level,
            'difficulty'  => $difficulty,
            'xpPerQ'      => $this->xpMap[$difficulty] + ($level === 'a' ? $this->aLevelBonus : 0),
        ]);
    }

    public function leaderboard()
    {
        $data = json_decode(file_get_contents(base_path('data/leaderboard.json')), true);
        return view('leaderboard', ['leaders' => $data]);
    }

    public function getQuestion(Request $request)
    {
        $level      = in_array($request->input('level'), ['o', 'a']) ? $request->input('level') : 'o';
        $subject    = $request->input('subject', 'biology');
        $difficulty = in_array($request->input('difficulty'), ['easy', 'medium', 'hard'])
            ? $request->input('difficulty') : 'medium';

        if (!isset($this->subjects[$level][$subject])) {
            $subject = 'biology';
        }
        $categoryId = $this->subjects[$level][$subject]['id'];

        try {
            $response = Http::timeout(5)->get('https://opentdb.com/api.php', [
                'amount'     => 1,
                'category'   => $categoryId,
                'type'       => 'multiple',
                'difficulty' => $difficulty,
            ]);

            $data = $response->json();

            if (($data['response_code'] ?? 1) === 0 && !empty($data['results'])) {
                $q       = $data['results'][0];
                $options = array_merge([$q['correct_answer']], $q['incorrect_answers']);
                shuffle($options);
                $correctIndex = array_search($q['correct_answer'], $options);

                return response()->json([
                    'question'    => html_entity_decode($q['question']),
                    'options'     => array_map('html_entity_decode', $options),
                    'answerIndex' => $correctIndex,
                    'source'      => 'live',
                ]);
            }
        } catch (\Exception $e) {
            // fall through to cache
        }

        return $this->fallbackQuestion($level, $subject, $difficulty);
    }

    public function getExplanation(Request $request)
    {
        $question   = $request->input('question');
        $answer     = $request->input('answer');
        $subject    = $request->input('subject', 'biology');
        $level      = $request->input('level', 'o');
        $luganda    = $request->boolean('luganda', false);
        $hfToken    = env('HUGGINGFACE_API_TOKEN', '');

        if (empty($hfToken)) {
            return response()->json(['explanation' => $this->fallbackExplanation($answer, $luganda)]);
        }

        $levelLabel = $level === 'a' ? 'A-level' : 'O-level';
        $lang       = $luganda ? 'in simple Luganda (Ugandan language)' : 'in simple English';
        $prompt     = "You are a UNEB Uganda $levelLabel examiner. Explain briefly $lang why the answer to this question is correct.\nQuestion: $question\nCorrect answer: $answer\nExplanation (2 sentences max):";

        try {
            $response = Http::timeout(8)
                ->withToken($hfToken)
                ->post('https://api-inference.huggingface.co/models/google/flan-t5-large', [
                    'inputs'     => $prompt,
                    'parameters' => ['max_new_tokens' => 100],
                ]);

            $result = $response->json();
            $text   = $result[0]['generated_text'] ?? $this->fallbackExplanation($answer, $luganda);

            return response()->json(['explanation' => $text]);
        } catch (\Exception $e) {
            return response()->json(['explanation' => $this->fallbackExplanation($answer, $luganda)]);
        }
    }

    private function fallbackQuestion(string $level, string $subject, string $difficulty)
    {
        $cache = json_decode(file_get_contents(base_path('data/questions.json')), true);
        $key   = $level . '_' . $subject . '_' . $difficulty;
        $pool  = $cache[$key] ?? $cache[$level . '_' . $subject . '_medium']
               ?? $cache['o_biology_medium'];
        $q     = $pool[array_rand($pool)];

        return response()->json(array_merge($q, ['source' => 'cache']));
    }

    private function fallbackExplanation(string $answer, bool $luganda): string
    {
        if ($luganda) {
            return "Ey'okuddamu ery'otuufu kwe: \"$answer\". Teeka omwoyo ku nsomo eno.";
        }
        return "The correct answer is \"$answer\". Review this topic to reinforce your understanding.";
    }
}
