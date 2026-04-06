<?php 
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: Login.php');
    exit;
}

include "Static/config.php";



function generateRandomId($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}


$message = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carInfo = [
        "Name"          => $_POST['brand'],
        "chassis_number" => $_POST['chassis'],
        "Invoice_number" => "INV-".generateRandomId(5)."-".generateRandomId(5),
        "plate_number"   => $_POST['plate'],
        "Date" => date('Y-m-d'),
        "Time" => date('h:i A'),
    ];

    $defaultData = json_decode(file_get_contents('Static/Default.json'), true);
    $newItems = [];

    if(isset($_POST['items'])) {
        foreach($_POST['items'] as $index => $item) {
            if(isset($item['selected'])) {
                $newItems[] = [
                    "name"              => $defaultData[$index]['name'],
                    "image"             => $defaultData[$index]['image'],
                    "price"             => (float)$item['price'],
                    "real_price"        => (float)$item['real_price'],
                    "installation_cost" => (float)$item['installation_cost'],
                    "quantity"          => (int)$item['quantity'],
                    "Paint"             => (float)$item['Paint'],
                    "URL_Link"          => $defaultData[$index]['URL_Link']
                ];
            }
        }
    }

    $newJson   = ["Invoice_info" => $carInfo, "accessories" => $newItems];
    $filename  = $carInfo["plate_number"] . ".json";
    file_put_contents("Static/ALL_Invoice/" . $filename, json_encode($newJson, JSON_PRETTY_PRINT));
    $message   = $carInfo["plate_number"];
}

$data = json_decode(file_get_contents('Static/Default.json'), true);
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?=translate('Add Invoice',$_SESSION['Lang'])['translated']?> — <?=$settings['System_name']?> (<?=$settings['company_name']?>)</title>
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
  
</head>
<body>



<!-- ── HERO ────────────────────────────────────────────── -->
<div class="hero">
  <div class="hero-inner-edit">
      <div class="main-title">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0" viewBox="0 0 64 64" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g transform="matrix(1.1399999999999992,0,0,1.1399999999999992,-4.479999999999976,-4.48000020027159)"><path d="M57 24H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h50a3 3 0 0 0 3-3V27a3 3 0 0 0-3-3ZM10 35a1 1 0 0 1-2 0v-6a1 1 0 0 1 2 0Zm9 0a1 1 0 0 1-1.768.641L14 31.762V35a1 1 0 0 1-2 0v-6a1 1 0 0 1 1.768-.641L17 32.238V29a1 1 0 0 1 2 0Zm9.894-5.551-3.016 6a1 1 0 0 1-.893.551 1 1 0 0 1-.894-.555l-2.985-6a1 1 0 0 1 1.792-.89l2.093 4.209 2.117-4.213a1 1 0 0 1 1.788.9ZM37 33a3 3 0 0 1-6 0v-2a3 3 0 0 1 6 0Zm4 2a1 1 0 0 1-2 0v-6a1 1 0 0 1 2 0Zm5-1h2a1 1 0 0 1 0 2h-2a3 3 0 0 1-3-3v-2a3 3 0 0 1 3-3h2a1 1 0 0 1 0 2h-2a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1Zm9-3a1 1 0 0 1 0 2h-2v1h2a1 1 0 0 1 0 2h-3a1 1 0 0 1-1-1v-6a1 1 0 0 1 1-1h3a1 1 0 0 1 0 2h-2v1Z" fill="currentColor" opacity="1" data-original="#000000"></path><path d="M34 30a1 1 0 0 0-1 1v2a1 1 0 0 0 2 0v-2a1 1 0 0 0-1-1Z" fill="currentColor" opacity="1" data-original="#000000"></path><rect width="26" height="8" x="19" y="11" rx="1" fill="currentColor" opacity="1" data-original="#000000"></rect><path d="M55 7a3.173 3.173 0 0 0-3.316-3H12.316A3.173 3.173 0 0 0 9 7v15h46Zm-8 11a3 3 0 0 1-3 3H20a3 3 0 0 1-3-3v-6a3 3 0 0 1 3-3h24a3 3 0 0 1 3 3ZM9 57a3.173 3.173 0 0 0 3.316 3h39.368A3.173 3.173 0 0 0 55 57V42H9Zm29-2H26a1 1 0 0 1 0-2h12a1 1 0 0 1 0 2Zm2-5H24a1 1 0 0 1 0-2h16a1 1 0 0 1 0 2Zm-18-7h20a1 1 0 0 1 0 2H22a1 1 0 0 1 0-2Z" fill="currentColor" opacity="1" data-original="#000000"></path></g></svg>
 <div>
    <div class="hero-title"><?=translate('Add New Invoice',$_SESSION['Lang'])['translated']?></div>
    <div class="hero-sub"><?=translate('Fill in the vehicle details and select the accessories to include',$_SESSION['Lang'])['translated']?></div>
  </div></div></div>
