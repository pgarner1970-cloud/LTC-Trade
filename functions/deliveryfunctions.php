<?php
/**
 * Supplier delivery calculations for the Trade site.
 * Supplier identity is operational data and must not be exposed to customers.
 */

function trade_delivery_estimate(PDO $pdo, string $supplier, ?DateTimeImmutable $now = null): ?array
{
    $tz = new DateTimeZone('Europe/London');
    $now = $now ? $now->setTimezone($tz) : new DateTimeImmutable('now', $tz);
    $orderDay = (int)$now->format('N');

    $stmt = $pdo->prepare(
        'SELECT r.CutoffTime, r.BeforeCutoffDeliveryDays, r.AfterCutoffDeliveryDays,
                COALESCE(u.ProcessingBufferMinutes, 0) AS ProcessingBufferMinutes
           FROM tblsupplierdeliveryrules r
           JOIN tbltyredataupdates u ON u.Supplier = r.Supplier
          WHERE r.Supplier = ? AND r.OrderDay = ? AND u.Active = 1 AND u.TradeEnabled = 1
          LIMIT 1'
    );
    $stmt->execute([$supplier, $orderDay]);
    $rule = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$rule) return null;

    $cutoff = $rule['CutoffTime'];
    $before = true;
    $effectiveCutoff = null;

    if ($cutoff !== null && $cutoff !== '') {
        $effectiveCutoff = new DateTimeImmutable($now->format('Y-m-d') . ' ' . $cutoff, $tz);
        $buffer = max(0, (int)$rule['ProcessingBufferMinutes']);
        if ($buffer > 0) {
            $effectiveCutoff = $effectiveCutoff->modify('-' . $buffer . ' minutes');
        }
        $before = $now < $effectiveCutoff;
    }

    $days = $before
        ? (int)$rule['BeforeCutoffDeliveryDays']
        : (($rule['AfterCutoffDeliveryDays'] === null) ? null : (int)$rule['AfterCutoffDeliveryDays']);

    if ($days === null) return null;

    $deliveryDate = $now->setTime(0, 0)->modify('+' . $days . ' days');
    $today = $now->setTime(0, 0);
    $tomorrow = $today->modify('+1 day');

    if ($deliveryDate == $today) {
        $label = 'Today';
    } elseif ($deliveryDate == $tomorrow) {
        $label = 'Tomorrow';
    } else {
        $label = $deliveryDate->format('D j M');
    }

    return [
        'date' => $deliveryDate->format('Y-m-d'),
        'label' => $label,
        'effective_cutoff' => $effectiveCutoff ? $effectiveCutoff->format('H:i') : null,
    ];
}
