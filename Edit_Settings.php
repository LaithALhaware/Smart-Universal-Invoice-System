<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: Login.php');
    exit;
}

$settingsFile = 'Static/settings.json';
$uploadDir    = 'Static/';
$message = '';
$error   = '';
include "Static/config.php";

if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $new = [
        'System_name'    => trim($_POST['System_name']    ?? ''),
        'company_name'    => trim($_POST['company_name']    ?? ''),
        'company_tagline' => trim($_POST['company_tagline'] ?? ''),
        'logo_path'       => $settings['logo_path'],
        'logo_initials'   => strtoupper(trim($_POST['logo_initials'] ?? '')),
        'invoice_prefix'  => strtoupper(trim($_POST['invoice_prefix'] ?? 'INV')),
        'vat_rate'        => (float)($_POST['vat_rate']     ?? 5),
        'currency'        => strtoupper(trim($_POST['currency'] ?? 'AED')),
        'email'           => trim($_POST['email']           ?? ''),
        'phone'           => trim($_POST['phone']           ?? ''),
        'address_line1'   => trim($_POST['address_line1']   ?? ''),
        'address_line2'   => trim($_POST['address_line2']   ?? ''),
        'city'            => trim($_POST['city']            ?? ''),
        'country'         => trim($_POST['country']         ?? ''),
        'Lang'         => trim($_POST['Language']         ?? ''),
        'Direction'         => trim($_POST['dir']         ?? ''),
    ];

    // Logo upload
    if (!empty($_FILES['logo_file']['name'])) {
        $file    = $_FILES['logo_file'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','svg','gif'];
        if (!in_array($ext, $allowed)) {
            $error = 'Invalid file type. Allowed: JPG, PNG, WEBP, SVG, GIF.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'File too large. Max 2 MB.';
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload error. Please try again.';
        } else {
            if (!empty($settings['logo_path']) && file_exists($settings['logo_path'])) unlink($settings['logo_path']);
            $dest = $uploadDir . 'logo_' . time() . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $new['logo_path'] = $dest;
            } else {
                $error = 'Could not save file. Check folder permissions.';
            }
        }
    }

    // Delete logo
    if (isset($_POST['delete_logo'])) {
        if (!empty($settings['logo_path']) && file_exists($settings['logo_path'])) unlink($settings['logo_path']);
        $new['logo_path'] = '';
    }

    if (empty($new['company_name'])) $error = 'Company name is required.';

    if (empty($error)) {
        file_put_contents($settingsFile, json_encode($new, JSON_PRETTY_PRINT));
        $settings = $new;
        $message  = 'Settings saved successfully.';
    }
}

function autoInitials($name) {
    $words = explode(' ', preg_replace('/[^a-zA-Z\s]/', '', $name));
    $init = '';
    foreach (array_slice($words, 0, 2) as $w) if (!empty($w)) $init .= strtoupper($w[0]);
    return $init ?: 'CO';
}

$initials = $settings['logo_initials'] ?: autoInitials($settings['company_name']);
?>



