<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QuizController extends Controller
{
    // OpenTrivia category IDs mapped to UNEB subjects
    private array $categories = [
        'biology'  => ['id' => 17, 'name' => 'Biology'],
        'history'  => ['id' => 23, 'name' => 'History'],
        'geography'=> ['id' => 22, 'name' => 'Geography'],
        'science'  => ['id' => 18, 'name' => 'General Science'],
    ];

    public function index()
    {
        return view('home', ['categories' => $this->categories]);
    }

    public function quiz(Request $request)
    {
        $subject = $request->get('subject', 'biology');
        if (!array_key_exists($subject, $this->categories)) {
            $subject = 'biology';
        }
        return view('quiz', [
            'subject'    => $subject,
            'subjectName'=> $this->categories[$subject]['name'],
        ]);
    }

    public function leaderboard()
    {
        $data = json_decode(file_get_contents(base_path('data/leaderboard.json')), true);
        return view('leaderboard', ['leaders' => $data]);
    }

    public function getQuestion(Request $request)
    {
        $subject = $request->input('subject', 'biology');
        $categoryId = $this->categories[$subject]['id'] ?? 17;

        try {
            $response = Http::timeout(5)->get('https://opentdb.com/api.php', [
                'amount'   => 1,
                'category' => $categoryId,
                'type'     => 'multiple',
                'difficulty' => 'medium',
            ]);

            $data = $response->json();

            if (($data['response_code'] ?? 1) === 0 && !empty($data['results'])) {
                $q = $data['results'][0];
                $options = array_merge([$q['correct_answer']], $q['incorrect_answers']);
                shuffle($options);
                $correctIndex = array_search($q['correct_answer'], $options);

                return response()->json([
                    'question'     => html_entity_decode($q['question']),
                    'options'      => array_map('html_entity_decode', $options),
                    'answerIndex'  => $correctIndex,
                    'source'       => 'live',
                ]);
            }
        } catch (\Exception $e) {
            // fall through to cache
        }

        // Fallback to local cache
        $cache = json_decode(file_get_contents(base_path('data/questions.json')), true);
        $pool  = $cache[$subject] ?? $cache['biology'];
        $q     = $pool[array_rand($pool)];

        return response()->json(array_merge($q, ['source' => 'cache']));
    }

    public function getExplanation(Request $request)
    {
        $question    = $request->input('question');
        $answer      = $request->input('answer');
        $subject     = $request->input('subject', 'biology');
        $luganda     = $request->boolean('luganda', false);
        $hfToken     = env('HUGGINGFACE_API_TOKEN', '');

        if (empty($hfToken)) {
            return response()->json([
                'explanation' => $this->fallbackExplanation($answer, $luganda),
            ]);
        }

        $lang   = $luganda ? 'in simple Luganda (Ugandan language)' : 'in simple English';
        $prompt = "You are a UNEB Uganda examiner. Explain briefly $lang why the answer to this question is correct.\nQuestion: $question\nCorrect answer: $answer\nExplanation (2 sentences max):";

        try {
            $response = Http::timeout(8)
                ->withToken($hfToken)
                ->post('https://api-inference.huggingface.co/models/google/flan-t5-large', [
                    'inputs' => $prompt,
                    'parameters' => ['max_new_tokens' => 100],
                ]);

            $result = $response->json();
            $text   = $result[0]['generated_text'] ?? $this->fallbackExplanation($answer, $luganda);

            return response()->json(['explanation' => $text]);
        } catch (\Exception $e) {
            return response()->json([
                'explanation' => $this->fallbackExplanation($answer, $luganda),
            ]);
        }
    }

    private function fallbackExplanation(string $answer, bool $luganda): string
    {
        if ($luganda) {
            return "Ey'okuddamu ery'otuufu kwe: \"$answer\". Teeka omwoyo ku nsomo eno.";
        }
        return "The correct answer is \"$answer\". Review this topic to understand why.";
    }
}
