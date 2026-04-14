<?php
session_start();
include("../api/db.php");

$bug = null;
$message = "";

if (isset($_GET['bug_id']) && !empty(trim($_GET['bug_id']))) {
    $bug_id = trim($_GET['bug_id']);

    $sql = "SELECT bug_id, title, description, severity, status, created_by, assigned_to, created_at
            FROM bugs
            WHERE bug_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bug_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $bug = $result->fetch_assoc();
    } else {
        $message = "No bug found with that ID.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Track Bug - BugOut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="trackbug-page">
  <div class="trackbug-grid">

    <a href="user.html" class="trackbug-card trackbug-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <div class="trackbug-card trackbug-title-card">
      <h2>Track Bug</h2>
    </div>

    <div class="trackbug-card trackbug-search-card">
      <form action="track.php" method="GET" class="ticket-search-form">
        <label for="bug_id">Search Ticket Number:</label>
        <input
          type="text"
          name="bug_id"
          id="bug_id"
          placeholder="Enter Ticket Number"
          value="<?php echo isset($_GET['bug_id']) ? htmlspecialchars($_GET['bug_id']) : ''; ?>"
          required
        >
        <button type="submit" class="search-btn">Search</button>
      </form>
    </div>

    <div class="trackbug-card trackbug-progress-card">
      <h2>Progress</h2>
      <img src="../assets/bug-icon.png" alt="Bug Icon" class="progress-bug-img">
    </div>

    <div class="trackbug-card trackbug-details-card">
      <?php if ($bug): ?>
        <div class="trackbug-details">
          <h2 class="trackbug-details-heading">Bug Details</h2>

          <p><strong>Bug ID:</strong> <?php echo htmlspecialchars($bug['bug_id']); ?></p>
          <p><strong>Title:</strong> <?php echo htmlspecialchars($bug['title']); ?></p>
          <p><strong>Description:</strong> <?php echo htmlspecialchars($bug['description']); ?></p>
          <p><strong>Severity:</strong> <?php echo htmlspecialchars($bug['severity']); ?></p>
          <p><strong>Status:</strong> <?php echo htmlspecialchars($bug['status']); ?></p>
          <p><strong>Created By:</strong> <?php echo htmlspecialchars($bug['created_by']); ?></p>
          <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($bug['assigned_to']); ?></p>
          <p><strong>Created At:</strong> <?php echo htmlspecialchars($bug['created_at']); ?></p>
        </div>

      <?php elseif (!empty($message)): ?>
        <div class="trackbug-details">
          <h2 class="trackbug-details-heading">Bug Details</h2>
          <p class="trackbug-message"><?php echo htmlspecialchars($message); ?></p>
        </div>

      <?php else: ?>
        <div class="trackbug-details">
          <h2 class="trackbug-details-heading">Bug Details</h2>
          <p><strong>Ticket Number:</strong></p>
          <p><strong>Title:</strong></p>
          <p><strong>Description:</strong></p>
          <p><strong>Severity:</strong></p>
          <p><strong>Status:</strong></p>
          <p><strong>Created By:</strong></p>
          <p><strong>Assigned To:</strong></p>
          <p><strong>Created At:</strong></p>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

</body>
</html>