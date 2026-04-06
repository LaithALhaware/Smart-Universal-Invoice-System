<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: Login.php');
    exit;
}

$defaultData = json_decode(file_get_contents('Static/Default.json'), true);
$existingData = null;
$filename = "";
$message = "";

include "Static/config.php";




if(isset($_GET['Invoice'])) {
    $filename = "Static/ALL_Invoice/" . $_GET['Invoice'];
    if(file_exists($filename)) {
        $existingData = json_decode(file_get_contents($filename), true);
    }
} else {
            header("Location: index.php");
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {




$oldCarInfo = $existingData['Invoice_info'] ?? [];

/* ✅ Update only allowed fields */
$carInfo = [
    "Name" => (!empty($_POST['Name'])) 
        ? $_POST['Name'] 
        : ($oldCarInfo['Name'] ?? null),

    "chassis_number" => (!empty($_POST['chassis'])) 
        ? $_POST['chassis'] 
        : ($oldCarInfo['chassis_number'] ?? null),

    "plate_number" => (!empty($_POST['plate'])) 
        ? $_POST['plate'] 
        : ($oldCarInfo['plate_number'] ?? null),

    /* 🔒 Protected fields (never change) */
    "Invoice_number" => $oldCarInfo['Invoice_number'] ?? null,
    "Date" => $oldCarInfo['Date'] ?? null,
    "Time" => $oldCarInfo['Time'] ?? null
];

/* ✅ Handle items */
$items = $_POST['items'] ?? [];

foreach ($items as $item) {

    $name = trim($item['name'] ?? '');
    $amount = $item['amount'] ?? null;

    if ($name === '' || $amount === null || $amount === '') {
        continue;
    }

    $key = str_replace(' ', '_', $name);

    if (!is_numeric($amount)) {
        continue;
    }

    $carInfo[$key] = (float) $amount;
}






    $currentItems = $existingData['accessories'] ?? [];

    if(isset($_POST['items'])) {
        $currentMap = [];
        foreach($currentItems as $key => $acc) {
            $currentMap[$acc['name']] = $key;
        }

        foreach($_POST['items'] as $index => $item) {
            $name = $defaultData[$index]['name'];

            if(isset($item['selected'])) {
                if(isset($currentMap[$name])) {
                    $key = $currentMap[$name];
                    $currentItems[$key]['price']             = (float)$item['price'];
                    $currentItems[$key]['real_price']        = (float)$item['real_price'];
                    $currentItems[$key]['installation_cost'] = (float)$item['installation_cost'];
                    $currentItems[$key]['Paint']             = (float)$item['Paint'];
                    $currentItems[$key]['quantity']          = (int)$item['quantity'];
                } else {
                    $currentItems[] = [
                        "name"              => $name,
                        "image"             => $defaultData[$index]['image'],
                        "price"             => (float)$item['price'],
                        "real_price"        => (float)$item['real_price'],
                        "installation_cost" => (float)$item['installation_cost'],
                        "Paint"             => (float)$item['Paint'],
                        "quantity"          => (int)$item['quantity'],
                        "URL_Link"          => $defaultData[$index]['URL_Link']
                    ];
                    $currentMap[$name] = count($currentItems) - 1;
                }
            } else {
                if(isset($currentMap[$name])) {
                    unset($currentItems[$currentMap[$name]]);
                    unset($currentMap[$name]);
                    $currentItems = array_values($currentItems);
                    $currentMap = [];
                    foreach($currentItems as $k => $acc) {
                        $currentMap[$acc['name']] = $k;
                    }
                }
            }
        }
    }

    $newJson = ["Invoice_info" => $carInfo, "accessories" => $currentItems];
    $filename = "Static/ALL_Invoice/" . $carInfo["plate_number"] . ".json";
    file_put_contents($filename, json_encode($newJson, JSON_PRETTY_PRINT));
    $message = $carInfo["plate_number"];
}

$formData = $existingData ?? ["Invoice_info" => ["Name"=>"","chassis_number"=>"","plate_number"=>""], "accessories"=>[]];
$car = $formData['Invoice_info'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?=translate('Edit Invoice',$_SESSION['Lang'])['translated']?> - <?=$settings['System_name']?> (<?=$settings['company_name']?>)</title>
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
    <div class="hero-title"><?=translate('Edit Invoice',$_SESSION['Lang'])['translated']?></div>
    <div class="hero-sub">
      <?=translate('Update Invoice info and Accessories for ',$_SESSION['Lang'])['translated']?>
      <?=htmlspecialchars($formData['Invoice_info']['Name'])?>
    </div></div>
  </div></div>
</div>

<!-- ── MAIN ─────────────────────────────────────────────── -->
<div class="main-up">

  <?php if($message): ?>

  <div class="alert alert-success">
      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
        <polyline points="22 4 12 14.01 9 11.01"/>
      </svg>
      <?=translate('Invoice Saved successfully!',$_SESSION['Lang'])['translated']?>
  </div>

  
  <?php endif; ?>

  <form method="POST">

    <!-- Car Info -->
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
            <input type="text" name="Name" class="field-input" required placeholder="<?=translate('e.g. Toyota Land Cruiser',$_SESSION['Lang'])['translated']?>"
              value="<?= htmlspecialchars($formData['Invoice_info']['Name']) ?>">
          </div>
          <div>
            <label class="field-label"><?=translate('Chassis Number',$_SESSION['Lang'])['translated']?></label>
            <input type="text" name="chassis" class="field-input" required placeholder="<?=translate('VIN / Chassis',$_SESSION['Lang'])['translated']?>"
              value="<?= htmlspecialchars($formData['Invoice_info']['chassis_number']) ?>">
          </div>
          <div>
            <label class="field-label"><?=translate('Plate Number',$_SESSION['Lang'])['translated']?></label>
            <input type="text" name="plate" class="field-input" required placeholder="<?=translate('Plate',$_SESSION['Lang'])['translated']?>"
              value="<?= htmlspecialchars($formData['Invoice_info']['plate_number']) ?>">
          </div>
        </div>
</div>

      <div class="card-header">
        <div class="card-header-icon">
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="16" height="16" x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve" fill-rule="evenodd"><g transform="matrix(1.1399999999999992,0,0,1.1399999999999992,-2.2399346780776845,-2.250920009613033)"><path d="M11 2.656c2.647 0 4.796 2.149 4.796 4.796S13.647 12.248 11 12.248s-4.796-2.149-4.796-4.796S8.353 2.656 11 2.656zm6.762 13.531A7.472 7.472 0 0 1 22.5 14.5c4.139 0 7.5 3.361 7.5 7.5s-3.361 7.5-7.5 7.5S15 26.139 15 22c0-2.619 1.349-4.66 2.762-5.813zm3.74 9.486a1 1 0 0 0 1.994.028c.075-.019.148-.041.218-.066.924-.327 1.592-1.038 1.592-2.275 0-1.211-.829-1.834-1.992-2.173-.403-.118-.847-.204-1.235-.334a1.711 1.711 0 0 1-.313-.132c-.031-.018-.072-.028-.072-.069 0-.305.239-.418.493-.453a2.322 2.322 0 0 1 1.546.37 1 1 0 0 0 1.145-1.64 4.216 4.216 0 0 0-1.38-.607 1.001 1.001 0 0 0-1.994-.021c-.557.153-1.029.452-1.352.887-.278.373-.458.853-.458 1.464 0 1.211.829 1.834 1.992 2.173.403.118.847.204 1.235.334.113.038.22.079.313.132.031.018.072.028.072.069 0 .302-.237.408-.488.442a2.415 2.415 0 0 1-1.564-.368 1 1 0 0 0-1.119 1.658c.385.26.863.459 1.367.581zm-6.369 2.323C11.897 28 7.482 28 5.007 28a3.001 3.001 0 0 1-2.998-2.855l-.001-.028a39.881 39.881 0 0 1 .5-7.195c.255-1.546 1.578-3.49 2.926-4.311l.163-.098a.998.998 0 0 1 1.1.05C7.941 14.467 9.472 15 11.126 15s3.185-.533 4.429-1.437a1 1 0 0 1 1.094-.053l.169.101c.185.112.369.245.55.394a9.515 9.515 0 0 0-.87.633C14.708 16.098 13 18.684 13 22c0 2.273.8 4.36 2.133 5.996z" fill="currentColor" opacity="1" data-original="#000000"></path></g></svg>
        </div>
        <div class="card-title"><?=translate('Cost Breakdown &s Profit Scenarios',$_SESSION['Lang'])['translated']?> <sub><?=translate('(This part is only visible to the seller.)',$_SESSION['Lang'])['translated']?></sub></div>
      </div>



<?php
$sum = 0;
?>


<table class="invoice-table">
  <tr>
    <th><?=translate('Name',$_SESSION['Lang'])['translated']?></th>
    <th><?=translate('Amount',$_SESSION['Lang'])['translated']?></th>
    <th></th>
  </tr>

<?php foreach ($car as $key => $value): ?>
  <?php 
    if (in_array($key, ['Name', 'chassis_number', 'plate_number', 'Date', 'Time', 'Invoice_number'])) continue;
    if (is_numeric($value)) $sum += $value;
  ?>
  
  <tr>
    <td>
      <input type="text" 
             name="items[<?= htmlspecialchars($key) ?>][name]" 
             value="<?= htmlspecialchars(str_replace('_', ' ', $key)) ?>" 
             class="field-input">
    </td>

    <td>
      <input type="number" step="0.01"
             name="items[<?= htmlspecialchars($key) ?>][amount]" 
             value="<?= htmlspecialchars($value) ?>" 
             class="field-input amount-input">
    </td>

    <td class="width50px">
      <button type="button" class="delete-row">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="14" height="14" x="0" y="0" viewBox="0 0 320.591 320.591" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M30.391 318.583a30.37 30.37 0 0 1-21.56-7.288c-11.774-11.844-11.774-30.973 0-42.817L266.643 10.665c12.246-11.459 31.462-10.822 42.921 1.424 10.362 11.074 10.966 28.095 1.414 39.875L51.647 311.295a30.366 30.366 0 0 1-21.256 7.288z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M287.9 318.583a30.37 30.37 0 0 1-21.257-8.806L8.83 51.963C-2.078 39.225-.595 20.055 12.143 9.146c11.369-9.736 28.136-9.736 39.504 0l259.331 257.813c12.243 11.462 12.876 30.679 1.414 42.922-.456.487-.927.958-1.414 1.414a30.368 30.368 0 0 1-23.078 7.288z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg></button>
    </td>
  </tr>

<?php endforeach; ?>

  <!-- TOTAL -->
  <tr>
    <td style="text-align:end;"><strong><?=translate('Total',$_SESSION['Lang'])['translated']?></strong></td>
    <td>
      <input type="text" id="totalAmount" 
             value="<?= number_format($sum, 2) ?>" 
             readonly class="field-input total-input">
    </td>
    <td class="width50px"><!-- ADD BUTTON -->
<button type="button" id="addRowBtn" class="tb-btn-success"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="14" height="14" x="0" y="0" viewBox="0 0 448 448" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M408 184H272a8 8 0 0 1-8-8V40c0-22.09-17.91-40-40-40s-40 17.91-40 40v136a8 8 0 0 1-8 8H40c-22.09 0-40 17.91-40 40s17.91 40 40 40h136a8 8 0 0 1 8 8v136c0 22.09 17.91 40 40 40s40-17.91 40-40V272a8 8 0 0 1 8-8h136c22.09 0 40-17.91 40-40s-17.91-40-40-40zm0 0" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg></button></td>
  </tr>

</table>


<style>

  .width50px {
    width: 50px;
}
 .invoice-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.invoice-table th,
.invoice-table td {
  padding: 10px;
  border-bottom: 1px solid #eee;
}

.field-input {
  width: 100%;
  padding: 6px 8px;
  border-radius: 6px;
  border: 1px solid #ddd;
}

.total-input {
  font-weight: bold;
  background: #eef2ff;
}

.delete-row {
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 4px 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 6px 8px;
}

.tb-btn-success {
  background: #10b981;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 4px 8px;
  cursor: pointer;  display: flex;
  align-items: center;
  padding: 6px 8px;
}
</style>

<script>
let rowIndex = 1000;

// ➕ ADD ROW
document.getElementById('addRowBtn').addEventListener('click', function () {

  const table = document.querySelector('.invoice-table');

  // Always get the last row (total row)
  const rows = table.querySelectorAll('tr');
  const totalRow = rows[rows.length - 1];

  const newRow = document.createElement('tr');

  newRow.innerHTML = `
    <td>
      <input type="text" 
             name="items[new_${rowIndex}][name]" 
             placeholder="Item name"
             class="field-input">
    </td>
    <td>
      <input type="number" step="0.01"
             name="items[new_${rowIndex}][amount]" 
             placeholder="0.00"
             class="field-input amount-input">
    </td>
    <td>
      <button type="button" class="delete-row"><svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="14" height="14" x="0" y="0" viewBox="0 0 320.591 320.591" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M30.391 318.583a30.37 30.37 0 0 1-21.56-7.288c-11.774-11.844-11.774-30.973 0-42.817L266.643 10.665c12.246-11.459 31.462-10.822 42.921 1.424 10.362 11.074 10.966 28.095 1.414 39.875L51.647 311.295a30.366 30.366 0 0 1-21.256 7.288z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path><path d="M287.9 318.583a30.37 30.37 0 0 1-21.257-8.806L8.83 51.963C-2.078 39.225-.595 20.055 12.143 9.146c11.369-9.736 28.136-9.736 39.504 0l259.331 257.813c12.243 11.462 12.876 30.679 1.414 42.922-.456.487-.927.958-1.414 1.414a30.368 30.368 0 0 1-23.078 7.288z" fill="#ffffff" opacity="1" data-original="#000000" class=""></path></g></svg></button>
    </td>
  `;

  // Insert before the last row (total row)
  table.tBodies[0].insertBefore(newRow, totalRow);

  rowIndex++;
});

// ❌ DELETE ROW
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('delete-row')) {
    e.target.closest('tr').remove();
    updateTotal();
  }
});

