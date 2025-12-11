<?php
require "nutrition_data.php";

/**
 * Heuristic detection (color + texture). Not real AI.
 *
 * Fixes in this version:
 * ✅ Fried chicken works even on white background (common PNG/JPG cutouts)
 * ✅ Rice won't steal chicken if orange/brown is strong
 * ✅ Banana/apple/burger/rice/chicken supported
 * ✅ Non-food => unknown
 * ✅ Dark mode toggle works (localStorage) for this page
 */

function load_image_gd(string $path) {
  if (!file_exists($path)) return null;
  $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
  if (($ext === "jpg" || $ext === "jpeg") && function_exists("imagecreatefromjpeg")) return @imagecreatefromjpeg($path);
  if ($ext === "png" && function_exists("imagecreatefrompng")) return @imagecreatefrompng($path);
  if ($ext === "webp" && function_exists("imagecreatefromwebp")) return @imagecreatefromwebp($path);
  return null;
}

function to_truecolor($img) {
  if (!$img) return null;
  if (function_exists("imageistruecolor") && imageistruecolor($img)) return $img;

  $w = imagesx($img); $h = imagesy($img);
  if ($w <= 0 || $h <= 0) return $img;

  $true = imagecreatetruecolor($w, $h);
  imagealphablending($true, false);
  imagesavealpha($true, true);
  $transparent = imagecolorallocatealpha($true, 0, 0, 0, 127);
  imagefill($true, 0, 0, $transparent);

  imagecopy($true, $img, 0, 0, 0, 0, $w, $h);
  imagedestroy($img);
  return $true;
}

function rgb_to_hsv(int $r, int $g, int $b): array {
  $r /= 255; $g /= 255; $b /= 255;
  $max = max($r,$g,$b);
  $min = min($r,$g,$b);
  $delta = $max - $min;

  if ($delta == 0) $h = 0;
  else if ($max == $r) $h = 60 * fmod((($g - $b) / $delta), 6);
  else if ($max == $g) $h = 60 * ((($b - $r) / $delta) + 2);
  else $h = 60 * ((($r - $g) / $delta) + 4);
  if ($h < 0) $h += 360;

  $s = ($max == 0) ? 0 : ($delta / $max);
  $v = $max;
  return [$h, $s, $v];
}

