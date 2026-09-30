<?php
// Trade system top navigation with basket summary
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$basketQty = 0;
$basketSub = 0.0;
$basketTotal = 0.0;

try {
  require_once __DIR__ . '/db.php';
  $stmt = $pdo->prepare("
    SELECT
      COALESCE(SUM(Quantity), 0) AS qty,
      COALESCE(SUM(UnitSellPrice * Quantity), 0) AS subtotal
    FROM tblbasket
    WHERE basket_id = ?
  ");
  $stmt->execute([session_id()]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($row) {
    $basketQty = (int)$row['qty'];
    $basketSub = (float)$row['subtotal'];
    $basketTotal = $basketSub * 1.2; // incl VAT (20%)
  }
} catch (Throwable $e) {
  // keep defaults
}

$basketTotalText = number_format($basketTotal, 2);
?>
<style>
.ltc-nav{position:sticky;top:0;z-index:1030;background:#0965d9;color:#fff;box-shadow:0 2px 8px rgba(16,42,91,.12)}
.ltc-nav *{box-sizing:border-box}
.ltc-nav-inner{min-height:58px;display:flex;align-items:center;gap:1.1rem;padding:0 1rem;max-width:1600px;margin:auto}
.ltc-brand{font-size:1.35rem;font-weight:700;letter-spacing:.01em;white-space:nowrap;color:#fff!important;text-decoration:none}
.ltc-nav-links{display:flex;align-items:center;gap:.25rem;margin-left:auto}
.ltc-nav a{color:#fff;text-decoration:none}
.ltc-nav-links a{padding:.55rem .8rem;border-radius:.45rem;white-space:nowrap}
.ltc-nav-links a:hover,.ltc-nav-links a[aria-current="page"]{background:rgba(255,255,255,.17)}
.ltc-basket{display:inline-flex;align-items:center;gap:.4rem;border:1px solid rgba(255,255,255,.65);border-radius:.55rem;padding:.45rem .65rem;white-space:nowrap;font-weight:600}
.ltc-basket:hover{background:rgba(255,255,255,.13)}
.ltc-basket svg,.ltc-menu-icon svg{width:22px;height:22px;display:block}
.ltc-count{background:#fff;color:#0759bc;border-radius:999px;min-width:1.45rem;height:1.45rem;padding:0 .3rem;display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700}
.ltc-mobile-menu{display:none;position:relative}
.ltc-mobile-menu summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.65);border-radius:.5rem;width:43px;height:40px}
.ltc-mobile-menu summary::-webkit-details-marker{display:none}
.ltc-mobile-menu summary:focus-visible,.ltc-nav a:focus-visible{outline:3px solid #fff;outline-offset:2px}
.ltc-mobile-links{position:absolute;right:0;top:calc(100% + 9px);min-width:195px;background:#fff;border:1px solid #dbe2ec;border-radius:.6rem;box-shadow:0 8px 22px rgba(0,0,0,.16);padding:.4rem;display:flex;flex-direction:column}
.ltc-mobile-links a{color:#142b47;padding:.7rem .8rem;border-radius:.35rem}
.ltc-mobile-links a:hover,.ltc-mobile-links a[aria-current="page"]{background:#eaf2ff}
@media(max-width:767.98px){
 .ltc-nav-inner{min-height:56px;gap:.65rem;padding:0 .75rem}
 .ltc-brand{font-size:1.22rem;margin-right:auto}
 .ltc-nav-links{display:none}
 .ltc-mobile-menu{display:block}
 .ltc-basket{padding:.42rem .55rem}
}
@media(max-width:355px){.ltc-brand{font-size:1.05rem}.ltc-nav-inner{gap:.4rem;padding:0 .5rem}}
</style>
<?php
$ltcPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
$ltcNavCurrent = static function ($pages) use ($ltcPage) {
    return in_array($ltcPage, $pages, true) ? ' aria-current="page"' : '';
};
?>
<nav class="ltc-nav" aria-label="Trade navigation">
  <div class="ltc-nav-inner">
    <a class="ltc-brand" href="index.php">LTC Tyres</a>
    <div class="ltc-nav-links">
      <a href="stock-multi.php"<?php echo $ltcNavCurrent(['stock-multi.php','stock.php'], $ltcPage); ?>>Tyre Search</a>
      <a href="index.php"<?php echo $ltcNavCurrent(['index.php'], $ltcPage); ?>>Orders</a>
      <a href="logout.php">Logout</a>
    </div>
    <a class="ltc-basket" href="basket.php" aria-label="Basket, <?php echo (int)$basketQty; ?> tyres"<?php echo $ltcNavCurrent(['basket.php','checkout.php'], $ltcPage); ?>>
      <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
      <span class="d-none d-md-inline">Basket</span>
      <span class="ltc-count"><?php echo (int)$basketQty; ?></span>
    </a>
    <details class="ltc-mobile-menu">
      <summary aria-label="Open navigation menu" title="Menu">
        <span class="ltc-menu-icon"><svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg></span>
      </summary>
      <div class="ltc-mobile-links">
        <a href="stock-multi.php"<?php echo $ltcNavCurrent(['stock-multi.php','stock.php'], $ltcPage); ?>>Tyre Search</a>
        <a href="index.php"<?php echo $ltcNavCurrent(['index.php'], $ltcPage); ?>>Orders</a>
        <a href="logout.php">Logout</a>
      </div>
    </details>
  </div>
</nav>
