<?php
// Securely protect page
include 'config.php';
include 'session.php';
require_login(); // redirects to login.php if user is not logged in

$username = $_SESSION['username'];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Banana Bliss — Advanced Game</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    
    :root {
      --banana-yellow: #FFE135;
      --banana-dark: #FFB800;
      --banana-light: #FFF4A3;
      --banana-orange: #FFA726;
      --bg-dark: #3a2f1a;
      --bg-darker: #2d2415;
      --text-light: #fff9e6;
      --text-muted: #d4c5a0;
      --card-bg: rgba(255, 244, 163, 0.08);
    }

    html, body { 
      height: 100%; 
      margin: 0;
    }
    
    body {
      font-family: "Fredoka", sans-serif;
      background: linear-gradient(135deg, #2d2415 0%, #3a2f1a 50%, #4a3d20 100%);
      color: var(--text-light);
      min-height: 100vh;
      padding: 60px 20px 40px 20px;
      overflow-y: auto;
      position: relative;
    }

    /* Floating banana background */
    .bg-bananas {
      position: fixed;
      inset: 0;
      pointer-events: none;
      overflow: hidden;
      z-index: 0;
    }
    
    .floating-banana {
      position: absolute;
      font-size: 30px;
      opacity: 0.1;
      animation: float 20s infinite ease-in-out;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      25% { transform: translateY(-30px) rotate(5deg); }
      50% { transform: translateY(-60px) rotate(-5deg); }
      75% { transform: translateY(-30px) rotate(3deg); }
    }

    /* Main card */
    .card {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 900px;
      margin: 0 auto;
      background: var(--card-bg);
      backdrop-filter: blur(15px);
      border-radius: 24px;
      padding: 28px;
      border: 1px solid rgba(255, 225, 53, 0.2);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
      text-align: center;
      animation: slideIn 0.6s ease-out;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Header */
    h1 {
      margin: 0 0 8px 0;
      font-size: 32px;
      font-weight: 700;
      background: linear-gradient(135deg, #FF8F00 0%, #FF6B00 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .subtitle {
      color: var(--text-muted);
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    /* Stats (Timer and Lives) */
    .stats-bar {
      display: flex;
      justify-content: center;
      gap: 16px;
      margin: 16px 0 24px 0;
      flex-wrap: wrap;
    }

    .time, .lives {
      display: inline-block;
      background: rgba(255, 225, 53, 0.15);
      border: 1px solid rgba(255, 225, 53, 0.3);
      padding: 10px 24px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 16px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .time {
      color: var(--banana-orange);
    }

    .lives {
      color: #ff8a8a;
    }

    #timer, #lives {
      font-size: 22px;
      font-weight: 700;
    }

    /* Game area */
    .game-area {
      background: rgba(0, 0, 0, 0.15);
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 24px;
      border: 1px solid rgba(255, 225, 53, 0.1);
    }

    /* Banana image */
    .banana-image {
      width: 100%;
      max-width: 320px;
      height: 320px;
      object-fit: contain;
      border-radius: 16px;
      margin: 16px auto;
      background: rgba(255, 255, 255, 0.05);
      padding: 16px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
      border: 2px solid rgba(255, 143, 0, 0.3);
      display: block;
    }

    /* Digits grid */
    .digits {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 12px;
      margin-top: 20px;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .digit-btn {
      background: linear-gradient(135deg, #FFB74D 0%, #FF8F00 100%);
      border: none;
      padding: 16px;
      border-radius: 12px;
      font-size: 22px;
      font-weight: 700;
      cursor: pointer;
      color: #3a2f1a;
      font-family: "Fredoka", sans-serif;
      box-shadow: 0 6px 18px rgba(255, 143, 0, 0.4);
      transition: all 0.3s ease;
    }

    .digit-btn:hover:not(:disabled) {
      transform: translateY(-4px);
      box-shadow: 0 10px 24px rgba(255, 143, 0, 0.6);
    }

    .digit-btn:active:not(:disabled) {
      transform: translateY(-2px);
    }

    .digit-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
    }

    /* Feedback message */
    .message {
      margin-top: 20px;
      font-weight: 700;
      font-size: 18px;
      min-height: 28px;
      padding: 8px;
      border-radius: 10px;
    }

    .message:not(:empty) {
      background: rgba(255, 143, 0, 0.15);
      border: 1px solid rgba(255, 143, 0, 0.3);
    }

    /* Controls */
    .controls {
      display: flex;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
      margin-top: 24px;
    }

    .btn {
      padding: 14px 24px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 15px;
      font-family: "Fredoka", sans-serif;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn.next {
      background: linear-gradient(135deg, #FFB74D 0%, #FF8F00 100%);
      color: #3a2f1a;
      box-shadow: 0 6px 18px rgba(255, 143, 0, 0.4);
    }

    .btn.next:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 24px rgba(255, 143, 0, 0.5);
    }

    .btn.leader {
      background: rgba(255, 225, 53, 0.15);
      color: var(--banana-yellow);
      border: 2px solid rgba(255, 225, 53, 0.3);
    }

    .btn.leader:hover {
      background: rgba(255, 225, 53, 0.25);
      transform: translateY(-2px);
    }

    .btn.logout {
      background: rgba(255, 138, 138, 0.2);
      color: #ff8a8a;
      border: 2px solid rgba(255, 138, 138, 0.3);
    }

    .btn.logout:hover {
      background: rgba(255, 138, 138, 0.3);
      transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
      body { padding: 50px 15px 30px 15px; }
      .card { padding: 22px; }
      h1 { font-size: 26px; }
      .banana-image { max-width: 260px; height: 260px; }
      .digits { grid-template-columns: repeat(5, 1fr); gap: 10px; }
      .digit-btn { padding: 14px; font-size: 20px; }
      .btn { padding: 12px 20px; font-size: 14px; }
      .stats-bar { gap: 12px; }
      .time, .lives { padding: 8px 18px; font-size: 14px; }
      #timer, #lives { font-size: 20px; }
    }

    @media (max-width: 480px) {
      body { padding: 40px 10px 20px 10px; }
      .card { padding: 18px; }
      h1 { font-size: 22px; }
      .banana-image { max-width: 220px; height: 220px; }
      .digits { gap: 8px; }
      .digit-btn { padding: 12px; font-size: 18px; }
      .game-area { padding: 16px; }
      .controls { gap: 10px; }
      .btn { padding: 10px 16px; font-size: 13px; }
    }
  </style>
</head>
<body>
  <!-- Floating banana background -->
  <div class="bg-bananas">
    <div class="floating-banana" style="top: 10%; left: 5%;">🍌</div>
    <div class="floating-banana" style="top: 20%; right: 8%; animation-delay: -5s;">🍌</div>
    <div class="floating-banana" style="top: 60%; left: 10%; animation-delay: -10s;">🍌</div>
    <div class="floating-banana" style="top: 70%; right: 15%; animation-delay: -15s;">🍌</div>
    <div class="floating-banana" style="top: 40%; left: 85%; animation-delay: -7s;">🍌</div>
  </div>

  <div class="card">
    <h1>🔥 Advanced Equation Game - Harder Mode</h1>
    <p class="subtitle">⚡ Faster timer! Lives system! Higher stakes!</p>
    
    <div class="stats-bar">
      <div class="time">⏱️ Time: <span id="timer">15</span>s</div>
      <div class="lives">❤️ Lives: <span id="lives">5</span></div>
    </div>

    <div class="game-area">
      <img id="bananaImage" class="banana-image" alt="Banana equation" src="">
      <div class="digits" id="digits"></div>
      <div id="feedback" class="message"></div>
    </div>

    <div class="controls">
      <button id="nextBtn" class="btn next">🔄 Next Game</button>
      <button onclick="location.href='logout.php'" class="btn logout">🚪 Logout</button>
      <button id="leaderBtn" class="btn leader">🏆 Leaderboard</button>
    </div>
  </div>

<script>
/* === Config === */
const TIME_LIMIT = 15; // Advanced mode
let timer = TIME_LIMIT, timerInterval = null;
let correctAnswer = null;
const POINTS_ON_CORRECT = 40;
let lives = 5;

function extractFirstInt(x){
  if (x === null || x === undefined) return NaN;
  if (Array.isArray(x)) x = x.join(" ");
  let s = String(x);
  const m = s.match(/-?\d+/);
  return m ? parseInt(m[0],10) : NaN;
}

async function fetchQuestion(){
  setDigitsEnabled(false);
  document.getElementById('feedback').textContent = 'Loading...';
  correctAnswer = null;
  try{
    const res = await fetch('https://marcconrad.com/uob/banana/api.php');
    const data = await res.json();
    const img = data.question || data.img || data.image || '';
    document.getElementById('bananaImage').src = img;
    let parsed = NaN;
    if (data.solution !== undefined) parsed = extractFirstInt(data.solution);
    if (isNaN(parsed) && data.answer !== undefined) parsed = extractFirstInt(data.answer);
    if (isNaN(parsed)) parsed = extractFirstInt(JSON.stringify(data));
    correctAnswer = Number.isNaN(parsed) ? null : parsed;
    if (correctAnswer === null) {
      document.getElementById('feedback').textContent = 'Could not parse answer from API. See console.';
      setDigitsEnabled(false);
    } else {
      document.getElementById('feedback').textContent = '';
      setDigitsEnabled(true);
    }
  } catch(err){
    console.error('Error fetching:', err);
    document.getElementById('feedback').textContent = 'Failed to load question.';
    setDigitsEnabled(false);
  }
}

function renderDigits(){
  const container = document.getElementById('digits');
  container.innerHTML = '';
  for(let i=0;i<=9;i++){
    const btn = document.createElement('button');
    btn.className = 'digit-btn';
    btn.type = 'button';
    btn.textContent = i;
    btn.dataset.value = i;
    btn.disabled = true;
    btn.addEventListener('click', onDigitClick);
    container.appendChild(btn);
  }
}

function setDigitsEnabled(enabled){
  document.querySelectorAll('#digits .digit-btn').forEach(b => b.disabled = !enabled);
}

function endGame(){
  alert('💀 Game Over! All lives lost. Returning to difficulty selection.');
  window.location.href = 'select_difficulty.php';
}

function onDigitClick(e){
  const value = parseInt(e.currentTarget.dataset.value,10);
  const correct = parseInt(correctAnswer,10);
  if (Number.isNaN(correct)){
    document.getElementById('feedback').textContent = 'Answer not ready.';
    return;
  }
  if (value === correct){
    document.getElementById('feedback').textContent = '🏁 Legendary! Final points saved — showing leaderboard...';
    setDigitsEnabled(false);
    stopTimer();
    fetch('update_points.php', {
      method:'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ level: 'advanced', points: POINTS_ON_CORRECT })
    })
    .then(r=>r.json())
    .catch(err=>console.error('Failed to save advanced points', err))
    .finally(()=> {
      setTimeout(()=> window.location.href = 'leaderboard.php', 1000);
    });
  } else {
    lives--;
    document.getElementById('lives').textContent = lives;
    document.getElementById('feedback').textContent = `❌ Wrong! Life lost.`;
    if(lives <= 0){
      endGame();
    }
  }
}

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

document.getElementById('nextBtn').addEventListener('click', async ()=>{
  await fetchQuestion(); 
  startTimer(); 
  document.getElementById('feedback').textContent = '';
});

document.getElementById('leaderBtn').addEventListener('click', ()=>{ window.location.href = 'leaderboard.php'; });

renderDigits();
fetchQuestion();
startTimer();
</script>
</body>
</html>