<!DOCTYPE html>
<html>

<head>
    <title>Voice AI (Hindi + English)</title>

    <style>
        body {
            font-family: Arial;
            background: linear-gradient(135deg, #667eea, #764ba2);
            margin: 0;
        }

        .container {
            width: 450px;
            margin: 80px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 25px;
            text-align: center;
            box-shadow: 0px 20px 50px rgba(0, 0, 0, 0.3);
        }

        .mic {
            font-size: 40px;
            animation: float 2s infinite;
        }

        @keyframes float {
            50% {
                transform: translateY(-5px);
            }
        }

        h2 {
            margin-top: 10px;
        }

        button {
            padding: 12px 25px;
            margin: 10px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        .start {
            background: linear-gradient(45deg, green, limegreen);
            color: white;
        }

        .stop {
            background: linear-gradient(45deg, red, tomato);
            color: white;
        }

        button:hover {
            transform: scale(1.1);
        }

        textarea {
            width: 90%;
            height: 140px;
            margin-top: 20px;
            padding: 15px;
            border-radius: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            resize: none;
        }

        .status {
            margin-top: 10px;
            font-weight: bold;
        }

        .recording {
            color: red;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="mic">🎤</div>
        <h2>Voice AI (Hindi + English)</h2>

        <button class="start" onclick="start()">▶ Start</button>
        <button class="stop" onclick="stop()">⏹ Stop</button>

        <div class="status" id="status">Status: Idle</div>

        <textarea id="textBox" placeholder="Speak in Hindi or English..."></textarea>

        <form>@csrf</form>

    </div>

    <script>

        let recognition;
        let finalText = "";

        // 🎤 Start Recording
        function start() {
            recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();

            // recognition.lang = 'en-IN';// Hindi + English mix तुम क्या कर रही हो, what are you doing?
            recognition.lang = 'hi-IN';// Hindi + English mix तुम क्या कर रही हो, what are you doing?
            recognition.continuous = true;
            recognition.interimResults = true;

            finalText = "";

            document.getElementById("status").innerText = "🎙 Listening...";
            document.getElementById("status").classList.add("recording");

            recognition.onresult = (event) => {
                let interimText = "";

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    let transcript = event.results[i][0].transcript;

                    if (event.results[i].isFinal) {
                        finalText += transcript + " ";
                    } else {
                        interimText += transcript;
                    }
                }

                document.getElementById("textBox").value = finalText + interimText;
            };

            recognition.onerror = (event) => {
                document.getElementById("status").innerText = "❌ Error: " + event.error;
            };

            recognition.start();
        }

        //  Stop + Send to AI
        function stop() {
            if (recognition) recognition.stop();

            document.getElementById("status").innerText = "⏳ Processing...";
            document.getElementById("status").classList.remove("recording");

            let text = document.getElementById("textBox").value.trim();

            if (!text) {
                document.getElementById("status").innerText = "⚠️ No speech detected";
                return;
            }

            fetch('/save-text', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify({ text: text })
            })
                .then(res => res.json())
                .then(data => {
                    document.getElementById("textBox").value = data.text;
                    document.getElementById("status").innerText = "✅ Done";
                })
                .catch(() => {
                    document.getElementById("status").innerText = "❌ Server Error";
                });
        }

    </script>

</body>

</html>