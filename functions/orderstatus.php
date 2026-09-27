<?php
require __DIR__ . '/../app/bootstrap.php';

use App\Db\Db;

header('Content-Type: text/html; charset=utf-8');

$type = isset($_GET['type']) ? (string)$_GET['type'] : '*';

$pdo = Db::pdo();

if ($type === '*' || $type === '') {
  $stmt = $pdo->query("SELECT * FROM tblOrderHdr ORDER BY order_id DESC LIMIT 100");
} else {
  $stmt = $pdo->prepare("SELECT * FROM tblOrderHdr WHERE order_processed = :t ORDER BY order_id DESC LIMIT 100");
  $stmt->execute([':t' => $type]);
}
$rows = $stmt->fetchAll();

$resultsHtml = "<div>";
$resultsHtml .= "<button type='button' class='btn btn-primary openorders'>Open Orders</button>&nbsp;";
$resultsHtml .= "<button type='button' class='btn btn-warning inprogress'>In-progress Orders</button>&nbsp;";
$resultsHtml .= "<button type='button' class='btn btn-secondary complete'>Complete Orders</button>&nbsp;";
$resultsHtml .= "</div><div>&nbsp;</div>";

$resultsHtml .= "<table cellspacing='2' cellpadding='2' border='0' class='w-100'>";
$resultsHtml .= "<thead><tr bgcolor='#ddd' style='font-weight:bold;'>
  <td>Order</td><td>Date</td><td>Ordered by</td><td>Name</td><td>Reference</td>
  <td>Quantity</td><td>Total Price (excl.VAT)</td><td>Status</td><td>Action</td>
</tr></thead><tbody>";

foreach ($rows as $row) {
  $oid = (int)$row['order_id'];
  $resultsHtml .= "<tr style='border-top:2px solid #ddd;'>";
  $resultsHtml .= "<td>{$oid}</td>";
  $resultsHtml .= "<td>{$row['order_date']}</td>";
  $resultsHtml .= "<td>{$row['order_user']}</td>";
  $resultsHtml .= "<td>{$row['order_name']}</td>";
  $resultsHtml .= "<td>{$row['order_ref']}</td>";
  $resultsHtml .= "<td>{$row['order_quantity']}</td>";
  $resultsHtml .= "<td>{$row['order_value']}</td>";
  $resultsHtml .= "<td>{$row['order_processed']}</td>";
  $resultsHtml .= "<td>
      <button type='button' name='view' id='{$oid}' class='btn btn-success btn-sm view'>View</button>&nbsp;
      <button type='button' name='update' id='{$oid}' class='btn btn-info btn-sm update'>Update</button>
    </td>";
  $resultsHtml .= "</tr>";

  // hidden detail row placeholder (JS can fill using orderfetchsingle.php)
  $resultsHtml .= "<tr id='orderLines{$oid}' style='display:none;background:#fafafa;'>
      <td colspan='9'>
        <div class='orderLines' data-order-id='{$oid}'>Loading...</div>
      </td>
    </tr>";
}

$resultsHtml .= "</tbody></table>";

echo $resultsHtml;
