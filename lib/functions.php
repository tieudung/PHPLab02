<?php
declare(strict_types=1);

function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function normalize_text(string $text): string {
    $text = strip_tags(trim($text));
    return preg_replace('/\s+/u', ' ', $text) ?? $text;
}

function validate_booking(array $input, array $workshops): array {
    $errors = [];
    $name = normalize_text((string)($input['full_name'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));
    $workshopId = (string)($input['workshop_id'] ?? '');
    $qtyRaw = $input['quantity'] ?? null;
    $qty = filter_var($qtyRaw, FILTER_VALIDATE_INT);

    if ($name === '') {
        $errors['full_name'] = 'Full name is required.';
    } elseif (strlen($name) < 2 || strlen($name) > 120) {
        $errors['full_name'] = 'Name must have 2–120 bytes.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Invalid email format.';
    }

    if (!isset($workshops[$workshopId])) {
        $errors['workshop_id'] = 'Please select an existing workshop.';
    }

    if ($qty === false || $qty < 1 || $qty > 4) {
        $errors['quantity'] = 'Quantity must be an integer from 1 to 4.';
    } elseif (isset($workshops[$workshopId]) && $qty > $workshops[$workshopId]['seats']) {
        $errors['quantity'] = 'Not enough seats for this booking.';
    }

    return [
        'errors' => $errors,
        'data' => [
            'full_name' => $name,
            'email' => $email,
            'workshop_id' => $workshopId,
            'quantity' => $qty === false ? 0 : $qty
        ]
    ];
}

function find_booking_index_by_ref(array $bookings, string $ref): ?int {
    if ($ref === '') {
        return null;
    }
    foreach ($bookings as $index => $booking) {
        if (isset($booking['ref']) && hash_equals((string)$booking['ref'], $ref)) {
            return (int)$index;
        }
    }
    return null;
}

function find_workshop_for_booking(array $booking, array $workshops): ?array {
    $workshopId = (string)($booking['workshop_id'] ?? '');
    if ($workshopId !== '' && isset($workshops[$workshopId])) {
        return $workshops[$workshopId];
    }
    $workshopTitle = (string)($booking['workshop'] ?? '');
    foreach ($workshops as $w) {
        if ($w['title'] === $workshopTitle) {
            return $w;
        }
    }
    return null;
}

function redirect_to(string $path): never {
    header('Location: ' . $path, true, 303);
    exit;
}

function csrf_valid(array $post): bool {
    return is_string($post['csrf_token'] ?? null)
        && hash_equals($_SESSION['csrf_token'] ?? '', $post['csrf_token']);
}