<?php
$langsJson = @file_get_contents('http://localhost:9000/languages');
$languages = $langsJson ? json_decode($langsJson, true) : [
    ['code'=>'ar','name'=>'العربية','name_en'=>'Arabic','dir'=>'rtl'],
    ['code'=>'en','name'=>'English','name_en'=>'English','dir'=>'ltr'],
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?=translate('Settings',$_SESSION['Lang'])['translated']?> — <?=$settings['System_name']?> (<?=$settings['company_name']?>)</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>

  <link rel="stylesheet" href="Static/css/Main.css">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&family=Inter:wght@300;400;700&display=swap" rel="stylesheet">

<style>
body {
   <?= $_SESSION['Direction']=='right' ? "font-family: 'Cairo', sans-serif;" : "font-family: 'Inter', 'Cairo', sans-serif;" ?> 
   direction:<?= $_SESSION['Direction']=='right' ? "rtl" : "ltl" ?>;
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
    .main-title svg {margin-<?= $_SESSION['Direction']=='right' ? 'left' : 'right' ?>: 10px;}
    .preview-badge {margin-<?=$_SESSION['Direction']?>: auto;}
  </style>

  
</head>
<body>



<!-- HERO -->
<div class="hero">
  <div class="hero-inner">
    <div class="main-title">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.03,0,0,1.03,-7.679989929199223,-7.680000200271593)"><path fill-rule="evenodd" d="M470.006 301.042c-16.074-9.26-26.057-26.511-26.057-45.041 0-18.521 9.982-35.772 26.01-45.023 16.45-9.457 23.292-29.107 16.262-46.719-8.811-22.247-20.808-43.026-35.57-61.768-11.763-14.922-32.196-18.83-48.645-9.312-16.027 9.312-35.992 9.34-52.066.07-16.028-9.27-25.963-26.549-25.916-45.112 0-18.99-13.591-34.75-32.383-37.468a247.327 247.327 0 0 0-71.328.07c-18.746 2.737-32.336 18.488-32.29 37.44 0 18.54-9.936 35.809-25.963 45.07-16.075 9.26-35.992 9.241-52.066-.061-16.449-9.518-36.882-5.591-48.645 9.331a249.99 249.99 0 0 0-20.011 29.562c-5.952 10.306-11.154 21.108-15.559 32.088-7.077 17.64-.234 37.318 16.215 46.799 16.075 9.251 26.057 26.511 26.057 45.032s-9.982 35.767-26.01 45.032c-16.449 9.458-23.291 29.098-16.262 46.705a246.096 246.096 0 0 0 35.57 61.772c11.763 14.922 32.196 18.83 48.645 9.312 16.028-9.312 35.992-9.331 52.066-.07 16.027 9.27 25.963 26.549 25.963 45.112-.047 18.985 13.59 34.745 32.336 37.469a247.073 247.073 0 0 0 35.382 2.549c11.998 0 23.995-.872 35.945-2.62 18.746-2.742 32.336-18.479 32.336-37.431-.047-18.549 9.888-35.819 25.916-45.079 16.074-9.26 35.992-9.241 52.066.061 16.449 9.518 36.882 5.6 48.645-9.331a251.455 251.455 0 0 0 20.011-29.553c5.905-10.305 11.201-21.108 15.559-32.097 7.079-17.64.237-37.323-16.213-46.789zm-150.247-8.22c-9.795 17.031-25.682 29.22-44.708 34.31-18.98 5.089-38.851 2.479-55.863-7.363-35.148-20.302-47.239-65.418-26.947-100.59 13.591-23.592 38.429-36.798 63.876-36.798 12.513 0 25.12 3.168 36.695 9.851 35.148 20.297 47.239 65.427 26.947 100.59z" clip-rule="evenodd" fill="#fff" opacity="1" data-original="#000000" class=""></path></g></svg>
     <div>
      <div class="hero-title"><?=translate('Edit Settings',$_SESSION['Lang'])['translated']?> </div>
      <div class="hero-sub"><?=translate('Manage your Settings',$_SESSION['Lang'])['translated']?> </div>
      </div>
    </div>

  </div>
</div>

<!-- MAIN -->
<div class="main-up">

  <?php if($message): ?>
  <div class="alert alert-success">
    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <?= htmlspecialchars($message) ?>
  </div>
  <?php endif; ?>
  <?php if($error): ?>
  <div class="alert alert-danger">
    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">

    <!-- ── LIVE PREVIEW BAR ─────────────────────────────── -->
    <div class="preview-bar">
      <div class="preview-logo" id="previewLogo">
        <?php if(!empty($settings['logo_path'])): ?>
          <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="logo">
        <?php else: ?>
          <span id="prevInitials"><?= htmlspecialchars($initials) ?></span>
        <?php endif; ?>
      </div>
      <div class="preview-info">
        <div class="p-name" id="prevName"><?= htmlspecialchars($settings['company_name']) ?></div>
        <div class="p-name" id="prevSystemname"><?= htmlspecialchars($settings['System_name']) ?></div>
        <div class="p-tag"  id="prevTag"><?= htmlspecialchars($settings['company_tagline']) ?></div>
        <div class="p-inv"  id="prevInv"><?= htmlspecialchars($settings['invoice_prefix']) ?>-XXXXX &middot; <?= htmlspecialchars($settings['vat_rate']) ?>% VAT &middot; <?= htmlspecialchars($settings['currency']) ?></div>
      </div>
      <span class="preview-badge"><?=translate('Live Preview',$_SESSION['Lang'])['translated']?></span>
    </div>

    <!-- ══ 1. COMPANY IDENTITY ══════════════════════════ -->
    <div class="s-card">
      <div class="s-card-header">
        <div class="s-card-icon">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
        </div>
        <div class="s-card-title"><?=translate('Company Identity',$_SESSION['Lang'])['translated']?></div>
      </div>
      <div class="s-card-body">

        <div class="field-row cols-2">
          <div>
            <label class="field-label"><?=translate('Company Name',$_SESSION['Lang'])['translated']?> <span class="req">*</span></label>
            <input class="field-input" type="text" name="company_name" id="companyName" required
              placeholder="<?=translate('e.g. Shop 102',$_SESSION['Lang'])['translated']?>"
              value="<?= htmlspecialchars($settings['company_name']) ?>">
          </div>
          <div>
            <label class="field-label"><?=translate('Tagline / Slogan',$_SESSION['Lang'])['translated']?></label>
            <input class="field-input" type="text" name="company_tagline" id="companyTagline"
              placeholder="<?=translate('e.g. Your Off-Road Partner',$_SESSION['Lang'])['translated']?>"
              value="<?= htmlspecialchars($settings['company_tagline']) ?>">
          </div>
        </div>

<div  class="field-row"><div>
            <label class="field-label"><?=translate('System Name',$_SESSION['Lang'])['translated']?> <span class="req">*</span></label>
            <input class="field-input" type="text" name="System_name" id="Systemname" required
              placeholder="<?=translate('e.g. Billing System',$_SESSION['Lang'])['translated']?>"
              value="<?= htmlspecialchars($settings['System_name']) ?>">
          </div></div>

        <div class="divider"></div>

        <!-- LOGO UPLOAD -->
        <label class="field-label" style="margin-bottom:14px;"><?=translate('Company Logo',$_SESSION['Lang'])['translated']?></label>
        <div class="logo-upload-wrap">

          <!-- Big preview bubble -->
          <div class="logo-bubble-lg" id="logoBubble">
            <?php if(!empty($settings['logo_path'])): ?>
              <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="logo">
            <?php else: ?>
              <span id="bubbleInit"><?= htmlspecialchars($initials) ?></span>
            <?php endif; ?>
          </div>

          <!-- Upload controls -->
          <div class="upload-right">
            <div class="drop-zone" id="dropZone">
              <input type="file" name="logo_file" id="logoFileInput" accept="image/*">
              <div class="drop-icon">
                <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
              </div>
              <div class="drop-title"><?=translate('Click to upload or drag & drop',$_SESSION['Lang'])['translated']?></div>
              <div class="drop-sub"><?=translate('PNG · JPG · WEBP · SVG · GIF — max 2 MB',$_SESSION['Lang'])['translated']?></div>
              <div class="drop-fn" id="dropFilename"></div>
            </div>

            <?php if(!empty($settings['logo_path'])): ?>
            <div class="current-logo-bar">
              <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="current logo">
              <span class="clb-name"><?= htmlspecialchars(basename($settings['logo_path'])) ?></span>
              <button type="submit" name="delete_logo" value="1" class="btn-remove"
                onclick="return confirm('<?=translate('Remove the current logo?',$_SESSION['Lang'])['translated']?>')">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                  <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                </svg>
                <?=translate('Remove',$_SESSION['Lang'])['translated']?>
              </button>
            </div>
            <?php endif; ?>

            <div class="initials-row">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              <?=translate('Fallback initials when no logo uploaded:',$_SESSION['Lang'])['translated']?>
              <input class="field-input mono" type="text" name="logo_initials" id="logoInitials"
                maxlength="3" placeholder="AR"
                value="<?= htmlspecialchars($settings['logo_initials'] ?: $initials) ?>">
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ══ 2. INVOICE DEFAULTS ═══════════════════════════ -->
    <div class="s-card">
      <div class="s-card-header">
        <div class="s-card-icon">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
        </div>
        <div class="s-card-title"><?=translate('Invoice Defaults',$_SESSION['Lang'])['translated']?></div>
      </div>
      <div class="s-card-body">
        <div class="field-row cols-3">
          <div>
            <label class="field-label"><?=translate('Invoice Prefix',$_SESSION['Lang'])['translated']?></label>
            <input class="field-input mono" type="text" name="invoice_prefix" id="invoicePrefix"
              maxlength="8" placeholder="INV"
              value="<?= htmlspecialchars($settings['invoice_prefix']) ?>">
            <div class="field-hint"><?=translate('e.g. INV → INV-00123',$_SESSION['Lang'])['translated']?></div>
          </div>
          <div>
            <label class="field-label"><?=translate('VAT Rate',$_SESSION['Lang'])['translated']?></label>
            <div class="input-group">
              <span class="input-addon">%</span>
              <input class="field-input mono" type="number" name="vat_rate" id="vatRate"
                min="0" max="100" step="0.1"
                value="<?= htmlspecialchars($settings['vat_rate']) ?>">
            </div>
          </div>
          <div>
            <label class="field-label"><?=translate('Currency',$_SESSION['Lang'])['translated']?></label>
            <input class="field-input mono" type="text" name="currency" id="currencyCode"
              maxlength="5" placeholder="<?=translate('AED',$_SESSION['Lang'])['translated']?>"
              value="<?= htmlspecialchars($settings['currency']) ?>">
            <div class="field-hint"><?=translate('ISO code — AED, USD, EUR…',$_SESSION['Lang'])['translated']?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ 3. CONTACT INFO ════════════════════════════════ -->
    <div class="s-card">
      <div class="s-card-header">
        <div class="s-card-icon">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
          </svg>
        </div>
        <div class="s-card-title"><?=translate('Contact Information',$_SESSION['Lang'])['translated']?></div>
      </div>
      <div class="s-card-body">
        <div class="field-row cols-2">
          <div>
            <label class="field-label"><?=translate('Email',$_SESSION['Lang'])['translated']?></label>
            <div class="input-group">
              <span class="input-addon"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
              <input class="field-input" type="email" name="email" placeholder="support@company.com" value="<?= htmlspecialchars($settings['email']) ?>">
            </div>
          </div>
          <div>
            <label class="field-label"><?=translate('Phone',$_SESSION['Lang'])['translated']?></label>
            <div class="input-group">
              <span class="input-addon"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.35 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.02-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
              <input class="field-input mono" type="text" name="phone" placeholder="+971 4 000 0000" value="<?= htmlspecialchars($settings['phone']) ?>">
            </div>
          </div>
        </div>
        <div class="field-row cols-1">
          <div>
            <label class="field-label"><?=translate('Address Line 1',$_SESSION['Lang'])['translated']?></label>
            <input class="field-input" type="text" name="address_line1" placeholder="<?=translate('Street / Building / Unit',$_SESSION['Lang'])['translated']?>" value="<?= htmlspecialchars($settings['address_line1']) ?>">
          </div>
        </div>
        <div class="field-row cols-1">
          <div>
            <label class="field-label"><?=translate('Address Line 2',$_SESSION['Lang'])['translated']?> <span style="color:var(--ink-3);font-weight:400;text-transform:none;font-size:11px;">(optional)</span></label>
            <input class="field-input" type="text" name="address_line2" placeholder="<?=translate('P.O. Box / Additional info',$_SESSION['Lang'])['translated']?>" value="<?= htmlspecialchars($settings['address_line2']) ?>">
          </div>
        </div>
        <div class="field-row cols-2">
          <div>
            <label class="field-label"><?=translate('City',$_SESSION['Lang'])['translated']?></label>
            <input class="field-input" type="text" name="city" placeholder="<?=translate('Dubai',$_SESSION['Lang'])['translated']?>" value="<?= htmlspecialchars($settings['city']) ?>">
          </div>
          <div>
            <label class="field-label"><?=translate('Country',$_SESSION['Lang'])['translated']?></label>
            <input class="field-input" type="text" name="country" placeholder="<?=translate('United Arab Emirates',$_SESSION['Lang'])['translated']?>" value="<?= htmlspecialchars($settings['country']) ?>">
          </div>
        </div>
      </div>
    </div>








    <!-- ══ 3. CONTACT INFO ════════════════════════════════ -->
    <div class="s-card">
      <div class="s-card-header">
        <div class="s-card-icon">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="15" height="15" x="0" y="0" viewBox="0 0 128 128" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.4900000000000002,0,0,1.4900000000000002,-31.359755134582514,-31.359755134582514)"><path d="M100.511 20.959H27.489a6.528 6.528 0 0 0-6.53 6.523v10.43a6.527 6.527 0 0 0 6.53 6.523h73.022a6.526 6.526 0 0 0 6.529-6.523v-10.43c0-3.6-2.922-6.523-6.529-6.523zM31.401 36.61c-2.177 0-3.913-1.755-3.913-3.913s1.736-3.912 3.913-3.912c2.152 0 3.912 1.754 3.912 3.912s-1.759 3.913-3.912 3.913zm33.638 0H49.388c-2.176 0-3.912-1.755-3.912-3.913s1.736-3.912 3.912-3.912h15.651c2.151 0 3.913 1.754 3.913 3.912s-1.762 3.913-3.913 3.913zM100.511 52.262H27.489a6.528 6.528 0 0 0-6.53 6.523v10.43a6.527 6.527 0 0 0 6.53 6.523h73.022a6.526 6.526 0 0 0 6.529-6.523v-10.43a6.526 6.526 0 0 0-6.529-6.523zm-69.11 15.651c-2.177 0-3.913-1.755-3.913-3.913s1.736-3.913 3.913-3.913c2.152 0 3.912 1.755 3.912 3.913s-1.759 3.913-3.912 3.913zm66.518 0H82.268c-2.177 0-3.913-1.755-3.913-3.913s1.736-3.913 3.913-3.913h15.651c2.152 0 3.912 1.755 3.912 3.913s-1.76 3.913-3.912 3.913zM100.511 83.564H27.489a6.529 6.529 0 0 0-6.53 6.529v10.418a6.53 6.53 0 0 0 6.53 6.529h73.022a6.528 6.528 0 0 0 6.529-6.529V90.094a6.528 6.528 0 0 0-6.529-6.53zm-69.11 15.651a3.908 3.908 0 0 1-3.913-3.912 3.909 3.909 0 0 1 3.913-3.913 3.924 3.924 0 0 1 3.912 3.913 3.923 3.923 0 0 1-3.912 3.912zm33.638 0H49.388a3.908 3.908 0 0 1-3.912-3.912 3.908 3.908 0 0 1 3.912-3.913h15.651a3.925 3.925 0 0 1 3.913 3.913 3.925 3.925 0 0 1-3.913 3.912zm32.88 0H82.268a3.908 3.908 0 0 1-3.913-3.912 3.909 3.909 0 0 1 3.913-3.913h15.651a3.924 3.924 0 0 1 3.912 3.913 3.924 3.924 0 0 1-3.912 3.912z" fill="currentColor" opacity="1" data-original="#000000" class=""></path></g></svg>
        </div>
        <div class="s-card-title"><?=translate('system Settings',$_SESSION['Lang'])['translated']?></div>
      </div>
      <div class="s-card-body">
        <div class="field-row">
          <div>
            
            <label class="field-label"><?=translate('Language',$_SESSION['Lang'])['translated']?></label>
            <div class="input-group">

          <select class="field-input" name="Language" id="langSelect" style="margin-bottom:0">
            <?php foreach ($languages as $l): ?>
              <option value="<?= htmlspecialchars($l['code']) ?>"
                data-dir="<?= htmlspecialchars($l['dir']) ?>"
                <?= ($l['code']==='ar') || ($l['code']==='en') ? 'selected' : '' ?>>
                <?= htmlspecialchars($l['flag']) ?> <?= htmlspecialchars($l['name']) ?> — <?= htmlspecialchars(translate($l['name_en'], $_SESSION['Lang'])['translated'] ?? $l['name_en']) ?>
              </option>
            <?php endforeach; ?>
          </select>
