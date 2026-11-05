<?php
require __DIR__ . '/lib/bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

$rawRef = $_GET['ref'] ?? '';
$ref = is_string($rawRef) ? trim($rawRef) : '';

$index = find_booking_index_by_ref($_SESSION['bookings'], $ref);
$booking = $index !== null ? $_SESSION['bookings'][$index] : null;
$workshopInfo = $booking !== null ? find_workshop_for_booking($booking, $workshops) : null;

$unitFee = $workshopInfo['fee'] ?? (
    ($booking !== null && (int)$booking['quantity'] > 0)
        ? (int)round($booking['amount'] / $booking['quantity'])
        : 0
);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Booking Details</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
<header>
<h1>Booking Details</h1>
<nav>
<a href="index.php">Catalog</a>
<a href="dashboard.php">My bookings</a>
</nav>
</header>

<?php if ($booking === null): ?>
<div class="panel">
<p class="error"><strong>Booking not found.</strong> The reference code you requested does not exist in the current session.</p>
<div class="action-group">
<a class="btn secondary" href="dashboard.php">Back to My Bookings</a>
</div>
</div>
<?php else: ?>
<div class="panel detail-card">
<div class="detail-row">
<span class="detail-label">Booking Reference</span>
<span class="detail-value"><span class="ref-badge"><?= e($booking['ref']) ?></span></span>
</div>
<div class="detail-row">
<span class="detail-label">Attendee</span>
<span class="detail-value"><?= e($booking['full_name']) ?></span>
</div>
<div class="detail-row">
<span class="detail-label">Email</span>
<span class="detail-value"><?= e($booking['email']) ?></span>
</div>
<div class="detail-row">
<span class="detail-label">Workshop</span>
<span class="detail-value"><?= e($booking['workshop']) ?></span>
</div>
<div class="detail-row">
<span class="detail-label">Quantity</span>
<span class="detail-value"><?= e($booking['quantity']) ?> tickets</span>
</div>
<div class="detail-row">
<span class="detail-label">Unit Fee</span>
<span class="detail-value"><?= number_format((int)$unitFee) ?> VND</span>
</div>
<div class="detail-row">
<span class="detail-label">Total Amount</span>
<span class="detail-value"><strong><?= number_format((int)$booking['amount']) ?> VND</strong></span>
</div>
<hr class="divider">
<div class="action-group">
<a class="btn warning" href="booking_edit.php?ref=<?= urlencode((string)$booking['ref']) ?>">Edit this booking</a>
<a class="btn secondary" href="dashboard.php">Back to My Bookings</a>
</div>
</div>
<p class="muted">Booking information is read from the current PHP session.</p>
<?php endif; ?>
</main>
</body>
</html>