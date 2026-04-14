<?php
session_start();
include("../api/db.php");

$staff_id = $_SESSION['user_id'] ?? 1;
$message = "";

$bug_id = isset($_GET['bug_id']) ? trim($_GET['bug_id']) : "";
$solution_id = isset($_GET['solution_id']) ? trim($_GET['solution_id']) : "";

$bug_preview = null;
$solution_preview = null;

/* Load bug preview if bug_id exists */
if (!empty($bug_id)) {
    $sql = "SELECT bug_id, title, status, severity, assigned_to, created_at
            FROM bugs
            WHERE bug_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bug_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $bug_preview = $result->fetch_assoc();
    }
    $stmt->close();
}

/* Load solution preview if solution_id exists */
if (!empty($solution_id)) {
    $sql = "SELECT id, bug_id, solution_text, approval_status, created_at, staff_id
            FROM solutions
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $solution_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $solution_preview = $result->fetch_assoc();
    }
    $stmt->close();
}

/* Submit comment */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bug_id = trim($_POST['bug_id'] ?? "");
    $solution_id = trim($_POST['solution_id'] ?? "");
    $comment_text = trim($_POST['comment_text'] ?? "");

    if (empty($comment_text)) {
        $message = "Please enter a comment.";
    } elseif (empty($bug_id) && empty($solution_id)) {
        $message = "Please enter a Ticket Number or Solution ID.";
    } else {
        if (!empty($bug_id) && !empty($solution_id)) {
            $sql = "INSERT INTO comments (bug_id, solution_id, user_id, comment_text, created_at)
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $bug_id, $solution_id, $staff_id, $comment_text);
        } elseif (!empty($bug_id)) {
            $sql = "INSERT INTO comments (bug_id, solution_id, user_id, comment_text, created_at)
                    VALUES (?, NULL, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $bug_id, $staff_id, $comment_text);
        } else {
            $sql = "INSERT INTO comments (bug_id, solution_id, user_id, comment_text, created_at)
                    VALUES (NULL, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $solution_id, $staff_id, $comment_text);
        }

        if ($stmt->execute()) {
            $message = "Comment posted successfully.";
        } else {
            $message = "Error posting comment.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Make a Comment - Staff</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="staffmakecomment-page">
  <div class="staffmakecomment-grid">

    <!-- Logo -->
    <a href="staff.html" class="staffmakecomment-card staffmakecomment-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <!-- Title -->
    <div class="staffmakecomment-card staffmakecomment-title-card">
      <h2>Make a Comment</h2>
    </div>

    <!-- Back -->
    <a href="viewcomments.php<?php echo (!empty($bug_id) || !empty($solution_id)) ? '?bug_id=' . urlencode($bug_id) . '&solution_id=' . urlencode($solution_id) : ''; ?>" class="staffmakecomment-card staffmakecomment-back-card">
      <h2>View Comments</h2>
    </a>

    <!-- Side Card -->
    <div class="staffmakecomment-card staffmakecomment-side-card">
      <h2>Reply</h2>
      <img src="../assets/mcomment-icon.png" alt="Comments Icon" class="staffmakecomment-side-img">
    </div>

    <!-- Main Form Card -->
    <div class="staffmakecomment-card staffmakecomment-main-card">
      <div class="staffmakecomment-content">

        <h2 class="staffmakecomment-heading">New Comment</h2>

        <?php if (!empty($message)): ?>
          <p class="staffmakecomment-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="makecomment.php" method="POST" class="staffmakecomment-form">
          <div class="staffmakecomment-row">
            <label for="bug_id">Ticket Number:</label>
            <input
              type="number"
              name="bug_id"
              id="bug_id"
              placeholder="Enter Ticket Number"
              value="<?php echo htmlspecialchars($bug_id); ?>"
            >
          </div>

          <div class="staffmakecomment-row">
            <label for="solution_id">Solution ID:</label>
            <input
              type="number"
              name="solution_id"
              id="solution_id"
              placeholder="Enter solution ID"
              value="<?php echo htmlspecialchars($solution_id); ?>"
            >
          </div>

          <div class="staffmakecomment-text-row">
            <label for="comment_text">Comment:</label>
            <textarea
              name="comment_text"
              id="comment_text"
              placeholder="Write your comment here..."
              required
            ></textarea>
          </div>

          <button type="submit" class="staffmakecomment-btn">Post Comment</button>
        </form>

        <?php if ($bug_preview): ?>
          <div class="staffmakecomment-preview-box">
            <h3>Bug Preview</h3>
            <p><strong>Ticket Number:</strong> <?php echo htmlspecialchars($bug_preview['bug_id']); ?></p>
            <p><strong>Title:</strong> <?php echo htmlspecialchars($bug_preview['title']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($bug_preview['status']); ?></p>
            <p><strong>Severity:</strong> <?php echo htmlspecialchars($bug_preview['severity']); ?></p>
            <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($bug_preview['assigned_to']); ?></p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($bug_preview['created_at']); ?></p>
          </div>
        <?php endif; ?>

        <?php if ($solution_preview): ?>
          <div class="staffmakecomment-preview-box">
            <h3>Solution Preview</h3>
            <p><strong>Solution ID:</strong> <?php echo htmlspecialchars($solution_preview['id']); ?></p>
            <p><strong>Bug ID:</strong> <?php echo htmlspecialchars($solution_preview['bug_id']); ?></p>
            <p><strong>Approval Status:</strong> <?php echo htmlspecialchars($solution_preview['approval_status']); ?></p>
            <p><strong>Staff ID:</strong> <?php echo htmlspecialchars($solution_preview['staff_id']); ?></p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($solution_preview['created_at']); ?></p>
            <div class="staffmakecomment-solution-text">
              <?php echo nl2br(htmlspecialchars($solution_preview['solution_text'])); ?>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>
</div>

</body>
</html>