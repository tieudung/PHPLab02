<?php
require __DIR__ . '/lib/bootstrap.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET' && $method !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$rawRef = $_GET['ref'] ?? $_POST['ref'] ?? '';
$ref = is_string($rawRef) ? trim($rawRef) : '';

$index = find_booking_index_by_ref($_SESSION['bookings'], $ref);
$booking = $index !== null ? $_SESSION['bookings'][$index] : null;
$workshopInfo = $booking !== null ? find_workshop_for_booking($booking, $workshops) : null;

$errors = [];
$formData = [
    'full_name' => $booking['full_name'] ?? '',
    'email' => $booking['email'] ?? '',
    'quantity' => $booking['quantity'] ?? 1,
];

if ($method === 'POST') {
    if (!csrf_valid($_POST)) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    if ($booking === null || $workshopInfo === null) {
        http_response_code(404);
    } else {
        $validationInput = [
            'full_name' => $_POST['full_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'workshop_id' => $workshopInfo['id'],
            'quantity' => $_POST['quantity'] ?? null,
        ];

        $result = validate_booking($validationInput, $workshops);
        $errors = $result['errors'];

        $formData = [
            'full_name' => is_string($_POST['full_name'] ?? null) ? $_POST['full_name'] : '',
            'email' => is_string($_POST['email'] ?? null) ? $_POST['email'] : '',
            'quantity' => is_scalar($_POST['quantity'] ?? null) ? (string)$_POST['quantity'] : '',
        ];

        if (!$errors) {
            $clean = $result['data'];
            $recalculatedAmount = $workshopInfo['fee'] * $clean['quantity'];

            $_SESSION['bookings'][$index]['full_name'] = $clean['full_name'];
            $_SESSION['bookings'][$index]['email'] = $clean['email'];
            $_SESSION['bookings'][$index]['quantity'] = $clean['quantity'];
            $_SESSION['bookings'][$index]['workshop_id'] = $workshopInfo['id'];
            $_SESSION['bookings'][$index]['workshop'] = $workshopInfo['title'];
            $_SESSION['bookings'][$index]['amount'] = $recalculatedAmount;

            $_SESSION['flash'] = 'Booking ' . $booking['ref'] . ' updated successfully.';
            redirect_to('dashboard.php');
        }
    }
}

$unitFee = $workshopInfo['fee'] ?? 0;
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Booking</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="<?= e($theme) ?>">
<main class="wrap">
<header>
<h1>Edit Booking</h1>
<nav>
<a href="index.php">Catalog</a>
<a href="dashboard.php">My bookings</a>
</nav>
</header>

<?php if ($booking === null || $workshopInfo === null): ?>
<div class="panel">
<p class="error"><strong>Booking not found.</strong> Cannot load or update a non-existent booking reference.</p>
<div class="action-group">
<a class="btn secondary" href="dashboard.php">Back to My Bookings</a>
</div>
</div>
<?php else: ?>
<form method="post" action="booking_edit.php?ref=<?= urlencode((string)$booking['ref']) ?>" class="panel form-stack" novalidate>
<input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
<input type="hidden" name="ref" value="<?= e($booking['ref']) ?>">

<label>Booking Reference
<input type="text" value="<?= e($booking['ref']) ?>" class="readonly-input" readonly>
</label>

<label>Workshop
<input type="text" value="<?= e($workshopInfo['title']) ?>" class="readonly-input" readonly>
</label>

<label>Full Name
<input name="full_name" value="<?= e($formData['full_name']) ?>" placeholder="Nguyen Van A">
</label>
<small class="error"><?= e($errors['full_name'] ?? '') ?></small>

<label>Email
<input name="email" type="email" value="<?= e($formData['email']) ?>" placeholder="user@example.com">
</label>
<small class="error"><?= e($errors['email'] ?? '') ?></small>

<label>Tickets (1–4)
<input name="quantity" type="number" min="1" max="4" value="<?= e($formData['quantity']) ?>">
</label>
<small class="error"><?= e($errors['quantity'] ?? '') ?></small>

<hr class="divider">
<div class="summary-box">
<p class="current-total"><strong>Current total: <?= number_format((int)$booking['amount']) ?> VND</strong></p>
<p class="muted">Unit fee: <?= number_format((int)$unitFee) ?> VND - Total will be recalculated after saving.</p>
</div>

<div class="action-group">
<button type="submit">Save Changes</button>
<a class="btn secondary" href="booking_detail.php?ref=<?= urlencode((string)$booking['ref']) ?>">Cancel</a>
</div>
</form>

<?php if ($errors): ?>
<p class="muted">Invalid data is not saved to the Session.</p>
<?php else: ?>
<p class="banner-note">The server must validate name, email and ticket quantity, verify the CSRF token and update the correct booking in $_SESSION.</p>
<?php endif; ?>
<?php endif; ?>
</main>
</body>
</html>