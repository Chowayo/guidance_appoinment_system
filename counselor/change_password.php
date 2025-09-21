<?php
session_start();
include '../db/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $counselor_id = $_SESSION['counselor_id'];
    $new_password = trim($_POST['new_password']);

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in database
    $stmt = $conn->prepare("UPDATE counselor SET password=? WHERE counselor_id=?");
    $stmt->bind_param("si", $hashed_password, $counselor_id);

    if ($stmt->execute()) {
        // Password updated successfully
        echo "✅ Password updated successfully! <a href='../user/user_table.php'>Go to dashboard</a>";

        // Optional: automatic redirect after 3 seconds
        echo "<script>setTimeout(function(){ window.location.href = '../user/user_table.php'; }, 3000);</script>";
    } else {
        echo "❌ Error updating password: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>


<form method="POST">
    <input type="password" name="new_password" placeholder="Enter New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
    <button type="submit">Update Password</button>
</form>
