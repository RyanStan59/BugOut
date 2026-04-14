<?php
session_start();
include("../api/db.php");

/* Get logged in staff ID */
$staff_id = $_SESSION['user_id'] ?? 1;

/* Handle search */
$search = $_GET['search'] ?? "";

/* Base query */
if (!empty($search)) {
    $sql = "SELECT * FROM bugs 
            WHERE assigned_to = ? AND bug_id = ?
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $staff_id, $search);
} else {
    $sql = "SELECT * FROM bugs 
            WHERE assigned_to = ?
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
}

$stmt->execute();
$result = $stmt->get_result();

/* Function for status color */
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
  <title>View Bugs - Staff</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="staffbugs-page">
  <div class="staffbugs-grid">

    <!-- Logo -->
    <a href="staff.html" class="staffbugs-card staffbugs-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <!-- Title -->
    <div class="staffbugs-card staffbugs-title-card">
      <h2>View Bugs</h2>
    </div>

    <!-- Search -->
    <div class="staffbugs-card staffbugs-search-card">
      <form method="GET" class="ticket-search-form">
        <label for="search">Search Ticket #:</label>
        <input 
          type="text" 
          name="search" 
          id="search"
          placeholder="Enter Ticket Number"
          value="<?php echo htmlspecialchars($search); ?>"
        >
        <button type="submit" class="staffbugs-search-btn">Search</button>
      </form>
    </div>

    <!-- Side Card -->
    <div class="staffbugs-card staffbugs-side-card">
      <h2>Assigned</h2>
      <img src="../assets/bug-icon.png" alt="Bug Icon" class="staffbugs-side-img">
    </div>

    <!-- Table -->
    <div class="staffbugs-card staffbugs-table-card">
      <div class="staffbugs-table-wrapper">

        <table class="staffbugs-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Severity</th>
              <th>Status</th>
              <th>Created</th>
              <th>View</th>
            </tr>
          </thead>

          <tbody>

          <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo $row['bug_id']; ?></td>

                <td><?php echo htmlspecialchars($row['title']); ?></td>

                <td><?php echo htmlspecialchars($row['severity']); ?></td>

                <td class="<?php echo getStatusClass($row['status']); ?>">
                  <?php echo htmlspecialchars($row['status']); ?>
                </td>

                <td><?php echo htmlspecialchars($row['created_at']); ?></td>

                <td>
                  <a href="bugdetails.php?bug_id=<?php echo $row['bug_id']; ?>" 
                     class="staffbugs-view-link">
                    View
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>

          <?php else: ?>
            <tr>
              <td colspan="6">No bugs found.</td>
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