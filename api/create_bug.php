<?php
session_start();
require_once __DIR__ . "/db.php";

if (!isset($conn)) {
    http_response_code(500);
    die("Database connection variable not found.");
}

if ($conn === null) {
    http_response_code(500);
    die($db_error ?? "Database is not configured.");
}

if ($conn->connect_error) {
    http_response_code(500);
    die("Database connection failed: " . $conn->connect_error);
}

$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$severity = trim($_POST["severity"] ?? "");

$severity_map = [
    "Low" => 1,
    "Medium" => 2,
    "High" => 3,
    "Critical" => 4,
];

$severity_value = $severity_map[$severity] ?? null;

// Assume user is logged in
$created_by = $_SESSION["user_id"] ?? 1; // fallback if session not set

if ($title === "" || $description === "" || $severity_value === null) {
    die("Please fill in all fields.");
}

// default values
$status = "Open";
$assigned_to = NULL;

$sql = "INSERT INTO bugs (title, description, severity, status, created_by, assigned_to)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL prepare failed: " . $conn->error);
}

$stmt->bind_param("ssisii", $title, $description, $severity_value, $status, $created_by, $assigned_to);

if ($stmt->execute()) {
    header("Location: ../pages/mybugs.php");
    exit;
} else {
    die("Error saving bug: " . $stmt->error);
}
?>