function analyze_image(string $path): array {
  if (!function_exists("imagecreatefromjpeg")) {
    return ["ok" => false, "error" => "GD library is not enabled."];
  }

  $img = load_image_gd($path);
  if (!$img) return ["ok" => false, "error" => "Unsupported image type. Use JPG/PNG/WEBP."];

  $img = to_truecolor($img);

  $w = imagesx($img);
  $h = imagesy($img);
  if ($w <= 0 || $h <= 0) {
    imagedestroy($img);
    return ["ok" => false, "error" => "Invalid image."];
  }

  $sample = 95;
  $stepX = max(1, (int)floor($w / $sample));
  $stepY = max(1, (int)floor($h / $sample));

  $counts = [
    "yellow" => 0, "orange" => 0, "red" => 0, "green" => 0,
    "brown" => 0, "tan" => 0, "white" => 0, "skin" => 0,
    "black" => 0, "gray" => 0,
    "total" => 0,
  ];

  $textureHits = 0;
  $textureChecks = 0;
  $texThresh = 0.18;

  for ($y = 0; $y < $h; $y += $stepY) {
    for ($x = 0; $x < $w; $x += $stepX) {
      $rgb = imagecolorat($img, $x, $y);
      $r = ($rgb >> 16) & 0xFF;
      $g = ($rgb >> 8) & 0xFF;
      $b = $rgb & 0xFF;

      [$hh, $ss, $vv] = rgb_to_hsv($r, $g, $b);
      $counts["total"]++;

      // black/gray/white
      if ($vv < 0.18) { $counts["black"]++; continue; }
      if ($ss < 0.10 && $vv >= 0.18 && $vv <= 0.82) { $counts["gray"]++; continue; }
      if ($ss < 0.12 && $vv > 0.82) { $counts["white"]++; continue; }

      // pale yellow (banana help)
      if ($hh >= 35 && $hh <= 95 && $vv > 0.70 && $ss >= 0.07 && $ss <= 0.28) {
        $counts["yellow"]++;
        continue;
      }

      // skin (tight)
      if ($hh >= 5 && $hh <= 35 && $ss >= 0.18 && $ss <= 0.65 && $vv >= 0.30 && $vv <= 0.90) {
        if ($r > $g && $g >= $b) $counts["skin"]++;
      }

      // strong yellow
      if ($hh >= 45 && $hh <= 95 && $ss > 0.18 && $vv > 0.25) { $counts["yellow"]++; continue; }

      // orange
      if ($hh >= 18 && $hh < 45 && $ss > 0.28 && $vv > 0.25) { $counts["orange"]++; }

      // red
      if (($hh >= 330 || $hh <= 15) && $ss > 0.20 && $vv > 0.18) { $counts["red"]++; }

      // green
      if ($hh >= 80 && $hh <= 160 && $ss > 0.18 && $vv > 0.18) { $counts["green"]++; }

      // brown
      if ($hh >= 10 && $hh <= 60 && $ss > 0.14 && $vv >= 0.32 && $vv <= 0.72) { $counts["brown"]++; }

      // tan (bun/bread)
      if ($hh >= 15 && $hh <= 70 && $ss > 0.10 && $ss < 0.55 && $vv >= 0.55 && $vv <= 0.93) { $counts["tan"]++; }

      // texture compare
      if ($x + $stepX < $w) {
        $rgb2 = imagecolorat($img, $x + $stepX, $y);
        $r2 = ($rgb2 >> 16) & 0xFF; $g2 = ($rgb2 >> 8) & 0xFF; $b2 = $rgb2 & 0xFF;
        [, , $v2] = rgb_to_hsv($r2, $g2, $b2);
        $textureChecks++;
        if (abs($vv - $v2) > $texThresh) $textureHits++;
      }
      if ($y + $stepY < $h) {
        $rgb3 = imagecolorat($img, $x, $y + $stepY);
        $r3 = ($rgb3 >> 16) & 0xFF; $g3 = ($rgb3 >> 8) & 0xFF; $b3 = $rgb3 & 0xFF;
        [, , $v3] = rgb_to_hsv($r3, $g3, $b3);
        $textureChecks++;
        if (abs($vv - $v3) > $texThresh) $textureHits++;
      }
    }
  }

  imagedestroy($img);

  $total = max(1, $counts["total"]);
  $pct = [];
  foreach ($counts as $k => $v) {
    if ($k === "total") continue;
    $pct[$k] = $v / $total;
  }

  $texture = ($textureChecks > 0) ? ($textureHits / $textureChecks) : 0.0;
  return ["ok" => true, "pct" => $pct, "texture" => $texture, "samples" => $counts["total"]];
}

function looks_like_non_food(array $pct, float $texture): array {
  $white = $pct["white"] ?? 0;
  $black = $pct["black"] ?? 0;
  $gray  = $pct["gray"] ?? 0;
  $skin  = $pct["skin"] ?? 0;

  $yellow = $pct["yellow"] ?? 0;
  $orange = $pct["orange"] ?? 0;
  $red    = $pct["red"] ?? 0;
  $green  = $pct["green"] ?? 0;
  $brown  = $pct["brown"] ?? 0;
  $tan    = $pct["tan"] ?? 0;

  $foodColor = $yellow + $orange + $red + $green + $brown + $tan;
  $mono = $white + $black + $gray;


if ($mono > 0.45 && $skin > 0.05 && $foodColor < 0.30) return [true, "Looks like a person/poster (High skin/low food color)."];

  if ($mono > 0.75 && $foodColor < 0.18) return [true, "Looks like a non-food image (Monochromatic)."];
  if ($foodColor < 0.14 && $texture < 0.10) return [true, "Very low food-color signal."];
  return [false, ""];
}

