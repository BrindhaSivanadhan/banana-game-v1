<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';
$username = $_SESSION['username'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Equation Game — Banana API</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* Pastel banana theme (keeps your existing look) */
    body { font-family: "Poppins", "Segoe UI", Arial, sans-serif; background: linear-gradient(180deg,#fffceb,#ffe7c2); margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; }
    .card { background:#fff; width:820px; max-width:96%; border-radius:16px; padding:28px; box-shadow:0 12px 40px rgba(20,30,50,0.08); text-align:center; }
    h1{margin:0;font-size:30px;color:#2a2a2a}
    .time{color:#5b6b7a;margin:12px 0 20px 0;font-weight:600;font-size:18px}
    .banana-image{width:260px;height:260px;object-fit:contain;border-radius:10px;margin-bottom:16px;box-shadow:0 8px 20px rgba(0,0,0,0.05)}
    .digits{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-top:10px}
    .digit-btn{background:linear-gradient(180deg,#bde0fe,#a6d2fc);border:none;padding:14px;border-radius:10px;font-size:20px;font-weight:700;cursor:pointer;color:#03396c;box-shadow:0 8px 18px rgba(10,20,40,0.04)}
    .controls{display:flex;justify-content:center;gap:20px;margin-top:24px}
    .btn{padding:10px 16px;border-radius:10px;font-weight:700;border:none;cursor:pointer}
    .btn.next{background:#ffd6a5;color:#603000}
    .btn.leader{background:#cde3ff;color:#023047}
    .message{margin-top:16px;font-weight:700;font-size:18px}
  </style>
</head>
<body>
  <div class="card">
    <h1>Equation Game</h1>
    <div class="time">Time: <span id="timer">30</span> s</div>

    <div class="game-area">
      <div class="subtitle">Guess the Missing Value</div>
      <img id="bananaImage" class="banana-image" alt="Banana equation" src="">
      <div class="digits" id="digits"></div>
      <div id="feedback" class="message"></div>
    </div>

    <div class="controls">
      <button id="nextBtn" class="btn next">Next Game</button>
      <button id="leaderBtn" class="btn leader">Leaderboard</button>
    </div>
  </div>

<script>
/* === Config === */
const TIME_LIMIT = 30;
let timer = TIME_LIMIT, timerInterval = null;
let correctAnswer = null;
const POINTS_ON_CORRECT = 20;

/* Helper to extract integer from response */
function extractFirstInt(x){
  if (x === null || x === undefined) return NaN;
  if (Array.isArray(x)) x = x.join(" ");
  let s = String(x);
  const m = s.match(/-?\d+/);
  return m ? parseInt(m[0],10) : NaN;
}

/* Fetch question from Banana API */
async function fetchQuestion(){
  setDigitsEnabled(false);
  document.getElementById('feedback').textContent = 'Loading...';
  correctAnswer = null;
  try{
    const res = await fetch('https://marcconrad.com/uob/banana/api.php');
    const data = await res.json();
    console.log('Banana API response:', data);
    // image field is usually 'question'
    const img = data.question || data.img || data.image || '';
    document.getElementById('bananaImage').src = img;
    // solution is under 'solution' for your API
    let parsed = NaN;
    if (data.solution !== undefined) parsed = extractFirstInt(data.solution);
    if (isNaN(parsed) && data.answer !== undefined) parsed = extractFirstInt(data.answer);
    if (isNaN(parsed)) parsed = extractFirstInt(JSON.stringify(data));
    correctAnswer = Number.isNaN(parsed) ? null : parsed;
    console.log('Parsed correctAnswer:', correctAnswer);
    if (correctAnswer === null) {
      document.getElementById('feedback').textContent = 'Could not parse answer from API. See console.';
      setDigitsEnabled(false);
    } else {
      document.getElementById('feedback').textContent = '';
      setDigitsEnabled(true);
    }
  }catch(err){
    console.error('Error fetching:', err);
    document.getElementById('feedback').textContent = 'Failed to load question.';
    setDigitsEnabled(false);
  }
}

/* Render digits 0-9 */
function renderDigits(){
  const container = document.getElementById('digits');
  container.innerHTML = '';
  for(let i=0;i<=9;i++){
    const btn = document.createElement('button');
    btn.className = 'digit-btn';
    btn.type = 'button';
    btn.textContent = i;
    btn.dataset.value = i;
    btn.disabled = true; // disabled until question loads
    btn.addEventListener('click', onDigitClick);
    container.appendChild(btn);
  }
}

function setDigitsEnabled(enabled){
  document.querySelectorAll('#digits .digit-btn').forEach(b => b.disabled = !enabled);
}

/* Digit click */
function onDigitClick(e){
  const value = parseInt(e.currentTarget.dataset.value,10);
  const correct = parseInt(correctAnswer,10);
  console.log('Clicked:', value, 'Correct:', correct);
  if (Number.isNaN(correct)){
    document.getElementById('feedback').textContent = 'Answer not ready.';
    return;
  }
  if (value === correct){
    document.getElementById('feedback').textContent = '✅ Correct! Saving points and redirecting...';
    setDigitsEnabled(false);
    stopTimer();
    // update points on server
    fetch('update_points.php', {
      method:'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ level: 'intermediate', points: POINTS_ON_CORRECT })
    }).then(r=>r.json()).then(()=> {
      setTimeout(()=> window.location.href = 'leaderboard.php', 800);
    }).catch(()=> window.location.href = 'leaderboard.php');
  } else {
    document.getElementById('feedback').textContent = '❌ Wrong! Try again.';
  }
}

/* Timer */
function startTimer(){
  timer = TIME_LIMIT;
  document.getElementById('timer').textContent = timer;
  if (timerInterval) clearInterval(timerInterval);
  timerInterval = setInterval(()=> {
    timer--;
    document.getElementById('timer').textContent = timer;
    if (timer <= 0){
      clearInterval(timerInterval);
      alert("⏰ Time up! Returning to difficulty selection.");
      window.location.href = 'select_difficulty.php';
    }
  },1000);
}
function stopTimer(){ if (timerInterval) clearInterval(timerInterval); }

/* Buttons */
document.getElementById('nextBtn').addEventListener('click', async ()=>{ await fetchQuestion(); startTimer(); document.getElementById('feedback').textContent = '';});
document.getElementById('leaderBtn').addEventListener('click', ()=>{ window.location.href = 'leaderboard.php'; });

/* Init */
renderDigits();
fetchQuestion();
startTimer();
</script>
</body>
</html>
