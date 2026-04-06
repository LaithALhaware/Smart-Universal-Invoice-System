<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: Login.php');
    exit;
}

include "Static/config.php";

$folder = "Static/ALL_Invoice";

$files = glob($folder . "/*.json");
$count = count($files);

// Pull brand name from each JSON for the card
$vehicles = [];
foreach ($files as $file) {
    $filename = basename($file);
    $Name    =  ''; // fallback
    $plate    = '';
    $chassis_number    = '';
    $Invoice_number = '';
    $Date = '';
    $json     = @file_get_contents($file);
    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data['Invoice_info']['Name']))        $Name = $data['Invoice_info']['Name'];
        if (!empty($data['Invoice_info']['plate_number'])) $plate = $data['Invoice_info']['plate_number'];
        if (!empty($data['Invoice_info']['chassis_number'])) $chassis_number = $data['Invoice_info']['chassis_number'];
        if (!empty($data['Invoice_info']['Invoice_number'])) $Invoice_number = $data['Invoice_info']['Invoice_number'];
        if (!empty($data['Invoice_info']['Date'])) $Invoice_number = $data['Invoice_info']['Date'];
    }
    $vehicles[] = compact('filename',  'Name', 'plate', 'chassis_number', 'Invoice_number');
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?=translate($settings['System_name'],$_SESSION['Lang'])['translated']?> (<?=$settings['company_name']?>)</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>
<link rel="icon" href="/Static/IMG/icon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="Static/css/Main.css">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&family=Inter:wght@300;400;700&display=swap" rel="stylesheet">

