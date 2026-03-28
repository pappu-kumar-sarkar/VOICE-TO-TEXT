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
            'Punjabi' => 'pa'
        ];

        $targetLang = $langMap[$target] ?? 'en';

        $translatedText = $text;

        try {
            // 🔥 STEP 1: Google Translate (FAST)
            $google = Http::get("https://translate.googleapis.com/translate_a/single", [
                'client' => 'gtx',
                'sl' => 'auto',
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $text
            ]);

            $gData = $google->json();
            $translatedText = $gData[0][0][0] ?? $text;

            // 🔥 STEP 2: OpenAI (SMART IMPROVE)
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

            Speech::create([
                'transcription' => $translatedText
            ]);

            return response()->json([
                'status' => true,
                'text' => $translatedText
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'text' => $text
            ]);
        }
    }
}