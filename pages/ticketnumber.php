<?php
session_start();
include("../api/db.php");

$message = "";
$bug = null;
$bug_id = $_GET['bug_id'] ?? $_POST['bug_id'] ?? "";

/* Save ticket number */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_ticket'])) {
    $bug_id = trim($_POST['bug_id'] ?? "");
    $ticket_no = trim($_POST['ticket_no'] ?? "");

    if (!empty($bug_id) && !empty($ticket_no)) {
        $sql = "UPDATE bugs SET ticket_no = ? WHERE bug_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $ticket_no, $bug_id);

        if ($stmt->execute()) {
            $message = "Ticket number assigned successfully.";
        } else {
            $message = "Could not save ticket number.";
        }
        $stmt->close();
    } else {
        $message = "Please enter both Bug ID and Ticket Number.";
    }
}

/* Load bug details */
if (!empty($bug_id)) {
    $sql = "SELECT bug_id, ticket_no, title, severity, status, assigned_to, created_at
            FROM bugs
            WHERE bug_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bug_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $bug = $result->fetch_assoc();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assigned Ticket Number - BugOut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="adminticket-page">
  <div class="adminticket-grid">

    <a href="admin.html" class="adminticket-card adminticket-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <div class="adminticket-card adminticket-title-card">
      <h2>Assigned Ticket Number</h2>
    </div>

    <div class="adminticket-card adminticket-search-card">
      <form action="ticketnumber.php" method="GET" class="ticket-search-form">
        <label for="bug_id">Search Bug ID:</label>
        <input
          type="number"
          name="bug_id"
          id="bug_id"
          placeholder="Enter bug ID"
          value="<?php echo htmlspecialchars($bug_id); ?>"
          required
        >
        <button type="submit" class="search-btn">Load Bug</button>
      </form>
    </div>

    <div class="adminticket-card adminticket-side-card">
      <h2>Ticket</h2>
      <img src="../assets/ticket-icon.png" alt="Ticket Icon" class="adminticket-side-img">
    </div>

    <div class="adminticket-card adminticket-main-card">
      <div class="adminticket-content">

        <h2 class="adminticket-heading">Ticket Assignment</h2>

        <?php if (!empty($message)): ?>
          <p class="adminticket-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="ticketnumber.php" method="POST" class="adminticket-form">
          <div class="adminticket-row">
            <label for="bug_id_post">Bug ID:</label>
            <input
              type="number"
              name="bug_id"
              id="bug_id_post"
              value="<?php echo htmlspecialchars($bug_id); ?>"
              required
            >
          </div>

          <div class="adminticket-row">
            <label for="ticket_no">Ticket #:</label>
            <input
              type="text"
              name="ticket_no"
              id="ticket_no"
              placeholder="Enter ticket number"
              value="<?php echo htmlspecialchars($bug['ticket_no'] ?? ''); ?>"
              required
            >
          </div>

          <button type="submit" name="save_ticket" class="adminticket-btn">Save Ticket</button>
        </form>

        <?php if ($bug): ?>
          <div class="adminticket-preview-box">
            <p><strong>Bug ID:</strong> <?php echo htmlspecialchars($bug['bug_id']); ?></p>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($bug['title']); ?></p>
            <p><strong>Severity:</strong> <?php echo htmlspecialchars($bug['severity']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($bug['status']); ?></p>
            <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($bug['assigned_to']); ?></p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($bug['created_at']); ?></p>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

</body>
</html>