<style>
body {
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


</head>
<body dir="<?= $_SESSION['Direction']=='right' ? "rtl" : "ltl" ?>">

<!-- ── TOPBAR ──────────────────────────────────────────── -->
<nav class="topbar">
  <div class="topbar-brand">

          <?php if(!empty($settings['logo_path'])): ?>
          <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="logo" style="height: 50px;">
        <?php else: ?>

          <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="50" height="50" x="0" y="0" viewBox="0 0 512.006 512.006" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M184.002 368.002h-7.572c-7.082 0-13.578-4.432-15.689-11.192-2.556-8.187 1.723-16.748 9.521-19.738l120.016-46.159c8.429-3.242 12.477-12.799 8.999-21.134-47.344-113.465-59.191-253.421-59.325-255.067-1.219-15.054-20.837-20.202-29.26-7.59l-23.13 34.69-34.68-23.12c-9.179-6.127-21.719-1.313-24.4 9.43l-10.5 41.97-29.1-19.4c-9.179-6.127-21.719-1.313-24.4 9.43l-10.5 41.97-29.1-19.4C13.796 75.301-.992 83.956.052 97.242c.32 4.15 8.23 102.86 38.24 203.34 17.88 59.85 39.98 107.77 65.68 142.45 18.034 24.311 45.676 36.97 73.602 36.97h5.579c30.732 0 56.376-24.405 56.843-55.134.475-31.275-24.826-56.866-55.994-56.866zm-102.86-170.06c-3.28-8.2.71-17.51 8.92-20.8l80-32c8.2-3.28 17.51.71 20.8 8.92 3.28 8.2-.71 17.51-8.92 20.8-86.636 34.654-81.523 33.14-85.94 33.14-6.34 0-12.35-3.8-14.86-10.06zm36.8 72.92c-8.085 3.234-17.472-.619-20.8-8.92-3.28-8.2.71-17.51 8.92-20.8l80-32c8.2-3.28 17.51.71 20.8 8.92 3.28 8.2-.71 17.51-8.92 20.8zm314.06 1.14c-4.179 0 4.086-2.709-111.59 41.78-.141-.234-77.388 29.589-77.257 29.538-5.69 2.189-6.909 9.647-2.262 13.593 48.686 41.344 38.574 118.94-19.023 146.514-4.3 2.059-2.859 8.567 1.908 8.575 13.287.022 26.037-3.221 37.184-9.04l172.53-71.04a15.9 15.9 0 0 0 8.68-3.57c46.758-19.245 44.562-17.829 47.74-21.15 48.334-50.675 12.124-135.2-57.91-135.2zM411.316 187.316c-6.238 6.236-25.779 6.22-32 0-6.248-6.248-16.379-6.249-22.627 0-6.249 6.249-6.248 16.379 0 22.627 6.711 6.711 16.326 11.272 27.313 13.126v16.933c0 8.836 7.163 16 16 16s16-7.164 16-16v-19.296c41.992-14.273 40.789-70.499 2.407-86.397l-24.568-10.177c-12.786-5.295-15.084-28.129 10.848-28.129 7.815 0 13.667 2.354 16 4.687 6.248 6.248 16.379 6.249 22.627 0 6.249-6.249 6.248-16.379 0-22.627-6.711-6.711-16.326-11.272-27.313-13.126V48.002c0-8.836-7.163-16-16-16s-16 7.164-16 16v19.296c-41.628 14.149-41.118 70.363-2.407 86.397l24.568 10.177c9.215 3.817 12.589 15.987 5.152 23.444z" fill="#ffffff" opacity="1" data-original="#000000"></path></g></svg>
          
          <?php endif; ?>

    <?=$settings['company_name']?>
  </div>



  <button class="tb-btn tb-btn-primary" onclick="openModal('<?=translate('Add New Invoice',$_SESSION['Lang'])['translated']?>','Add_Invoice.php')">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
    
      <?=translate('Add New Invoice',$_SESSION['Lang'])['translated']?>
  </button>


<button class="tb-btn tb-btn-secondary" onclick="openModal('<?=translate('Accessories',$_SESSION['Lang'])['translated']?>','Edit_Parts.php')">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 0 0 4.93 19.07M4.93 4.93A10 10 0 0 0 19.07 19.07"/></svg>
    <?=translate('Accessories',$_SESSION['Lang'])['translated']?>
  </button>
  

<button class="tb-btn tb-btn-success" onclick="openModal('<?=translate('Settings',$_SESSION['Lang'])['translated']?>','Edit_Settings.php')">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="14" height="14" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.03,0,0,1.03,-7.679989929199223,-7.680000200271593)"><path fill-rule="evenodd" d="M470.006 301.042c-16.074-9.26-26.057-26.511-26.057-45.041 0-18.521 9.982-35.772 26.01-45.023 16.45-9.457 23.292-29.107 16.262-46.719-8.811-22.247-20.808-43.026-35.57-61.768-11.763-14.922-32.196-18.83-48.645-9.312-16.027 9.312-35.992 9.34-52.066.07-16.028-9.27-25.963-26.549-25.916-45.112 0-18.99-13.591-34.75-32.383-37.468a247.327 247.327 0 0 0-71.328.07c-18.746 2.737-32.336 18.488-32.29 37.44 0 18.54-9.936 35.809-25.963 45.07-16.075 9.26-35.992 9.241-52.066-.061-16.449-9.518-36.882-5.591-48.645 9.331a249.99 249.99 0 0 0-20.011 29.562c-5.952 10.306-11.154 21.108-15.559 32.088-7.077 17.64-.234 37.318 16.215 46.799 16.075 9.251 26.057 26.511 26.057 45.032s-9.982 35.767-26.01 45.032c-16.449 9.458-23.291 29.098-16.262 46.705a246.096 246.096 0 0 0 35.57 61.772c11.763 14.922 32.196 18.83 48.645 9.312 16.028-9.312 35.992-9.331 52.066-.07 16.027 9.27 25.963 26.549 25.963 45.112-.047 18.985 13.59 34.745 32.336 37.469a247.073 247.073 0 0 0 35.382 2.549c11.998 0 23.995-.872 35.945-2.62 18.746-2.742 32.336-18.479 32.336-37.431-.047-18.549 9.888-35.819 25.916-45.079 16.074-9.26 35.992-9.241 52.066.061 16.449 9.518 36.882 5.6 48.645-9.331a251.455 251.455 0 0 0 20.011-29.553c5.905-10.305 11.201-21.108 15.559-32.097 7.079-17.64.237-37.323-16.213-46.789zm-150.247-8.22c-9.795 17.031-25.682 29.22-44.708 34.31-18.98 5.089-38.851 2.479-55.863-7.363-35.148-20.302-47.239-65.418-26.947-100.59 13.591-23.592 38.429-36.798 63.876-36.798 12.513 0 25.12 3.168 36.695 9.851 35.148 20.297 47.239 65.427 26.947 100.59z" clip-rule="evenodd" fill="#fff" opacity="1" data-original="#000000" class=""></path></g></svg>
    <?=translate('Settings',$_SESSION['Lang'])['translated']?>
  </button>


  <a href="Logout.php" class="tb-btn tb-btn-danger">
  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
    <polyline points="16 17 21 12 16 7"/>
    <line x1="21" y1="12" x2="9" y2="12"/>
  </svg>
  
    <?=translate('Logout',$_SESSION['Lang'])['translated']?>
  </a>


  
