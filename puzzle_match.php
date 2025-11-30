<?php
// Protect page and start session securely
include 'config.php';
include 'session.php';

// Allow forcing guest mode via query (e.g., Continue as Guest button)
if (isset($_GET['guest'])) {
    $_SESSION['guest_mode'] = true;
}

// Check if user is logged in; allow non-logged-in users as Guest
$loggedIn = is_logged_in();
$username = $loggedIn ? $_SESSION['username'] : "Guest";

// Track which level the player is attempting (defaults to beginner for this puzzle)
$level = isset($_GET['level']) ? strtolower($_GET['level']) : 'beginner';
$allowedLevels = ['beginner', 'intermediate', 'advanced'];
if (!in_array($level, $allowedLevels, true)) {
    $level = 'beginner';
}

// Configuration for this run (PHP → JS)
$initialHints    = 3;    // how many hints user starts with
$initialAttempts = 20;   // allowed wrong attempts
$timeLimit       = 120;  // seconds (2 minutes)
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Banana Bliss — Beginner Puzzle</title>
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

    /* Main container */
    .wrap {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 1000px;
      margin: 0 auto;
      background: var(--card-bg);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      padding: 24px;
      border: 1px solid rgba(255, 225, 53, 0.2);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
      animation: slideIn 0.6s ease-out;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Top section */
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .header-section h2 {
      margin: 0 0 4px 0;
      font-size: 24px;
      font-weight: 700;
      background: linear-gradient(135deg, var(--banana-yellow) 0%, var(--banana-dark) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .subtitle {
      color: var(--text-muted);
      font-size: 12px;
      font-weight: 600;
      margin: 0;
    }

    /* Stats grid */
    .stats {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }

    .stat {
      background: rgba(255, 225, 53, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 8px 14px;
      min-width: 90px;
      text-align: center;
      border: 1px solid rgba(255, 225, 53, 0.2);
      transition: all 0.3s ease;
    }

    .stat:hover {
      background: rgba(255, 225, 53, 0.15);
      transform: translateY(-2px);
    }

    .stat .label { 
      display: block; 
      font-size: 10px; 
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 3px;
    }

    .stat .value { 
      font-weight: 700; 
      font-size: 18px;
      color: var(--banana-yellow);
    }

    /* Center section with board and controls */
    .center {
      display: flex;
      gap: 24px;
      align-items: flex-start;
      justify-content: center;
      flex-wrap: wrap;
    }

    /* Game board */
    #board {
      display: grid;
      gap: 10px;
      padding: 14px;
      background: rgba(0, 0, 0, 0.15);
      border-radius: 14px;
      border: 1px solid rgba(255, 225, 53, 0.1);
      justify-content: center;
    }

    .tile {
      width: 80px;
      height: 80px;
      border-radius: 12px;
      overflow: hidden;
      cursor: pointer;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      user-select: none;
      background: rgba(255, 255, 255, 0.05);
      border: 2px solid rgba(255, 225, 53, 0.2);
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .tile:hover {
      transform: scale(1.05);
      border-color: var(--banana-yellow);
      box-shadow: 0 8px 20px rgba(255, 225, 53, 0.4);
    }

    .tile img { 
      width: 100%; 
      height: 100%; 
      object-fit: cover; 
      display: block;
    }

    .tile.matched {
      opacity: 0.6;
      pointer-events: none;
      border-color: #81ec72;
      animation: matchPulse 0.5s ease;
    }

    @keyframes matchPulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    .tile.revealed {
      border-color: var(--banana-yellow);
      box-shadow: 0 0 20px rgba(255, 225, 53, 0.6);
    }

    /* Controls panel */
    .controls {
      display: flex;
      flex-direction: column;
      gap: 14px;
      min-width: 240px;
      background: rgba(255, 225, 53, 0.05);
      padding: 20px;
      border-radius: 16px;
      border: 1px solid rgba(255, 225, 53, 0.15);
    }

    .controls-title {
      font-size: 16px;
      font-weight: 700;
      color: var(--banana-yellow);
      text-align: center;
      margin-bottom: 8px;
    }

    .hint-btn, .logout-btn, .back-btn {
      padding: 12px 20px;
      border: none;
      border-radius: 12px;
      font-weight: 700;
      font-size: 14px;
      font-family: "Fredoka", sans-serif;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
    }

    .hint-btn {
      background: linear-gradient(135deg, var(--banana-yellow) 0%, var(--banana-dark) 100%);
      color: #3a2f1a;
      box-shadow: 0 8px 20px rgba(255, 225, 53, 0.3);
    }

    .hint-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 28px rgba(255, 225, 53, 0.4);
    }

    .hint-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }

    .logout-btn {
      background: rgba(255, 138, 138, 0.2);
      color: #ff8a8a;
      border: 2px solid rgba(255, 138, 138, 0.3);
    }

    .logout-btn:hover {
      background: rgba(255, 138, 138, 0.3);
      transform: translateY(-2px);
    }

    .back-btn {
      background: rgba(255, 255, 255, 0.1);
      color: var(--text-light);
      border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .back-btn:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }

    .small-note {
      font-size: 12px;
      color: var(--text-muted);
      text-align: center;
      line-height: 1.4;
      padding: 8px;
      background: rgba(0, 0, 0, 0.15);
      border-radius: 8px;
    }

    /* Message box */
    #message {
      margin-top: 20px;
      text-align: center;
    }

    .message-box {
      display: inline-block;
      padding: 16px 24px;
      border-radius: 12px;
      background: rgba(129, 236, 114, 0.15);
      border: 1px solid rgba(95, 211, 93, 0.4);
      color: var(--text-light);
      font-weight: 600;
      animation: slideIn 0.5s ease;
    }

    .message-box a {
      color: var(--banana-yellow);
      text-decoration: none;
      font-weight: 700;
    }

    .message-box a:hover {
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 768px) {
      body { padding: 50px 15px 30px 15px; }
      .wrap { padding: 18px; }
      .topbar { flex-direction: column; align-items: flex-start; }
      .stats { width: 100%; justify-content: space-between; }
      .stat { min-width: auto; flex: 1; padding: 6px 10px; }
      .stat .value { font-size: 16px; }
      .tile { width: 68px; height: 68px; }
      #board { gap: 8px; padding: 12px; }
      .controls { min-width: 100%; }
      .header-section h2 { font-size: 20px; }
    }

    @media (max-width: 480px) {
      body { padding: 40px 10px 20px 10px; }
      .tile { width: 58px; height: 58px; }
      .stat { padding: 6px 8px; }
      .stat .value { font-size: 15px; }
      .stat .label { font-size: 9px; }
      #board { gap: 6px; padding: 10px; }
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

  <div class="wrap" role="main">
    <div class="topbar">
      <div class="header-section">
        <h2>🍌 Banana Bliss — Beginner Level</h2>
        <p class="subtitle">Match pairs of fruits. All tiles are covered by bananas. Use hints if stuck!</p>
      </div>

      <div class="stats" aria-hidden="false">
        <div class="stat">
          <span class="label">Hints</span>
          <span class="value" id="hints"><?= $initialHints ?></span>
        </div>
        <div class="stat">
          <span class="label">Attempts</span>
          <span class="value" id="attempts"><?= $initialAttempts ?></span>
        </div>
        <div class="stat">
          <span class="label">Score</span>
          <span class="value" id="score">0</span>
        </div>
        <div class="stat">
          <span class="label">Time</span>
          <span class="value" id="time"><?= $timeLimit ?></span>
        </div>
      </div>
    </div>

    <div class="center">
      <div id="board" aria-label="game board"></div>

      <div class="controls">
        <div class="controls-title">🎮 Game Controls</div>
        
        <button id="useHint" class="hint-btn">💡 Use Hint</button>
        
        <?php if($loggedIn): ?>
          <button onclick="location.href='logout.php'" class="logout-btn">🚪 Logout</button>
        <?php endif; ?>
        
        <button class="back-btn" onclick="location.href='select_difficulty.php'">← Back to Menu</button>
        
        <div class="small-note">
          💡 Hints reveal all tiles for 2 seconds and cost 1 hint
        </div>
      </div>
    </div>

    <div id="message"></div>
  </div>

<script>
const INITIAL_HINTS    = <?= (int)$initialHints ?>;
const INITIAL_ATTEMPTS = <?= (int)$initialAttempts ?>;
const GAME_LEVEL = '<?= $level ?>';
let TIME_LEFT = <?= (int)$timeLimit ?>;

const FRUITS = ['images/apple.png','images/orange.png','images/strawberry.png','images/avocado.png'];
let hints = INITIAL_HINTS, attemptsLeft = INITIAL_ATTEMPTS, score = 0, timerInterval = null;
let board = document.getElementById('board'), firstIndex = null, secondIndex = null, lock = false, matchedCount = 0;
const TOTAL_TILES = 16, TOTAL_PAIRS = TOTAL_TILES/2, tiles = [];

function shuffle(arr){ for(let i=arr.length-1;i>0;i--){ const j=Math.floor(Math.random()*(i+1)); [arr[i],arr[j]]=[arr[j],arr[i]]; } }
function buildTiles(){ tiles.length=0; FRUITS.forEach(f=>{for(let i=0;i<4;i++)tiles.push(f);}); shuffle(tiles); }
function renderBoard(){ 
    board.innerHTML=''; 
    board.style.gridTemplateColumns='repeat(4,1fr)'; 
    for(let i=0;i<TOTAL_TILES;i++){ 
        const div=document.createElement('div'); 
        div.className='tile covered'; 
        div.dataset.index=i; 
        div.dataset.front=tiles[i]; 
        const img=document.createElement('img'); 
        img.src='images/banana_cover.png'; 
        img.alt='covered tile'; 
        div.appendChild(img); 
        div.addEventListener('click', onTileClick); 
        board.appendChild(div); 
    } 
}
function reveal(index){ if(lock) return; const tile=board.querySelector(`.tile[data-index="${index}"]`); if(!tile||tile.classList.contains('matched')) return; tile.querySelector('img').src=tile.dataset.front; tile.classList.add('revealed'); }
function hide(index){ const tile=board.querySelector(`.tile[data-index="${index}"]`); if(!tile||tile.classList.contains('matched')) return; tile.querySelector('img').src='images/banana_cover.png'; tile.classList.remove('revealed'); }
function markMatched(i,j){ const ti=board.querySelector(`.tile[data-index="${i}"]`); const tj=board.querySelector(`.tile[data-index="${j}"]`); ti.classList.add('matched'); tj.classList.add('matched'); }

function onTileClick(e){
    const idx=parseInt(e.currentTarget.dataset.index,10);
    const tileEl=e.currentTarget;
    if(tileEl.classList.contains('revealed')||tileEl.classList.contains('matched')||lock) return;
    reveal(idx);
    if(firstIndex===null){ firstIndex=idx; return; }
    if(secondIndex===null&&idx!==firstIndex){
        secondIndex=idx;
        lock=true;
        const a=board.querySelector(`.tile[data-index="${firstIndex}"]`).dataset.front;
        const b=board.querySelector(`.tile[data-index="${secondIndex}"]`).dataset.front;
        if(a===b){
            setTimeout(()=>{
                markMatched(firstIndex,secondIndex);
                matchedCount+=1;
                score+=10;
                updateUI();
                firstIndex=null;
                secondIndex=null;
                lock=false;
                if(matchedCount===TOTAL_PAIRS) onWin();
            },350);
        } else {
            attemptsLeft-=1;
            updateUI();
            setTimeout(()=>{
                hide(firstIndex);
                hide(secondIndex);
                firstIndex=null;
                secondIndex=null;
                lock=false;
                if(attemptsLeft<=0) onLose();
            },700);
        }
    }
}

function useHint(){
    if(hints<=0){ alert('❌ No hints left!'); return; }
    hints-=1; updateUI();
    const hintBtn = document.getElementById('useHint');
    hintBtn.disabled = true;
    hintBtn.textContent = '⏳ Showing...';
    
    const all=board.querySelectorAll('.tile');
    all.forEach(t=> t.querySelector('img').src=t.dataset.front);
    lock=true;
    setTimeout(()=>{
        all.forEach(t=>{ if(!t.classList.contains('matched')) t.querySelector('img').src='images/banana_cover.png'; });
        lock=false;
        hintBtn.disabled = false;
        hintBtn.textContent = '💡 Use Hint';
    },2000);
}

function updateUI(){
    document.getElementById('hints').textContent=hints;
    document.getElementById('attempts').textContent=attemptsLeft;
    document.getElementById('score').textContent=score;
    document.getElementById('time').textContent=TIME_LEFT;
    
    // Disable hint button if no hints left
    if(hints <= 0) {
        document.getElementById('useHint').disabled = true;
        document.getElementById('useHint').textContent = '❌ No Hints';
    }
}

function startTimer(){
    timerInterval=setInterval(()=>{
        TIME_LEFT-=1;
        document.getElementById('time').textContent=TIME_LEFT;
        if(TIME_LEFT<=0){ clearInterval(timerInterval); onLose(); }
    },1000);
}

function onWin(){
    clearInterval(timerInterval);
    if(<?= $loggedIn ? 'true' : 'false' ?>){
        // Logged-in: save points and redirect
        fetch('update_points.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({level:GAME_LEVEL,points:score})
        })
        .then(r=>r.json())
        .then(data=>{
            alert('🎉 Great! Puzzle complete. Points saved. Moving to banana math game.');
            window.location.href='banana.php';
        }).catch(err=>{
            console.error(err);
            alert('⚠️ Network error; redirecting to banana game.');
            window.location.href='banana.php';
        });
    } else {
        // Not logged-in: store points in the session so they can be restored after login
        storeGuestPoints(score).finally(() => {
            alert('Create an account or login to keep your score and unlock the Banana Math game.');
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `
                <div class="message-box">
                    <strong>🎉 Puzzle Complete!</strong><br>
                    Your ${score} points are saved temporarily.<br>
                    Please <a href="login.php">login</a> or <a href="register.php">create an account</a> to claim them,
                    continue playing, and appear on the leaderboard.
                </div>
            `;
            lock = true;
        });
    }
}

function onLose(){
    lock=true;
    alert('⏰ Game over: no attempts or time left. Try again!');
    location.reload();
}

function init(){
    buildTiles();
    renderBoard();
    updateUI();
    startTimer();
    document.getElementById('useHint').addEventListener('click',useHint);
}
init();

function storeGuestPoints(points){
    return fetch('guest_points.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({level:GAME_LEVEL,points})
    }).catch(err=>{
        console.error('Failed to store guest points',err);
    });
}
</script>
</body>
</html>