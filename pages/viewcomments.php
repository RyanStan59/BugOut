<?php
session_start();
include("../api/db.php");

$staff_id = $_SESSION['user_id'] ?? 1;
$message = "";

/*
  This page supports:
  - viewing comments for a bug or solution
  - adding a new comment
  - deleting only the logged-in staff member's own comments
*/

$bug_id = isset($_GET['bug_id']) ? trim($_GET['bug_id']) : "";
$solution_id = isset($_GET['solution_id']) ? trim($_GET['solution_id']) : "";

/* ADD COMMENT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_comment'])) {
    $bug_id = trim($_POST['bug_id'] ?? "");
    $solution_id = trim($_POST['solution_id'] ?? "");
    $comment_text = trim($_POST['comment_text'] ?? "");

    if (empty($comment_text)) {
        $message = "Please enter a comment.";
    } elseif (empty($bug_id) && empty($solution_id)) {
        $message = "Please provide a Bug ID or Solution ID.";
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
            $message = "Comment added successfully.";
        } else {
            $message = "Error adding comment.";
        }

        $stmt->close();
    }
}

/* DELETE OWN COMMENT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_comment'])) {
    $comment_id = trim($_POST['comment_id'] ?? "");

    if (!empty($comment_id)) {
        $sql = "DELETE FROM comments WHERE comment_id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $comment_id, $staff_id);

        if ($stmt->execute()) {
            $message = "Comment deleted successfully.";
        } else {
            $message = "Could not delete comment.";
        }

        $stmt->close();
    }
}

/* LOAD COMMENTS */
$comments = [];

if (!empty($bug_id) || !empty($solution_id)) {
    if (!empty($bug_id) && !empty($solution_id)) {
        $sql = "SELECT comment_id, bug_id, solution_id, user_id, comment_text, created_at
                FROM comments
                WHERE bug_id = ? OR solution_id = ?
                ORDER BY created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $bug_id, $solution_id);
    } elseif (!empty($bug_id)) {
        $sql = "SELECT comment_id, bug_id, solution_id, user_id, comment_text, created_at
                FROM comments
                WHERE bug_id = ?
                ORDER BY created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $bug_id);
    } else {
        $sql = "SELECT comment_id, bug_id, solution_id, user_id, comment_text, created_at
                FROM comments
                WHERE solution_id = ?
                ORDER BY created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $solution_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Comments - Staff</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="staffcomments-page">
  <div class="staffcomments-grid">

    <!-- Logo -->
    <a href="staff.html" class="staffcomments-card staffcomments-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <!-- Title -->
    <div class="staffcomments-card staffcomments-title-card">
      <h2>View Comments</h2>
    </div>

    <!-- Search / Load Thread -->
    <div class="staffcomments-card staffcomments-search-card">
      <form action="viewcomments.php" method="GET" class="ticket-search-form">
        <label for="bug_id">Ticket Number:</label>
        <input
          type="number"
          name="bug_id"
          id="bug_id"
          placeholder="Enter Ticket Number"
          value="<?php echo htmlspecialchars($bug_id); ?>"
        >

        <label for="solution_id">Solution ID:</label>
        <input
          type="number"
          name="solution_id"
          id="solution_id"
          placeholder="Enter solution ID"
          value="<?php echo htmlspecialchars($solution_id); ?>"
        >

        <button type="submit" class="staffcomments-btn">Load Comments</button>
      </form>
    </div>

    <!-- Side card -->
    <div class="staffcomments-card staffcomments-side-card">
      <h2>Thread</h2>
      <img src="../assets/vcomments-icon.png" alt="Comments Icon" class="staffcomments-side-img">
    </div>

    <!-- Main card -->
    <div class="staffcomments-card staffcomments-main-card">
      <div class="staffcomments-content">

        <h2 class="staffcomments-heading">Comments</h2>

        <?php if (!empty($message)): ?>
          <p class="staffcomments-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Add comment form -->
        <form action="viewcomments.php" method="POST" class="staffcomments-form">
          <input type="hidden" name="bug_id" value="<?php echo htmlspecialchars($bug_id); ?>">
          <input type="hidden" name="solution_id" value="<?php echo htmlspecialchars($solution_id); ?>">

          <label for="comment_text">Add Comment:</label>
          <textarea
            name="comment_text"
            id="comment_text"
            placeholder="Write your comment here..."
            required
          ></textarea>

          <button type="submit" name="add_comment" class="staffcomments-btn">Post Comment</button>
        </form>

        <!-- Thread -->
        <div class="staffcomments-thread-box">
          <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
              <div class="staffcomments-thread-item <?php echo ($comment['user_id'] == $staff_id) ? 'my-comment' : 'other-comment'; ?>">
                <p><strong>Comment ID:</strong> <?php echo htmlspecialchars($comment['comment_id']); ?></p>
                <p><strong>User ID:</strong> <?php echo htmlspecialchars($comment['user_id']); ?></p>

                <?php if (!empty($comment['bug_id'])): ?>
                  <p><strong>Ticket Number:</strong> <?php echo htmlspecialchars($comment['bug_id']); ?></p>
                <?php endif; ?>

                <?php if (!empty($comment['solution_id'])): ?>
                  <p><strong>Solution ID:</strong> <?php echo htmlspecialchars($comment['solution_id']); ?></p>
                <?php endif; ?>

                <div class="staffcomments-text-box">
                  <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                </div>

                <p><strong>Created At:</strong> <?php echo htmlspecialchars($comment['created_at']); ?></p>

                <?php if ($comment['user_id'] == $staff_id): ?>
                  <form action="viewcomments.php?bug_id=<?php echo urlencode($bug_id); ?>&solution_id=<?php echo urlencode($solution_id); ?>" method="POST" class="delete-comment-form">
                    <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['comment_id']); ?>">
                    <button type="submit" name="delete_comment" class="delete-comment-btn">Delete My Comment</button>
                  </form>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="staffcomments-empty">No comments found yet.</p>
          <?php endif; ?>
        </div>

      </div>
    </div>

  </div>
</div>

</body>
</html>