</nav>

<!-- ── HERO ────────────────────────────────────────────── -->
<div class="hero">
  <div class="hero-inner">
    <div>
      <div class="hero-title">
      <?=translate($settings['System_name'],$_SESSION['Lang'])['translated']?></div>
      <div class="hero-sub">
      <?=translate('Organize, Track, and Manage Your Invoices Effortlessly',$_SESSION['Lang'])['translated']?></div>
    </div>
    <div class="hero-stats">
      <div class="stat-pill">
        <div class="s-num"><?= $count ?></div>
        <div class="s-label">
      <?=translate('Invoice',$_SESSION['Lang'])['translated']?></div>
      </div>
    </div>
  </div>
</div>

<!-- ── TOOLBAR ──────────────────────────────────────────── -->
<div class="toolbar">
  <div class="search-wrap">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
    </svg>
    <input class="search-input" type="text" id="search" placeholder=" <?=translate('Search by Name',$_SESSION['Lang'])['translated']?>…">
  </div>
</div>





<!-- ── MAIN ─────────────────────────────────────────────── -->
<div class="main">


  <div class="vehicle-grid" id="vehicleList">

    <?php if (empty($vehicles)): ?>
    <div class="empty-state">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="60" height="60" x="0" y="0" viewBox="0 0 64 64" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.1399999999999992,0,0,1.1399999999999992,-4.479999999999976,-4.48000020027159)"><path d="M57 24H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h50a3 3 0 0 0 3-3V27a3 3 0 0 0-3-3ZM10 35a1 1 0 0 1-2 0v-6a1 1 0 0 1 2 0Zm9 0a1 1 0 0 1-1.768.641L14 31.762V35a1 1 0 0 1-2 0v-6a1 1 0 0 1 1.768-.641L17 32.238V29a1 1 0 0 1 2 0Zm9.894-5.551-3.016 6a1 1 0 0 1-.893.551 1 1 0 0 1-.894-.555l-2.985-6a1 1 0 0 1 1.792-.89l2.093 4.209 2.117-4.213a1 1 0 0 1 1.788.9ZM37 33a3 3 0 0 1-6 0v-2a3 3 0 0 1 6 0Zm4 2a1 1 0 0 1-2 0v-6a1 1 0 0 1 2 0Zm5-1h2a1 1 0 0 1 0 2h-2a3 3 0 0 1-3-3v-2a3 3 0 0 1 3-3h2a1 1 0 0 1 0 2h-2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1Zm9-3a1 1 0 0 1 0 2h-2v1h2a1 1 0 0 1 0 2h-3a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h3a1 1 0 0 1 0 2h-2v1Z" fill="currentColor" opacity="1" data-original="#000000"></path><path d="M34 30a1 1 0 0 0-1 1v2a1 1 0 0 0 2 0v-2a1 1 0 0 0-1-1Z" fill="currentColor" opacity="1" data-original="#000000"></path><rect width="26" height="8" x="19" y="11" rx="1" fill="currentColor" opacity="1" data-original="#000000"></rect><path d="M55 7a3.173 3.173 0 0 0-3.316-3H12.316A3.173 3.173 0 0 0 9 7v15h46Zm-8 11a3 3 0 0 1-3 3H20a3 3 0 0 1-3-3v-6a3 3 0 0 1 3-3h24a3 3 0 0 1 3 3ZM9 57a3.173 3.173 0 0 0 3.316 3h39.368A3.173 3.173 0 0 0 55 57V42H9Zm29-2H26a1 1 0 0 1 0-2h12a1 1 0 0 1 0 2Zm2-5H24a1 1 0 0 1 0-2h16a1 1 0 0 1 0 2Zm-18-7h20a1 1 0 0 1 0 2H22a1 1 0 0 1 0-2Z" fill="currentColor" opacity="1" data-original="#000000"></path></g></svg>
      <h3>
      <?=translate('No Invoice found',$_SESSION['Lang'])['translated']?></h3>
      <p>
      <?=translate('Add your first Invoice to get started.',$_SESSION['Lang'])['translated']?></p>
    </div>
    <?php endif; ?>

    <?php foreach ($vehicles as $i => $v): ?>
    <div class="v-card vehicle-item" data-search="<?= strtolower(htmlspecialchars( $v['Date'] . ' ' .$v['Invoice_number'] . ' ' . $v['chassis_number'] . ' ' . $v['Name'] . ' ' . $v['plate'] . ' ' . $v['name'])) ?>">
      <div class="v-card-top">
        <span class="card-num">#<?= str_pad($i + 1, 3, '0', STR_PAD_LEFT) ?></span>
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="70" height="70" x="0" y="0" viewBox="0 0 64 64" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.1399999999999992,0,0,1.1399999999999992,-4.479999999999976,-4.48000020027159)"><path d="M57 24H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h50a3 3 0 0 0 3-3V27a3 3 0 0 0-3-3ZM10 35a1 1 0 0 1-2 0v-6a1 1 0 0 1 2 0Zm9 0a1 1 0 0 1-1.768.641L14 31.762V35a1 1 0 0 1-2 0v-6a1 1 0 0 1 1.768-.641L17 32.238V29a1 1 0 0 1 2 0Zm9.894-5.551-3.016 6a1 1 0 0 1-.893.551 1 1 0 0 1-.894-.555l-2.985-6a1 1 0 0 1 1.792-.89l2.093 4.209 2.117-4.213a1 1 0 0 1 1.788.9ZM37 33a3 3 0 0 1-6 0v-2a3 3 0 0 1 6 0Zm4 2a1 1 0 0 1-2 0v-6a1 1 0 0 1 2 0Zm5-1h2a1 1 0 0 1 0 2h-2a3 3 0 0 1-3-3v-2a3 3 0 0 1 3-3h2a1 1 0 0 1 0 2h-2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1Zm9-3a1 1 0 0 1 0 2h-2v1h2a1 1 0 0 1 0 2h-3a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h3a1 1 0 0 1 0 2h-2v1Z" fill="currentColor" opacity="1" data-original="#000000"></path><path d="M34 30a1 1 0 0 0-1 1v2a1 1 0 0 0 2 0v-2a1 1 0 0 0-1-1Z" fill="currentColor" opacity="1" data-original="#000000"></path><rect width="26" height="8" x="19" y="11" rx="1" fill="currentColor" opacity="1" data-original="#000000"></rect><path d="M55 7a3.173 3.173 0 0 0-3.316-3H12.316A3.173 3.173 0 0 0 9 7v15h46Zm-8 11a3 3 0 0 1-3 3H20a3 3 0 0 1-3-3v-6a3 3 0 0 1 3-3h24a3 3 0 0 1 3 3ZM9 57a3.173 3.173 0 0 0 3.316 3h39.368A3.173 3.173 0 0 0 55 57V42H9Zm29-2H26a1 1 0 0 1 0-2h12a1 1 0 0 1 0 2Zm2-5H24a1 1 0 0 1 0-2h16a1 1 0 0 1 0 2Zm-18-7h20a1 1 0 0 1 0 2H22a1 1 0 0 1 0-2Z" fill="currentColor" opacity="1" data-original="#000000"></path></g></svg>
      </div>
      <div class="v-card-body">
        <div class="v-card-brand"><?= htmlspecialchars($v['Name']) ?></div>
        <?php if ($v['plate']): ?>
        <div class="v-card-plate"><?= htmlspecialchars($v['plate']) ?></div>
        <?php endif; ?>
        <div class="v-card-footer">
        

