<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: Login.php');
    exit;
}

$jsonFile = "Static/Default.json";
include "Static/config.php";

// Load data
$data = json_decode(file_get_contents($jsonFile), true);
if (!$data) { die("❌ Error: Could not load or decode JSON file."); }

// Extract allowed names
$allowedNames = array_column($data, 'name');

$message = "";
$count = count($data);

// ── DELETE ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_index'])) {
    $idx = (int)$_POST['delete_index'];
    if (isset($data[$idx])) {
        array_splice($data, $idx, 1);
        $data = array_values($data);
        file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    // redirect to avoid resubmit on refresh
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// ── SAVE / UPDATE ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
    $submitted = $_POST['data'];
    foreach ($submitted as $i => $item) {
        if (in_array($item['name'], $allowedNames)) {
            $data[$i]['name']              = $item['name'];
            $data[$i]['price']             = (float)$item['price'];
            $data[$i]['real_price']        = (float)$item['real_price'];
            $data[$i]['installation_cost'] = (float)$item['installation_cost'];
            $data[$i]['URL_Link']          = $item['URL_Link'];
            $data[$i]['image']             = $item['image'];
            $data[$i]['Paint']             = $item['Paint'];
        } else {
            $data[] = [
                'name'              => $item['name'],
                'price'             => (float)$item['price'],
                'real_price'        => (float)$item['real_price'],
                'installation_cost' => (float)$item['installation_cost'],
                'URL_Link'          => $item['URL_Link'],
                'image'             => $item['image'] ?: '',
                'Paint'             => $item['Paint'] ?: 0,
            ];
        }
    }

    if (file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $message = "Data saved successfully!";
    } else {
        $message = "Failed to save data.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?=translate('Edit Parts',$_SESSION['Lang'])['translated']?> - <?=$settings['System_name']?> (<?=$settings['company_name']?>)</title>
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
  </style>

<style>
.inv-table tbody td {
    padding: 10px 5px;
    /* vertical-align: middle; */
}
</style>
</head>
<body>



<!-- ── HERO ────────────────────────────────────────────── -->
<div class="hero">
  <div class="hero-inner">
    <div class="main-title">
     <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.1199999999999992,0,0,1.1199999999999992,-30.96000183105457,-31.474201354980323)"><path fill-rule="evenodd" d="M133.76 130.02c33.77-31.78 77.65-49.2 124.24-49.2s90.47 17.43 124.24 49.21l26.77-26.68C368.09 64.41 314.71 43.04 258 43.04s-110.09 21.37-151.02 60.31zm99.17 132.27c0 13.82 11.25 25.07 25.07 25.07s25.07-11.25 25.07-25.07-11.25-25.07-25.07-25.07c-13.82-.01-25.07 11.24-25.07 25.07zM131.31 378.23c.73.79.7 2-.05 2.76l-37.64 37.77c-.44.44-.85.58-1.45.58-.62 0-1-.17-1.43-.63C50.93 376.14 29 320.6 29 262.29c0-58.01 21.73-113.35 61.19-155.82.42-.46.8-.63 1.42-.64h.07c.59 0 .96.16 1.38.58l37.77 37.64c.76.75.79 1.97.07 2.76-20.9 22.99-34.97 50.92-41.12 80.89-.08.31-.14.62-.18.94a172.194 172.194 0 0 0-3.33 33.65c0 9.9.86 19.69 2.52 29.29.02.31.08.61.15.9 5.66 31.84 20.22 61.54 42.37 85.75zm250.46 16.76c-33.72 31.49-77.42 48.77-123.77 48.77s-90.06-17.28-123.77-48.77l-26.68 26.78c40.85 38.58 94.04 59.76 150.45 59.76s109.6-21.18 150.45-59.76zm33.71-159.42c1.48 8.77 2.24 17.7 2.24 26.72 0 7.66-.54 15.25-1.61 22.73h-56.25c-42.29 0-76.69 34.41-76.69 76.69v68.21a172.24 172.24 0 0 1-50.34 0V361.7c0-42.28-34.41-76.69-76.69-76.69H99.89c-1.07-7.48-1.62-15.07-1.62-22.73 0-9.02.77-17.95 2.25-26.71 4.03.14 8.06.22 12.08.22 34.68 0 68.76-5.41 95.57-15.36 4.95-1.84 9.79-3.73 14.46-5.56 14.27-5.59 27.72-10.86 35.16-10.62.14.01.28.01.42 0 7.38-.25 20.88 5.02 35.16 10.62 4.67 1.83 9.51 3.72 14.46 5.56 29.92 11.11 68.89 16.55 107.65 15.14zM258 225.22c20.44 0 37.07 16.63 37.07 37.07s-16.63 37.07-37.07 37.07-37.07-16.63-37.07-37.07 16.63-37.07 37.07-37.07zm127.1-78.41c-.72-.79-.69-2.01.07-2.76l37.77-37.64c.42-.42.79-.58 1.38-.58h.07c.62.01 1 .18 1.42.64 39.46 42.46 61.19 97.8 61.19 155.82 0 58.31-21.93 113.86-61.75 156.42-.42.46-.8.63-1.43.63-.63.01-1-.14-1.44-.58l-37.64-37.77c-.75-.76-.78-1.97-.06-2.76 22.16-24.21 36.72-53.91 42.38-85.75.07-.29.12-.59.15-.9 1.67-9.6 2.49-19.39 2.49-29.29 0-11.4-1.11-22.66-3.3-33.66-.04-.32-.1-.62-.18-.92a171.218 171.218 0 0 0-41.12-80.9z" clip-rule="evenodd" fill="currentColor" opacity="1" data-original="" class=""></path></g></svg>
     <div>
      <div class="hero-title"><?=translate('Edit Parts',$_SESSION['Lang'])['translated']?> </div>
      <div class="hero-sub"><?=translate('Manage your Parts & accessories',$_SESSION['Lang'])['translated']?> </div>
      </div>
    </div>
    <div class="hero-stats">
      <div class="stat-pill">
        <div class="s-num"><?= $count ?></div>
        <div class="s-label"><?=translate('Parts',$_SESSION['Lang'])['translated']?></div>
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
    <input class="search-input" type="text" id="search" placeholder="<?=translate('Parts Search…"',$_SESSION['Lang'])['translated']?>">
  </div>
</div>
                        


    
<!-- ── MAIN ─────────────────────────────────────────────── -->
<div class="main">

    <?php if($message): ?>


  <div class="alert alert-success">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
     <?= $message ?>
  </div>

    <?php endif; ?>

    <?php if (empty($data)): ?>
    <div class="empty-state">
      <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
        <rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 4v4h-7V8z"/>
        <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
      </svg>
      <h3><?=translate('No Accessories found',$_SESSION['Lang'])['translated']?></h3>
      <p><?=translate('Add your first Accessories to get started.',$_SESSION['Lang'])['translated']?></p>
    </div>
    <?php endif; ?>

           












<div  id="accessoryForm" style="
    background-color: #fff;
    padding: 36px 48px;
    box-shadow: var(--shadow-sm);
    border-radius: 9px;
">
<form method="post">
    <table class="inv-table" id="accessoryTable">
      <thead class="table-dark">
        <tr>
          <th>#</th>
          <th><?=translate('Image',$_SESSION['Lang'])['translated']?></th>
          <th><?=translate('Item Name',$_SESSION['Lang'])['translated']?></th>
          <th><?=translate('Displayed Price (AED)',$_SESSION['Lang'])['translated']?></th>
          <th><?=translate('Real Price (AED)',$_SESSION['Lang'])['translated']?></th>
          <th><?=translate('Installation Cost (AED)',$_SESSION['Lang'])['translated']?></th>
          <th><?=translate('Paint (AED)',$_SESSION['Lang'])['translated']?></th>
          <th><?=translate('URL',$_SESSION['Lang'])['translated']?></th>
          <th><?=translate('Action',$_SESSION['Lang'])['translated']?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data as $i => $item): ?>
        <tr class="vehicle-item"
            data-search="<?= strtolower(htmlspecialchars(
                $item['name'] . ' ' . $item['price'] . ' ' . $item['real_price']
            )) ?>">
          <td><?= $i + 1 ?></td>
          <td style="text-align:-webkit-center;">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="Product">
            <input name="data[<?= $i ?>][image]" value="<?= htmlspecialchars($item['image']) ?>">
          </td>
          <td><input name="data[<?= $i ?>][name]"              value="<?= htmlspecialchars($item['name']) ?>"></td>
          <td><input name="data[<?= $i ?>][price]"             value="<?= (float)$item['price'] ?>"></td>
          <td><input name="data[<?= $i ?>][real_price]"        value="<?= (float)$item['real_price'] ?>"></td>
          <td><input name="data[<?= $i ?>][installation_cost]" value="<?= (float)$item['installation_cost'] ?>"></td>
          <td><input name="data[<?= $i ?>][Paint]"             value="<?= (float)$item['Paint'] ?>"></td>
          <td><input name="data[<?= $i ?>][URL_Link]"          value="<?= htmlspecialchars($item['URL_Link']) ?>"></td>
          <td>
            <?php if (!empty($item['URL_Link'])): ?>
              <a href="<?= htmlspecialchars($item['URL_Link']) ?>" target="_blank" class="v-btn v-btn-view">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 488.85 488.85"><g><path d="M244.425 98.725c-93.4 0-178.1 51.1-240.6 134.1-5.1 6.8-5.1 16.3 0 23.1 62.5 83.1 147.2 134.2 240.6 134.2s178.1-51.1 240.6-134.1c5.1-6.8 5.1-16.3 0-23.1-62.5-83.1-147.2-134.2-240.6-134.2zm6.7 248.3c-62 3.9-113.2-47.2-109.3-109.3 3.2-51.2 44.7-92.7 95.9-95.9 62-3.9 113.2 47.2 109.3 109.3-3.3 51.1-44.8 92.6-95.9 95.9zm-3.1-47.4c-33.4 2.1-61-25.4-58.8-58.8 1.7-27.6 24.1-49.9 51.7-51.7 33.4-2.1 61 25.4 58.8 58.8-1.8 27.7-24.2 50-51.7 51.7z" fill="currentColor"/></g></svg>
              </a>
            <?php endif; ?>

            <!-- Delete button — posts delete_index to the same page -->
            <a href="#"
               class="v-btn v-btn-delet"
               onclick="if(confirm('<?= translate('Delete this record?', $_SESSION['Lang'])['translated'] ?>')) {
                            document.getElementById('del-<?= $i ?>').submit();
                        } return false;">
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6l-1 14H6L5 6"></path>
                <path d="M10 11v6M14 11v6"></path>
                <path d="M9 6V4h6v2"></path>
              </svg>
            </a>

            <!-- Hidden mini-form just for delete — outside the main form -->
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="no-results" id="noResults">
        <?=translate('No Parts match your search.',$_SESSION['Lang'])['translated']?>
    </div>

    <div class="save-bar">
        <button type="button" class="tb-btn tb-btn-receipt" id="addRowBtn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"></path></svg>
            <?=translate('Add New Part',$_SESSION['Lang'])['translated']?>
        </button>
        <button type="submit" class="btn-save">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            <?=translate('Save Changes',$_SESSION['Lang'])['translated']?>
        </button>
    </div>
