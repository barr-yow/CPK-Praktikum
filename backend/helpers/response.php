<?php

declare(strict_types=1);

/**
 * Set CORS and JSON response headers, and handle preflight OPTIONS requests.
 */
function handleCors(): void
{
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

/**
 * Send a standardized JSON response and terminate execution.
 */
function jsonResponse(bool $success, string $message, mixed $data = null, int $statusCode = 200): never
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Ensure the incoming HTTP request uses one of the allowed methods.
 *
 * @param string|array<int, string> $allowedMethods
 */
function requireMethod(string|array $allowedMethods): void
{
    $allowed = is_array($allowedMethods) ? $allowedMethods : [$allowedMethods];
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');

    if (!in_array($method, $allowed, true)) {
        jsonResponse(false, 'Method tidak diizinkan.', null, 405);
    }
}

/**
 * Parse and return JSON request body as an associative array.
 *
 * @return array<string, mixed>
 */
function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        jsonResponse(false, 'Format JSON pada request body tidak valid.', null, 400);
    }

    return $decoded;
}
