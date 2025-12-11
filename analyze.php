<?php // analyze.php ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nutri - Detector | Analyze</title>
  <link rel="stylesheet" href="assets/style.css">
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
    <div class="grid">
      <div class="card">
        <h1 class="h1">Nutri - Detector</h1>
        <p class="sub">Upload a food image to detect calories & nutrition.</p>

        <form action="upload.php" method="POST" enctype="multipart/form-data" class="upload-form" id="uploadForm">
          <input class="file-real" type="file" name="food_image" id="foodImage" accept="image/*" required>

          <label for="foodImage" class="file-ui">
            <span class="file-button-label">Select Photo</span>
            <div class="file-name" id="fileName">No file chosen (JPG, PNG, WEBP)</div>
          </label>

          <button class="btn" type="submit">✨ Analyze Food</button>
        </form>

        <div style="margin-top:14px; display:flex; gap:12px; flex-wrap:wrap;">
            <a class="btn-secondary" href="instructions.php">📘 Instructions & Supported Foods</a>
            <a class="btn-secondary" href="index.php">← Back to Home</a>
        </div>
        
      </div>

      <div class="right-panel">
        <div class="card">
          <h2 class="h2">Healthy Tips:</h2>
          <div class="tip-item">
            <div><b>🍏 Eat a variety of fruits</b> for essential vitamins.</div>
          </div>
          <div class="tip-item">
            <div><b>💧 Drink water</b> regularly throughout the day.</div>
          </div>
        </div>

        <div class="card">
          <h2 class="h2">How to Use:</h2>
          <ul class="list">
            <li>📷 Upload a picture of your food.</li>
            <li>🧠 The system analyzes your meal.</li>
            <li>📊 See calories and nutrition instantly.</li>
          </ul>
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

<script src="theme-switcher.js"></script>
<script>
    document.getElementById('foodImage').addEventListener('change', function(e) {
        const fileNameElement = document.getElementById('fileName');
        if (this.files && this.files.length > 0) {
            fileNameElement.textContent = this.files[0].name;
            fileNameElement.style.color = 'var(--text)'; // Make text stand out once file is chosen
        } else {
            fileNameElement.textContent = 'No file chosen (JPG, PNG, WEBP)';
            fileNameElement.style.color = 'var(--muted)';
        }
    });

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
</body>
</html>