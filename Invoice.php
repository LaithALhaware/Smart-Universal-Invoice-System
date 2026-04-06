<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: Login.php');
    exit;
}



include "Static/config.php";






// Get filename from GET parameter
if (!isset($_GET['Invoice'])) {
    header("Location: index.php");
    exit();
}

$jsonData = file_get_contents('Static/ALL_Invoice/'.$_GET['Invoice']);
$data = json_decode($jsonData, true);
$car = $data['Invoice_info'];
$accessories = $data['accessories'];
$total_price = 0;

if(isset($_GET['Delet'])) {
    $filename = basename($_GET['Delet']);
    $filepath = "Static/ALL_Invoice/" . $filename;
    if(file_exists($filepath)) {
        if(unlink($filepath)) {
            header("Location: index.php");
        } else {
            echo "❌ Failed to delete '$filename'. Check permissions.";
        }
    } else {
        echo "❌ File '$filename' not found!";
    }
}

function generateRandomId($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= htmlspecialchars($car['brand']) ?> — <?= htmlspecialchars($car['chassis_number']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet"/>
  
    <title> <?=translate('Invoice',$_SESSION['Lang'])['translated']?> — <?=$settings['System_name']?> (<?=$settings['company_name']?>)</title>

   
  
  
        <link rel="stylesheet" href="Static/css/Main.css">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&family=Inter:wght@300;400;700&display=swap" rel="stylesheet">

<style>
body {
   <?= $_SESSION['Direction']=='right' ? "font-family: 'Cairo', sans-serif;" : "font-family: 'Inter', 'Cairo', sans-serif;" ?> 
}
  
  </style>


</head>
<body>

<!-- ── ACTION BAR (screen only) ────────────────────────────── -->
<div class="topbar" style="display:flex;" id="actionBar" dir="<?= $_SESSION['Direction']=='right' ? "rtl" : "ltl" ?>">

<?php
$langsJson = @file_get_contents('http://localhost:9000/languages');
$languages = $langsJson ? json_decode($langsJson, true) : [
    ['code'=>'ar','name'=>'العربية','name_en'=>'Arabic','dir'=>'rtl'],
    ['code'=>'en','name'=>'English','name_en'=>'English','dir'=>'ltr'],
];
?>

<form method="POST" id="langForm">
          <select class="field-input" name="Language" id="langSelect" style="padding: 5px 10px;width: auto;">
            <option value="" selected><?=translate('Invoice language',$_SESSION['Lang'])['translated']?></option>
            <?php foreach ($languages as $l): ?>
              <option value="<?= htmlspecialchars($l['code']) ?>"
                data-dir="<?= htmlspecialchars($l['dir']) ?>">
                <?= htmlspecialchars($l['name']) ?> — <?= htmlspecialchars($l['name_en']) ?>
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

let form = document.getElementById("langForm");
let select = document.getElementById("langSelect");

// submit automatically on change
select.addEventListener("change", function() {
    form.submit(); // 🔥 auto submit
});
</script>

</form>


  <button class="tb-btn tb-btn-ghost" onclick="window.print()">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
     <?=translate('Print Invoice',$_SESSION['Lang'])['translated']?>
  </button>


</div>
<?php
$langInvoice = $_POST['Language'] ?? 'en';
$dirInvoice  = $_POST['dir'] ?? 'left';
?>
<!-- ── PAGE WRAP ────────────────────────────────────────────── -->
<div class="page-wrap">
<div class="invoice-card"  dir="<?= $dirInvoice=='right' ? "rtl" : "ltl" ?>">

  <!-- ── HEADER ─────────────────────────────────────────────── -->
  <div class="inv-header">
    <div style="display: flex;align-items: center;">
     
<?php if(!empty($settings['logo_path'])): ?>
          <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="logo" style="height: 80px;">
<?php else: ?>
          <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="50" height="50" x="0" y="0" viewBox="0 0 512.006 512.006" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M184.002 368.002h-7.572c-7.082 0-13.578-4.432-15.689-11.192-2.556-8.187 1.723-16.748 9.521-19.738l120.016-46.159c8.429-3.242 12.477-12.799 8.999-21.134-47.344-113.465-59.191-253.421-59.325-255.067-1.219-15.054-20.837-20.202-29.26-7.59l-23.13 34.69-34.68-23.12c-9.179-6.127-21.719-1.313-24.4 9.43l-10.5 41.97-29.1-19.4c-9.179-6.127-21.719-1.313-24.4 9.43l-10.5 41.97-29.1-19.4C13.796 75.301-.992 83.956.052 97.242c.32 4.15 8.23 102.86 38.24 203.34 17.88 59.85 39.98 107.77 65.68 142.45 18.034 24.311 45.676 36.97 73.602 36.97h5.579c30.732 0 56.376-24.405 56.843-55.134.475-31.275-24.826-56.866-55.994-56.866zm-102.86-170.06c-3.28-8.2.71-17.51 8.92-20.8l80-32c8.2-3.28 17.51.71 20.8 8.92 3.28 8.2-.71 17.51-8.92 20.8-86.636 34.654-81.523 33.14-85.94 33.14-6.34 0-12.35-3.8-14.86-10.06zm36.8 72.92c-8.085 3.234-17.472-.619-20.8-8.92-3.28-8.2.71-17.51 8.92-20.8l80-32c8.2-3.28 17.51.71 20.8 8.92 3.28 8.2-.71 17.51-8.92 20.8zm314.06 1.14c-4.179 0 4.086-2.709-111.59 41.78-.141-.234-77.388 29.589-77.257 29.538-5.69 2.189-6.909 9.647-2.262 13.593 48.686 41.344 38.574 118.94-19.023 146.514-4.3 2.059-2.859 8.567 1.908 8.575 13.287.022 26.037-3.221 37.184-9.04l172.53-71.04a15.9 15.9 0 0 0 8.68-3.57c46.758-19.245 44.562-17.829 47.74-21.15 48.334-50.675 12.124-135.2-57.91-135.2zM411.316 187.316c-6.238 6.236-25.779 6.22-32 0-6.248-6.248-16.379-6.249-22.627 0-6.249 6.249-6.248 16.379 0 22.627 6.711 6.711 16.326 11.272 27.313 13.126v16.933c0 8.836 7.163 16 16 16s16-7.164 16-16v-19.296c41.992-14.273 40.789-70.499 2.407-86.397l-24.568-10.177c-12.786-5.295-15.084-28.129 10.848-28.129 7.815 0 13.667 2.354 16 4.687 6.248 6.248 16.379 6.249 22.627 0 6.249-6.249 6.248-16.379 0-22.627-6.711-6.711-16.326-11.272-27.313-13.126V48.002c0-8.836-7.163-16-16-16s-16 7.164-16 16v19.296c-41.628 14.149-41.118 70.363-2.407 86.397l24.568 10.177c9.215 3.817 12.589 15.987 5.152 23.444z" fill="#ffffff" opacity="1" data-original="#000000"></path></g></svg>
          <?php endif; ?> &nbsp;&nbsp;
          <div>
          <div class="company-name"><?=$settings['company_name']?></div>
      <address>
        <?=translate($settings['country'],$langInvoice)['translated']?>,<?=translate($settings['city'],$langInvoice)['translated']?><br>
        <a href="mailto:<?=$settings['email']?>"><?=$settings['email']?></a>
      </address></div>
    </div>
    <div class="inv-meta">
      <div class="inv-number"><?= htmlspecialchars($car['Invoice_number']) ?></div>
      <div class="inv-date"><?= htmlspecialchars($car['Date']) ?> <?= htmlspecialchars($car['Time']) ?></div>
    </div>
  </div>

  <!-- ── CAR INFO STRIP ──────────────────────────────────────── -->
  <div class="car-strip">
    <div>
      <div class="label"><?=translate('Name',$langInvoice)['translated']?></div>
      <div class="value"><?= htmlspecialchars($car['Name']) ?></div>
    </div>
    <div class="sep"></div>
    <div>
      <div class="label"><?=translate('Plate Number',$langInvoice)['translated']?></div>
      <div class="value"><?= htmlspecialchars($car['plate_number']) ?></div>
    </div>
    <?php if (!empty($car['chassis_number'])): ?>
    <div class="sep"></div>
    <div>
      <div class="label"><?=translate('Chassis',$langInvoice)['translated']?></div>
      <div class="value" style="font-family:'JetBrains Mono',monospace;font-size:13px;"><?= htmlspecialchars($car['chassis_number']) ?></div>
    </div>
    <?php endif; ?>
  </div>




  <!-- ── COST SUMMARY ────────────────────────────────────────── -->
  <div class="cost-summary">
    <h3><?=translate('Cost Breakdown & Profit Scenarios',$langInvoice)['translated']?> <sub>(<?=translate('This part is only visible to the seller.',$langInvoice)['translated']?>)</sub></h3>
    <table>
      <?php
      $sum = 0;
      foreach ($car as $key => $value) {
          if (in_array($key, ['Name', 'chassis_number', 'plate_number', 'Date', 'Time', 'Invoice_number'])) continue;
          echo "<tr><td>" . htmlspecialchars(str_replace('_', ' ', $key)) . "</td><td>" . htmlspecialchars($value) . " AED</td></tr>";
          if (is_numeric($value)) $sum += $value;
      }
      ?>
      <tr style="font-weight:700;"><td><?=translate('Total Cost',$langInvoice)['translated']?></td><td><?= number_format($sum, 0) ?> AED</td></tr>
    </table>
    <div class="profit-grid">
      <div class="profit-card">
        <div class="p-label"><?=translate('+5,000 AED profit',$langInvoice)['translated']?></div>
        <div class="p-value"><?= number_format($sum + 5000, 0) ?></div>
      </div>
      <div class="profit-card">
        <div class="p-label"><?=translate('+10,000 AED profit',$langInvoice)['translated']?></div>
        <div class="p-value"><?= number_format($sum + 10000, 0) ?></div>
      </div>
      <div class="profit-card">
        <div class="p-label"><?=translate('+15,000 AED profit',$langInvoice)['translated']?></div>
        <div class="p-value"><?= number_format($sum + 15000, 0) ?></div>
      </div>
    </div>
  </div>
  
  
  <!-- ── BODY ───────────────────────────────────────────────── -->
  <div class="inv-body">
    <table class="inv-table">
      <thead>
        <tr>
          <th>#</th>
          <th><?=translate('Image',$langInvoice)['translated']?></th>
          <th><?=translate('Product',$langInvoice)['translated']?></th>
          <th class="center"><?=translate('Qty',$langInvoice)['translated']?></th>
          <th><?=translate('Unit Price',$langInvoice)['translated']?></th>
          <th><?=translate('Extra Costs',$langInvoice)['translated']?></th>
          <th><?=translate('Total',$langInvoice)['translated']?>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($accessories as $index => $item):
          $item_total = ($item['price'] * $item['quantity']) + $item['installation_cost'] + $item['Paint'];
          $total_price += $item_total;
        ?>
        <tr>
          <td style="color:var(--ink-3);font-size:12px;"><?= $index + 1 ?></td>
          <td>
            <img class="product-img" src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
          </td>
          <td>
            <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
            <div class="product-code">P<?= $item['real_price'] ?>J<?= generateRandomId(3) ?>o<?= $item['installation_cost'] ?><?= generateRandomId(1) ?></div>
          </td>
          <td class="center">
            <span class="qty-badge"><?= $item['quantity'] ?></span>
          </td>
          <td><?= number_format($item['price'], 0) ?> <span style="color:var(--ink-3);font-size:12px;"><?=translate('AED',$langInvoice)['translated']?></span></td>
          <td class="extra-cost">
            <?php if ($item['installation_cost'] != 0): ?>
              <?=translate('Installation',$langInvoice)['translated']?>: <?= number_format($item['installation_cost'], 0) ?> <?=translate('AED',$langInvoice)['translated']?><br>
            <?php endif; ?>
            <?php if ($item['Paint'] != 0): ?>
              <?=translate('Paint',$langInvoice)['translated']?>: <?= number_format($item['Paint'], 0) ?> <?=translate('PaiAEDnt',$langInvoice)['translated']?>
            <?php endif; ?>
            <?php if ($item['installation_cost'] == 0 && $item['Paint'] == 0): ?>
              <span style="color:var(--rule);">—</span>
            <?php endif; ?>
          </td>
          <td class="row-total"><?= number_format($item_total, 0) ?> <span style="color:var(--ink-3);font-size:12px;"><?=translate('AED',$langInvoice)['translated']?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- TOTALS -->
    <div class="totals-wrap">
    
    
      <div class="stamp-wrap">
<svg id="h-o-l-ds" width="250" height="250" style="
    /* position: absolute; */
    rotate: -27deg;
    /* width: 144px; */
    height: 100px;

" viewBox="0 0 250 250" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><circle class="Rang" layer="0" id="Rang0" cx="125" cy="125" r="124" fill="none" stroke="#2f69c2" stroke-width="2"></circle><g id="RangText1" layer="1" font-size="18" font-style="normal" font-family="Arial" fill="#2f69c2" stroke="none" font-weight="normal" in="0" rad="125" cut="122" len="360"><text transform="translate(215.74,181.70) rotate(125.22)"><tspan>A</tspan></text><text transform="translate(190.34,209.73) rotate(145.84)"><tspan>R</tspan></text><text transform="translate(155.83,227.46) rotate(166.47)"><tspan>B</tspan></text><text transform="translate(118.25,231.79) rotate(185.22)"><tspan> </tspan></text><text transform="translate(87.08,225.05) rotate(203.44)"><tspan>4</tspan></text><text transform="translate(56.16,206.91) rotate(223.26)"><tspan>X</tspan></text><text transform="translate(31.96,177.84) rotate(243.09)"><tspan>4</tspan></text><text transform="translate(19.73,144.14) rotate(261.30)"><tspan> </tspan></text><text transform="translate(18.76,112.26) rotate(280.05)"><tspan>A</tspan></text><text transform="translate(29.83,76.09) rotate(299.61)"><tspan>c</tspan></text><text transform="translate(50.60,48.10) rotate(318.35)"><tspan>c</tspan></text><text transform="translate(79.26,28.27) rotate(337.37)"><tspan>e</tspan></text><text transform="translate(113.78,18.59) rotate(356.39)"><tspan>s</tspan></text><text transform="translate(148.57,20.63) rotate(375.13)"><tspan>s</tspan></text><text transform="translate(180.86,33.74) rotate(394.15)"><tspan>o</tspan></text><text transform="translate(207.87,57.31) rotate(412.36)"><tspan>r</tspan></text><text transform="translate(224.13,84.73) rotate(428.97)"><tspan>i</tspan></text><text transform="translate(231.41,113.75) rotate(446.65)"><tspan>e</tspan></text><text transform="translate(229.15,149.53) rotate(465.66)"><tspan>s</tspan></text></g><g class="Img_l" layer="2" id="Img2" x="125.5" y="125.5" s="73.5" al="0" transform="translate(33.75, 33.75) rotate(0 91.25 91.25) scale(0.73) " fill="#2f69c2"><svg xmlns="http://www.w3.org/2000/svg" width="250" height="250" viewBox="0 0 85.812 23.25" id="Img-upsvg2" color="#2f69c2"><path d="M83.513 6.5H69.409L84.124 0H67.812c-2.094 0-3.495.501-3.553.522 0 0-14.217 6.122-14.873 6.429l-1.671-.806 4.576-2.396h-4.912l-2.317 1.117-2.318-1.116h-4.912l4.575 2.396-1.852.893c-.626-.482-1.342-.537-1.355-.538H25.096L39.812 0H23.499c-2.094 0-3.495.501-3.553.522L6.25 6.4 2.366 8.217c-.019.009-2.638 1.191-2.24 4.57l.032.276h23.903v2.562h11.546c.21.02 1.634.081 2.435-1.688h1.269c1.559 0 2.111-1.452 2.186-2.22l.002-.015V9.188a3.7 3.7 0 0 0-.021-.375h1.144l2.439-1.277 1.46.765c-.561.32-2.463 1.696-2.083 4.76h23.936v2.565H79.92c.21.02 1.634.081 2.435-1.688h1.269c1.559 0 2.111-1.452 2.186-2.22l.002-.015V9.188c0-2.515-2.276-2.686-2.299-2.688z"></path><path d="M40.874 9.188v2.482c-.02.164-.231 1.643-1.562 1.643H28.499V10.5H4.606c-.138-.554-.346-2.115 1.311-3.174 0 0 13.856-6.05 14.258-6.223.103-.035 1.418-.479 3.324-.479h13.277l-14.84 6.5h17.237c.057.005.379.04.729.228l-3.028 1.459h3.976c.015.119.024.243.024.377zM85.187 11.67c-.02.164-.231 1.643-1.562 1.643H72.812V10.5H48.919a2.966 2.966 0 0 1 .067-1.688h4.263l-3.142-1.514c.348-.162 13.979-6.022 14.38-6.195.103-.035 1.418-.479 3.324-.479h13.277l-14.84 6.5h17.237c.131.011 1.7.184 1.7 2.062v2.484z" fill="#2f69c2"></path><path fill="#2f69c2" d="M50.976 8.312l-4.368-2.134 3.739-1.928h-2.853l-2.428 1.168-2.437-1.168h-2.854l3.775 1.911-4.404 2.151H42.5l2.549-1.32 2.573 1.32z"></path><path d="M15.875 20.008c.005-.01.224-.362 1.068-.29h4.479l.707-1.28h-6.036c-2.414 0-2.977.97-3.041 1.099l-2.065 3.588h3.087l.969-1.625h4.472l.618-1.094h-4.51l.252-.398z"></path><path d="M19.49 20.781l-.194.344H14.83l-.969 1.625h-2.225l1.753-3.05c.003-.006.449-.888 2.704-.888h5.401l-.294.532h-4.224c-1.053-.068-1.406.432-1.434.484l-.604.953h4.552z" fill="#2f69c2"></path><path d="M22.802 19.537l-2.065 3.588h3.087l.969-1.625h4.472l.618-1.094h-4.51l.253-.398c.005-.01.224-.362 1.068-.29h4.479l.707-1.28h-6.036c-2.415 0-2.978.969-3.042 1.099z"></path><path d="M30.95 19.344h-4.224c-1.053-.068-1.406.432-1.434.484l-.604.953h4.552l-.194.344H24.58l-.969 1.625h-2.225l1.753-3.05c.003-.006.449-.888 2.704-.888h5.401l-.294.532z" fill="#2f69c2"></path><path d="M11.577 19.105c-.238-.422-.831-.637-1.764-.637H4.405c-2.408 0-2.973.938-3.039 1.065L.181 21.528c-.013.022-.35.587-.071 1.084.238.423.831.637 1.763.637h5.409c2.408 0 2.973-.938 3.039-1.065l1.185-1.995c.014-.021.35-.585.071-1.084z"></path><path d="M11.185 19.997l-1.198 2.02c-.003.006-.45.858-2.704.858H1.874c-.774 0-1.271-.153-1.435-.444-.173-.305.062-.705.064-.709l1.198-2.02c.003-.006.45-.858 2.704-.858h5.409c.774 0 1.271.153 1.436.444.172.305-.063.705-.065.709z" fill="#2f69c2"></path><path d="M8.143 19.417H4.895c-.675 0-.865.246-.915.347l-1.06 1.645c-.15.25-.168.454-.07.621.132.227.621.261.73.252h3.248c.679.045.929-.337.94-.353l1.038-1.644c.147-.244.17-.451.068-.616-.175-.278-.622-.261-.731-.252z"></path><path d="M8.484 20.091l-1.032 1.634c-.01.014-.162.212-.612.182l-3.284.001c-.149.013-.352-.018-.389-.076-.011-.019-.011-.089.071-.224l1.079-1.679c.018-.021.134-.137.577-.137l3.272-.001c.149-.013.352.018.388.076.012.018.012.088-.07.224z" fill="#2f69c2"></path><path d="M60.577 19.105c-.238-.422-.831-.637-1.764-.637h-5.408c-2.408 0-2.974.938-3.04 1.065l-1.185 1.995c-.013.022-.35.587-.071 1.084.237.423.831.637 1.764.637h5.408c2.408 0 2.973-.938 3.039-1.065l1.185-1.995c.015-.021.351-.585.072-1.084z"></path><path d="M60.185 19.997l-1.198 2.02c-.003.006-.45.858-2.704.858h-5.408c-.774 0-1.271-.153-1.436-.444-.173-.305.062-.705.064-.709l1.198-2.02c.003-.006.45-.858 2.705-.858h5.408c.774 0 1.271.153 1.436.444.172.305-.063.705-.065.709z" fill="#2f69c2"></path><path d="M57.143 19.417h-3.248c-.675 0-.865.246-.915.347l-1.06 1.645c-.15.25-.168.454-.07.621.132.227.621.261.73.252h3.248c.679.045.929-.337.94-.353l1.038-1.644c.147-.244.17-.451.068-.616-.175-.278-.622-.261-.731-.252z"></path><path d="M57.484 20.091l-1.032 1.634c-.01.014-.162.212-.612.182l-3.284.001c-.149.013-.352-.018-.389-.076-.011-.019-.011-.089.071-.224l1.079-1.679c.018-.021.134-.137.577-.137l3.272-.001c.15-.013.352.018.388.076.012.018.012.088-.07.224z" fill="#2f69c2"></path><path d="M82.077 19.105c-.238-.422-.831-.637-1.764-.637h-5.408c-2.402 0-2.971.933-3.039 1.064l-2.264 3.717h8.18c2.408 0 2.973-.938 3.039-1.065l1.185-1.995c.014-.021.35-.585.071-1.084z"></path><path d="M81.685 19.997l-1.198 2.02c-.003.006-.45.858-2.704.858h-4.705l.351-.594h3.899c.679.045.929-.337.94-.353l1.036-1.64c.149-.248.172-.455.07-.62-.174-.277-.621-.26-.73-.251h-3.248c-.684 0-.87.252-.916.35l-1.837 3.108H70.27l1.93-3.17c.008-.017.461-.861 2.706-.861h5.408c.774 0 1.271.153 1.436.444.172.305-.063.705-.065.709z" fill="#2f69c2"></path><path d="M73.65 21.906l1.167-1.978c.018-.021.134-.137.577-.137h3.264l.008-.001c.15-.013.352.018.388.076.012.018.011.088-.071.224l-1.034 1.637c-.003.005-.154.209-.61.179H73.65z" fill="#2f69c2"></path><path d="M64.38 18.312c-.139-.009-1.796-.059-2.607 1.408l-1.996 3.414h2.905l1.188-1.78h2.616l-.996 1.771h2.93l2.711-4.812H64.38z"></path><path d="M68.202 22.75h-2.07l1.889-3.354c.054-.187-.137-.167-.137-.167H65.84c-.561-.004-1.272.174-1.749.969l-.203.322h3.067l-.258.459h-3.028l-1.187 1.791-2.053-.018 1.669-2.846c.744-1.314 2.2-1.225 2.262-1.22h6.13l-2.288 4.064z" fill="#2f69c2"></path><path d="M67.166 20.146h-2.467c.314-.56.833-.546.858-.543l1.914.002-.305.541z" fill="#2f69c2"></path><path d="M48.021 20.136c.264-.443.304-.818.121-1.123-.392-.653-1.667-.613-1.812-.606h-7.313l-3.101 4.844h3.29l1.938-3.406h3.136c.655-.019.626.147.626.147.003.037-.02.199-.675.197h-2.714l-.682 1.092 3.569 1.971h4.156l-3.001-1.664c1.694-.293 2.427-1.399 2.462-1.452z"></path><path d="M46.626 22.75h-2.094l-2.994-1.653.255-.409h2.424c.107.007.729.024 1.028-.285a.557.557 0 0 0 .157-.399.442.442 0 0 0-.083-.342c-.159-.218-.5-.321-1.042-.317h-3.425l-1.938 3.406h-2.085l2.461-3.844h7.051c.374-.017 1.184.051 1.371.364.08.134.041.337-.117.602-.03.045-.865 1.284-2.879 1.284h-.967l2.877 1.593z" fill="#2f69c2"></path></svg></g><circle class="Rang" layer="3" id="Rang3" cx="125" cy="125" r="100.25" fill="none" stroke="#2f69c2" stroke-width="2"></circle></svg>
    </div>
    
    
    
      <table class="totals-table">
        <tr>
          <td><?=translate('Subtotal',$langInvoice)['translated']?></td>
          <td><?= number_format($total_price, 0) ?> <?=translate('AED',$langInvoice)['translated']?></td>
        </tr>
        <tr class="vat-row">
          <td><?=translate('VAT (5%)',$langInvoice)['translated']?></td>
          <td><?= number_format(ceil($total_price * 0.05), 0) ?> <?=translate('SubAEDtotal',$langInvoice)['translated']?></td>
        </tr>
        <tr class="grand-total">
          <td><?=translate('Total Due',$langInvoice)['translated']?></td>
          <td><?= number_format(ceil($total_price + ($total_price * 0.05)), 0) ?> <?=translate('AED',$langInvoice)['translated']?></td>
        </tr>
      </table>
    </div>
  </div>


  <!-- ── FOOTER ─────────────────────────────────────────────── -->
  <div class="inv-footer">
    <p><?=translate('Thank you for choosing',$langInvoice)['translated']?> <?=$settings['company_name']?>.<?=translate('We look forward to serving you again and keeping your vehicle at its best.',$langInvoice)['translated']?></p>
    <!-- Stamp SVG (same as original, scaled down) -->
  
  </div>

</div><!-- /invoice-card -->
</div><!-- /page-wrap -->

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
