<?php
include 'config.php';
include 'session.php';
require_login(); // redirects to login.php if not logged in

$username = $_SESSION['username'];

// Fetch user points
$sql = "SELECT beginner_points, intermediate_points, advanced_points FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

$flashMessage = $_SESSION['flash_message'] ?? '';
$flashType = $_SESSION['flash_message_type'] ?? 'info';
if ($flashMessage) {
    unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Banana Match Dashboard</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
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
      --card-bg: rgba(255, 244, 163, 0.08);
      --card-hover: rgba(255, 225, 53, 0.15);
      --text-light: #fff9e6;
      --text-muted: #d4c5a0;
    }

    html, body { 
      height: 100%; 
      margin: 0;
      overflow-x: hidden;
    }
    
    body {
      font-family: "Fredoka", sans-serif;
      background: linear-gradient(135deg, #2d2415 0%, #3a2f1a 50%, #4a3d20 100%);
      color: var(--text-light);
      min-height: 100vh;
      position: relative;
      padding: 90px 20px 40px 20px;
    }

    /* Animated floating bananas */
    .bg-bananas {
      position: fixed;
      inset: 0;
      pointer-events: none;
      overflow: hidden;
      z-index: 0;
    }
    
    .floating-banana {
      position: absolute;
      font-size: 40px;
      opacity: 0.15;
      animation: float 20s infinite ease-in-out;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      25% { transform: translateY(-30px) rotate(5deg); }
      50% { transform: translateY(-60px) rotate(-5deg); }
      75% { transform: translateY(-30px) rotate(3deg); }
    }

    /* Main container */
    .dashboard {
      position: relative;
      z-index: 1;
      width: 95%;
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 20px;
    }

    /* Header with logo and title */
    .header {
      text-align: center;
      margin-bottom: 30px;
      animation: slideDown 0.6s ease-out;
    }
    
    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .logo-title {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 15px;
    }

    .banana-icon {
      font-size: 48px;
      animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    h1 {
      margin: 0;
      font-size: 36px;
      font-weight: 700;
      background: linear-gradient(135deg, var(--banana-yellow) 0%, var(--banana-dark) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      text-shadow: 0 4px 12px rgba(255, 225, 53, 0.3);
    }

    .welcome {
      font-size: 15px;
      color: var(--text-light);
      margin-top: 8px;
    }

    /* Profile icon - top right - MADE SMALLER */
    .profile-badge {
      position: absolute;
      top: 20px;
      right: 20px;
      text-align: center;
      cursor: pointer;
      animation: slideLeft 0.6s ease-out;
      z-index: 10;
    }
    
    @keyframes slideLeft {
      from { opacity: 0; transform: translateX(30px); }
      to { opacity: 1; transform: translateX(0); }
    }

    .profile-circle {
      width: 55px;
      height: 55px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--banana-yellow), var(--banana-dark));
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      font-weight: 700;
      color: var(--bg-darker);
      border: 3px solid rgba(255, 255, 255, 0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 6px 18px rgba(255, 225, 53, 0.3);
    }
    
    .profile-circle:hover {
      transform: scale(1.1) rotate(5deg);
      box-shadow: 0 8px 24px rgba(255, 225, 53, 0.5);
    }

    .profile-name {
      margin-top: 6px;
      font-weight: 600;
      font-size: 12px;
      color: var(--text-light);
    }

    .profile-level {
      font-size: 11px;
      color: var(--text-muted);
      font-weight: 600;
    }

    /* Dropdown */
    .dropdown {
      position: absolute;
      right: 0;
      top: 95px;
      width: 260px;
      background: var(--bg-darker);
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
      overflow: hidden;
      transform-origin: top right;
      opacity: 0;
      pointer-events: none;
      transform: translateY(-10px) scale(0.95);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      z-index: 100;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .dropdown.open {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0) scale(1);
    }

    .points-section {
      padding: 18px;
      background: linear-gradient(180deg, rgba(255, 225, 53, 0.15), rgba(255, 184, 0, 0.05));
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .points-section h4 {
      margin: 0 0 14px 0;
      font-size: 15px;
      color: var(--banana-yellow);
      font-weight: 700;
    }
    
    .points-row {
      display: flex;
      justify-content: space-between;
      font-size: 13px;
      margin-bottom: 9px;
      color: var(--text-light);
      font-weight: 600;
    }
    
    .points-row span:last-child {
      color: var(--banana-yellow);
      font-weight: 700;
    }

    .dropdown a {
      display: block;
      padding: 14px 18px;
      color: var(--text-light);
      text-decoration: none;
      font-weight: 600;
      font-size: 13px;
      transition: background 0.2s ease;
      border-top: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .dropdown a:hover {
      background: rgba(255, 225, 53, 0.1);
    }

    /* Stats cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .stat-card {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 24px;
      text-align: center;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      animation: fadeInUp 0.6s ease-out backwards;
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .stat-card:hover {
      background: var(--card-hover);
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .stat-value {
      font-size: 46px;
      font-weight: 700;
      margin: 10px 0;
      background: linear-gradient(135deg, var(--banana-yellow), var(--banana-dark));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .stat-label {
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--text-muted);
      font-weight: 600;
    }

    /* Difficulty section */
    .difficulty-section {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 28px;
      padding: 32px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      animation: fadeInUp 0.6s ease-out 0.4s backwards;
    }

    .difficulty-header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 24px;
    }

    .difficulty-header h2 {
      margin: 0;
      font-size: 26px;
      font-weight: 700;
      color: var(--banana-yellow);
    }

    /* Difficulty buttons */
    .difficulty-buttons {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 16px;
      margin-bottom: 16px;
    }

    .diff-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 20px 28px;
      border-radius: 18px;
      font-size: 20px;
      font-weight: 700;
      text-decoration: none;
      color: var(--bg-darker);
      position: relative;
      overflow: hidden;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .diff-btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    
    .diff-btn:hover::before {
      width: 400px;
      height: 400px;
    }
    
    .diff-btn:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }
    
    .diff-btn span {
      position: relative;
      z-index: 1;
    }

    .btn-beginner {
      background: linear-gradient(135deg, #FFF4A3 0%, #FFE135 100%);
      color: #5a4000;
    }

    .btn-intermediate {
      background: linear-gradient(135deg, #FFD54F 0%, #FFA726 100%);
      color: #5a3000;
    }

    .btn-advanced {
      background: linear-gradient(135deg, #FFB74D 0%, #FF8F00 100%);
      color: #4a2800;
    }

    .tip {
      text-align: center;
      color: var(--text-muted);
      font-size: 14px;
      font-weight: 600;
      margin-top: 24px;
    }

    /* Flash message - GOLD THEME NOTIFICATION */
    .flash {
      position: fixed;
      top: 15px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1000;
      min-width: 320px;
      max-width: 500px;
      background: linear-gradient(to bottom right, #ca8a04, #eab308, #d97706);
      border-radius: 16px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
      overflow: hidden;
      animation: slideDownFlash 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
      position: relative;
    }

    @keyframes slideDownFlash {
      from {
        transform: translateX(-50%) translateY(-100px);
        opacity: 0;
      }
      to {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
      }
    }

    /* Shine effect for flash */
    .flash::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(to right, transparent, white, transparent);
      opacity: 0.2;
      animation: shine 3s ease-in-out infinite;
    }

    @keyframes shine {
      0% { transform: translateX(-100%) skewX(-15deg); }
      100% { transform: translateX(200%) skewX(-15deg); }
    }

    /* Top glow border */
    .flash::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(to right, #fde047, #fef08a, #fde047);
      animation: pulse 2s ease-in-out infinite;
    }

    .flash-content {
      position: relative;
      z-index: 1;
      padding: 20px 24px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .flash-icon-wrapper {
      position: relative;
      flex-shrink: 0;
    }

    .flash-icon-ping {
      position: absolute;
      inset: 0;
      background: #fde047;
      border-radius: 50%;
      animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
      opacity: 0.75;
    }

    @keyframes ping {
      75%, 100% {
        transform: scale(2);
        opacity: 0;
      }
    }

    .flash-icon {
      position: relative;
      background: linear-gradient(to bottom right, #fef08a, #fde047);
      padding: 12px;
      border-radius: 50%;
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
      color: #854d0e;
      font-size: 24px;
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: bounce 2s infinite;
    }

    .flash-text-wrapper {
      flex: 1;
      min-width: 0;
    }

    .flash-label {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;
    }

    .flash-label-icon {
      color: #fef3c7;
      font-size: 14px;
      animation: pulse 2s ease-in-out infinite;
    }

    .flash-label-text {
      color: #fef3c7;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    .flash-message {
      color: white;
      font-size: 16px;
      font-weight: 700;
      letter-spacing: 0.025em;
      line-height: 1.2;
    }

    .flash-stars {
      display: flex;
      align-items: center;
      gap: 4px;
      margin-top: 8px;
    }

    .flash-star {
      color: #fef3c7;
      font-size: 12px;
      animation: twinkle 1.5s ease-in-out infinite;
    }

    .flash-star:nth-child(2) {
      animation-delay: 0.2s;
    }

    .flash-star:nth-child(3) {
      animation-delay: 0.4s;
    }

    @keyframes twinkle {
      0%, 100% {
        opacity: 1;
        transform: scale(1);
      }
      50% {
        opacity: 0.4;
        transform: scale(0.8);
      }
    }

    .flash-close {
      flex-shrink: 0;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: #a16207;
      color: white;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0.8;
      font-size: 18px;
      font-weight: bold;
      transition: all 0.2s;
    }

    .flash-close:hover {
      background: #92400e;
      opacity: 1;
      transform: scale(1.1);
    }

    .flash-bottom-border {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(to right, #a16207, #d97706, #a16207);
      z-index: 2;
    }

    /* Responsive */
    @media (max-width: 768px) {
      body { padding: 75px 15px 30px 15px; }
      h1 { font-size: 28px; }
      .banana-icon { font-size: 40px; }
      .welcome { font-size: 14px; }
      .stats-grid { grid-template-columns: 1fr; gap: 14px; }
      .stat-card { padding: 20px; }
      .stat-value { font-size: 38px; }
      .difficulty-buttons { grid-template-columns: 1fr; }
      .difficulty-section { padding: 24px; border-radius: 20px; }
      .difficulty-header h2 { font-size: 22px; }
      .diff-btn { font-size: 18px; padding: 18px 24px; }
      .profile-badge { top: 15px; right: 15px; }
      .profile-circle { width: 50px; height: 50px; font-size: 20px; }
      .profile-name { font-size: 11px; }
      .dropdown { width: calc(100vw - 60px); right: -5px; }
      .flash { min-width: 280px; max-width: calc(100vw - 30px); }
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

  <!-- Flash Message Notification - GOLD THEME -->
  <?php if($flashMessage): ?>
    <div class="flash" id="flashNotification">
      <div class="flash-content">
        <!-- Trophy Icon with ping animation -->
        <div class="flash-icon-wrapper">
          <div class="flash-icon-ping"></div>
          <div class="flash-icon">🏆</div>
        </div>

        <!-- Text content -->
        <div class="flash-text-wrapper">
          <div class="flash-label">
            <span class="flash-label-icon">✨</span>
            <span class="flash-label-text">Progress Recovered!</span>
          </div>
          <div class="flash-message"><?= htmlspecialchars($flashMessage) ?></div>
          <div class="flash-stars">
            <span class="flash-star">⭐</span>
            <span class="flash-star">⭐</span>
            <span class="flash-star">⭐</span>
          </div>
        </div>

        <!-- Close button -->
        <button class="flash-close" onclick="document.getElementById('flashNotification').remove()">×</button>
      </div>
      <div class="flash-bottom-border"></div>
    </div>
    <script>
      setTimeout(() => {
        const flash = document.getElementById('flashNotification');
        if(flash) {
          flash.style.transition = 'all 0.4s ease-out';
          flash.style.opacity = '0';
          flash.style.transform = 'translateX(-50%) translateY(-100px)';
          setTimeout(() => flash.remove(), 400);
        }
      }, 5000);
    </script>
  <?php endif; ?>

  <!-- Profile badge with dropdown -->
  <div class="profile-badge" id="profileWrap">
    <div class="profile-circle" id="profileIcon" tabindex="0">
      <?= strtoupper(htmlspecialchars(substr($username, 0, 1))) ?>
    </div>
    <div class="profile-name"><?= htmlspecialchars($username) ?></div>
    <div class="profile-level">Player</div>

    <!-- Dropdown menu -->
    <div class="dropdown" id="profileDropdown">
      <div class="points-section">
        <h4>🏆 Your Points</h4>
        <div class="points-row">
          <span>Beginner</span>
          <span><?= htmlspecialchars($user['beginner_points'] ?? 0) ?></span>
        </div>
        <div class="points-row">
          <span>Intermediate</span>
          <span><?= htmlspecialchars($user['intermediate_points'] ?? 0) ?></span>
        </div>
        <div class="points-row">
          <span>Advanced</span>
          <span><?= htmlspecialchars($user['advanced_points'] ?? 0) ?></span>
        </div>
      </div>
      <a href="profile.php">📊 View Profile / History</a>
      <a href="logout.php">🚪 Logout</a>
    </div>
  </div>

  <div class="dashboard">
    <!-- Header -->
    <div class="header">
      <div class="logo-title">
        <div class="banana-icon">🍌</div>
        <h1>Banana Bliss</h1>
      </div>
      <div class="welcome">🎮 Welcome vinai, <?= htmlspecialchars($username) ?>! 👋</div>
    </div>

    <!-- Stats cards with real database values -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-label">Beginner Points</div>
        <div class="stat-value"><?= htmlspecialchars($user['beginner_points'] ?? 0) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Intermediate Points</div>
        <div class="stat-value"><?= htmlspecialchars($user['intermediate_points'] ?? 0) ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Advanced Points</div>
        <div class="stat-value"><?= htmlspecialchars($user['advanced_points'] ?? 0) ?></div>
      </div>
    </div>

    <!-- Difficulty selection -->
    <div class="difficulty-section">
      <div class="difficulty-header">
        <span>🎮</span>
        <h2>Select Difficulty</h2>
      </div>
      
      <div class="difficulty-buttons">
        <a href="puzzle_match.php?level=beginner" class="diff-btn btn-beginner">
          <span>🍌 Beginner</span>
        </a>
        <a href="banana.php" class="diff-btn btn-intermediate">
          <span>🍌 Intermediate</span>
        </a>
        <a href="advanced.php" class="diff-btn btn-advanced">
          <span>🍌 Advanced</span>
        </a>
      </div>
      
      <div class="tip">💡 Tip: Start with Beginner to unlock special banana skins!</div>
    </div>
  </div>

  <script>
    // Dropdown toggle functionality
    (function() {
      const icon = document.getElementById('profileIcon');
      const dropdown = document.getElementById('profileDropdown');
      const wrap = document.getElementById('profileWrap');

      function closeDropdown() {
        dropdown.classList.remove('open');
      }

      function openDropdown() {
        dropdown.classList.add('open');
      }

      function toggleDropdown() {
        dropdown.classList.toggle('open');
      }

      icon.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown();
      });

      // Close when clicking outside
      document.addEventListener('click', (e) => {
        if (!wrap.contains(e.target)) {
          closeDropdown();
        }
      });

      // Keyboard accessibility
      icon.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggleDropdown();
        } else if (e.key === 'Escape') {
          closeDropdown();
        }
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          closeDropdown();
        }
      });
    })();

    // Button ripple effect
    document.querySelectorAll('.diff-btn').forEach(button => {
      button.addEventListener('click', function(e) {
        const rect = this.getBoundingClientRect();
        const ripple = document.createElement('span');
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.style.position = 'absolute';
        ripple.style.borderRadius = '50%';
        ripple.style.background = 'rgba(255, 255, 255, 0.6)';
        ripple.style.transform = 'scale(0)';
        ripple.style.animation = 'ripple 0.6s ease-out';
        ripple.style.pointerEvents = 'none';
        
        this.appendChild(ripple);
        
        setTimeout(() => ripple.remove(), 600);
      });
    });

    // Add ripple animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes ripple {
        to {
          transform: scale(2);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>