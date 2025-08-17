<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : "User";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard</title>
<style>
html, body {
    margin: 0; padding: 0; height: 100%;
    font-family: 'Poppins', sans-serif;
    overflow: hidden; color: #fff;
    background: linear-gradient(135deg,#ff0000,#ff7f00,#ffff00,#7fff00,#00ff00,#00ff7f,#00ffff,#007fff,#0000ff,#7f00ff,#ff00ff,#ff007f,#ff6666,#ff9966,#ffcc66,#ccff66,#66ff66,#66ffcc,#66ccff,#6699ff,#6666ff,#9966ff,#cc66ff,#ff66ff,#ff66cc);
    background-size: 400% 400%;
    animation: gradientAnimation 20s ease infinite;
}
@keyframes gradientAnimation {0% {background-position:0% 50%;} 50% {background-position:100% 50%;} 100% {background-position:0% 50%;}}

.bg-video { position: fixed; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -1; }
.top-nav { position: absolute; top: 20px; right: 30px; display: flex; gap: 15px; align-items: center; }
.profile-menu { position: relative; display: inline-block; }
.profile-circle { width: 45px; height: 45px; background: #ffde59; border-radius: 50%; cursor: pointer; border: 2px solid #fff; display:flex;align-items:center;justify-content:center;font-weight:bold;color:black;font-size:1.2rem; }
.dropdown-content { display: none; position: absolute; top: 55px; right: 0; background: rgba(0,0,0,0.8); border-radius:6px; min-width:150px; z-index:10; box-shadow:0 4px 8px rgba(0,0,0,0.5); }
.dropdown-content a { display:block; padding:10px; color:white; text-decoration:none; transition: background 0.2s; }
.dropdown-content a:hover { background: rgba(255,255,255,0.1); }

.container { position: relative; top: 120px; margin: auto; width: 55%; max-width: 800px; background: rgba(0,0,0,0.6); color: white; padding: 30px; border-radius: 10px; text-align: center; }
h1 { font-size:2rem; margin-bottom:20px; color:#ffde59; }
label { display:block; text-align:left; margin-bottom:5px; font-weight:600; }
textarea { width:100%; height:120px; margin-bottom:20px; border-radius:6px; border:none; padding:10px; font-size:1rem; resize:vertical; }
.button-row { display:flex; justify-content:space-around; gap:15px; margin-top:20px; flex-wrap: wrap; }
.button-row button { flex:1; min-width:120px; padding:12px; border:none; border-radius:6px; cursor:pointer; color:white; font-size:1rem; font-weight:600; background: linear-gradient(90deg,#ff512f,#dd2476); transition: transform 0.2s; }
.button-row button:hover { transform: scale(1.05); }

/* POS & Translation containers */
#translateContainer, #posContainer {
    position: fixed; top: 100px; width: 300px; bottom: 40px;
    height: calc(100vh - 140px);
    overflow-y: auto; background: rgba(0,0,0,0.7);
    padding: 15px; border-radius: 10px; color: #fff;
    font-size: 0.95rem; display: none;
}
#translateContainer { left: 20px; }
#posContainer { right: 20px; }
h2 { margin-top:0; font-size:1.2rem; color:#ffde59; }
.pos-item { margin-bottom:4px; }

/* Spinner CSS */
#spinner { display:none; position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); z-index:20; }
.loader { border:6px solid #f3f3f3; border-top:6px solid #ff512f; border-radius:50%; width:50px; height:50px; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

/* Language selection popup */
#languagePopup { display:none; position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background: rgba(0,0,0,0.85); color:#fff; padding:20px; border-radius:10px; z-index:30; }
#languagePopup select { width:100%; padding:16px; border-radius:10px; border:none; margin-bottom:20px; font-size:1.5rem; }
#languagePopup button { padding:12px 20px; border:none; border-radius:12px; background:#ff512f; color:white; cursor:pointer; font-weight:600; }
</style>
</head>
<body>

<video autoplay muted loop playsinline class="bg-video">
    <source src="../videos/bus.mp4" type="video/mp4">
</video>

<div class="top-nav">
    <span style="color:white; font-weight:bold;"><?php echo "Welcome " . $username; ?></span>
    <div class="profile-menu">
        <div class="profile-circle" id="profileBtn">
            <?php echo strtoupper(substr($username, 0, 1)); ?>
        </div>
        <div class="dropdown-content" id="dropdownMenu">
            <a href="settings.php">Settings</a>
            <a href="user_details.php">Profile Details</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="container">
    <h1>User Dashboard</h1>
    <label for="inputText">Input:</label>
    <textarea id="inputText" placeholder="Enter your text here..."></textarea>

    <label for="outputText">Output:</label>
    <textarea id="outputText" placeholder="Output will appear here..." readonly></textarea>

    <div class="button-row">
        <button type="button" id="submitBtn">Submit</button>
        <button type="button" id="translateBtn">Translate</button>
        <button type="button" id="speechBtn">Speech Out</button>
        <button type="button" id="speechRecBtn">Speech Input</button>
        <button type="button" id="posBtn">POS Tags</button>
    </div>
</div>

<div id="spinner"><div class="loader"></div></div>
<div id="translateContainer"></div>
<div id="posContainer"></div>

<div id="languagePopup">
    <h2>Select Language</h2>
    <select id="languageSelect">
        <option value="Hindi">Hindi</option>
        <option value="Bengali">Bengali</option>
        <option value="Telugu">Telugu</option>
        <option value="Marathi">Marathi</option>
        <option value="Tamil">Tamil</option>
        <option value="Urdu">Urdu</option>
        <option value="Gujarati">Gujarati</option>
        <option value="Kannada">Kannada</option>
        <option value="Odia">Odia</option>
        <option value="Malayalam">Malayalam</option>
        <option value="Punjabi">Punjabi</option>
        <option value="Assamese">Assamese</option>
        <option value="Sanskrit">Sanskrit</option>
    </select>
    <button id="translateConfirm">Confirm</button>
</div>

<script>
const profileBtn = document.getElementById('profileBtn');
const dropdownMenu = document.getElementById('dropdownMenu');
const submitBtn = document.getElementById('submitBtn');
const translateBtn = document.getElementById('translateBtn');
const speechBtn = document.getElementById('speechBtn');
const speechRecBtn = document.getElementById('speechRecBtn');
const posBtn = document.getElementById('posBtn');
const inputText = document.getElementById('inputText');
const outputText = document.getElementById('outputText');
const translateContainer = document.getElementById('translateContainer');
const posContainer = document.getElementById('posContainer');
const spinner = document.getElementById('spinner');
const languagePopup = document.getElementById('languagePopup');
const languageSelect = document.getElementById('languageSelect');
const translateConfirm = document.getElementById('translateConfirm');

const LANG_CODES = {
    "English": "en", "Hindi": "hi", "Bengali": "bn", "Telugu": "te",
    "Marathi": "mr", "Tamil": "ta", "Urdu": "ur", "Gujarati": "gu",
    "Kannada": "kn", "Odia": "or", "Malayalam": "ml", "Punjabi": "pa",
    "Assamese": "as", "Sanskrit": "sa"
};

let lastTranslatedText = "";
let lastTranslatedLanguage = "English";
let recognition = null;
let isListening = false;

// Profile dropdown
profileBtn.addEventListener('click', e => {
    dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    e.stopPropagation();
});
document.addEventListener('click', () => dropdownMenu.style.display = 'none');

// Correct sentence
submitBtn.addEventListener('click', async () => {
    const text = inputText.value.trim();
    if (!text) return;
    spinner.style.display = 'block';
    outputText.value = "";
    try {
        const response = await fetch('http://127.0.0.1:5000/process_text', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({input_text: text})
        });
        const data = await response.json();
        outputText.value = data.corrected_text;
    } catch (err) {
        console.error(err);
        outputText.value = "Error processing input.";
    } finally { spinner.style.display = 'none'; }
});

// POS Tags
posBtn.addEventListener('click', async () => {
    const text = inputText.value.trim();
    if (!text) return;
    spinner.style.display = 'block';
    try {
        const response = await fetch('http://127.0.0.1:5000/process_text', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({input_text: text})
        });
        const data = await response.json();
        posContainer.innerHTML = "<h2>POS Tags</h2>";
        data.pos_tags.forEach(item => {
            const word = item[0];
            const fullPos = item[1];
            const tenseObj = data.tense_info.find(t => t[0] === word);
            const tense = tenseObj ? tenseObj[1] : "";
            posContainer.innerHTML += `<div class="pos-item">Word: <b>${word}</b> - POS: <b>${fullPos}</b>${tense ? ` - Tense: <b>${tense}</b>` : ""}</div>`;
        });
        posContainer.style.display = 'block';
    } catch (err) {
        posContainer.innerHTML = "Error fetching POS tags.";
        console.error(err);
    } finally { spinner.style.display = 'none'; }
});

// Translate button
translateBtn.addEventListener('click', () => {
    if (!outputText.value.trim()) return;
    languagePopup.style.display = 'block';
});

// Confirm translation
translateConfirm.addEventListener('click', async () => {
    const text = outputText.value.trim();
    const language = languageSelect.value;
    if (!text) return;
    languagePopup.style.display = 'none';
    spinner.style.display = 'block';
    try {
        const responseTrans = await fetch('http://127.0.0.1:5000/translate_text', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({input_text: text, target_language: language})
        });
        const dataTrans = await responseTrans.json();
        translateContainer.innerHTML = `<h2>${language} Translation</h2><p>${dataTrans.translated_text}</p>`;
        translateContainer.style.display = 'block';
        lastTranslatedText = dataTrans.translated_text;
        lastTranslatedLanguage = language;
    } catch (err) {
        console.error(err);
        alert("Error translating text.");
    } finally { spinner.style.display = 'none'; }
});

// Speech Out button
speechBtn.addEventListener('click', async () => {
    if (!lastTranslatedText) {
        alert("Please translate the text first.");
        return;
    }
    spinner.style.display = 'block';
    try {
        const responseSpeech = await fetch('http://127.0.0.1:5000/speech_output', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({input_text: lastTranslatedText, language: lastTranslatedLanguage})
        });
        const dataSpeech = await responseSpeech.json();
        if (dataSpeech.audio_base64) {
            const audio = new Audio("data:audio/mpeg;base64," + dataSpeech.audio_base64);
            audio.play();
        } else { alert("Error generating speech."); }
    } catch (err) {
        console.error(err);
        alert("Error generating speech.");
    } finally { spinner.style.display = 'none'; }
});

// Speech Recognition Toggle
speechRecBtn.addEventListener('click', () => {
    if (!('webkitSpeechRecognition' in window)) {
        alert("Speech Recognition not supported.");
        return;
    }

    if (!isListening) {
        recognition = new webkitSpeechRecognition();
        recognition.lang = "en-US";
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;

        recognition.onresult = (event) => {
            const speechText = event.results[0][0].transcript;
            inputText.value = speechText;
        };

        recognition.onerror = (event) => { 
            console.error(event.error); 
            alert("Speech recognition error."); 
            isListening = false;
            speechRecBtn.textContent = "Speech Input";
        };

        recognition.onend = () => { 
            isListening = false; 
            speechRecBtn.textContent = "Speech Input"; 
        };

        recognition.start();
        isListening = true;
        speechRecBtn.textContent = "Stop Listening";
    } else {
        recognition.stop();
        isListening = false;
        speechRecBtn.textContent = "Speech Input";
    }
});
</script>

</body>
</html>