</form>

<!-- Delete forms live OUTSIDE the main form (nested forms are invalid HTML) -->
<?php foreach ($data as $i => $item): ?>
<form id="del-<?= $i ?>" method="POST" style="display:none">
    <input type="hidden" name="delete_index" value="<?= $i ?>">
</form>
<?php endforeach; ?>







  </div>


<script>
const searchInput = document.getElementById('search');
const noResults   = document.getElementById('noResults');

searchInput.addEventListener('input', function () {
  const q = this.value.toLowerCase().trim();
  let visible = 0;

  document.querySelectorAll('.vehicle-item').forEach(row => {
    const text = row.dataset.search || '';

    if (!q || text.includes(q)) {
      row.style.display = '';
      visible++;
    } else {
      row.style.display = 'none';
    }
  });

  noResults.style.display = (q && visible === 0) ? 'block' : 'none';
});
</script>


<script>
// Handle adding new row dynamically
document.getElementById('addRowBtn').addEventListener('click', function() {
    const table = document.getElementById('accessoryTable').querySelector('tbody');
    const rowCount = table.rows.length;
    const row = table.insertRow();

    row.innerHTML = `
        <td>${rowCount + 1}</td>
        <td>
            <img src="" alt="Product">
            <input name="data[${rowCount}][image]" value="">
        </td>
        <td><input name="data[${rowCount}][name]" value=""></td>
        <td><input name="data[${rowCount}][price]" value="0"></td>
        <td><input name="data[${rowCount}][real_price]" value="0"></td>
        <td><input name="data[${rowCount}][installation_cost]" value="0"></td>
        <td><input name="data[${rowCount}][Paint]" value="0"></td>
        <td>
            <input name="data[${rowCount}][URL_Link]" value="">
            <a href="#" target="_blank" class="btn btn-sm btn-info mt-1 visitBtn" style="display:none;">Visit</a>
        </td>
    `;

    // Update the "Visit" button when URL changes
    const urlInput = row.querySelector(`input[name="data[${rowCount}][URL_Link]"]`);
    const visitBtn = row.querySelector('.visitBtn');
    urlInput.addEventListener('input', function() {
        if (this.value.trim()) {
            visitBtn.href = this.value;
            visitBtn.style.display = 'inline-block';
        } else {
            visitBtn.style.display = 'none';
        }
    });
});


setTimeout(() => {
  document.querySelectorAll('.custom-alert').forEach(alert => {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    setTimeout(() => alert.remove(), 300);
  });
}, 3000);
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
