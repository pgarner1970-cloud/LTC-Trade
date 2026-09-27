<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Db\Db;

require_once __DIR__ . '/email_template.php';

/**
 * Build and send an order email.
 * Kept compatible with legacy usage: orderemail($orderId).
 * Optionally accepts a recipient email (trade user email) as second param.
 */
function orderemail($orderId, $toEmail = null) {
  $pdo = Db::pdo();

  $stmt = $pdo->prepare("SELECT * FROM tblOrderHdr WHERE order_id = :id LIMIT 1");
  $stmt->execute([':id' => (int)$orderId]);
  $hdr = $stmt->fetch();
  if (!$hdr) return false;

  $stmt2 = $pdo->prepare("SELECT Supplier, EAN, TyreDesc, Quantity, UnitSellPrice,
                                 (COALESCE(Quantity,0) * COALESCE(UnitSellPrice,0)) AS line_total
                          FROM tblOrderLines
                          WHERE order_id = :id
                          ORDER BY Supplier, EAN");
  $stmt2->execute([':id' => (int)$orderId]);
  $lines = $stmt2->fetchAll();

  $html = "<h3>New Order</h3>";
  $html .= "<table cellspacing='2' cellpadding='6' border='0' style='border-collapse:collapse; width:100%'>";
  $html .= "<thead><tr style='background:#ddd;font-weight:bold;'>
              <td>Order</td><td>Date</td><td>Ordered by</td><td>Name</td><td>Reference</td>
              <td>Quantity</td><td>Total Price (excl.VAT)</td><td>Status</td>
            </tr></thead>";
  $html .= "<tbody>";
  $html .= "<tr style='border-top:2px solid #ddd;'>
              <td>{$hdr['order_id']}</td>
              <td>{$hdr['order_date']}</td>
              <td>{$hdr['order_user']}</td>
              <td>{$hdr['order_name']}</td>
              <td>{$hdr['order_ref']}</td>
              <td>{$hdr['order_quantity']}</td>
              <td>{$hdr['order_value']}</td>
              <td>{$hdr['order_processed']}</td>
            </tr>";
  if (!empty($hdr['order_notes'])) {
    $notes = nl2br(htmlspecialchars($hdr['order_notes']));
    $html .= "<tr><td colspan='8'><b>Notes:</b><br>{$notes}</td></tr>";
  }
  $html .= "</tbody></table>";

  $html .= "<br><table cellspacing='2' cellpadding='6' border='0' style='border-collapse:collapse; width:100%'>";
  $html .= "<thead><tr style='background:#f2f2f2;font-weight:bold;'>
              <td>Supplier</td><td>EAN</td><td>Description</td><td>Unit Price</td><td>Qty</td><td>Line Value</td>
            </tr></thead><tbody>";

  foreach ($lines as $ln) {
    $desc = htmlspecialchars((string)$ln['TyreDesc']);
    $html .= "<tr style='border-top:1px solid #eee;'>
                <td>{$ln['Supplier']}</td>
                <td>{$ln['EAN']}</td>
                <td>{$desc}</td>
                <td>{$ln['UnitSellPrice']}</td>
                <td>{$ln['Quantity']}</td>
                <td>{$ln['line_total']}</td>
              </tr>";
  }
  $html .= "</tbody></table>";

  $subject = "Order {$hdr['order_id']} - {$hdr['order_ref']}";
  $to = $toEmail ?: (string)($hdr['order_user'] ?? '');
  if (!$to) {
    // fallback: try to email the trade user from tblusers.email
    $u = $pdo->prepare("SELECT email FROM tblusers WHERE username = :u LIMIT 1");
    $u->execute([':u' => (string)$hdr['order_user']]);
    $to = (string)($u->fetchColumn() ?: '');
  }
  if (!$to) return false;

  // Wrap with standard header (logo) if available
  if (function_exists('email_wrap_html')) {
    $html = email_wrap_html($html, 'Order confirmation');
  }

  $headers = function_exists('email_headers') ? email_headers('no-reply@tyres4sale.com') : ("MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: no-reply@tyres4sale.com\r\n");

  return @mail($to, $subject, $html, $headers);
}