<input type="hidden" name="dir" id="dirInput">
<script>
document.getElementById("langSelect").addEventListener("change", function() {
    let selected = this.options[this.selectedIndex];
    document.getElementById("dirInput").value = selected.getAttribute("data-dir");
});

// trigger on load (important if default selected)
window.onload = function() {
    let select = document.getElementById("langSelect");
    let selected = select.options[select.selectedIndex];
    document.getElementById("dirInput").value = selected.getAttribute("data-dir");
};
</script>

            </div>
          </div>


  
      </div>
      </div>
    </div>







    <div class="save-bar">
      <button type="submit" class="btn-save">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
        </svg>
        <?=translate('Save Settings',$_SESSION['Lang'])['translated']?>
      </button>
    </div>

  </form>
</div>

<script>
  const elName    = document.getElementById('companyName');
  const elSystemnameme    = document.getElementById('Systemname');
  const elTag     = document.getElementById('companyTagline');
  const elInit    = document.getElementById('logoInitials');
  const elPrefix  = document.getElementById('invoicePrefix');
  const elVat     = document.getElementById('vatRate');
  const elCur     = document.getElementById('currencyCode');
  const elFile    = document.getElementById('logoFileInput');
  const dropZone  = document.getElementById('dropZone');
  const dropFn    = document.getElementById('dropFilename');
  const bubble    = document.getElementById('logoBubble');
  const prevLogo  = document.getElementById('previewLogo');
  const prevName  = document.getElementById('prevName');
  const prevSystemname  = document.getElementById('prevSystemname');
  const prevTag   = document.getElementById('prevTag');
  const prevInv   = document.getElementById('prevInv');

  let imageLoaded = <?= !empty($settings['logo_path']) ? 'true' : 'false' ?>;

  function autoInit(name) {
    return name.split(' ').filter(Boolean).slice(0,2).map(w => w[0].toUpperCase()).join('') || 'CO';
  }

  function setLogoImg(src) {
    imageLoaded = true;
    bubble.innerHTML   = '<img src="' + src + '" alt="logo">';
    prevLogo.innerHTML = '<img src="' + src + '" alt="logo" style="width:100%;height:100%;object-fit:contain;">';
  }

  function setLogoText(txt) {
    imageLoaded = false;
    bubble.innerHTML   = '<span>' + txt + '</span>';
    prevLogo.innerHTML = '<span id="prevInitials">' + txt + '</span>';
  }

  function refreshPreview() {
    const name   = elName.value.trim();
    const prefix = (elPrefix.value.trim().toUpperCase()) || 'INV';
    const vat    = elVat.value || '0';
    const cur    = (elCur.value.trim().toUpperCase()) || 'AED';
    const Systemnameme   = elSystemnameme.value.trim();

    prevName.textContent = name || 'System Name';
    prevSystemname.textContent = Systemnameme || 'Company Name';
    prevTag.textContent  = elTag.value.trim();
    prevInv.textContent  = prefix + '-XXXXX · ' + vat + '% VAT · ' + cur;
    if (!imageLoaded) {
      const init = elInit.value.trim().toUpperCase().slice(0,3) || autoInit(name);
      setLogoText(init);
    }
  }

  [elName, elTag, elInit, elPrefix, elVat, elCur, elSystemnameme].forEach(el => el && el.addEventListener('input', refreshPreview));

  function handleFile(file) {
    if (!file || !file.type.startsWith('image/')) return;
    dropFn.textContent = '✓ ' + file.name;
    dropFn.style.display = 'block';
    const reader = new FileReader();
    reader.onload = e => setLogoImg(e.target.result);
    reader.readAsDataURL(file);
  }

  elFile.addEventListener('change', () => handleFile(elFile.files[0]));

  dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
  dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('drag-over'));
  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
      const dt = new DataTransfer();
      dt.items.add(file);
      elFile.files = dt.files;
      handleFile(file);
    }
  });
