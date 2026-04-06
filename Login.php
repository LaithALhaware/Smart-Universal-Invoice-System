<?php
session_start();


include "Static/config.php";



$error = '';

// ── Hardcoded credentials (change these or move to a config file) ──
$USERS = [
    'admin' => password_hash('admin123', PASSWORD_DEFAULT),
    'arb'   => password_hash('arb2024',  PASSWORD_DEFAULT),
];

// ── Handle login ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (isset($USERS[$user]) && password_verify($pass, $USERS[$user])) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username']  = $user;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Incorrect username or password.';
    }
}

// ── Already logged in ──
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?=translate('Login',$_SESSION['Lang'])['translated']?> — <?=$settings['company_name']?> </title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>

    <link rel="stylesheet" href="Static/css/Main.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&family=Inter:wght@300;400;700&display=swap" rel="stylesheet">

<style>
.bodyLogin {
   <?= $_SESSION['Direction']=='right' ? "font-family: 'Cairo', sans-serif;" : "font-family: 'Inter', 'Cairo', sans-serif;" ?> 
}
  
    .spinner {
      display: inline-block; width: 14px; height: 14px;
      border: 2px solid rgba(7,9,15,.35); border-top-color: #07090f;
      border-radius: 50%; animation: spin .6s linear infinite;
      vertical-align: middle;
      margin-<?= $_SESSION['Direction']=='right' ? 'left' : 'right' ?>: 6px;
    }

    .topbar-brand {margin-<?= $_SESSION['Direction']=='right' ? 'left' : 'right' ?>: auto;}
    .theme-switcher {<?= $_SESSION['Direction']=='right' ? 'left' : 'right' ?>: 24px;}
    .theme-swatch {<?= $_SESSION['Direction']=='right' ? 'flex-direction: row-reverse;' : '' ?>}
    .theme-swatch:hover {transform: <?= $_SESSION['Direction']=='right' ? 'translateX(3px)' : 'translateX(-3px)' ?>;}
    .switchers-wrap {<?= $_SESSION['Direction']=='right' ? 'left' : 'right' ?>: 24px; }
    .sw-palette {<?= $_SESSION['Direction']=='right' ? 'left' : 'right' ?>: 0px;}
  </style>


<link rel="icon" href="/Static/IMG/icon.svg" type="image/svg+xml">

</head>
<body class="bodyLogin"  dir="<?= $_SESSION['Direction']=='right' ? "rtl" : "ltl" ?>">

  <!-- ── LEFT ── -->
  <div class="left">
    <div class="left-top">
      <div class="brand-mark">
        <div class="brand-icon">
          <?php if(!empty($settings['logo_path'])): ?>
          <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="logo" style="height: 24px;">
        <?php else: ?>

          <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" x="0" y="0" viewBox="0 0 512.006 512.006" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M184.002 368.002h-7.572c-7.082 0-13.578-4.432-15.689-11.192-2.556-8.187 1.723-16.748 9.521-19.738l120.016-46.159c8.429-3.242 12.477-12.799 8.999-21.134-47.344-113.465-59.191-253.421-59.325-255.067-1.219-15.054-20.837-20.202-29.26-7.59l-23.13 34.69-34.68-23.12c-9.179-6.127-21.719-1.313-24.4 9.43l-10.5 41.97-29.1-19.4c-9.179-6.127-21.719-1.313-24.4 9.43l-10.5 41.97-29.1-19.4C13.796 75.301-.992 83.956.052 97.242c.32 4.15 8.23 102.86 38.24 203.34 17.88 59.85 39.98 107.77 65.68 142.45 18.034 24.311 45.676 36.97 73.602 36.97h5.579c30.732 0 56.376-24.405 56.843-55.134.475-31.275-24.826-56.866-55.994-56.866zm-102.86-170.06c-3.28-8.2.71-17.51 8.92-20.8l80-32c8.2-3.28 17.51.71 20.8 8.92 3.28 8.2-.71 17.51-8.92 20.8-86.636 34.654-81.523 33.14-85.94 33.14-6.34 0-12.35-3.8-14.86-10.06zm36.8 72.92c-8.085 3.234-17.472-.619-20.8-8.92-3.28-8.2.71-17.51 8.92-20.8l80-32c8.2-3.28 17.51.71 20.8 8.92 3.28 8.2-.71 17.51-8.92 20.8zm314.06 1.14c-4.179 0 4.086-2.709-111.59 41.78-.141-.234-77.388 29.589-77.257 29.538-5.69 2.189-6.909 9.647-2.262 13.593 48.686 41.344 38.574 118.94-19.023 146.514-4.3 2.059-2.859 8.567 1.908 8.575 13.287.022 26.037-3.221 37.184-9.04l172.53-71.04a15.9 15.9 0 0 0 8.68-3.57c46.758-19.245 44.562-17.829 47.74-21.15 48.334-50.675 12.124-135.2-57.91-135.2zM411.316 187.316c-6.238 6.236-25.779 6.22-32 0-6.248-6.248-16.379-6.249-22.627 0-6.249 6.249-6.248 16.379 0 22.627 6.711 6.711 16.326 11.272 27.313 13.126v16.933c0 8.836 7.163 16 16 16s16-7.164 16-16v-19.296c41.992-14.273 40.789-70.499 2.407-86.397l-24.568-10.177c-12.786-5.295-15.084-28.129 10.848-28.129 7.815 0 13.667 2.354 16 4.687 6.248 6.248 16.379 6.249 22.627 0 6.249-6.249 6.248-16.379 0-22.627-6.711-6.711-16.326-11.272-27.313-13.126V48.002c0-8.836-7.163-16-16-16s-16 7.164-16 16v19.296c-41.628 14.149-41.118 70.363-2.407 86.397l24.568 10.177c9.215 3.817 12.589 15.987 5.152 23.444z" fill="#ffffff" opacity="1" data-original="#000000"></path></g></svg>
          
          <?php endif; ?>
        </div>
        <span class="brand-name"> <?=$settings['company_name']?></span>
      </div>

      <div class="left-headline">
        <?=translate('Your billing,',$_SESSION['Lang'])['translated']?><br><?=translate('fully in control.',$_SESSION['Lang'])['translated']?>
      </div>
      <div class="left-sub">