<button class="v-btn v-btn-view" onclick="openModal('<?=translate('Invoice',$_SESSION['Lang'])['translated']?>',     'Invoice.php?Invoice=<?= urlencode($v['filename']) ?>')"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" x="0" y="0" viewBox="0 0 488.85 488.85" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M244.425 98.725c-93.4 0-178.1 51.1-240.6 134.1-5.1 6.8-5.1 16.3 0 23.1 62.5 83.1 147.2 134.2 240.6 134.2s178.1-51.1 240.6-134.1c5.1-6.8 5.1-16.3 0-23.1-62.5-83.1-147.2-134.2-240.6-134.2zm6.7 248.3c-62 3.9-113.2-47.2-109.3-109.3 3.2-51.2 44.7-92.7 95.9-95.9 62-3.9 113.2 47.2 109.3 109.3-3.3 51.1-44.8 92.6-95.9 95.9zm-3.1-47.4c-33.4 2.1-61-25.4-58.8-58.8 1.7-27.6 24.1-49.9 51.7-51.7 33.4-2.1 61 25.4 58.8 58.8-1.8 27.7-24.2 50-51.7 51.7z" fill="currentColor" opacity="1" data-original="#000000"></path></g></svg>
</button>
 
       
       
         
         

<button class="v-btn v-btn-receipt" onclick="openModal('<?=translate('Receipt',$_SESSION['Lang'])['translated']?>',     'Receipt.php?Invoice=<?= urlencode($v['filename']) ?>')">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" x="0" y="0" viewBox="0 0 64 64" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.1299999999999994,0,0,1.1299999999999994,-4.159999938011133,-4.157727985382067)"><path d="M59 5.93H5a1 1 0 1 0 0 1.93h6a17.45 17.45 0 0 1 3.79 10.89v35.79a2.17 2.17 0 0 0 1.36 2.05 2 2 0 0 0 2.3-.51l3.75-3.91a.17.17 0 0 1 .26 0l5 5.24a2.09 2.09 0 0 0 1.53.66 2.05 2.05 0 0 0 1.52-.66l5-5.24a.18.18 0 0 1 .27 0l5 5.19a2.1 2.1 0 0 0 3.06 0L49 52a.17.17 0 0 1 .14-.07s.08 0 .12.06l4.47 4.34a2.06 2.06 0 0 0 2.29.44 2.19 2.19 0 0 0 1.32-2V18.75A19.36 19.36 0 0 0 54 7.86h5a1 1 0 1 0 0-1.93zm-38.6 37.8a1 1 0 0 1 1-1h27.91a1 1 0 1 1 0 1.93H21.37a1 1 0 0 1-.97-.93zm1-20.76h4.86a1 1 0 0 1 0 1.93h-4.89a1 1 0 1 1 0-1.93zm-1-6.21a1 1 0 0 1 1-1h4.86a1 1 0 0 1 0 1.93h-4.89a1 1 0 0 1-.97-.93zm1 18.83h27.91a1 1 0 1 1 0 1.93H21.37a1 1 0 1 1 0-1.93zm10.25-11.65a1 1 0 0 1 1-1h16.66a1 1 0 1 1 0 1.93H32.62a1 1 0 0 1-.97-.93zm17.66-6.21H32.62a1 1 0 1 1 0-1.93h16.69a1 1 0 1 1 0 1.93z" fill="currentColor" opacity="1" data-original="#000000" class=""></path></g></svg>
</button>
             
         