</script>
<script>
(function(){
  var STORAGE_KEY = 'arb_theme';

  /* Apply theme */
  function setTheme(theme) {
    if (theme) {
      document.documentElement.setAttribute('data-theme', theme);
    } else {
      document.documentElement.removeAttribute('data-theme');
    }
    localStorage.setItem(STORAGE_KEY, theme);
    updateActive(theme);
    closePalette();
  }
  window.setTheme = setTheme;

  /* Mark active swatch */
  function updateActive(theme) {
    document.querySelectorAll('.theme-swatch').forEach(function(s) {
      s.classList.remove('active');
    });
    var swatches = document.querySelectorAll('.theme-swatch');
    var names = ['', 'green', 'purple', 'red', 'orange', 'dark'];
    var idx = names.indexOf(theme);
    if (idx > -1 && swatches[idx]) swatches[idx].classList.add('active');
  }

  /* Toggle palette open/close */
  window.togglePalette = function() {
    document.getElementById('themePalette').classList.toggle('open');
  };
  function closePalette() {
    document.getElementById('themePalette').classList.remove('open');
  }

  /* Close when clicking outside */
  document.addEventListener('click', function(e) {
    if (!document.getElementById('themeSwitcher').contains(e.target)) {
      closePalette();
    }
  });

  /* Restore saved theme on load */
  var saved = localStorage.getItem(STORAGE_KEY) || '';
  setTheme(saved);
}());
</script>
</body>
</html>