<?php
session_start();

// Check if counselor is logged in
if (!isset($_SESSION['counselor_id'])) {
    header("Location: ../counselor/counselor_login.php");
    exit;
}

include '../db/dbconn.php';

// Only show students of the counselor's grade level
$grade_level = $_SESSION['grade_level'];

$sql = "SELECT student_id, first_name, last_name, email, grade_level 
        FROM student 
        WHERE grade_level = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $grade_level);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Students</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?>!</h2>
            <a href="../counselor/counselor_logout.php" class="btn btn-danger">Logout</a>
        </div>

        <h3>Students - <?= htmlspecialchars($grade_level); ?></h3>

        <?php if ($result->num_rows > 0): ?>
            <div class="alert alert-info text-center">
                Total Records: <?= $result->num_rows; ?>
            </div>

            <table class="table table-striped table-bordered table-hover text-center shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Grade Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_id']); ?></td>
                            <td><?= htmlspecialchars($row['first_name']); ?></td>
                            <td><?= htmlspecialchars($row['last_name']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td><?= htmlspecialchars($row['grade_level']); ?></td>
                            <td>
                                <a href="user_update.php?student_id=<?= $row['student_id']; ?>" class="btn btn-sm btn-primary">Update</a>
                                <a href="user_delete.php?student_id=<?= $row['student_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning text-center">No records found for your grade level.</div>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>
</body>
</html>
