<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Speech;

class SpeechController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function saveText(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'language' => 'required|string'
        ]);

        $text = trim($request->text);
        $language = $request->language;

        try {
            $response = Http::withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Translate the following text into {$language}. Keep meaning same and fix grammar."
                        ],
                        [
                            'role' => 'user',
                            'content' => $text
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['choices'][0]['message']['content'])) {
                    $text = trim($data['choices'][0]['message']['content']);
                }
            }

            Speech::create([
                'transcription' => $text
            ]);

            return response()->json([
                'status' => true,
                'text' => $text
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'text' => $text
            ]);
        }
    }
}