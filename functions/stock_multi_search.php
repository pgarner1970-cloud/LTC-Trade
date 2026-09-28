<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

require_once 'db.php';
require_once 'deliveryfunctions.php';

$eans = $_POST['eans'] ?? [];
if (!is_array($eans)) $eans = [$eans];
$eans = array_values(array_unique(array_filter(array_map('strval', $eans), static fn($v) => $v !== '')));

if (!$eans) {
    echo json_encode([]);
    exit;
}

// Keep the request bounded; the public search endpoint remains responsible for finding matching products.
$eans = array_slice($eans, 0, 250);
$placeholders = implode(',', array_fill(0, count($eans), '?'));

try {
    $sql = "SELECT t.EAN, t.Supplier, t.UnitBuyPrice,
                   ROUND(t.UnitBuyPrice + r.rimMarkupTrade, 2) AS UnitTrade
              FROM tbltyredata t
              INNER JOIN tblrim r ON t.Diameter = r.rimDesc
              INNER JOIN tbltyredataupdates u ON u.Supplier = t.Supplier
             WHERE t.EAN IN ($placeholders)
               AND COALESCE(t.UnitBuyPrice, 0) > 0
               AND u.TradeEnabled = 1
               AND u.Active = 1
             ORDER BY t.EAN, UnitTrade ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($eans);

    $out = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $delivery = trade_delivery_estimate($pdo, (string)$row['Supplier']);
        if ($delivery === null) continue;

        $out[] = [
            'EAN' => (string)$row['EAN'],
            // Supplier is returned only to authenticated Trade JS so the existing Buy action can identify stock.
            // It is deliberately never rendered on the customer-facing page.
            'Supplier' => (string)$row['Supplier'],
            'UnitTrade' => number_format((float)$row['UnitTrade'], 2, '.', ''),
            'DeliveryDate' => $delivery['date'],
            'DeliveryLabel' => $delivery['label'],
        ];
    }

    echo json_encode($out);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load supplier offers. Check that the Trade supplier/delivery SQL has been applied.']);
}
