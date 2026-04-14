<?php
session_start();
include("../api/db.php");

$message = "";
$bug_id = $_GET['bug_id'] ?? $_POST['bug_id'] ?? "";
$bug = null;
$staff_members = [];

/* Load all staff users */
$sql_staff = "SELECT user_id, full_name FROM users WHERE role = 'Staff' ORDER BY full_name ASC";
$result_staff = $conn->query($sql_staff);
if ($result_staff) {
    while ($row = $result_staff->fetch_assoc()) {
        $staff_members[] = $row;
    }
}

/* Save staff assignment */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['assign_bug'])) {
    $bug_id = trim($_POST['bug_id'] ?? "");
    $assigned_to = trim($_POST['assigned_to'] ?? "");

    if (!empty($bug_id) && !empty($assigned_to)) {
        $sql = "UPDATE bugs SET assigned_to = ? WHERE bug_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $assigned_to, $bug_id);

        if ($stmt->execute()) {
            $message = "Bug assigned successfully.";
        } else {
            $message = "Could not assign bug.";
        }
        $stmt->close();
    } else {
        $message = "Please choose a bug and a staff member.";
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
  <title>Assign Bugs - BugOut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="adminassign-page">
  <div class="adminassign-grid">

    <a href="admin.html" class="adminassign-card adminassign-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <div class="adminassign-card adminassign-title-card">
      <h2>Assign Bugs</h2>
    </div>

    <div class="adminassign-card adminassign-search-card">
      <form action="assignbugs.php" method="GET" class="ticket-search-form">
        <label for="bug_id">Search Ticket Number:</label>
        <input
          type="number"
          name="bug_id"
          id="bug_id"
          placeholder="Enter Ticket Number"
          value="<?php echo htmlspecialchars($bug_id); ?>"
          required
        >
        <button type="submit" class="search-btn">Load Bug</button>
      </form>
    </div>

    <div class="adminassign-card adminassign-side-card">
      <h2>Assign</h2>
      <img src="../assets/bug-icon.png" alt="Bug Icon" class="adminassign-side-img">
    </div>

    <div class="adminassign-card adminassign-main-card">
      <div class="adminassign-content">

        <h2 class="adminassign-heading">Assign Staff</h2>

        <?php if (!empty($message)): ?>
          <p class="adminassign-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="assignbugs.php" method="POST" class="adminassign-form">
          <div class="adminassign-row">
            <label for="bug_id_post">Ticket Number:</label>
            <input
              type="number"
              name="bug_id"
              id="bug_id_post"
              value="<?php echo htmlspecialchars($bug_id); ?>"
              required
            >
          </div>

          <div class="adminassign-row">
            <label for="assigned_to">Assign To:</label>
            <select name="assigned_to" id="assigned_to" required>
              <option value="">Select staff member</option>
              <?php foreach ($staff_members as $staff): ?>
                <option value="<?php echo htmlspecialchars($staff['user_id']); ?>"
                  <?php echo (isset($bug['assigned_to']) && $bug['assigned_to'] == $staff['user_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($staff['full_name']); ?> (ID: <?php echo htmlspecialchars($staff['user_id']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" name="assign_bug" class="adminassign-btn">Assign Bug</button>
        </form>

        <?php if ($bug): ?>
          <div class="adminassign-preview-box">
            <p><strong>Bug ID:</strong> <?php echo htmlspecialchars($bug['bug_id']); ?></p>
            <p><strong>Ticket #:</strong> <?php echo htmlspecialchars($bug['ticket_no'] ?? ''); ?></p>
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