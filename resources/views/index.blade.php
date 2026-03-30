<!DOCTYPE html>
<html>
<head>
    <title>Voice Translator Pro 🌍</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: #f2f2f2;
        }

        .container {
            width: 850px;
            margin: 60px auto;
            padding: 30px;
            border-radius: 25px;
            background: linear-gradient(135deg, #ff5fa2, #ff3d7f);
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .top-bar {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        select {
            width: 45%;
            padding: 12px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
        }

        .swap {
            font-size: 22px;
            color: white;
            cursor: pointer;
        }

        .boxes {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        textarea {
            width: 50%;
            height: 180px;
            border-radius: 20px;
            border: none;
            padding: 20px;
            font-size: 18px;
            resize: none;
        }

        .buttons {
            text-align: center;
            margin-top: 25px;
        }

        button {
            padding: 12px 30px;
            margin: 10px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            color: white;
            cursor: pointer;
        }

        .start { background: #2ecc71; }
        .stop { background: #ff4d4d; }
        .listen { background: #3498db; }

        .status {
            text-align: center;
            margin-top: 15px;
            color: white;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="top-bar">
        <select id="fromLang">
            <option value="hi-IN">Hindi</option>
            <option value="en-US">English</option>
            <option value="bn-IN">Bengali</option>
            <option value="pa-IN">Punjabi</option>
        </select>

        <div class="swap" onclick="swapLang()">⇄</div>

        <select id="toLang">
            <option value="English">English</option>
            <option value="Hindi">Hindi</option>
            <option value="Bengali">Bengali</option>
            <option value="Punjabi">Punjabi</option>
        </select>
    </div>

    <div class="boxes">
        <textarea id="inputBox" placeholder="Speak or type..."></textarea>
        <textarea id="outputBox" placeholder="Translation..." readonly></textarea>
    </div>

    <div class="buttons">
        <button class="start" onclick="start()">🎤 Start</button>
        <button class="stop" onclick="stop()">⏹ Stop</button>
        <button class="listen" onclick="speakText()">🔊 Listen</button>
    </div>

    <div class="status" id="status">Status: Idle</div>
</div>

<script>
let recognition;

function start() {
    recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();

    recognition.lang = document.getElementById("fromLang").value;
    recognition.continuous = true;
    recognition.interimResults = true;

    recognition.onresult = function(event) {
        let text = "";
        for (let i = 0; i < event.results.length; i++) {
            text += event.results[i][0].transcript;
        }
        document.getElementById("inputBox").value = text;
    };

    recognition.start();
    document.getElementById("status").innerText = "🎙 Listening...";
}

// translate
document.getElementById("toLang").addEventListener("change", function() {
    let text = document.getElementById("inputBox").value;
    if (text.trim() === "") return;

    document.getElementById("status").innerText = "⏳ Translating...";

    fetch('/save-text', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            text: text,
            language: this.value
        })
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("outputBox").value = data.text;
        document.getElementById("status").innerText = "✅ Done";
    });
});

function stop() {
    if (recognition) recognition.stop();
    document.getElementById("status").innerText = "🛑 Stopped";
}

// 🔊 FEMALE VOICE FUNCTION
function speakText() {
    let text = document.getElementById("outputBox").value;

    if (text.trim() === "") {
        alert("No text to speak");
        return;
    }

    let speech = new SpeechSynthesisUtterance();
    speech.text = text;

    let lang = document.getElementById("toLang").value;

    if (lang === "Hindi") speech.lang = "hi-IN";
    else if (lang === "English") speech.lang = "en-US";
    else if (lang === "Bengali") speech.lang = "bn-IN";
    else if (lang === "Punjabi") speech.lang = "pa-IN";

    let voices = window.speechSynthesis.getVoices();

    let femaleVoice = voices.find(v =>
        v.lang.includes(speech.lang) &&
        (v.name.toLowerCase().includes("female") ||
         v.name.toLowerCase().includes("zira") ||
         v.name.toLowerCase().includes("google"))
    );

    if (femaleVoice) speech.voice = femaleVoice;

    speech.pitch = 1.2;
    speech.rate = 0.9;

    window.speechSynthesis.speak(speech);
}

// load voices fix
window.speechSynthesis.onvoiceschanged = () => {
    window.speechSynthesis.getVoices();
};

function swapLang() {
    let from = document.getElementById("fromLang");
    let to = document.getElementById("toLang");

    let temp = from.value;
    from.value = to.value === "English" ? "en-US" : "hi-IN";
    to.value = temp === "hi-IN" ? "Hindi" : "English";
}
</script>

</body>
</html>