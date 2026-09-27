<?php

function displayCounter() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/db.php';

    try {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(Quantity), 0) AS counter FROM tblbasket WHERE basket_id = ?");
        $stmt->execute([session_id()]);
        $counter = (int)$stmt->fetchColumn();
    } catch (Throwable $e) {
        $counter = 0;
    }

    if ($counter > 0) {
        echo "<p class='noteBlue'>You have $counter tyres in your basket - <a href='basket.php'>View basket</a></p>";
    }
};

function displayOrderThanks() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION["lastid"])) {
        return;
    }

    $orderId = (int)$_SESSION["lastid"];
    unset($_SESSION['lastid']);

    // A nicer confirmation banner with a close button.
    // Works best if Bootstrap 5 is loaded (btn-close + alert-dismissible).
    echo "
    <div class='alert alert-success alert-dismissible fade show mt-3' role='alert' id='order-confirmation'>
      <div style='display:flex; gap:12px; align-items:flex-start;'>
        <div style='font-size:22px; line-height:1;'>✅</div>
        <div>
          <div style='font-weight:700; font-size:16px;'>Order submitted successfully</div>
          <div style='margin-top:2px;'>
            Thanks, we’ve received your order.
            <strong>Order number:</strong> {$orderId}
          </div>
          <div style='margin-top:4px; font-size:13px; opacity:0.85;'>
            A confirmation email has been sent. You can review the order below.
          </div>
        </div>
      </div>

      <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>
    ";
}

