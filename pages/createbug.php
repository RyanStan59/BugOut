<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to submit a bug.");
}

$created_by = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $severity = trim($_POST['severity'] ?? '');

    if (empty($title) || empty($description) || empty($severity)) {
        die("Please fill in all required fields.");
    }

    $status = "Open";

    $sql = "INSERT INTO bugs (title, description, severity, status, created_by, assigned_to, created_at)
            VALUES (?, ?, ?, ?, ?, NULL, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $title, $description, $severity, $status, $created_by);

    if ($stmt->execute()) {
        header("Location: ../pages/mybugs.php");
        exit();
    } else {
        echo "Error creating bug: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>