</div>

<!-- ── MAIN ─────────────────────────────────────────────── -->
<div class="main-up">

<?php if($message): ?>
  <div class="alert alert-success">
    <div>
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
      <?=translate('Invoice created successfully!',$_SESSION['Lang'])['translated']?>
    </div>
    <a href="Invoice.php?Car=<?= urlencode($message) ?>.json"><?=translate('View Invoice →',$_SESSION['Lang'])['translated']?></a>
  </div>

  <script>
    // refresh parent page
    window.parent.location.reload();
  </script>
<?php endif; ?>

  <form method="POST">

    <!-- ── Vehicle Info Card ── -->
    <div class="card">
      <div class="card-header">
        <div class="card-header-icon">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 4v4h-7V8z"/>
            <circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
          </svg>
        </div>
        <div class="card-title"><?=translate('Invoice Information',$_SESSION['Lang'])['translated']?></div>
      </div>
      <div class="card-body">
        <div class="form-grid">
          <div>
            <label class="field-label"><?=translate('Name',$_SESSION['Lang'])['translated']?></label>
            <input type="text" name="brand" class="field-input" required placeholder="<?=translate('e.g. Toyota Land Cruiser',$_SESSION['Lang'])['translated']?>">
          </div>
          <div>
            <label class="field-label"><?=translate('Chassis Number',$_SESSION['Lang'])['translated']?></label>
            <input type="text" name="chassis" class="field-input" required placeholder="<?=translate('VIN / Chassis',$_SESSION['Lang'])['translated']?>">
          </div>
          <div>
            <label class="field-label"><?=translate('Plate Number',$_SESSION['Lang'])['translated']?></label>
            <input type="text" name="plate" class="field-input" required placeholder="<?=translate('Plate',$_SESSION['Lang'])['translated']?>">
          </div>
        </div>
      </div>
    </div>

    <!-- ── Accessories ── -->
    <div class="section-label"><?=translate('Select Accessories',$_SESSION['Lang'])['translated']?> (<?= count($data) ?> <?=translate('available',$_SESSION['Lang'])['translated']?>)</div>

    <div class="acc-grid">
      <?php foreach($data as $index => $item): ?>
      <div class="acc-card" id="acc-<?= $index ?>">

        <div class="acc-card-top" onclick="toggleAcc(<?= $index ?>)">
          <img class="acc-img" src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
          <span class="acc-name"><?= htmlspecialchars($item['name']) ?></span>
          <div class="acc-toggle">
            <svg width="11" height="11" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <input class="real-checkbox" type="checkbox"
            name="items[<?= $index ?>][selected]"
            id="chk-<?= $index ?>">
        </div>

        <div class="acc-fields">
          <div>
            <label class="mini-label"><?=translate('Price (AED)',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][price]" value="<?= $item['price'] ?>">
          </div>
          <div>
            <label class="mini-label"><?=translate('Real Price (AED)',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][real_price]" value="<?= $item['real_price'] ?>">
          </div>
          <div>
            <label class="mini-label"><?=translate('Installation (AED)',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][installation_cost]" value="<?= $item['installation_cost'] ?>">
          </div>
          <div>
            <label class="mini-label"><?=translate('Paint (AED)',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][Paint]" value="<?= $item['Paint'] ?>">
          </div>
          <div style="grid-column: 1 / -1;">
            <label class="mini-label"><?=translate('Quantity',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][quantity]" value="1" min="1" style="max-width:120px;">
          </div>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── Sticky Save Bar ── -->
    <div class="save-bar">
      <button type="submit" class="btn-save">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
          <polyline points="17 21 17 13 7 13 7 21"/>
          <polyline points="7 3 7 8 15 8"/>
        </svg>
        <?=translate('Save',$_SESSION['Lang'])['translated']?>
      </button>
    </div>

  </form>
</div>

<script>
function toggleAcc(index) {
  const card = document.getElementById('acc-' + index);
  const chk  = document.getElementById('chk-' + index);
  chk.checked = !chk.checked;
  card.classList.toggle('is-selected', chk.checked);
}
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