<button class="v-btn v-btn-receipt" onclick="openModal('<?=translate('Edit Invoice',$_SESSION['Lang'])['translated']?>',     'Edit_Invoice.php?Invoice=<?= urlencode($v['filename']) ?>')">
  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
</button>   
       
          
       <a href="Invoice.php?Invoice=DXB V 69918.json&amp;Delet=<?= urlencode($v['filename']) ?>" class="v-btn v-btn-delet" onclick="return confirm('<?=translate('Delete this record',$_SESSION['Lang'])['translated']?>?')">
    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14H6L5 6"></path><path d="M10 11v6M14 11v6"></path><path d="M9 6V4h6v2"></path></svg>  </a>

  
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="no-results" id="noResults">
      
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="200" height="200" x="0" y="0" viewBox="0 0 64 64" style="enable-background:new 0 0 512 512" xml:space="preserve" fill-rule="evenodd" class=""><g transform="matrix(1.0799999999999996,0,0,1.0799999999999996,-2.559999961853009,-2.5564804077148295)"><linearGradient id="a"><stop offset="0" stop-color="#cadcf0"></stop><stop offset="1" stop-color="#a4bbdb"></stop></linearGradient><linearGradient xlink:href="#a" id="d" x1="0" x2="1" y1="0" y2="0" gradientTransform="matrix(40 0 0 25.912 12 48.573)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient id="b"><stop offset="0" stop-color="#a4bbdb"></stop><stop offset="1" stop-color="#8da3be"></stop></linearGradient><linearGradient xlink:href="#b" id="e" x1="0" x2="1" y1="0" y2="0" gradientTransform="matrix(20.086 0 0 25.912 31.914 48.573)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient id="c"><stop offset="0" stop-color="#e9f3fc"></stop><stop offset="1" stop-color="#cadcf0"></stop></linearGradient><linearGradient xlink:href="#c" id="f" x1="0" x2="1" y1="0" y2=".337" gradientTransform="matrix(14.679 14.768 -21.492 8.973 12.133 35.609)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient xlink:href="#c" id="g" x1="0" x2="1" y1="0" y2=".227" gradientTransform="matrix(25.743 1.634 -3.837 17.203 32.081 42.038)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient xlink:href="#c" id="h" x1="0" x2="1" y1="0" y2="-.619" gradientTransform="matrix(20.007 -6.501 5.236 7.762 9.382 31.736)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient xlink:href="#c" id="i" x1="0" x2="1" y1="0" y2=".429" gradientTransform="matrix(15.249 13.945 -20.61 7.651 37.354 21.357)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient xlink:href="#a" id="j" x1="0" x2="1" y1="0" y2="0" gradientTransform="matrix(40 0 0 13 12 35.606)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient xlink:href="#b" id="k" x1="0" x2="1" y1="0" y2="0" gradientTransform="scale(16.159) rotate(88.472 .138 1.974)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient id="l" x1="0" x2="1" y1="0" y2="0" gradientTransform="rotate(49.719 3.124 26.068) scale(30.507)" gradientUnits="userSpaceOnUse"><stop offset="0" stop-color="#559aff"></stop><stop offset="1" stop-color="#2e69ef"></stop></linearGradient><linearGradient xlink:href="#c" id="m" x1="0" x2="1" y1="0" y2="0" gradientTransform="matrix(6.695 0 0 4.448 30.933 4.607)" gradientUnits="userSpaceOnUse"></linearGradient><linearGradient xlink:href="#c" id="n" x1="0" x2="1" y1="0" y2="0" gradientTransform="matrix(6.695 0 0 -4.448 30.933 10.607)" gradientUnits="userSpaceOnUse"></linearGradient><path fill="url(#d)" d="M52 35.617H12v18.512a2 2 0 0 0 1.425 1.916l18 5.4a2.01 2.01 0 0 0 1.15 0l18-5.4A2 2 0 0 0 52 54.129V35.617z" opacity="1" data-original="url(#d)"></path><path fill="url(#e)" d="M52 35.617H32s-.194 25.912 0 25.912.387-.028.575-.084l18-5.4A2 2 0 0 0 52 54.129V35.617z" opacity="1" data-original="url(#e)"></path><path fill="url(#f)" d="m32 42.106-20-6.489-5.075 7.524a1 1 0 0 0 .52 1.511l17.995 5.846a1 1 0 0 0 1.137-.39L32 42.106z" opacity="1" data-original="url(#f)"></path><path fill="url(#g)" d="m52 35.617-20 6.489 5.423 8.002a1 1 0 0 0 1.137.39l17.995-5.846a1 1 0 0 0 .52-1.511L52 35.617z" opacity="1" data-original="url(#g)"></path><path fill="url(#h)" d="M27.159 21.986a1 1 0 0 0-1.136-.388L8.027 27.445a.998.998 0 0 0-.52 1.51L12 35.617l20-6.511-4.841-7.12z" opacity="1" data-original="url(#h)"></path><path fill="url(#i)" d="M56.493 28.955a1 1 0 0 0-.52-1.51l-17.996-5.847a1 1 0 0 0-1.136.388L32 29.106l20 6.511 4.493-6.662z" opacity="1" data-original="url(#i)"></path><path fill="url(#j)" d="m52 35.617-20-6.511-20 6.511 20 6.489z" opacity="1" data-original="url(#j)"></path><path fill="url(#k)" d="M32 42.106v-13l-20 6.511z" opacity="1" data-original="url(#k)"></path><g fill="url(#l)"><path d="M27.982 31.978a8.757 8.757 0 0 1-1.124-.868 1 1 0 0 0-1.352 1.473c.409.376.87.734 1.382 1.069a1 1 0 0 0 1.094-1.674zM24.866 28.906a8.689 8.689 0 0 1-.754-1.213 1 1 0 0 0-1.783.907c.254.498.562.997.927 1.492a1 1 0 0 0 1.61-1.186zM23.404 24.825c.01-.403.069-.794.177-1.169a1 1 0 0 0-1.923-.551 6.71 6.71 0 0 0-.254 1.67 1 1 0 0 0 2 .05zM24.942 21.485c.257-.238.545-.458.86-.657a1 1 0 0 0-1.067-1.692 7.855 7.855 0 0 0-1.152.882 1 1 0 0 0 1.359 1.467zM28.661 19.76a11.338 11.338 0 0 1 1.598-.176 1.001 1.001 0 0 0-.085-1.999c-.66.028-1.286.099-1.877.208a1 1 0 0 0 .364 1.967zM33.873 19.701c.853.01 1.647-.02 2.384-.085a1 1 0 0 0-.177-1.992c-.675.059-1.402.087-2.184.077a1 1 0 0 0-.023 2zM39.544 19.003c.99-.303 1.826-.691 2.526-1.136a1 1 0 0 0-1.075-1.687 8.302 8.302 0 0 1-2.036.91 1 1 0 0 0 .585 1.913zM44.634 15.068a6.21 6.21 0 0 0 .607-3.003 1 1 0 0 0-1.998.093 4.215 4.215 0 0 1-.41 2.04 1.001 1.001 0 0 0 1.801.87zM43.997 8.529c-.729-.985-1.718-1.671-2.796-1.892a1.001 1.001 0 0 0-.402 1.96c.622.127 1.17.554 1.591 1.123a1 1 0 1 0 1.607-1.191z" fill="" opacity="1"></path></g><path fill="url(#m)" d="M30.933 6.831c1.082-6.127 10.459-5.731 5 0z" opacity="1" data-original="url(#m)"></path><path fill="url(#n)" d="M30.933 8.383c1.082 6.126 10.459 5.731 5 0z" opacity="1" data-original="url(#n)"></path><path fill="url(#l)" d="M30.843 8.617h6.696a1 1 0 0 0 0-2h-6.696a1.001 1.001 0 0 0 0 2z" opacity="1" data-original="url(#l)"></path></g></svg>
    <br><br>
    
      <?=translate('No Invoices match your search.',$_SESSION['Lang'])['translated']?>
    </div>
  </div>
