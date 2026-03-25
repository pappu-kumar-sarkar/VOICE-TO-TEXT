<!DOCTYPE html>
<html>

<head>
    <title>Voice Translator Pro 🌍</title>

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            /* background-color: black; */
            
            /* background: linear-gradient(135deg, #ff512f, #dd2476); */
            
        }

        /* Container */
        .container {
            width: 850px;
            margin: 60px auto;
            padding: 30px;
            border-radius: 25px;

            /* background: rgba(255, 255, 255, 0.1); */
            background-color: hotpink;
            backdrop-filter: blur(10px);

            /* box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3); */
            

        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        /* Dropdown */
        select {
            width: 45%;
            padding: 12px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
            outline: none;

            background: white;
            color: #333;
        }

        /* Swap */
        .swap {
            font-size: 24px;
            cursor: pointer;
            color: white;
            transition: 0.3s;
        }

        .swap:hover {
            transform: rotate(180deg) scale(1.2);
        }

        /* Boxes */
        .boxes {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        /* Textarea */
        textarea {
            width: 50%;
            height: 200px;
            border-radius: 20px;
            border: none;
            padding: 20px;
            font-size: 18px;
            outline: none;
            resize: none;

            /* 🔥 WHITE BOX */
            background: white;
            color: #333;

            /* box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); */
            transition: 0.3s;
        }

        /* Hover effect */
        textarea:focus {
            transform: scale(1.02);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        /* Buttons */
        .buttons {
            text-align: center;
            margin-top: 25px;
        }

        button {
            padding: 12px 30px;
            margin: 8px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            cursor: pointer;
            color: white;
            transition: 0.3s;
        }

        /* Start */
        .start {
            background: linear-gradient(45deg, #00c853, #64dd17);
        }

        .start:hover {
            transform: scale(1.1);
        }

        /* Stop */
        .stop {
            background: linear-gradient(45deg, #ff1744, #ff616f);
        }

        .stop:hover {
            transform: scale(1.1);
        }

        /* Status */
        .status {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            color: white;
            font-size: 18px;
        }

        /* Heading */
        h2 {
            text-align: center;
            color: white;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- 🔝 Language Select -->
        <div class="top-bar">
            <select id="fromLang">
                <option value="English">English</option>
                <option value="Hindi">Hindi</option>
            </select>

            <div class="swap" onclick="swapLang()">⇄</div>

            <select id="toLang">
                <option value="Hindi">Hindi</option>
                <option value="English">English</option>
                <option value="Bengali">Bengali</option>
                <option value="Punjabi">Punjabi</option>
            </select>
        </div>

        <!-- 🔲 BOXES -->
        <div class="boxes">
            <textarea id="inputBox" class="input" placeholder="Speak or type..."></textarea>
            <textarea id="outputBox" class="output" placeholder="Translation..." readonly></textarea>
        </div>

        <!-- 🎤 BUTTONS -->
        <div class="buttons">
            <button class="start" onclick="start()">🎤 Start</button>
            <button class="stop" onclick="stop()">⏹ Stop</button>
        </div>

        <div class="status" id="status">Status: Idle</div>

        <form>@csrf</form>

    </div>

    <script>

        let recognition;
        let finalText = "";

        function start() {
            recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            recognition.lang = 'hi-IN';
            recognition.continuous = true;
            recognition.interimResults = true;

            finalText = "";
            document.getElementById("status").innerText = "🎙 Listening...";

            recognition.onresult = (event) => {
                let text = "";

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    text += event.results[i][0].transcript;
                }

                document.getElementById("inputBox").value = text;
            };

            recognition.start();
        }

        function stop() {
            if (recognition) recognition.stop();

            let text = document.getElementById("inputBox").value.trim();
            let language = document.getElementById("toLang").value;

            if (!text) {
                document.getElementById("status").innerText = "⚠️ No speech";
                return;
            }

            document.getElementById("status").innerText = "⏳ Translating...";

            fetch('/save-text', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify({
                    text: text,
                    language: language
                })
            })
                .then(res => res.json())
                .then(data => {
                    document.getElementById("outputBox").value = data.text;
                    document.getElementById("status").innerText = "✅ Done";
                });
        }

        function swapLang() {
            let from = document.getElementById("fromLang");
            let to = document.getElementById("toLang");

            let temp = from.value;
            from.value = to.value;
            to.value = temp;
        }

    </script>

</body>

</html>