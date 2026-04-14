<?php
session_start();
include("../api/db.php");

$staff_id = $_SESSION['user_id'] ?? 1;
$message = "";
$bug_id_value = "";

/* Handle form submission */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bug_id = trim($_POST['bug_id'] ?? "");
    $solution_text = trim($_POST['solution_text'] ?? "");

    $bug_id_value = $bug_id;

    if (!empty($bug_id) && !empty($solution_text)) {
        $approval_status = "Pending";

        $sql = "INSERT INTO solutions (bug_id, solution_text, approval_status, created_at, staff_id)
                VALUES (?, ?, ?, NOW(), ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $bug_id, $solution_text, $approval_status, $staff_id);

        if ($stmt->execute()) {
            $message = "Solution submitted successfully.";
            $bug_id_value = "";
        } else {
            $message = "Error submitting solution.";
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Solution - BugOut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="staffsolution-page">
  <div class="staffsolution-grid">

    <!-- Logo -->
    <a href="staff.html" class="staffsolution-card staffsolution-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <!-- Title -->
    <div class="staffsolution-card staffsolution-title-card">
      <h2>Submit Solution</h2>
    </div>

    <!-- Back Card -->
    <a href="viewbugs.php" class="staffsolution-card staffsolution-back-card">
      <h2>View Bugs</h2>
    </a>

    <!-- Left Info Card -->
    <div class="staffsolution-card staffsolution-side-card">
      <h2>Solution</h2>
      <img src="../assets/bug-icon.png" alt="Bug Icon" class="staffsolution-side-img">
    </div>

    <!-- Form Card -->
    <div class="staffsolution-card staffsolution-form-card">
      <div class="staffsolution-form-wrap">

        <?php if (!empty($message)): ?>
          <p class="staffsolution-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="submitsolution.php" method="POST" id="solutionForm">

          <div class="solution-row">
            <label for="bug_id">Ticket Number:</label>
            <input
              type="number"
              name="bug_id"
              id="bug_id"
              placeholder="Enter Ticket Number"
              value="<?php echo htmlspecialchars($bug_id_value); ?>"
              required
            >
          </div>

          <div class="solution-text-row">
            <label for="solution_text">Solution:</label>
            <textarea
              name="solution_text"
              id="solution_text"
              placeholder="Write the solution for this bug..."
              required
            ></textarea>
          </div>

          <div class="solution-bottom-row">
            <div class="approval-box">
              <label>Approval Status:</label>
              <input type="text" value="Pending" disabled>
            </div>

            <button type="submit" class="submit-solution-btn">Submit</button>
          </div>

        </form>
      </div>
    </div>

  </div>
</div>

</body>
</html>