function predict_food(array $pct, float $texture): array {
  [$nonFood, $why] = looks_like_non_food($pct, $texture);
  if ($nonFood) return ["food" => "unknown", "confidence" => 0, "reason" => $why];

  $yellow = $pct["yellow"] ?? 0;
  $orange = $pct["orange"] ?? 0;
  $red    = $pct["red"] ?? 0;
  $green  = $pct["green"] ?? 0;
  $brown  = $pct["brown"] ?? 0;
  $tan    = $pct["tan"] ?? 0;
  $white  = $pct["white"] ?? 0;
  $gray   = $pct["gray"] ?? 0;

  $orangeBrown = $orange + $brown;

  // ✅ FRIED CHICKEN FIRST (handles white-background chicken cutouts)
  // Requirements: strong orange/brown presence AND some texture.
  if ($orangeBrown >= 0.45 && $texture >= 0.06) {
    $score = ($orange * 1.4) + ($brown * 1.2) + ($texture * 2.0) - ($tan * 0.3);
    return ["food" => "fried chicken", "confidence" => min(1.0, $score / 1.20), "reason" => "Strong orange/brown + texture (fried chicken)."];
  }

  // ✅ RICE: high white, low orange/brown, low texture
  if (
    $white >= 0.32 &&
    $orangeBrown < 0.25 &&
    $texture < 0.24
  ) {
    $conf = min(1.0, ($white / 0.60));
    return ["food" => "rice", "confidence" => $conf, "reason" => "White dominant + low orange/brown (rice)."];
  }

  // ✅ BANANA
  $bananaSignal = $yellow + ($tan * 0.25);
  if ($bananaSignal > 0.12 && $yellow > 0.06 && $bananaSignal > ($orange * 0.55)) {
    return ["food" => "banana", "confidence" => min(1.0, $bananaSignal / 0.30), "reason" => "Yellow/pale-yellow dominant."];
  }
 // APPLE
$appleSignal = max($red, $green);
$skin = $tan; // or $orangeBrown

if ($skin > 0.03) {
    $appleSignal = max(0, $appleSignal - ($skin * 1.5));  // penalty
}

if ($appleSignal > 0.12 && $orange < 0.22) {
    return [
        "food" => "apple",
        "confidence" => min(1.0, $appleSignal / 0.25),
        "reason" => "Red/Green ratio"
    ];
}

  // ✅ BURGER (bun+patty)
  $bunPatty = ($tan * 1.45) + ($brown * 1.10);
  if ($bunPatty >= 0.30) {
    return ["food" => "burger", "confidence" => min(1.0, $bunPatty / 0.70), "reason" => "Tan bun + brown patty dominant."];
  }

  // ✅ fallback chicken (weaker)
  if ($orangeBrown >= 0.30 && $texture >= 0.08 && $white < 0.55) {
    $score = ($orange * 1.1) + ($brown * 1.1) + ($texture * 2.0) - ($white * 0.6) - ($gray * 0.2);
    if ($score > 0.35) {
      return ["food" => "fried chicken", "confidence" => min(1.0, $score / 1.10), "reason" => "Orange/brown + texture suggests fried chicken."];
    }
  }

  return ["food" => "unknown", "confidence" => 0, "reason" => "Not confident enough."];
}

// ------------------ Main ------------------
$img = $_GET["img"] ?? "";
$imgSafe = basename($img);
$imgPath = __DIR__ . "/uploads/" . $imgSafe;

$predictedFood = "unknown";
$result = null;
$error = null;
$debug = null;

if ($imgSafe !== "" && file_exists($imgPath)) {
  $analysis = analyze_image($imgPath);
  if (!$analysis["ok"]) {
    $error = $analysis["error"];
  } else {
    $pct = $analysis["pct"];
    $texture = $analysis["texture"];
    $pred = predict_food($pct, $texture);

    $predictedFood = $pred["food"];
    $reason = $pred["reason"] ?? "";

    if ($predictedFood !== "unknown") {
      $result = $nutrition[$predictedFood] ?? null;
      if ($result === null) $predictedFood = "unknown";
    }

    $debug = [
      "confidence" => round(($pred["confidence"] ?? 0) * 100, 1) . "%",
      "reason" => $reason,
      "texture" => round($texture * 100, 1) . "%",
      "samples" => (int)($analysis["samples"] ?? 0),
      "pct" => array_map(fn($v) => round($v * 100, 1) . "%", $pct),
    ];
  }
} else {
  $error = "No image file found to analyze.";
}