</div>







<!-- ═══════════════════════════════════════════════════════
     FAST IFRAME MODAL  —  paste once before </body>
     Usage:  onclick="openModal('Title', 'url.php')"
═══════════════════════════════════════════════════════ -->

<div class="lm-overlay" id="lmOverlay" onclick="if(event.target===this)lmClose()">
  <div class="lm-panel" id="lmPanel">

    <!-- Header -->
    <div class="lm-header">
      <div class="lm-header-left">
        <div class="lm-header-icon">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="20" height="20" x="0" y="0" viewBox="0 0 24 24" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M17.5 7h-15A2.5 2.5 0 0 0 0 9.5v12A2.5 2.5 0 0 0 2.5 24h15a2.5 2.5 0 0 0 2.5-2.5v-12A2.5 2.5 0 0 0 17.5 7zM17 22H3c-.551 0-1-.448-1-1v-9h16v9c0 .552-.449 1-1 1z" fill="#000000" opacity="1" data-original="#000000" class=""></path><path d="M21.5 0h-15A2.5 2.5 0 0 0 4 2.5V5h18v11.95a2.5 2.5 0 0 0 2-2.45v-12A2.5 2.5 0 0 0 21.5 0z" fill="#000000" opacity="1" data-original="#000000" class=""></path></g></svg>
        </div>
        <div class="lm-title" id="lmTitle">
      <?=translate('Loading',$_SESSION['Lang'])['translated']?>…</div>
      </div>
      <div class="lm-header-right">
        <button class="lm-icon-btn" id="lmTabBtn" title="Open in new tab" onclick="lmOpenTab()" style="display:none">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
            <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
          </svg>
        </button>
        <button class="lm-icon-btn lm-x" onclick="lmClose()">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Slim progress bar -->
    <div class="lm-bar-wrap">
      <div class="lm-bar-fill" id="lmBar"></div>
    </div>

    <!-- Loading overlay (shown over iframe while loading) -->
    <div class="lm-splash" id="lmSplash">
      <div class="lm-rings">
        <div class="lm-ring r1"></div>
        <div class="lm-ring r2"></div>
      </div>
      <div class="lm-splash-msg" id="lmSplashMsg">
      <?=translate('Opening page…',$_SESSION['Lang'])['translated']?></div>
    </div>

    <!-- The actual iframe, always present, src set on open -->
    <iframe class="lm-frame" id="lmFrame" src="about:blank" frameborder="0" allowfullscreen></iframe>

  </div>
