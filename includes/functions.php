<?php
// Admin credentials — hardcoded (school project)
define('ADMIN_ID',       'admin');
define('ADMIN_PASSWORD', 'admin123');

// Session helpers
function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('salon_sess');
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['pelanggan_id']);
}

function isAdmin(): bool {
    startSession();
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireLogin(): void {
    if (!isLoggedIn() && !isAdmin()) {
        header('Location: /log-masuk.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: /log-masuk.php');
        exit;
    }
}

function logout(): void {
    startSession();
    session_unset();
    session_destroy();
    header('Location: /index.php');
    exit;
}

// Sanitize input
function clean(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// CSRF token (simple)
function csrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    startSession();
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('Ralat keselamatan: token tidak sah.');
    }
}

// Flash messages
function flashMessage(string $type, string $msg): void {
    startSession();
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    startSession();
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// Time slots
function getTimeSlots(): array {
    return ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
}

// Get booked slots for a date (excluding one optional id)
function getBookedSlots(PDO $pdo, string $tarikh, int $excludeId = 0): array {
    $stmt = $pdo->prepare("SELECT masa FROM JANJITEMU WHERE tarikh = ? AND id != ?");
    $stmt->execute([$tarikh, $excludeId]);
    return array_column($stmt->fetchAll(), 'masa');
}
