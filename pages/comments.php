<?php
session_start();
include("../api/db.php");

/*
  Assumes comments table columns:
  comment_id
  bug_id
  solution_id
  parent_comment_id
  user_id
  comment_text
  created_at
*/

$admin_id = $_SESSION['user_id'] ?? 1;
$message = "";

$bug_id = trim($_GET['bug_id'] ?? $_POST['bug_id'] ?? "");
$solution_id = trim($_GET['solution_id'] ?? $_POST['solution_id'] ?? "");

/* ADD COMMENT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_comment'])) {
    $comment_text = trim($_POST['comment_text'] ?? "");

    if (empty($comment_text)) {
        $message = "Please enter a comment.";
    } elseif (empty($bug_id) && empty($solution_id)) {
        $message = "Please enter a Bug ID or Solution ID.";
    } else {
        if (!empty($bug_id) && !empty($solution_id)) {
            $sql = "INSERT INTO comments (bug_id, solution_id, user_id, comment_text, created_at)
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $bug_id, $solution_id, $admin_id, $comment_text);
        } elseif (!empty($bug_id)) {
            $sql = "INSERT INTO comments (bug_id, solution_id, user_id, comment_text, created_at)
                    VALUES (?, NULL, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $bug_id, $admin_id, $comment_text);
        } else {
            $sql = "INSERT INTO comments (bug_id, solution_id, user_id, comment_text, created_at)
                    VALUES (NULL, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $solution_id, $admin_id, $comment_text);
        }

        if ($stmt->execute()) {
            $message = "Comment added successfully.";
        } else {
            $message = "Could not add comment.";
        }

        $stmt->close();
    }
}

/* DELETE COMMENT */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_comment'])) {
    $comment_id = trim($_POST['comment_id'] ?? "");

    if (!empty($comment_id)) {
        $sql = "DELETE FROM comments WHERE comment_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $comment_id);

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
} elseif (!empty($solution_id)) {
    $sql = "SELECT comment_id, bug_id, solution_id, user_id, comment_text, created_at
            FROM comments
            WHERE solution_id = ?
            ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $solution_id);
} else {
    $sql = "SELECT comment_id, bug_id, solution_id, user_id, comment_text, created_at
            FROM comments
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Comments - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="admincomments-page">
  <div class="admincomments-grid">

    <!-- Logo -->
    <a href="admin.html" class="admincomments-card admincomments-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <!-- Title -->
    <div class="admincomments-card admincomments-title-card">
      <h2>Comments</h2>
    </div>

    <!-- Search -->
    <div class="admincomments-card admincomments-search-card">
      <form action="comments.php" method="GET" class="ticket-search-form">
        <label for="bug_id">Bug ID:</label>
        <input
          type="number"
          name="bug_id"
          id="bug_id"
          placeholder="Enter bug ID"
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

        <button type="submit" class="admincomments-btn">Load Comments</button>
      </form>
    </div>

    <!-- Side Card -->
    <div class="admincomments-card admincomments-side-card">
      <h2>Thread</h2>
      <img src="../assets/vcomments-icon.png" alt="Comments Icon" class="admincomments-side-img">
    </div>

    <!-- Main Card -->
    <div class="admincomments-card admincomments-main-card">
      <div class="admincomments-content">

        <h2 class="admincomments-heading">Manage Comments</h2>

        <?php if (!empty($message)): ?>
          <p class="admincomments-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Add comment form -->
        <form action="comments.php" method="POST" class="admincomments-form">
          <input type="hidden" name="bug_id" value="<?php echo htmlspecialchars($bug_id); ?>">
          <input type="hidden" name="solution_id" value="<?php echo htmlspecialchars($solution_id); ?>">

          <label for="comment_text">Add Comment:</label>
          <textarea
            name="comment_text"
            id="comment_text"
            placeholder="Write a new admin comment..."
            required
          ></textarea>

          <button type="submit" name="add_comment" class="admincomments-btn">Post Comment</button>
        </form>

        <!-- Comments list -->
        <div class="admincomments-thread-box">
          <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
              <div class="admincomments-thread-item">
                <p><strong>Comment ID:</strong> <?php echo htmlspecialchars($comment['comment_id']); ?></p>

                <?php if (!empty($comment['bug_id'])): ?>
                  <p><strong>Bug ID:</strong> <?php echo htmlspecialchars($comment['bug_id']); ?></p>
                <?php endif; ?>

                <?php if (!empty($comment['solution_id'])): ?>
                  <p><strong>Solution ID:</strong> <?php echo htmlspecialchars($comment['solution_id']); ?></p>
                <?php endif; ?>

                <p><strong>User ID:</strong> <?php echo htmlspecialchars($comment['user_id']); ?></p>

                <div class="admincomments-text-box">
                  <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                </div>

                <p><strong>Created At:</strong> <?php echo htmlspecialchars($comment['created_at']); ?></p>

                <form action="comments.php?bug_id=<?php echo urlencode($bug_id); ?>&solution_id=<?php echo urlencode($solution_id); ?>" method="POST" class="admincomments-delete-form">
                  <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['comment_id']); ?>">
                  <button type="submit" name="delete_comment" class="admincomments-delete-btn">
                    Delete Comment
                  </button>
                </form>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="admincomments-empty">No comments found.</p>
          <?php endif; ?>
        </div>

      </div>
    </div>

  </div>
</div>

</body>
</html>