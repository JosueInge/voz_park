<?php
// helpers/response.php

/**
 * Envía una respuesta JSON y termina la ejecución.
 */
function jsonResponse(mixed $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Respuesta de éxito estandarizada.
 */
function success(mixed $data = null, string $message = 'OK', int $status = 200): void {
    $body = ['success' => true, 'message' => $message];
    if ($data !== null) $body['data'] = $data;
    jsonResponse($body, $status);
}

/**
 * Respuesta de error estandarizada.
 */
function error(string $message = 'Error al procesar la solicitud', int $status = 400): void {
    jsonResponse(['success' => false, 'message' => $message], $status);
}