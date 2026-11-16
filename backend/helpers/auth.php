<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/response.php';

function base64UrlEncode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string|false
{
    $remainder = strlen($data) % 4;
    if ($remainder !== 0) {
        $data .= str_repeat('=', 4 - $remainder);
    }

    return base64_decode(strtr($data, '-_', '+/'), true);
}

/**
 * Generate a native HMAC-SHA256 signed bearer token.
 */
function generateAuthToken(int $userId, string $username): string
{
    $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'TOKEN']));
    $now = time();
    $payload = base64UrlEncode(json_encode([
        'sub' => $userId,
        'username' => $username,
        'iat' => $now,
        'exp' => $now + TOKEN_TTL_SECONDS,
    ]));

    $signature = hash_hmac('sha256', "$header.$payload", APP_SECRET, true);

    return "$header.$payload." . base64UrlEncode($signature);
}

/**
 * Verify token signature and expiration. Returns payload array or null.
 *
 * @return array<string, mixed>|null
 */
function verifyAuthToken(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$header, $payload, $signatureB64] = $parts;
    $expectedSig = base64UrlEncode(hash_hmac('sha256', "$header.$payload", APP_SECRET, true));

    if (!hash_equals($expectedSig, $signatureB64)) {
        return null;
    }

    $decodedPayload = base64UrlDecode($payload);
    if ($decodedPayload === false) {
        return null;
    }

    $data = json_decode($decodedPayload, true);
    if (!is_array($data) || !isset($data['sub'], $data['exp'])) {
        return null;
    }

    if ((int) $data['exp'] < time()) {
        return null;
    }

    return $data;
}

/**
 * Extract bearer token from Authorization header.
 */
function getBearerToken(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    if ($header === '' && function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $matches) === 1) {
        return trim($matches[1]);
    }

    return null;
}

/**
 * Require an authenticated user and return their database record.
 *
 * @return array<string, mixed>
 */
function requireAuth(PDO $pdo): array
{
    $token = getBearerToken();
    if ($token === null) {
        jsonResponse(false, 'Autentikasi diperlukan. Token tidak ditemukan.', null, 401);
    }

    $payload = verifyAuthToken($token);
    if ($payload === null) {
        jsonResponse(false, 'Token tidak valid atau sudah kedaluwarsa.', null, 401);
    }

    $stmt = $pdo->prepare('SELECT id, name, username, email, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => (int) $payload['sub']]);
    $user = $stmt->fetch();

    if ($user === false) {
        jsonResponse(false, 'Akun pengguna tidak ditemukan.', null, 401);
    }

    return $user;
}
