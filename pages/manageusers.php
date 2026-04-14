<?php
session_start();
include("../api/db.php");

$message = "";
$edit_user = null;

/* ADD STAFF */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_staff'])) {
    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if (!empty($full_name) && !empty($email) && !empty($password)) {
        $role = "Staff";

        $sql = "INSERT INTO users (full_name, email, password, role)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $full_name, $email, $password, $role);

        if ($stmt->execute()) {
            $message = "Staff member added successfully.";
        } else {
            $message = "Could not add staff member.";
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields to add a staff member.";
    }
}

/* LOAD STAFF MEMBER FOR EDIT */
if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
    $edit_id = trim($_GET['edit_id']);

    $sql = "SELECT user_id, full_name, email, password, role
            FROM users
            WHERE user_id = ? AND role = 'Staff'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $edit_user = $result->fetch_assoc();
    }

    $stmt->close();
}

/* UPDATE STAFF */
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_staff'])) {
    $user_id = trim($_POST['user_id'] ?? "");
    $full_name = trim($_POST['full_name'] ?? "");
    $email = trim($_POST['email'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if (!empty($user_id) && !empty($full_name) && !empty($email) && !empty($password)) {
        $sql = "UPDATE users
                SET full_name = ?, email = ?, password = ?
                WHERE user_id = ? AND role = 'Staff'";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $full_name, $email, $password, $user_id);

        if ($stmt->execute()) {
            $message = "Staff member updated successfully.";
        } else {
            $message = "Could not update staff member.";
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields to update the staff member.";
    }
}

/* LOAD ALL STAFF */
$staff_users = [];
$sql = "SELECT user_id, full_name, email, role
        FROM users
        WHERE role = 'Staff'
        ORDER BY full_name ASC";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $staff_users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - BugOut</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="adminusers-page">
  <div class="adminusers-grid">

    <!-- Logo -->
    <a href="admin.html" class="adminusers-card adminusers-logo-card">
      <img src="../assets/logo.png" alt="BugOut Logo">
    </a>

    <!-- Title -->
    <div class="adminusers-card adminusers-title-card">
      <h2>Manage Users</h2>
    </div>

    <!-- Add/Edit label -->
    <div class="adminusers-card adminusers-top-card">
      <h2><?php echo $edit_user ? "Edit Staff" : "Add Staff"; ?></h2>
    </div>

    <!-- Left side -->
    <div class="adminusers-card adminusers-side-card">
      <h2>Staff</h2>
      <img src="../assets/settings-icon.png" alt="Staff Icon" class="adminusers-side-img">
    </div>

    <!-- Form card -->
    <div class="adminusers-card adminusers-form-card">
      <div class="adminusers-content">

        <h2 class="adminusers-heading"><?php echo $edit_user ? "Update Staff Member" : "Create Staff Member"; ?></h2>

        <?php if (!empty($message)): ?>
          <p class="adminusers-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <form action="manageusers.php<?php echo $edit_user ? '?edit_id=' . urlencode($edit_user['user_id']) : ''; ?>" method="POST" class="adminusers-form">
          <?php if ($edit_user): ?>
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($edit_user['user_id']); ?>">
          <?php endif; ?>

          <div class="adminusers-row">
            <label for="full_name">Full Name:</label>
            <input
              type="text"
              name="full_name"
              id="full_name"
              value="<?php echo htmlspecialchars($edit_user['full_name'] ?? ''); ?>"
              placeholder="Enter full name"
              required
            >
          </div>

          <div class="adminusers-row">
            <label for="email">Email:</label>
            <input
              type="email"
              name="email"
              id="email"
              value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>"
              placeholder="Enter email"
              required
            >
          </div>

          <div class="adminusers-row">
            <label for="password">Password:</label>
            <input
              type="text"
              name="password"
              id="password"
              value="<?php echo htmlspecialchars($edit_user['password'] ?? ''); ?>"
              placeholder="Enter password"
              required
            >
          </div>

          <button type="submit" name="<?php echo $edit_user ? 'update_staff' : 'add_staff'; ?>" class="adminusers-btn">
            <?php echo $edit_user ? "Update Staff" : "Add Staff"; ?>
          </button>
        </form>

      </div>
    </div>

    <!-- Staff table -->
    <div class="adminusers-card adminusers-table-card">
      <div class="adminusers-table-wrapper">
        <table class="adminusers-table">
          <thead>
            <tr>
              <th>User ID</th>
              <th>Full Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Edit</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($staff_users)): ?>
              <?php foreach ($staff_users as $staff): ?>
                <tr>
                  <td><?php echo htmlspecialchars($staff['user_id']); ?></td>
                  <td><?php echo htmlspecialchars($staff['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($staff['email']); ?></td>
                  <td><?php echo htmlspecialchars($staff['role']); ?></td>
                  <td>
                    <a href="manageusers.php?edit_id=<?php echo urlencode($staff['user_id']); ?>" class="adminusers-link">
                      Edit
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5">No staff users found.</td>
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