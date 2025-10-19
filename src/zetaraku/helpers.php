<?php
function allow_cors() {
    // Adjust origins as needed
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function send_json($data, $code = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json_body() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if ($raw !== '' && json_last_error() !== JSON_ERROR_NONE) {
        send_json(['error' => 'Invalid JSON body'], 400);
    }
    return $data ?: [];
}

/** Parse path into segments: /songs/123/sheets -> ['songs','123','sheets'] */
function path_segments() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';
    // If the app is NOT at site root, trim your base path here
    $segments = array_values(array_filter(explode('/', $path), 'strlen'));
    return array_slice($segments, 2);
}

function get_query($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

function validate_int($val, $name='id') {
    if (!preg_match('/^\d+$/', (string)$val)) {
        send_json(['error' => "Invalid $name"], 400);
    }
    return (int)$val;
}
?>
