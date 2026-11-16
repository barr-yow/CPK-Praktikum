<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/auth.php';

handleCors();
requireMethod('POST');

$input = getJsonInput();
$identifier = trim((string) ($input['identifier'] ?? $input['email'] ?? $input['username'] ?? ''));
$password = (string) ($input['password'] ?? '');

if ($identifier === '' || $password === '') {
    jsonResponse(false, 'Email/username dan password wajib diisi.', null, 422);
}

$pdo = getDbConnection();

$stmt = $pdo->prepare(
    'SELECT id, name, username, email, password_hash FROM users WHERE email = :email OR username = :username LIMIT 1'
);
$stmt->execute([
    ':email' => strtolower($identifier),
    ':username' => $identifier,
]);
$user = $stmt->fetch();

if ($user === false || !password_verify($password, (string) $user['password_hash'])) {
    jsonResponse(false, 'Email/username atau password salah.', null, 401);
}

$token = generateAuthToken((int) $user['id'], (string) $user['username']);

jsonResponse(true, 'Login berhasil.', [
    'token' => $token,
    'user' => [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'username' => $user['username'],
        'email' => $user['email'],
    ],
], 200);
