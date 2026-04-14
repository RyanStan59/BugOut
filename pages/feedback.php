<?php
session_start();
include("../api/db.php");

$user_id = $_SESSION['user_id'] ?? 1;
$bug_id = isset($_GET['bug_id']) ? trim($_GET['bug_id']) : "";
$message = "";

/* Handle new comment submission */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bug_id = trim($_POST['bug_id'] ?? "");
    $comment_text = trim($_POST['comment_text'] ?? "");

    if (!empty($bug_id) && !empty($comment_text)) {
        $sql = "INSERT INTO comments (bug_id, user_id, comment_text, created_at)
                VALUES (?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $bug_id, $user_id, $comment_text);

        if ($stmt->execute()) {
            $message = "Feedback sent successfully.";
        } else {
            $message = "Could not send feedback.";
        }

        $stmt->close();
    } else {
        $message = "Please enter a bug ID and feedback message.";
    }
}

/* Load comments thread if bug_id exists */
$thread = [];
if (!empty($bug_id)) {
    $sql = "SELECT c.comment_text, c.created_at, c.user_id
            FROM comments c
            WHERE c.bug_id = ?
            ORDER BY c.created_at ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bug_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $thread[] = $row;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ticket Feedback - BugOut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="feedback-page">
  <div class="feedback-grid">

    <a href="user.html" class="feedback-card feedback-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <div class="feedback-card feedback-title-card">
      <h2>Ticket Feedback</h2>
    </div>

    <div class="feedback-card feedback-search-card">
      <form action="feedback.php" method="GET" class="ticket-search-form">
        <label for="bug_id">Search Ticket Number:</label>
        <input
          type="text"
          name="bug_id"
          id="bug_id"
          placeholder="Enter Ticket Number"
          value="<?php echo htmlspecialchars($bug_id); ?>"
          required
        >
        <button type="submit" class="search-btn">Load Thread</button>
      </form>
    </div>

    <div class="feedback-card feedback-side-card">
      <h2>Thread</h2>
      <img src="../assets/bug-icon.png" alt="Bug Icon" class="feedback-bug-img">
    </div>

    <div class="feedback-card feedback-main-card">
      <div class="feedback-main-content">

        <h2 class="feedback-details-heading">Comments / Feedback</h2>

        <?php if (!empty($message)): ?>
          <p class="feedback-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="feedback.php" method="POST" class="feedback-form">
          <label for="bug_id_post">Ticket Number:</label>
          <input
            type="text"
            name="bug_id"
            id="bug_id_post"
            value="<?php echo htmlspecialchars($bug_id); ?>"
            placeholder="Enter Ticket Number"
            required
          >

          <label for="comment_text">Your Feedback / Reply:</label>
          <textarea
            name="comment_text"
            id="comment_text"
            placeholder="Write your feedback or message to staff..."
            required
          ></textarea>

          <button type="submit" class="submit-feedback-btn">Send Message</button>
        </form>

        <div class="thread-box">
          <?php if (!empty($thread)): ?>
            <?php foreach ($thread as $comment): ?>
              <div class="thread-message <?php echo ($comment['user_id'] == $user_id) ? 'user-thread' : 'staff-thread'; ?>">
                <p class="thread-role">
                  <?php echo ($comment['user_id'] == $user_id) ? 'You' : 'Staff'; ?>
                </p>
                <p><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                <small><?php echo htmlspecialchars($comment['created_at']); ?></small>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="no-thread-message">No messages yet for this ticket.</p>
          <?php endif; ?>
        </div>

      </div>
    </div>

  </div>
</div>

</body>
</html>