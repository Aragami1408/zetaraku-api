<?php
require_once __DIR__ . '/config.php';

/** Get a singleton mysqli connection */
function db() {
    static $conn = null;
    if ($conn) return $conn;

    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conn = mysqli_init();
    // Optional: timeouts
    mysqli_options($conn, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    $conn->real_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset($DB_CHARSET);
    $conn->query("SET time_zone = '+00:00'");
    return $conn;
}

/** Prepare statement safely */
function db_prepare($sql) {
    $stmt = db()->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }
    return $stmt;
}

/** Bind params with $types (e.g. 'ssi') and array $params */
function stmt_bind_array(mysqli_stmt $stmt, string $types, array $params) {
    if ($types === '' || empty($params)) return;
    // mysqli::bind_param needs references
    $refs = [];
    foreach ($params as $k => $v) {
        $refs[$k] = &$params[$k];
    }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

/** Run a SELECT and fetch all rows */
function select_all(string $sql, string $types = '', array $params = []) {
    $stmt = db_prepare($sql);
    stmt_bind_array($stmt, $types, $params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/** Run a SELECT and fetch one row (or null) */
function select_one(string $sql, string $types = '', array $params = []) {
    $rows = select_all($sql, $types, $params);
    return $rows[0] ?? null;
}

/** Run INSERT/UPDATE/DELETE; returns ['affected' => n, 'insert_id' => id] */
function exec_write(string $sql, string $types = '', array $params = []) {
    $stmt = db_prepare($sql);
    stmt_bind_array($stmt, $types, $params);
    $stmt->execute();
    $info = [
        'affected'  => $stmt->affected_rows,
        'insert_id' => $stmt->insert_id
    ];
    $stmt->close();
    return $info;
}
?>
