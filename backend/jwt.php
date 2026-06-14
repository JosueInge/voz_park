<?php
// helpers/jwt.php

/**
 * Genera un JWT HS256 firmado con JWT_SECRET.
 */
function jwtEncode(array $payload): string {
    $secret  = $_ENV['JWT_SECRET'] ?? 'vozpark_secret';
    $expiry  = (int)($_ENV['JWT_EXPIRY'] ?? 3600);

    $header  = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['iat'] = time();
    $payload['exp'] = time() + $expiry;
    $body    = base64UrlEncode(json_encode($payload));
    $sig     = base64UrlEncode(hash_hmac('sha256', "{$header}.{$body}", $secret, true));

    return "{$header}.{$body}.{$sig}";
}

/**
 * Decodifica y valida un JWT. Retorna el payload o null si inválido/expirado.
 */
function jwtDecode(string $token): ?array {
    $secret = $_ENV['JWT_SECRET'] ?? 'vozpark_secret';
    $parts  = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $body, $sig] = $parts;
    $expected = base64UrlEncode(hash_hmac('sha256', "{$header}.{$body}", $secret, true));
    if (!hash_equals($expected, $sig)) return null;

    $payload = json_decode(base64UrlDecode($body), true);
    if (!$payload || (isset($payload['exp']) && $payload['exp'] < time())) return null;

    return $payload;
}

function base64UrlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
}