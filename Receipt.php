<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: Login.php');
    exit;
}



include "Static/config.php";






$Total = 0;
$accessories = [];
$Invoice_info = [];

// Load from Car file if provided
if (isset($_GET['Invoice'])) {
    $jsonData    = file_get_contents('Static/ALL_Invoice/' . $_GET['Invoice']);
    $data        = json_decode($jsonData, true);
    $Invoice_info     = $data['Invoice_info'] ?? [];
    $accessories = $data['accessories'] ?? [];
    foreach ($accessories as $item) {
        $Total += ($item['price'] * $item['quantity']) + $item['installation_cost'] + $item['Paint'];
    }
}

// Handle form POST (manual mode)
$postItems = [];
if (!isset($_GET['Invoice']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $defaultData = json_decode(file_get_contents('Default.json'), true);
    if (isset($_POST['items'])) {
        foreach ($_POST['items'] as $index => $item) {
            if (isset($item['selected'])) {
                $unitPrice = $defaultData[$index]['price'] + (float)$item['Paint'] + (float)$item['installation_cost'];
                $qty       = (int)$item['quantity'];
                $rowTotal  = $unitPrice * $qty;
                $Total    += $rowTotal;
                $postItems[] = [
                    'name'       => $defaultData[$index]['name'],
                    'image'      => $defaultData[$index]['image'],
                    'price'      => $unitPrice,
                    'quantity'   => $qty,
                    'row_total'  => $rowTotal,
                ];
            }
        }
    }
}

// Receipt meta
$vat       = $Total * 0.05;
$bankFee   = $Total * 0.07;
$discount  = $bankFee; // discount cancels bank fee
$final     = $Total + $vat;
$tseq      = rand(1000, 9999);
$machineId = 'M' . rand(100, 999);
$approvalCode = rand(1000000, 9999999);
$date = new DateTime($Invoice_info['Date']);
$receiptDate = $date->format('d M Y');
$receiptTime  = $Invoice_info['Time'];

// Default.json for manual form
$defaultData = !isset($_GET['Invoice']) ? json_decode(file_get_contents('Default.json'), true) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Receipt — <?=$settings['System_name']?> (<?=$settings['company_name']?>)</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
         <link rel="stylesheet" href="Static/css/Main.css">
</head>
<body>

<!-- TOPBAR -->
<nav class="topbar" dir="<?= $_SESSION['Direction']=='right' ? "rtl" : "ltl" ?>">

      <button class="tb-btn tb-btn-ghost" onclick="printReceipt()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8" rx="1"/></svg>
        <?=translate('Print',$_SESSION['Lang'])['translated']?>
      </button>
      <button class="tb-btn tb-btn-white" onclick="savePNG()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <?=translate('Save PNG',$_SESSION['Lang'])['translated']?>
      </button>
</nav>

<div class="page-wrap">



  <!-- ── RECEIPT ─────────────────────────────────────────── -->
  <?php
  $showReceipt = isset($_GET['Invoice']) || !empty($postItems);
  $itemsToShow = isset($_GET['Invoice']) ? $accessories : $postItems;
  ?>
  <?php if($showReceipt): ?>
  <div class="receipt-wrap">

    <div class="receipt" id="ticket">
      <div class="receipt-inner">

        <!-- HEAD -->
        <div class="r-head">
          <div class="r-brand"><?=$settings['company_name']?></div>
          <div class="r-sub"><?=$settings['city']?> · <?=$settings['address_line2']?></div>
          <div class="r-addr"><?=$settings['address_line2']?>, <?=$settings['city']?>, <?=$settings['country']?><br><?=$settings['email']?>
        <br><?=$settings['phone']?>
        </div>
        </div>

        <hr class="r-rule">

        <!-- META -->
        <div class="r-meta"><span>Date</span><span><?= $receiptDate ?></span></div>
        <div class="r-meta"><span>Time</span><span><?= $receiptTime ?></span></div>
        <div class="r-meta"><span>Receipt</span><span><?= $Invoice_info['Invoice_number'] ?></span></div>
        <div class="r-meta"><span>TSEQ</span><span><?= $tseq ?></span></div>
        <?php if(!empty($Invoice_info['Name'])): ?>
        <div class="r-meta"><span>Name</span><span><?= htmlspecialchars($Invoice_info['Name']) ?></span></div>
        <?php endif; ?>
        <?php if(!empty($Invoice_info['chassis_number'])): ?>
        <div class="r-meta"><span>number</span><span><?= htmlspecialchars($Invoice_info['chassis_number']) ?></span></div>
        <?php endif; ?>

        <hr class="r-rule-solid">

        <!-- ITEMS -->
        <?php foreach($itemsToShow as $item):
          if(isset($_GET['Invoice'])) {
            $unitPrice = $item['price'] + $item['installation_cost'] + $item['Paint'];
            $qty       = $item['quantity'];
            $rowTotal  = ($item['price'] * $qty) + $item['installation_cost'] + $item['Paint'];
            $name      = $item['name'];
          } else {
            $unitPrice = $item['price'];
            $qty       = $item['quantity'];
            $rowTotal  = $item['row_total'];
            $name      = $item['name'];
          }
        ?>
        <div class="r-item">
          <div class="r-item-row">
            <span class="r-item-name"><?= htmlspecialchars($name) ?></span>
          </div>
          <div class="r-item-row">
            <span class="r-item-qty"><?= $qty ?>x <?= number_format($unitPrice, 0) ?></span>
            <span class="r-item-price"><?= number_format($rowTotal, 0) ?></span>
          </div>
        </div>
        <?php endforeach; ?>

        <hr class="r-rule">

        <!-- TOTALS -->
        <div class="r-total-row muted"><span>Subtotal</span><span><?= number_format($Total, 0) ?> AED</span></div>
        <div class="r-total-row muted"><span>VAT 5%</span><span><?= number_format($vat, 2) ?> AED</span></div>
        <div class="r-total-row muted"><span>Bank Fee 7%</span><span><?= number_format($bankFee, 2) ?> AED</span></div>
        <div class="r-total-row discount"><span>Discount</span><span>-<?= number_format($discount, 2) ?> AED</span></div>

        <hr class="r-rule">

        <div class="r-total-row final"><span>TOTAL</span><span><?= number_format($final, 0) ?> AED</span></div>

        <hr class="r-rule-solid">

        <!-- BARCODE (fake SVG bars) -->
        <div class="r-barcode">
          <?php
          $barcodeStr = str_pad($Invoice_info['Invoice_number'], 12, '0', STR_PAD_LEFT);
          $bars = '';
          $x = 10;
          for ($i = 0; $i < 60; $i++) {
              $w = ($i % 3 === 0) ? 3 : (($i % 5 === 0) ? 2 : 1);
              $h = ($i % 7 === 0) ? 50 : (($i % 4 === 0) ? 40 : 45);
              $bars .= "<rect x='$x' y='0' width='$w' height='$h' fill='#111'/>";
              $x += $w + 1 + ($i % 4 === 0 ? 1 : 0);
          }
          ?>
          <svg width="280" height="52" viewBox="0 0 280 52" xmlns="http://www.w3.org/2000/svg"><?= $bars ?></svg>
          <div class="r-barcode-num"><?= $barcodeStr ?></div>
        </div>

        <!-- FOOTER -->
        <div class="r-footer">
          <div class="r-approval">Approval Code: <?= $Invoice_info['Invoice_number'] ?></div>
          <div class="r-thank">Thank You For<br>Choosing Our Shop</div>
          <div class="r-copy">— CUSTOMER COPY —</div>
        </div>

      </div>
    </div>



  </div>
  <?php endif; ?>

</div><!-- /page-wrap -->

<script>
function toggleAcc(index) {
  const card = document.getElementById('acc-' + index);
  const chk  = document.getElementById('chk-' + index);
  chk.checked = !chk.checked;
  card.classList.toggle('is-selected', chk.checked);
}

function savePNG() {
  const ticket = document.getElementById('ticket');
  html2canvas(ticket, { scale: 3, backgroundColor: '#fff' }).then(canvas => {
    const link = document.createElement('a');
    link.download = 'receipt-<?= date("Ymd") ?>.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
  });
}

function printReceipt() {
  const ticket = document.getElementById('ticket');
  html2canvas(ticket, { scale: 3, backgroundColor: '#fff' }).then(canvas => {
    const imgData = canvas.toDataURL('image/png');
    const win = window.open('', '_blank');
    win.document.write(`
      <html><head><title>Receipt</title>
      <style>
        body { margin: 0; display: flex; justify-content: center; padding: 20px; background: #fff; }
        img { max-width: 100%; height: auto; }
      </style></head>
      <body><img src="${imgData}"><script>window.onload=()=>window.print()<\/script></body>
      </html>`);
    win.document.close();
  });
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