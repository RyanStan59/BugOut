<?php

/**
 * Database connection for BugOut.
 *
 * Expected environment variables:
 * - BUGOUT_DB_HOST
 * - BUGOUT_DB_NAME
 * - BUGOUT_DB_USER
 * - BUGOUT_DB_PASS
 */

$host = getenv("BUGOUT_DB_HOST") ?: "";
$dbname = getenv("BUGOUT_DB_NAME") ?: "";
$username = getenv("BUGOUT_DB_USER") ?: "";
$password = getenv("BUGOUT_DB_PASS") ?: "";

// Expose a consistent shape for callers.
$conn = null;
$db_error = null;

if ($host === "" || $dbname === "" || $username === "") {
    $db_error = "Missing DB configuration. Set BUGOUT_DB_HOST/BUGOUT_DB_NAME/BUGOUT_DB_USER/BUGOUT_DB_PASS.";
    return;
}

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    $db_error = "Database connection failed: " . $conn->connect_error;
    return;
}

$conn->set_charset("utf8mb4");
?>