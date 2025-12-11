<?php
// index.php (Home / Landing)
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Nutri - Detector | Home</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    /* Extra polish for HOME only (safe even if style.css is simple) */
    .hero {
      display: grid;
      grid-template-columns: 1.2fr 0.8fr;
      gap: 22px;
      align-items: stretch;
      width: min(980px, 92vw);
      margin: 0 auto;
    }
    .hero-card {
      padding: 28px;
      position: relative;
      overflow: hidden;
    }
    .hero-title {
      margin: 0 0 10px;
      font-size: 40px;
      letter-spacing: -0.5px;
      line-height: 1.05;
      color: var(--accent);
    }
    .hero-sub {
      margin: 0 0 18px;
      max-width: 56ch;
      opacity: 0.9;
    }
    .pill-row {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin: 14px 0 18px;
    }
    .pill {
      font-size: 13px;
      padding: 8px 10px;
      border-radius: 999px;
      background: rgba(47,179,74,0.12);
      border: 1px solid rgba(47,179,74,0.22);
      color: inherit;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .pill .dot {
      width: 8px;
      height: 8px;
      border-radius: 999px;
      background: var(--accent);
      display: inline-block;
    }

    .cta-row {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 18px;
    }
    .btn-primary {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 12px 16px;
      border-radius: 12px;
      background: var(--accent); /* Original color */
      color: #fff;
      text-decoration: none;
      font-weight: 700;
      box-shadow: 0 10px 26px rgba(0,0,0,0.12);
      /* --- NEW ANIMATION PROPERTIES --- */
      transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
    }
    .btn-primary:hover { 
      background: var(--accent); /* Slightly darker green on hover */
      transform: translateY(-2px); /* Lift effect */
      box-shadow: 0 14px 30px rgba(0,0,0,0.20); /* Larger shadow */
    }
    .btn-ghost {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 12px 16px;
      border-radius: 12px;
      background: transparent;
      color: inherit;
      text-decoration: none;
      font-weight: 700;
      border: 1px solid rgba(0,0,0,0.12);
      /* --- NEW ANIMATION PROPERTIES --- */
      transition: background 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
    }
    body.dark .btn-ghost { border-color: rgba(255,255,255,0.12); }
    .btn-ghost:hover {
      background: rgba(0,0,0,0.03); /* Light background for default mode */
      border-color: rgba(0,0,0,0.20);
      transform: translateY(-1px); /* Slight lift */
    }
    body.dark .btn-ghost:hover {
      background: rgba(255,255,255,0.08); /* Dark background for dark mode */
      border-color: rgba(255,255,255,0.20);
    }
    
    .mini-grid {
      display: grid;
      gap: 14px;
      height: 100%;
    }
    .mini {
      padding: 16px 18px;
    }
    .mini h3 {
      margin: 0 0 8px;
      font-size: 16px;
    }
    .mini p {
      margin: 0;
      opacity: 0.9;
      line-height: 1.5;
      font-size: 14px;
    }
    .badge-mini {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .badge-mini .ico {
      width: 34px; height: 34px;
      border-radius: 12px;
      display: grid;
      place-items: center;
      background: rgba(0,0,0,0.06);
    }
    body.dark .badge-mini .ico { background: rgba(255,255,255,0.08); }

    /* Make it responsive */
    @media (max-width: 900px){
      .hero { grid-template-columns: 1fr; }
      .hero-title { font-size: 34px; }
    }
  </style>
</head>
<body>

<?php
// Define current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
// Special case: make 'Analyze Food' active for both analyze.php and detect.php
$analyze_is_active = ($current_page === 'analyze.php' || $current_page === 'detect.php' ? 'active' : '');
?>
<div class="header-container">
  <div class="header-content">

    <div class="nav-bar">
      <ul class="nav-list">
        <li><a class="nav-link <?php echo ($current_page === 'index.php' ? 'active' : ''); ?>" href="index.php">Home</a></li>
        <li><a class="nav-link <?php echo $analyze_is_active; ?>" href="analyze.php">Analyze Food</a></li>
        <li><a class="nav-link <?php echo ($current_page === 'instructions.php' ? 'active' : ''); ?>" href="instructions.php">Instructions</a></li>
      </ul>
    </div>

    <div class="topbar">
      <span class="mode-label" id="themeLabel">Default</span>
      <label class="switch">
        <input type="checkbox" id="themeToggle">
        <span class="slider">
          <span class="icon sun">☀️</span>
          <span class="icon moon">🌙</span>
        </span>
      </label>
    </div>
  </div>
</div>

<div class="page">
  <div class="shell">

    <div class="hero">
      <!-- Left: Main landing card -->
      <div class="card hero-card">
        <div class="hero-title">Nutri - Detector</div>
        <p class="hero-sub">
          Upload a food photo and get a quick nutrition estimate (Calories, Protein, Carbs, Fat).
          This is a simple demo detector using image heuristics.
        </p>

        <div class="pill-row" aria-label="Supported foods">
          <span class="pill"><span class="dot"></span> Apple</span>
          <span class="pill"><span class="dot"></span> Banana</span>
          <span class="pill"><span class="dot"></span> Fried Chicken</span>
          <span class="pill"><span class="dot"></span> Burger</span>
          <span class="pill"><span class="dot"></span> Rice</span>
        </div>

        <div class="cta-row">
          <a class="btn-primary" href="analyze.php">🍽️ Start Analyzing</a>
          <a class="btn-ghost" href="instructions.php">📘 View Instructions</a>
        </div>

        <p class="sub" style="margin:16px 0 0;">
          Tip: Use clear, close-up photos for better results.
        </p>
      </div>

      <!-- Right: Feature cards -->
      <div class="mini-grid">
        <div class="card mini">
          <div class="badge-mini">
            <span class="ico">⚡</span>
            <span>Fast & Simple</span>
          </div>
          <p>Upload one food item per photo for the most accurate match.</p>
        </div>

        <div class="card mini">
          <div class="badge-mini">
            <span class="ico">🧠</span>
            <span>Smart Guessing</span>
          </div>
          <p>The detector uses dominant colors + texture to predict the food category.</p>
        </div>

        <div class="card mini">
          <div class="badge-mini">
            <span class="ico">🌗</span>
            <span>Theme Toggle</span>
          </div>
          <p>Switch between Default and Dark Mode anytime. Your choice is saved.</p>
        </div>
      </div>
    </div>

  </div>
</div>

<button class="fab" type="button" id="openCustomizer">🎨 Customize Theme</button>

<div id="themeCustomizer" class="customizer-panel">
  <div class="customizer-header">
    <h3>Design Your Theme</h3>
    <button id="customizerClose" class="close-btn" type="button">&times;</button>
  </div>

  <div class="customizer-body">
    <div class="color-option">
      <label for="bgColorPicker">Background Color</label>
      <input type="color" id="bgColorPicker" value="#f2b861"> 
    </div>

    <div class="color-option">
      <label for="cardColorPicker">Card Color</label>
      <input type="color" id="cardColorPicker" value="#fbf1df">
    </div>

    <div class="color-option">
      <label for="accentColorPicker">Accent / Buttons</label>
      <input type="color" id="accentColorPicker" value="#29b34a">
    </div>
  </div>

  <button id="resetTheme" class="btn-reset" type="button">Reset to Defaults</button>
</div>
<script>
    // This script runs right after the panel is defined to set the initial colors
    document.addEventListener("DOMContentLoaded", () => {
        const rootStyles = getComputedStyle(document.documentElement);
        
        // 1. Set the initial color values for the pickers from CSS variables
        document.getElementById('bgColorPicker').value = 
            rootStyles.getPropertyValue('--bg').trim() || '#f2b861';
        
        document.getElementById('cardColorPicker').value = 
            rootStyles.getPropertyValue('--card').trim() || '#fbf1df';
        
        document.getElementById('accentColorPicker').value = 
            rootStyles.getPropertyValue('--accent').trim() || '#29b34a';

        // 2. Also ensure the FAB button has the correct ID
        const fabBtn = document.querySelector('.fab');
        if (fabBtn && fabBtn.id !== 'openCustomizer') {
            fabBtn.id = 'openCustomizer';
        }
    });
</script>
<script src="theme-switcher.js"></script>
</div>
</body>
</html>