</div>



<script>
(function(){
  var url = '';
  var barTimer = null;
  var barVal = 0;

  /* Fake-fast bar: jumps to 85% quickly, waits for real load to finish */
  function startBar() {
    clearInterval(barTimer);
    barVal = 0;
    setBar(0);

    barTimer = setInterval(function(){
      if (barVal >= 85) { clearInterval(barTimer); return; }
      /* accelerate at start, slow near 85 */
      var step = barVal < 40 ? 4 : barVal < 70 ? 2 : 0.5;
      barVal = Math.min(barVal + step, 85);
      setBar(barVal);
    }, 30);
  }

  function setBar(v) {
    document.getElementById('lmBar').style.width = v + '%';
  }

  function finishBar() {
    clearInterval(barTimer);
    barVal = 100;
    setBar(100);
  }

  window.openModal = function(title, target) {
    url = target;

    var frame   = document.getElementById('lmFrame');
    var splash  = document.getElementById('lmSplash');
    var tabBtn  = document.getElementById('lmTabBtn');
    var overlay = document.getElementById('lmOverlay');

    /* reset */
    frame.src = 'about:blank';
    splash.classList.remove('lm-hide');
    tabBtn.style.display = 'none';
    document.getElementById('lmTitle').textContent    = title;
    document.getElementById('lmSplashMsg').textContent = '<?=translate('Opening page…',$_SESSION['Lang'])['translated']?>';

    /* show modal */
    overlay.classList.add('lm-on');
    document.body.style.overflow = 'hidden';

    /* start progress bar immediately */
    startBar();

    /* load iframe RIGHT AWAY — no fake delay */
    frame.onload = function(){
      if (frame.src === 'about:blank') return;
      finishBar();
      /* tiny pause so 100% renders before splash fades */
      setTimeout(function(){
        splash.classList.add('lm-hide');
        tabBtn.style.display = 'flex';
      }, 120);
    };

    frame.src = url;
  };

  window.lmOpenTab = function(){ window.open(url, '_blank'); };

  window.printpage = function(){
    var frame = document.getElementById('lmFrame');
    try {
      frame.contentWindow.focus();
      frame.contentWindow.print();
    } catch(e) {
      /* cross-origin fallback: open in new tab and print */
      var w = window.open(url, '_blank');
      w.onload = function(){ w.print(); };
    }
  };

  window.lmClose = function(){
    clearInterval(barTimer);
    var overlay = document.getElementById('lmOverlay');
    var frame   = document.getElementById('lmFrame');

    /* trigger slide-up animation */
    overlay.classList.remove('lm-on');
    overlay.classList.add('lm-closing');

    /* after animation finishes, fully reset */
    setTimeout(function(){
      overlay.classList.remove('lm-closing');
      frame.onload = null;
      frame.src = 'about:blank';
      document.getElementById('lmSplash').classList.remove('lm-hide');
      document.getElementById('lmTabBtn').style.display = 'none';
      document.body.style.overflow = '';
    }, 320);
  };

  document.addEventListener('keydown', function(e){ if(e.key==='Escape') lmClose(); });
}());
</script>
<!-- ═══════════════════════════════════════════════════════
     USAGE:
     <?php include 'modal-iframe.html'; ?>  (before </body>)

     <button onclick="openModal('View Invoice',  'Car.php?Car=<?=$f?>')">View</button>
     <button onclick="openModal('Open Receipt',  'Receipt.php?Car=<?=$f?>')">Receipt</button>
     <button onclick="openModal('Edit Vehicle',  'Edit.php?Car=<?=$f?>')">Edit</button>
     <button onclick="openModal('Settings',      'Settings.php')">Settings</button>
═══════════════════════════════════════════════════════ -->









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









<script>
  const searchInput = document.getElementById('search');
  const noResults   = document.getElementById('noResults');

  searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase().trim();
    let visible = 0;

    document.querySelectorAll('.vehicle-item').forEach(card => {
      const match = !q || card.dataset.search.includes(q);
      card.style.display = match ? '' : 'none';
      if (match) visible++;
    });

    noResults.style.display = (q && visible === 0) ? 'block' : 'none';
  });
</script>
</body>
</html>
