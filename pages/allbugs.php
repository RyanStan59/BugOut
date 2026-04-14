<?php
session_start();
include("../api/db.php");

$search = trim($_GET['search'] ?? "");
$bugs = [];

/* Load all bugs, with optional search */
if (!empty($search)) {
    $sql = "SELECT bug_id, ticket_no, title, severity, status, created_by, assigned_to, created_at
            FROM bugs
            WHERE bug_id LIKE CONCAT('%', ?, '%')
               OR ticket_no LIKE CONCAT('%', ?, '%')
               OR title LIKE CONCAT('%', ?, '%')
               OR status LIKE CONCAT('%', ?, '%')
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $search, $search, $search, $search);
} else {
    $sql = "SELECT bug_id, ticket_no, title, severity, status, created_by, assigned_to, created_at
            FROM bugs
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bugs[] = $row;
}

$stmt->close();

function getStatusClass($status) {
    $status = strtolower(trim($status));

    if ($status === "open") return "status-open-text";
    if ($status === "in progress") return "status-progress-text";
    if ($status === "resolved") return "status-resolved-text";
    if ($status === "closed") return "status-closed-text";

    return "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Bugs - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="adminbugs-page">
  <div class="adminbugs-grid">

    <!-- Logo -->
    <a href="admin.html" class="adminbugs-card adminbugs-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <!-- Title -->
    <div class="adminbugs-card adminbugs-title-card">
      <h2>All Bugs</h2>
    </div>

    <!-- Search -->
    <div class="adminbugs-card adminbugs-search-card">
      <form action="allbugs.php" method="GET" class="ticket-search-form">
        <label for="search">Search Bug / Ticket:</label>
        <input
          type="text"
          name="search"
          id="search"
          placeholder="Enter bug ID, ticket no, title, or status"
          value="<?php echo htmlspecialchars($search); ?>"
        >
        <button type="submit" class="search-btn">Search</button>
      </form>
    </div>

    <!-- Side Card -->
    <div class="adminbugs-card adminbugs-side-card">
      <h2>Admin</h2>
      <img src="../assets/bug-icon.png" alt="Bug Icon" class="adminbugs-side-img">
    </div>

    <!-- Table Card -->
    <div class="adminbugs-card adminbugs-table-card">
      <div class="adminbugs-table-wrapper">
        <table class="adminbugs-table">
          <thead>
            <tr>
              <th>Bug ID</th>
              <th>Ticket #</th>
              <th>Title</th>
              <th>Severity</th>
              <th>Status</th>
              <th>Created By</th>
              <th>Assigned To</th>
              <th>Created At</th>
              <th>Ticket</th>
              <th>Assign</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($bugs)): ?>
              <?php foreach ($bugs as $bug): ?>
                <tr>
                  <td><?php echo htmlspecialchars($bug['bug_id']); ?></td>
                  <td><?php echo htmlspecialchars($bug['ticket_no'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($bug['title']); ?></td>
                  <td><?php echo htmlspecialchars($bug['severity']); ?></td>
                  <td class="<?php echo getStatusClass($bug['status']); ?>">
                    <?php echo htmlspecialchars($bug['status']); ?>
                  </td>
                  <td><?php echo htmlspecialchars($bug['created_by']); ?></td>
                  <td><?php echo htmlspecialchars($bug['assigned_to']); ?></td>
                  <td><?php echo htmlspecialchars($bug['created_at']); ?></td>
                  <td>
                    <a href="ticketnumber.php?bug_id=<?php echo urlencode($bug['bug_id']); ?>" class="adminbugs-link">
                      Ticket
                    </a>
                  </td>
                  <td>
                    <a href="assignbugs.php?bug_id=<?php echo urlencode($bug['bug_id']); ?>" class="adminbugs-link">
                      Assign
                    </a>
                  </td>
                  <td>
                    <a href="bugdetails.php?bug_id=<?php echo urlencode($bug['bug_id']); ?>" class="adminbugs-link">
                      View
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="11">No bugs found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

</body>
</html>