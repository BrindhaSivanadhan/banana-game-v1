<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'config.php';

// configuration for this run (PHP → JS)
$initialHints    = 3;    // how many hints user starts with
$initialAttempts = 20;   // allowed wrong attempts
$timeLimit       = 120;  // seconds (2 minutes)

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Puzzle Matching — Beginner</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* ---- pastel theme and layout ---- */
    :root {
      --bg: #f7fafc;
      --card: #ffffff;
      --accent: #bde0fe;
      --muted: #7a8a99;
      --success: #9de5b4;
    }
    body {
      margin:0;
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      background:var(--bg);
      font-family: "Poppins", system-ui, Arial, sans-serif;
      color:#233;
    }
    .wrap {
      width: 960px;
      max-width: 96%;
      background: var(--card);
      border-radius: 14px;
      box-shadow: 0 16px 40px rgba(20,30,50,0.08);
      padding: 22px;
    }
    .topbar {
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:12px;
      margin-bottom:18px;
    }
    .stats {
      display:flex;
      gap:18px;
      align-items:center;
    }
    .stat {
      background:#fbfdff;
      border-radius:10px;
      padding:8px 12px;
      min-width:110px;
      text-align:center;
      box-shadow:0 6px 18px rgba(0,0,0,0.03);
    }
    .stat .label { display:block; font-size:12px; color:var(--muted); }
    .stat .value { font-weight:700; font-size:18px; margin-top:4px; }
    .center {
      display:flex;
      gap:24px;
      align-items:flex-start;
      justify-content:center;
      flex-wrap:wrap;
    }
    /* board */
    #board {
      display:grid;
      gap:10px;
      background:transparent;
      padding:10px;
      border-radius:12px;
      justify-content:center;
    }
    .tile {
      width:90px;
      height:90px;
      border-radius:10px;
      overflow:hidden;
      cursor:pointer;
      position:relative;
      box-shadow: 0 8px 18px rgba(10,20,40,0.04);
      display:flex;
      align-items:center;
      justify-content:center;
      user-select:none;
      background:#fff;
    }
    .tile img {
      width:100%;
      height:100%;
      object-fit:cover;
      display:block;
    }
    .tile.covered { background:#fff; }
    .controls {
      display:flex;
      flex-direction:column;
      gap:12px;
      align-items:center;
      min-width:220px;
    }
    .hint-btn {
      background: linear-gradient(90deg,#ffd6a5,#ffcad8);
      border:none;
      padding:12px 18px;
      border-radius:10px;
      font-weight:700;
      cursor:pointer;
      box-shadow:0 8px 18px rgba(0,0,0,0.06);
    }
    .small-note { font-size:13px; color:var(--muted); text-align:center; }
    .footer {
      margin-top:16px;
      display:flex;
      justify-content:center;
      gap:12px;
    }
    .btn-quit {
      background:#f1f3f5;border-radius:8px;padding:8px 12px;border:none;cursor:pointer;
    }
    .hidden { display:none !important; }
    /* responsive smaller tiles */
    @media (max-width:700px){
      .tile { width:68px; height:68px; }
    }
  </style>
</head>
<body>
  <div class="wrap" role="main">
    <div class="topbar">
      <div><h2 style="margin:0 0 6px 0">Matching Puzzle — Beginner</h2>
      <div class="small-note">Match pairs. All tiles are covered by bananas. Use hints if stuck.</div></div>

      <div class="stats" aria-hidden="false">
        <div class="stat"><span class="label">Hints</span><span class="value" id="hints"><?= $initialHints ?></span></div>
        <div class="stat"><span class="label">Attempt</span><span class="value" id="attempts"><?= $initialAttempts ?></span></div>
        <div class="stat"><span class="label">Score</span><span class="value" id="score">0</span></div>
        <div class="stat"><span class="label">Time Left</span><span class="value" id="time"><?= $timeLimit ?></span></div>
      </div>
    </div>

    <div class="center">
      <!-- board will be injected here -->
      <div id="board" aria-label="game board"></div>

      <div class="controls">
        <button id="useHint" class="hint-btn">Use Hint</button>
        <div class="small-note">Hints reveal all tiles for 2 seconds and subtract one hint.</div>
        <div class="footer">
          <button class="btn-quit" onclick="location.href='select_difficulty.php'">Back</button>
        </div>
      </div>
    </div>

    <div id="message" class="hidden" style="text-align:center;margin-top:14px;"></div>
  </div>

<script>
/* ========== Game config (from PHP) ========== */
const INITIAL_HINTS    = <?= (int)$initialHints ?>;
const INITIAL_ATTEMPTS = <?= (int)$initialAttempts ?>;
let TIME_LEFT = <?= (int)$timeLimit ?>;

/* ========== Images: put these files in images/ folder ========== */
/*
  images/apple.png
  images/orange.png
  images/strawberry.png
  images/avocado.png
  images/banana_cover.png
*/
const FRUITS = [
  'images/apple.png',
  'images/orange.png',
  'images/strawberry.png',
  'images/avocado.png'
];

/* ======== game state ======== */
let hints = INITIAL_HINTS;
let attemptsLeft = INITIAL_ATTEMPTS;
let score = 0;
let timerInterval = null;
let board = document.getElementById('board');
let firstIndex = null;
let secondIndex = null;
let lock = false;
let matchedCount = 0;
const TOTAL_TILES = 16;           // 4x4 grid
const TOTAL_PAIRS = TOTAL_TILES/2; // 8 pairs
let tiles = []; // will hold image path strings length 16

/* ======= helper functions ======= */
function shuffle(arr){
  for(let i=arr.length-1;i>0;i--){
    const j = Math.floor(Math.random()* (i+1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
}

/* build the tile list: 4 fruit types repeated 4 times (makes 2 pairs per fruit) */
function buildTiles(){
  tiles = [];
  // each fruit repeated 4 times -> total 16
  FRUITS.forEach(f => {
    for(let i=0;i<4;i++) tiles.push(f);
  });
  shuffle(tiles);
}

/* render the board grid */
function renderBoard(){
  board.innerHTML = '';
  // make grid 4x4
  board.style.gridTemplateColumns = 'repeat(4, 1fr)';
  for(let i=0;i<TOTAL_TILES;i++){
    const div = document.createElement('div');
    div.className = 'tile covered';
    div.dataset.index = i;
    div.dataset.front = tiles[i];
    // use an <img> to show cover initially
    const img = document.createElement('img');
    img.src = 'images/banana_cover.png';
    img.alt = 'covered tile';
    div.appendChild(img);
    div.addEventListener('click', onTileClick);
    board.appendChild(div);
  }
}

/* reveal tile at index (shows underlying image) */
function reveal(index){
  if(lock) return;
  const tile = board.querySelector(`.tile[data-index="${index}"]`);
  if(!tile || tile.classList.contains('matched') ) return;
  const img = tile.querySelector('img');
  img.src = tile.dataset.front; // show fruit
  tile.classList.add('revealed');
}

/* hide tile */
function hide(index){
  const tile = board.querySelector(`.tile[data-index="${index}"]`);
  if(!tile || tile.classList.contains('matched')) return;
  const img = tile.querySelector('img');
  img.src = 'images/banana_cover.png';
  tile.classList.remove('revealed');
}

/* mark as matched */
function markMatched(i, j){
  const ti = board.querySelector(`.tile[data-index="${i}"]`);
  const tj = board.querySelector(`.tile[data-index="${j}"]`);
  ti.classList.add('matched');
  tj.classList.add('matched');
  // small visual for matched
  ti.style.transform = 'scale(0.98)';
  tj.style.transform = 'scale(0.98)';
}

/* tile click handler */
function onTileClick(e){
  const idx = parseInt(e.currentTarget.dataset.index, 10);
  const tileEl = e.currentTarget;
  if(tileEl.classList.contains('revealed') || tileEl.classList.contains('matched')) return;
  if(lock) return;
  // reveal tile
  reveal(idx);

  if(firstIndex === null){
    firstIndex = idx;
    return;
  }
  if(secondIndex === null && idx !== firstIndex){
    secondIndex = idx;
    // check match
    lock = true;
    const a = board.querySelector(`.tile[data-index="${firstIndex}"]`).dataset.front;
    const b = board.querySelector(`.tile[data-index="${secondIndex}"]`).dataset.front;
    if(a === b){
      // matched
      setTimeout(() => {
        markMatched(firstIndex, secondIndex);
        matchedCount += 1;
        score += 10; // +10 per pair
        updateUI();
        firstIndex = null; secondIndex = null; lock = false;
        // if all pairs found -> win
        if(matchedCount === TOTAL_PAIRS) onWin();
      }, 350);
    } else {
      // not matched -> hide after short delay, reduce attempts
      attemptsLeft -= 1;
      updateUI();
      setTimeout(() => {
        hide(firstIndex);
        hide(secondIndex);
        firstIndex = null; secondIndex = null; lock = false;
        if(attemptsLeft <= 0){
          onLose();
        }
      }, 700);
    }
  }
}

/* Use Hint: reveal all for 2 seconds and decrement hints */
function useHint(){
  if(hints <= 0) {
    alert('No hints left!');
    return;
  }
  hints -= 1;
  updateUI();
  // reveal all tiles
  const all = board.querySelectorAll('.tile');
  all.forEach(t => {
    const img = t.querySelector('img');
    img.src = t.dataset.front;
  });
  // temporarily block clicks
  lock = true;
  setTimeout(() => {
    all.forEach(t => {
      if(!t.classList.contains('matched')){
        const img = t.querySelector('img');
        img.src = 'images/banana_cover.png';
      }
    });
    lock = false;
  }, 2000);
}

/* update UI top stats */
function updateUI(){
  document.getElementById('hints').textContent = hints;
  document.getElementById('attempts').textContent = attemptsLeft;
  document.getElementById('score').textContent = score;
  document.getElementById('time').textContent = TIME_LEFT;
}

/* Timer */
function startTimer(){
  timerInterval = setInterval(() => {
    TIME_LEFT -= 1;
    document.getElementById('time').textContent = TIME_LEFT;
    if(TIME_LEFT <= 0){
      clearInterval(timerInterval);
      onLose();
    }
  }, 1000);
}

/* Win handler: send points to server and then redirect to banana (math) game */
function onWin(){
  clearInterval(timerInterval);
  // Points: matched pairs * 10 (score already holds that)
  const pointsEarned = score; // use score as points
  // send to server to update beginner_points
  fetch('update_points.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ level: 'beginner', points: pointsEarned })
  }).then(r => r.json())
    .then(data => {
      if(data && data.ok){
        // success -> go to banana.php (Math / Banana API)
        alert('Great! Puzzle complete. Points saved. Moving to banana math game.');
        window.location.href = 'banana.php';
      } else {
        alert('Saved failed but continuing. Redirecting...');
        window.location.href = 'banana.php';
      }
    }).catch(err => {
      console.error(err);
      alert('Network error; redirecting to banana game.');
      window.location.href = 'banana.php';
    });
}

/* Lose handler */
function onLose(){
  lock = true;
  alert('Game over: no attempts or time left. Try again.');
  // optional: send zero points or just let user retry
  // reload page to restart game
  location.reload();
}

/* initialize game */
function init(){
  buildTiles();
  renderBoard();
  updateUI();
  startTimer();
  document.getElementById('useHint').addEventListener('click', useHint);
}
init();
</script>
</body>
</html>
