<?php
session_start();
include("../api/db.php");

$message = "";
$search = trim($_GET['search'] ?? "");

/* Handle approval actions */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $solution_id = trim($_POST['solution_id'] ?? "");
    $new_status = trim($_POST['new_status'] ?? "");

    if (!empty($solution_id) && !empty($new_status)) {
        /* First get the bug_id for this solution */
        $sql_bug = "SELECT bug_id FROM solutions WHERE id = ?";
        $stmt_bug = $conn->prepare($sql_bug);
        $stmt_bug->bind_param("i", $solution_id);
        $stmt_bug->execute();
        $result_bug = $stmt_bug->get_result();

        if ($result_bug->num_rows > 0) {
            $solution_row = $result_bug->fetch_assoc();
            $bug_id = $solution_row['bug_id'];

            /* Update solution status */
            $sql = "UPDATE solutions SET approval_status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_status, $solution_id);

            if ($stmt->execute()) {
                /* If approved, also mark bug as resolved */
                if (strtolower($new_status) === "approved") {
                    $sql_bug_update = "UPDATE bugs SET status = 'Resolved' WHERE bug_id = ?";
                    $stmt_bug_update = $conn->prepare($sql_bug_update);
                    $stmt_bug_update->bind_param("i", $bug_id);
                    $stmt_bug_update->execute();
                    $stmt_bug_update->close();

                    $message = "Solution approved and bug marked as Resolved.";
                } else {
                    $message = "Solution status updated successfully.";
                }
            } else {
                $message = "Could not update solution status.";
            }

            $stmt->close();
        } else {
            $message = "Solution not found.";
        }

        $stmt_bug->close();
    }
}

/* Load solutions */
$solutions = [];

if (!empty($search)) {
    $sql = "SELECT id, bug_id, solution_text, approval_status, created_at, staff_id
            FROM solutions
            WHERE id LIKE CONCAT('%', ?, '%')
               OR bug_id LIKE CONCAT('%', ?, '%')
               OR approval_status LIKE CONCAT('%', ?, '%')
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search, $search, $search);
} else {
    $sql = "SELECT id, bug_id, solution_text, approval_status, created_at, staff_id
            FROM solutions
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $solutions[] = $row;
}

$stmt->close();

function getApprovalClass($status) {
    $status = strtolower(trim($status));

    if ($status === "approved") return "approval-approved";
    if ($status === "pending") return "approval-pending";
    if ($status === "rejected") return "approval-rejected";

    return "approval-default";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Approved Solutions - BugOut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="adminsolutions-page">
  <div class="adminsolutions-grid">

    <a href="admin.html" class="adminsolutions-card adminsolutions-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <div class="adminsolutions-card adminsolutions-title-card">
      <h2>Approved Solutions</h2>
    </div>

    <div class="adminsolutions-card adminsolutions-search-card">
      <form action="approvedsolutions.php" method="GET" class="ticket-search-form">
        <label for="search">Search Solution / Bug:</label>
        <input
          type="text"
          name="search"
          id="search"
          placeholder="Enter solution ID, bug ID, or status"
          value="<?php echo htmlspecialchars($search); ?>"
        >
        <button type="submit" class="search-btn">Search</button>
      </form>
    </div>

    <div class="adminsolutions-card adminsolutions-side-card">
      <h2>Review</h2>
      <img src="../assets/bug-icon.png" alt="Review Icon" class="adminsolutions-side-img">
    </div>

    <div class="adminsolutions-card adminsolutions-table-card">
      <div class="adminsolutions-content">

        <h2 class="adminsolutions-heading">Solution Review Table</h2>

        <?php if (!empty($message)): ?>
          <p class="adminsolutions-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <div class="adminsolutions-table-wrapper">
          <table class="adminsolutions-table">
            <thead>
              <tr>
                <th>Solution ID</th>
                <th>Bug ID</th>
                <th>Staff ID</th>
                <th>Solution Text</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Update Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($solutions)): ?>
                <?php foreach ($solutions as $solution): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($solution['id']); ?></td>
                    <td><?php echo htmlspecialchars($solution['bug_id']); ?></td>
                    <td><?php echo htmlspecialchars($solution['staff_id']); ?></td>
                    <td>
                      <div class="adminsolutions-text-box">
                        <?php echo nl2br(htmlspecialchars($solution['solution_text'])); ?>
                      </div>
                    </td>
                    <td class="<?php echo getApprovalClass($solution['approval_status']); ?>">
                      <?php echo htmlspecialchars($solution['approval_status']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($solution['created_at']); ?></td>
                    <td>
                      <form action="approvedsolutions.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" method="POST" class="adminsolutions-action-form">
                        <input type="hidden" name="solution_id" value="<?php echo htmlspecialchars($solution['id']); ?>">

                        <select name="new_status" required>
                          <option value="">Choose</option>
                          <option value="Approved">Approved</option>
                          <option value="Pending">Pending</option>
                          <option value="Rejected">Rejected</option>
                        </select>

                        <button type="submit" class="adminsolutions-btn">Save</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7">No solutions found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

      </div>
    </div>

  </div>
</div>

</body>
</html>