<?php
header("Content-Type: application/json");
session_start();

require_once __DIR__ . "/db.php";

if (!isset($conn)) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection variable not found."
    ]);
    exit;
}

if ($conn === null) {
    echo json_encode([
        "status" => "error",
        "message" => $db_error ?? "Database is not configured."
    ]);
    exit;
}

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");

if ($email === "" || $password === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Missing email or password."
    ]);
    exit;
}

$sql = "SELECT user_id, full_name, email, password, role FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL prepare failed: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "SQL execute failed: " . $stmt->error
    ]);
    exit;
}

$stmt->store_result();

if ($stmt->num_rows !== 1) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found."
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->bind_result($user_id, $full_name, $db_email, $db_password, $role);
$stmt->fetch();

if ($password !== $db_password) {
    echo json_encode([
        "status" => "error",
        "message" => "Wrong password."
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$_SESSION["user_id"] = $user_id;
$_SESSION["full_name"] = $full_name;
$_SESSION["email"] = $db_email;
$_SESSION["role"] = $role;

echo json_encode([
    "status" => "success",
    "message" => "Login successful.",
    "role" => $role,
    "user_id" => $user_id,
    "full_name" => $full_name
]);

$stmt->close();
$conn->close();
exit;
?>