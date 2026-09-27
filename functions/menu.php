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
<nav class="navbar navbar-expand-sm bg-primary navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">LTC Tyres</a>
    <ul class="navbar-nav me-auto">
      <li class="nav-item">
        <a class="nav-link" href="stock.php">Tyre Search</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="basket.php">
          Basket
          <?php if ($basketQty > 0): ?>
            <span class="badge bg-light text-dark ms-1"><?php echo htmlspecialchars((string)$basketQty, ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="ms-2" style="opacity:.9;">£<?php echo htmlspecialchars($basketTotalText, ENT_QUOTES, 'UTF-8'); ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">Logout</a>
      </li>
    </ul>
  </div>
</nav>
