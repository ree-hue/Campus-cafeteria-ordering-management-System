<?php

function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateImagePath($itemName) {
    $imageName = strtolower(str_replace([' ', '-'], '_', preg_replace('/[^a-zA-Z0-9\s-]/', '', $itemName)));
    $extensions = ['jpeg', 'jpg', 'png', 'gif'];
    foreach ($extensions as $ext) {
        $path = 'image/' . $imageName . '.' . $ext;
        if (file_exists($path)) {
            return $path;
        }
    }
    return 'image/Cafeteria.jpeg';
}

function getStatusClass($status) {
    return match(strtolower($status)) {
        'pending' => 'pending',
        'paid' => 'paid',
        'preparing' => 'preparing',
        'ready' => 'ready',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        default => 'pending'
    };
}

function formatCurrency($amount) {
    return 'Ksh ' . number_format((float)$amount, 2);
}

function timeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) return $diff . ' seconds ago';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}

function isValidKenyanPhone($phone) {
    return preg_match('/^(?:254|0)[17][0-9]{8}$/', $phone);
}
?>