<?=translate('Manage invoices, payments, expenses, and reports — all from one place, designed for speed.',$_SESSION['Lang'])['translated']?>
      </div>
    </div>

    <div class="left-stats">
      <div class="stat-card">
        <div class="stat-num">100%</div>
        <div class="stat-label"><?=translate('Local & Fast',$_SESSION['Lang'])['translated']?></div>
      </div>
      <div class="stat-card">
        <div class="stat-num">∞</div>
        <div class="stat-label"><?=translate('invoices',$_SESSION['Lang'])['translated']?></div>
      </div>
      <div class="stat-card">
        <div class="stat-num">+100</div>
        <div class="stat-label"><?=translate('Currency',$_SESSION['Lang'])['translated']?></div>
      </div>
      <div class="stat-card">
        <div class="stat-num">+10</div>
        <div class="stat-label"><?=translate('Language',$_SESSION['Lang'])['translated']?></div>
      </div>
    </div>
  </div>

  <!-- ── RIGHT ── -->
  <div class="right">
    <div class="login-box">

      <div class="login-heading"><?=translate('Welcome back',$_SESSION['Lang'])['translated']?></div>
      <div class="login-sub"><?=translate('Sign in to your dashboard',$_SESSION['Lang'])['translated']?></div>

      <?php if ($error): ?>
      <div class="alert-error">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?=translate($error,$_SESSION['Lang'])['translated']?>
      </div>
      <?php endif; ?>

      <form method="POST" autocomplete="on">

        <!-- Username -->
        <div class="field">
          <label class="field-label" for="username"><?=translate('Username',$_SESSION['Lang'])['translated']?></label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </span>
            <input
              class="field-input"
              type="text"
              id="username"
              name="username"
              placeholder="<?=translate('Enter your username',$_SESSION['Lang'])['translated']?>"
              autocomplete="username"
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
              required
            >
          </div>
        </div>

        <!-- Password -->
        <div class="field">
          <label class="field-label" for="password"><?=translate('Password',$_SESSION['Lang'])['translated']?></label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </span>
            <input
              class="field-input"
              type="password"
              id="password"
              name="password"
              placeholder="<?=translate('Enter your password',$_SESSION['Lang'])['translated']?>"
              autocomplete="current-password"
              required
            >
            <button type="button" class="toggle-pass" onclick="togglePwd()" tabindex="-1" aria-label="Show password">
              <svg id="eyeIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Remember row -->
        <div class="remember-row">
          <label class="remember-label">
            <input class="remember-cb" type="checkbox" name="remember">
            <?=translate('Remember me',$_SESSION['Lang'])['translated']?>
          </label>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-login">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" y1="12" x2="3" y2="12"/>
          </svg>
          <?=translate('Sign In',$_SESSION['Lang'])['translated']?>
        </button>

      </form>

      <div class="login-divider"><?=translate($settings['country'],$_SESSION['Lang'])['translated']?> · <?=translate($settings['city'],$_SESSION['Lang'])['translated']?></div>

      <div class="login-footer">
        <?=translate('Protected area. Authorized personnel only.',$_SESSION['Lang'])['translated']?>
      </div>

    </div>
  </div>

<script>
  function togglePwd() {
    var inp  = document.getElementById('password');
    var icon = document.getElementById('eyeIcon');
    var show = inp.type === 'password';
    inp.type = show ? 'text' : 'password';
    icon.innerHTML = show
      ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
      : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }

  /* shake animation on error */
  <?php if($error): ?>
  document.querySelector('.login-box').style.animation = 'shake .4s ease';
  <?php endif; ?>
</script>