$isUnknown = ($predictedFood === "unknown" || $result === null);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Nutri - Detector | Result</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="header-container">
  <div class="header-content">
    <div class="nav-bar">
      <ul class="nav-list">
        <li><a class="nav-link" href="index.php">Home</a></li>
        <li><a class="nav-link active" href="analyze.php">Analyze Food</a></li>
        <li><a class="nav-link" href="instructions.php">Instructions</a></li>
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
    <div class="card center-card">
      
      <?php if ($imgSafe !== "" && file_exists($imgPath)): ?>
        <div style="margin-bottom: 20px; text-align: center;">
          <img src="uploads/<?= htmlspecialchars($imgSafe) ?>" 
               alt="Uploaded Food" 
               class="preview" 
               style="max-width: 100%; height: auto; border-radius: 15px; box-shadow: var(--shadow);">
        </div>
      <?php endif; ?>

      <h1 class="h1">Analysis Result</h1>
      <p class="sub">Food detection powered by basic image heuristics.</p>
      
      <div style="margin-top:20px;">
        <div class="badge <?= $isUnknown ? 'unknown' : '' ?>">
          <?= $isUnknown ? '⚠️ UNKNOWN FOOD' : '✅ DETECTED FOOD' ?>
        </div>
        <h2 class="h2" style="font-size: 28px; margin-bottom: 15px;"><?= htmlspecialchars(ucwords($predictedFood)) ?></h2>
      </div>

      <?php if ($result): ?>
        <table class="table">
          <tr>
            <td>Confidence</td>
            <td><?= htmlspecialchars($debug["confidence"] ?? 'N/A') ?></td>
          </tr>
          <tr>
            <td>Calories</td>
            <td><?= htmlspecialchars($result["calories"]) ?> kcal</td>
          </tr>
          <tr>
            <td>Protein</td>
            <td><?= htmlspecialchars($result["protein"]) ?>g</td>
          </tr>
          <tr>
            <td>Fat</td>
            <td><?= htmlspecialchars($result["fat"]) ?>g</td>
          </tr>
          <tr>
            <td>Carbs</td>
            <td><?= htmlspecialchars($result["carbs"]) ?>g</td>
          </tr>
        </table>
        
        <p class="tip" style="margin-top:20px;">
          *This is an estimate for a typical serving size (~100g) of 
          <b><?= htmlspecialchars(ucwords($predictedFood)) ?></b>.
        </p>
      <?php else: ?>
        <p style="color:red; font-weight:700;"><?= htmlspecialchars($error ?? 'An unknown error occurred.') ?></p>
      <?php endif; ?>

      <div style="margin-top:18px; display:flex; gap:12px; flex-wrap:wrap;">
        <a class="btn" href="analyze.php">Analyze Another Photo</a>
        <a class="btn-secondary" href="index.php">← Back to Home</a>
      </div>
      
      <div style="margin-top:30px; border-top: 1px solid var(--border); padding-top:20px;">
        <h3 class="h2" style="font-size:16px; color:var(--muted);">Analysis Debug Info</h3>
        <p class="tip">Reason: <?= htmlspecialchars($debug["reason"] ?? "N/A") ?> (Texture: <?= htmlspecialchars($debug["texture"] ?? "N/A") ?>)</p>

        <?php if (isset($debug["pct"])): ?>
          <div style="display:flex; gap:20px; flex-wrap:wrap; margin-top:10px;">
            <ul class="list" style="margin-top:0;">
              <li>Yellow: <?= htmlspecialchars($debug["pct"]["yellow"] ?? "") ?></li>
              <li>Orange: <?= htmlspecialchars($debug["pct"]["orange"] ?? "") ?></li>
              <li>Red: <?= htmlspecialchars($debug["pct"]["red"] ?? "") ?></li>
              <li>Green: <?= htmlspecialchars($debug["pct"]["green"] ?? "") ?></li>
            </ul>
            <ul class="list" style="margin-top:0;">
              <li>Brown: <?= htmlspecialchars($debug["pct"]["brown"] ?? "") ?></li>
              <li>Tan: <?= htmlspecialchars($debug["pct"]["tan"] ?? "") ?></li>
              <li>White: <?= htmlspecialchars($debug["pct"]["white"] ?? "") ?></li>
              <li>Skin: <?= htmlspecialchars($debug["pct"]["skin"] ?? "") ?></li>
            </ul>
            <ul class="list" style="margin-top:0;">
              <li>Gray: <?= htmlspecialchars($debug["pct"]["gray"] ?? "") ?></li>
              <li>Black: <?= htmlspecialchars($debug["pct"]["black"] ?? "") ?></li>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<button class="fab" id="openCustomizer">🎨 Customize Theme</button>

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
  <button id="resetTheme" class="btn-reset">Reset to Defaults</button>
</div>

<script src="theme-switcher.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const rootStyles = getComputedStyle(document.documentElement);
        document.getElementById('bgColorPicker').value = rootStyles.getPropertyValue('--bg').trim() || '#f2b861';
        document.getElementById('cardColorPicker').value = rootStyles.getPropertyValue('--card').trim() || '#fbf1df';
        document.getElementById('accentColorPicker').value = rootStyles.getPropertyValue('--accent').trim() || '#29b34a';
    });
</script>
</body>
</html>