// 🔄 UPDATE TOTAL
function updateTotal() {
  let total = 0;

  document.querySelectorAll('.amount-input').forEach(input => {
    const val = parseFloat(input.value);
    if (!isNaN(val)) total += val;
  });

  document.getElementById('totalAmount').value = total.toFixed(2);
}

// LIVE UPDATE
document.addEventListener('input', function(e) {
  if (e.target.classList.contains('amount-input')) {
    updateTotal();
  }
});
</script>

      
    </div>

    <!-- Accessories -->
    <div class="section-label"><?=translate('Select Accessories',$_SESSION['Lang'])['translated']?> (<?= count($defaultData) ?> <?=translate('available',$_SESSION['Lang'])['translated']?>)</div>

    <div class="acc-grid">
      <?php foreach($defaultData as $index => $item):
        $selectedItem = null;
        foreach($formData['accessories'] as $acc) {
            if($acc['name'] === $item['name']) { $selectedItem = $acc; break; }
        }
        $isChecked         = $selectedItem ? true : false;
        $price             = $selectedItem['price']             ?? $item['price'];
        $real_price        = $selectedItem['real_price']        ?? $item['real_price'];
        $installation_cost = $selectedItem['installation_cost'] ?? $item['installation_cost'];
        $Paint             = $selectedItem['Paint']             ?? $item['Paint'];
        $quantity          = $selectedItem['quantity']          ?? 1;
      ?>
      <div class="acc-card <?= $isChecked ? 'is-selected' : '' ?>" id="acc-<?= $index ?>">

        <!-- Header row — click to toggle -->
        <div class="acc-card-top" onclick="toggleAcc(<?= $index ?>)">
          <img class="acc-img" src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
          <span class="acc-name"><?= htmlspecialchars($item['name']) ?></span>
          <div class="acc-toggle">
            <svg width="11" height="11" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <!-- real checkbox (hidden) -->
          <input class="real-checkbox" type="checkbox"
            name="items[<?= $index ?>][selected]"
            id="chk-<?= $index ?>"
            <?= $isChecked ? 'checked' : '' ?>>
        </div>

        <!-- Fields panel -->
        <div class="acc-fields">
          <div>
            <label class="mini-label"><?=translate('Price (AED)',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][price]" value="<?= $price ?>">
          </div>
          <div>
            <label class="mini-label"><?=translate('Real Price (AED)',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][real_price]" value="<?= $real_price ?>">
          </div>
          <div>
            <label class="mini-label"><?=translate('Installation (AED)',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][installation_cost]" value="<?= $installation_cost ?>">
          </div>
          <div>
            <label class="mini-label"><?=translate('Paint (AED)',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][Paint]" value="<?= $Paint ?>">
          </div>
          <div style="grid-column: 1 / -1;">
            <label class="mini-label"><?=translate('Quantity',$_SESSION['Lang'])['translated']?></label>
            <input class="mini-input" type="number" name="items[<?= $index ?>][quantity]" value="<?= $quantity ?>" min="1" style="max-width:120px;">
          </div>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

    <!-- Sticky save bar -->
    <div class="save-bar">
      <button type="submit" class="btn-save">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
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
  const now  = !chk.checked;
  chk.checked = now;
  card.classList.toggle('is-selected', now);
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