<style>
@keyframes shake {
  0%,100% { transform: translateX(0);   }
  20%      { transform: translateX(-8px);}
  40%      { transform: translateX(8px); }
  60%      { transform: translateX(-5px);}
  80%      { transform: translateX(5px); }
}
</style>


<?php
// ── Fetch languages from Python API ───────────────────────────────────────────
$langsRaw = @file_get_contents('http://localhost:9000/languages');
$allLangs = $langsRaw ? json_decode($langsRaw, true) : [
    ['code'=>'en','name'=>'English','name_en'=>'English','dir'=>'left','flag'=>'🇬🇧'],
    ['code'=>'ar','name'=>'العربية','name_en'=>'Arabic', 'dir'=>'right','flag'=>'🇸🇦'],
];
$currentLang = $_SESSION['Lang'] ?? 'en';
?>


<!-- ═══════════════════════════════════════════════════════════════════════════
     BOTH SWITCHERS — paste once before </body>
══════════════════════════════════════════════════════════════════════════════ -->
<div class="switchers-wrap" id="switchersWrap">

    <!-- ── LANGUAGE ── -->
    <div class="switcher-col" id="langSwitcher">
        <button class="sw-toggle-btn" onclick="togglePanel('langPalette')"
                title="Change language" aria-label="Change language">
            🌐
        </button>
        <div class="sw-palette lang-palette" id="langPalette">
            <?php foreach ($allLangs as $l): ?>
            <a class="sw-swatch <?= $l['code'] === $currentLang ? 'active' : '' ?>"
               href="?Lang=<?= htmlspecialchars($l['code']) ?>&Direction=<?= htmlspecialchars($l['dir']) ?>">
                <span class="swatch-flag"><?= htmlspecialchars($l['flag'] ?? '🌐') ?></span>
                <?= htmlspecialchars(translate($l['name_en'], $currentLang)['translated'] ?? $l['name_en']) ?>
                <span class="dir-badge"><?= strtoupper($l['dir']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── THEME ── -->
    <div class="switcher-col" id="themeSwitcher">
        <button class="sw-toggle-btn" onclick="togglePanel('themePalette')"
                title="Change theme" aria-label="Change theme">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
        </button>
        <div class="sw-palette" id="themePalette">
            <div class="sw-swatch" onclick="setTheme('')">
                <span class="swatch-dot" style="background:#206bc4"></span>
                <?= translate('Blue (Default)', $currentLang)['translated'] ?>
            </div>
            <div class="sw-swatch" onclick="setTheme('green')">
                <span class="swatch-dot" style="background:#1a7f4b"></span>
                <?= translate('Green', $currentLang)['translated'] ?>
            </div>
            <div class="sw-swatch" onclick="setTheme('purple')">
                <span class="swatch-dot" style="background:#7c3aed"></span>
                <?= translate('Purple', $currentLang)['translated'] ?>
            </div>
            <div class="sw-swatch" onclick="setTheme('red')">
                <span class="swatch-dot" style="background:#dc2626"></span>
                <?= translate('Red', $currentLang)['translated'] ?>
            </div>
            <div class="sw-swatch" onclick="setTheme('orange')">
                <span class="swatch-dot" style="background:#d97706"></span>
                <?= translate('Orange', $currentLang)['translated'] ?>
            </div>
            <div class="sw-swatch" onclick="setTheme('dark')">
                <span class="swatch-dot" style="background:#1e293b;border:1.5px solid #334155"></span>
                <?= translate('Dark', $currentLang)['translated'] ?>
            </div>
        </div>
    </div>

</div>

<script>
(function () {

    // ── Toggle: open one, close the other ────────────────────────────────────
    window.togglePanel = function (id) {
        ['langPalette', 'themePalette'].forEach(function (p) {
            var el = document.getElementById(p);
            if (p === id) { el.classList.toggle('open'); }
            else          { el.classList.remove('open'); }
        });
    };

    // ── Close both on outside click ──────────────────────────────────────────
    document.addEventListener('click', function (e) {
        if (!document.getElementById('switchersWrap').contains(e.target)) {
            document.getElementById('langPalette').classList.remove('open');
            document.getElementById('themePalette').classList.remove('open');
        }
    });

    // ── Theme ────────────────────────────────────────────────────────────────
    var STORAGE_KEY = 'arb_theme';

    window.setTheme = function (theme) {
        if (theme) { document.documentElement.setAttribute('data-theme', theme); }
        else       { document.documentElement.removeAttribute('data-theme'); }
        localStorage.setItem(STORAGE_KEY, theme);
        updateActiveTheme(theme);
        document.getElementById('themePalette').classList.remove('open');
    };

    function updateActiveTheme(theme) {
        var names = ['', 'green', 'purple', 'red', 'orange', 'dark'];
        document.querySelectorAll('#themePalette .sw-swatch').forEach(function (s, i) {
            s.classList.toggle('active', names[i] === theme);
        });
    }

    // Restore saved theme
    setTheme(localStorage.getItem(STORAGE_KEY) || '');

}());
</script>

</body>
</html>