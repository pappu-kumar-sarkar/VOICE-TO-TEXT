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

        $langMap = [
            'English' => 'en',
            'Hindi' => 'hi',
            'Bengali' => 'bn',
            'Punjabi' => 'pa',
            'Spanish' => 'es',
            'French' => 'fr',
            'German' => 'de',
            'Urdu' => 'ur',
            'Tamil' => 'ta',
            'Telugu' => 'te',
            'Marathi' => 'mr'
        ];

        $targetLang = $langMap[$target] ?? 'en';

        try {

            $google = Http::get("https://translate.googleapis.com/translate_a/single", [
                'client' => 'gtx',
                'sl' => 'auto',
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $text
            ]);

            $translatedText = $google->json()[0][0][0] ?? $text;

            $ai = Http::withToken(env('OPENAI_API_KEY'))
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Improve and correct this translation in {$target}. Make it natural."
                        ],
                        [
                            'role' => 'user',
                            'content' => $translatedText
                        ]
                    ]
                ]);

            if ($ai->successful()) {
                $aData = $ai->json();
                if (!empty($aData['choices'][0]['message']['content'])) {
                    $translatedText = trim($aData['choices'][0]['message']['content']);
                }
            }

            $audioResponse = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0'
            ])->get("https://translate.google.com/translate_tts", [
                'ie' => 'UTF-8',
                'q' => $translatedText,
                'tl' => $targetLang,
                'client' => 'tw-ob'
            ]);

            $audioBase64 = base64_encode($audioResponse->body());

            Speech::create([
                'transcription' => $translatedText
            ]);

            return response()->json([
                'status' => true,
                'text' => $translatedText,
                'audio' => "data:audio/mpeg;base64," . $audioBase64
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'text' => $text
            ]);
        }
    }
}