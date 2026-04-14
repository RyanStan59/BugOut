<?php
session_start();
include("../api/db.php");

$staff_id = $_SESSION['user_id'] ?? 1;
$bug = null;
$message = "";

/* Get bug ID from URL */
if (isset($_GET['bug_id']) && !empty(trim($_GET['bug_id']))) {
    $bug_id = trim($_GET['bug_id']);

    /* Pull bug details */
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
} else {
    $message = "No bug selected.";
}

/* Optional: get solutions already submitted for this bug */
$solutions = [];
if ($bug) {
    $sql2 = "SELECT id, solution_text, approval_status, created_at, staff_id
             FROM solutions
             WHERE bug_id = ?
             ORDER BY created_at DESC";

    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $bug['bug_id']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();

    while ($row = $result2->fetch_assoc()) {
        $solutions[] = $row;
    }

    $stmt2->close();
}

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
  <title>Bug Details - Staff</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="staffdetail-page">
  <div class="staffdetail-grid">

    <!-- Logo -->
    <a href="staff.html" class="staffdetail-card staffdetail-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <!-- Title -->
    <div class="staffdetail-card staffdetail-title-card">
      <h2>Bug Details</h2>
    </div>

    <!-- Action Card -->
    <div class="staffdetail-card staffdetail-action-card">
      <?php if ($bug): ?>
        <a href="submitsolution.php?bug_id=<?php echo urlencode($bug['bug_id']); ?>" class="staffdetail-action-link">
          Submit Solution
        </a>
      <?php else: ?>
        <span class="staffdetail-action-link disabled-link">Submit Solution</span>
      <?php endif; ?>
    </div>

    <!-- Side Card -->
    <div class="staffdetail-card staffdetail-side-card">
      <h2>Details</h2>
      <img src="../assets/bug-icon.png" alt="Bug Icon" class="staffdetail-side-img">
    </div>

    <!-- Main Details -->
    <div class="staffdetail-card staffdetail-main-card">
      <div class="staffdetail-content">

        <?php if (!empty($message)): ?>
          <p class="staffdetail-message"><?php echo htmlspecialchars($message); ?></p>
        <?php elseif ($bug): ?>

          <h2 class="staffdetail-heading">Bug Information</h2>

          <div class="staffdetail-info-box">
            <p><strong>Bug ID:</strong> <?php echo htmlspecialchars($bug['bug_id']); ?></p>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($bug['title']); ?></p>
            <p><strong>Description:</strong></p>
            <div class="staffdetail-description-box">
              <?php echo nl2br(htmlspecialchars($bug['description'])); ?>
            </div>

            <p><strong>Severity:</strong> <?php echo htmlspecialchars($bug['severity']); ?></p>
            <p>
              <strong>Status:</strong>
              <span class="<?php echo getStatusClass($bug['status']); ?>">
                <?php echo htmlspecialchars($bug['status']); ?>
              </span>
            </p>
            <p><strong>Created By:</strong> <?php echo htmlspecialchars($bug['created_by']); ?></p>
            <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($bug['assigned_to']); ?></p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($bug['created_at']); ?></p>
          </div>

          <h2 class="staffdetail-heading">Submitted Solutions</h2>

          <div class="staffdetail-solution-list">
            <?php if (!empty($solutions)): ?>
              <?php foreach ($solutions as $solution): ?>
                <div class="staffdetail-solution-item">
                  <p><strong>Solution ID:</strong> <?php echo htmlspecialchars($solution['id']); ?></p>
                  <p><strong>Staff ID:</strong> <?php echo htmlspecialchars($solution['staff_id']); ?></p>
                  <p><strong>Approval Status:</strong> <?php echo htmlspecialchars($solution['approval_status']); ?></p>
                  <p><strong>Created At:</strong> <?php echo htmlspecialchars($solution['created_at']); ?></p>
                  <div class="staffdetail-description-box">
                    <?php echo nl2br(htmlspecialchars($solution['solution_text'])); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="staffdetail-empty">No solutions submitted yet for this bug.</p>
            <?php endif; ?>
          </div>

        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

</body>
</html>