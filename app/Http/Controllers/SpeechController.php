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
        $text = trim($request->text);
        $target = $request->language;

        if (!$text) {
            return response()->json([
                'status' => false,
                'text' => ''
            ]);
        }

        $langMap = [
            'English' => 'en',
            'Hindi' => 'hi',
            'Bengali' => 'bn',
            'Punjabi' => 'pa',
            'Tamil' => 'ta',
            'Telugu' => 'te',
            'Marathi' => 'mr',
            'Urdu' => 'ur',
            'Spanish' => 'es',
            'French' => 'fr',
            'German' => 'de'
        ];

        $targetLang = $langMap[$target] ?? 'en';

        try {

            // ⚡ Google Translate
            $google = Http::timeout(5)->get("https://translate.googleapis.com/translate_a/single", [
                'client' => 'gtx',
                'sl' => 'auto',
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $text
            ]);

            $translatedText = $google->json()[0][0][0] ?? $text;

            // 🧠 OpenAI Improve
            try {
                $ai = Http::withToken(env('OPENAI_API_KEY'))
                    ->timeout(5)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => "Improve this {$target} translation naturally."
                            ],
                            [
                                'role' => 'user',
                                'content' => $translatedText
                            ]
                        ]
                    ]);

                if ($ai->successful()) {
                    $translatedText = $ai->json()['choices'][0]['message']['content'] ?? $translatedText;
                }
            } catch (\Exception $e) {
            }

            // 🔊 Audio
            $audio = Http::get("https://translate.google.com/translate_tts", [
                'ie' => 'UTF-8',
                'q' => $translatedText,
                'tl' => $targetLang,
                'client' => 'tw-ob'
            ]);

            Speech::create([
                'transcription' => $translatedText
            ]);

            return response()->json([
                'status' => true,
                'text' => trim($translatedText),
                'audio' => "data:audio/mpeg;base64," . base64_encode($audio->body())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'text' => $text
            ]);
        }
    }
}