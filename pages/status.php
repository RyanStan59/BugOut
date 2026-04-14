<?php
session_start();
include("../api/db.php");

$statusData = null;
$message = "";

if (isset($_GET['bug_id']) && !empty(trim($_GET['bug_id']))) {
    $bug_id = trim($_GET['bug_id']);

    $sql = "SELECT bug_id, title, status, severity, assigned_to, created_at
            FROM bugs
            WHERE bug_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bug_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $statusData = $result->fetch_assoc();
    } else {
        $message = "No ticket found with that Bug ID.";
    }

    $stmt->close();
}

function getStatusClass($status) {
    $status = strtolower(trim($status));

    if ($status === "open") return "status-open";
    if ($status === "in progress") return "status-progress";
    if ($status === "resolved") return "status-resolved";
    if ($status === "closed") return "status-closed";

    return "status-default";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ticket Status - BugOut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="ticketstatus-page">
  <div class="ticketstatus-grid">

    <a href="user.html" class="ticketstatus-card ticketstatus-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <div class="ticketstatus-card ticketstatus-title-card">
      <h2>Ticket Status</h2>
    </div>

    <div class="ticketstatus-card ticketstatus-search-card">
      <form action="ticketstatus.php" method="GET" class="ticket-search-form">
        <label for="bug_id">Search Ticket Number:</label>
        <input
          type="text"
          name="bug_id"
          id="bug_id"
          placeholder="Ticket Number"
          value="<?php echo isset($_GET['bug_id']) ? htmlspecialchars($_GET['bug_id']) : ''; ?>"
          required
        >
        <button type="submit" class="search-btn">Search</button>
      </form>
    </div>

    <div class="ticketstatus-card ticketstatus-side-card">
      <h2>Status</h2>
      <img src="../assets/bug-icon.png" alt="Bug Icon" class="status-bug-img">
    </div>

    <div class="ticketstatus-card ticketstatus-details-card">
      <?php if ($statusData): ?>
        <div class="ticketstatus-details">
          <h2 class="ticketstatus-details-heading">Ticket Details</h2>

          <p><strong>Ticket Number:</strong> <?php echo htmlspecialchars($statusData['bug_id']); ?></p>
          <p><strong>Title:</strong> <?php echo htmlspecialchars($statusData['title']); ?></p>
          <p><strong>Severity:</strong> <?php echo htmlspecialchars($statusData['severity']); ?></p>
          <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($statusData['assigned_to']); ?></p>
          <p><strong>Created At:</strong> <?php echo htmlspecialchars($statusData['created_at']); ?></p>

          <div class="status-row">
            <span class="status-label">Current Status:</span>
            <span class="status-badge <?php echo getStatusClass($statusData['status']); ?>">
              <?php echo htmlspecialchars($statusData['status']); ?>
            </span>
          </div>
        </div>
      <?php elseif (!empty($message)): ?>
        <div class="ticketstatus-details">
          <h2 class="ticketstatus-details-heading">Ticket Details</h2>
          <p class="ticketstatus-message"><?php echo htmlspecialchars($message); ?></p>
        </div>
      <?php else: ?>
        <div class="ticketstatus-details">
          <h2 class="ticketstatus-details-heading">Ticket Details</h2>
          <p><strong>Ticket Number:</strong></p>
          <p><strong>Title:</strong></p>
          <p><strong>Severity:</strong></p>
          <p><strong>Assigned To:</strong></p>
          <p><strong>Created At:</strong></p>
          <div class="status-row">
            <span class="status-label">Current Status:</span>
            <span class="status-badge status-default">No Status</span>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

</body>
</html>