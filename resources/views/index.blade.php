<!DOCTYPE html>
<html>

<head>
    <title>Voice Translator Pro 🌍</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- ✅ SAME CSS -->
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f2f2f2;
        }

        .container {
            width: 900px;
            margin: 60px auto;
            padding: 30px;
            border-radius: 25px;
            background: linear-gradient(135deg, #ff5fa2, #ff3d7f);
        }

        .top-bar {
            display: flex;
            gap: 27px;
        }

        select {
            width: 48%;
            padding: 12px;
            border-radius: 12px;
            border: none;
        }

        .boxes {
            display: flex;
            gap: 20px;
            margin-top: 25px;
            align-items: stretch;
        }

        .box {
            width: 50%;
            display: flex;
            flex-direction: column;
        }

        textarea {
            width: 100%;
            height: 200px;
            border-radius: 25px;
            border: none;
            padding: 20px;
            font-size: 18px;
            resize: none;
            background: #ffffff;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
        }

        .listen {
            width: 50%;
            margin: 12px auto 0;
            padding: 10px;
            border-radius: 25px;
            border: none;
            background: #3498db;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }

        .buttons {
            text-align: center;
            margin-top: 30px;
        }

        button {
            padding: 12px 30px;
            margin: 10px;
            border-radius: 30px;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
        }

        .start {
            background: #2ecc71;
        }

        .stop {
            background: red;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- 🌍 10 LANGUAGES -->
        <div class="top-bar">
            <select id="fromLang">
                <option value="hi-IN">Hindi</option>
                <option value="en-US">English</option>
                <option value="bn-IN">Bengali</option>
                <option value="pa-IN">Punjabi</option>
                <option value="ta-IN">Tamil</option>
                <option value="te-IN">Telugu</option>
                <option value="mr-IN">Marathi</option>
                <option value="ur-PK">Urdu</option>
                <option value="es-ES">Spanish</option>
                <option value="fr-FR">French</option>
            </select>

            <select id="toLang">
                <option>English</option>
                <option>Hindi</option>
                <option>Bengali</option>
                <option>Punjabi</option>
                <option>Tamil</option>
                <option>Telugu</option>
                <option>Marathi</option>
                <option>Urdu</option>
                <option>Spanish</option>
                <option>French</option>
            </select>
        </div>

        <!-- 📦 BOX -->
        <div class="boxes">
            <div class="box">
                <textarea id="inputBox" placeholder="Speak..."></textarea>
                <button class="listen" onclick="speakInput()">🔊 Listen Input</button>
            </div>

            <div class="box">
                <textarea id="outputBox" placeholder="Translation..." readonly></textarea>
                <button class="listen" onclick="speakOutput()">🔊 Listen Output</button>
            </div>
        </div>

        <!-- 🎤 BUTTON -->
        <div class="buttons">
            <button class="start" onclick="start()">🎤 Start</button>
            <button class="stop" onclick="stop()">⏹ Stop</button>
        </div>

    </div>

    <script>
        let recognition;
        let lastAudio = "";
        let finalBuffer = "";

        // 🎤 START
        function start() {
            recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();

            recognition.lang = document.getElementById("fromLang").value;
            recognition.continuous = true;
            recognition.interimResults = true;

            document.getElementById("inputBox").value = "🎙 Listening...";
            finalBuffer = "";

            recognition.onresult = function (e) {
                let interim = "";

                for (let i = e.resultIndex; i < e.results.length; i++) {
                    let transcript = e.results[i][0].transcript;

                    if (e.results[i].isFinal) {
                        finalBuffer += transcript + " ";
                    } else {
                        interim += transcript;
                    }
                }

                document.getElementById("inputBox").value = finalBuffer + interim;

                if (finalBuffer.trim() !== "") {
                    translateNow(finalBuffer);
                }
            };

            recognition.start();
        }

        // 🛑 STOP
        function stop() {
            if (recognition) recognition.stop();
        }

        // 🔥 LANGUAGE CHANGE → INSTANT FAST TRANSLATE
        document.getElementById("toLang").addEventListener("change", function () {
            let text = document.getElementById("inputBox").value;

            if (text && !text.includes("Listening")) {
                translateNow(text);
            }
        });

        // 🔁 FAST TRANSLATE + STATUS
        function translateNow(text) {
            let outputBox = document.getElementById("outputBox");

            outputBox.value = "⏳ Translating...";

            fetch('/save-text', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    text: text,
                    language: document.getElementById("toLang").value
                })
            })
                .then(res => res.json())
                .then(data => {
                    outputBox.value = data.text;
                    if (data.audio) lastAudio = data.audio;
                });
        }

        // 🔊 LEFT
        function speakInput() {
            stop();
            let text = document.getElementById("inputBox").value;
            if (!text || text.includes("Listening")) return;

            let speech = new SpeechSynthesisUtterance(text);
            speech.lang = document.getElementById("fromLang").value;

            speechSynthesis.cancel();
            speechSynthesis.speak(speech);
        }

        // 🔊 RIGHT
        function speakOutput() {
            stop();
            if (lastAudio) new Audio(lastAudio).play();
        }
    </script>

</body>

</html>