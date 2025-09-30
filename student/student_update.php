<?php
include '../db/dbconn.php';
include 'student_update_delete_function.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $student_id   = intval($_POST['student_id']);
    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $email        = trim($_POST['email']);
    $grade_level  = trim($_POST['grade_level']);
    $password     = trim($_POST['password']);

    if (updateRecord($student_id, $first_name, $last_name, $email, $grade_level, $password)) {
        echo "<div class='alert alert-success text-center'><h1>Record updated <span style='color:green;'>successfully</h1><span></div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error updating record</div>";
    }

    echo "<div class='text-center mt-3'><a href='student_table.php' class='btn btn-primary'>Back to Records</a></div>";
    exit;
}

if (isset($_GET['student_id'])) {
    $id = intval($_GET['student_id']);
    $result = $conn->query("SELECT * FROM student WHERE student_id = $id");

    if ($result->num_rows > 0) {
        $row = $result->fetch_object();
    } else {
        echo "<div class='alert alert-warning text-center'>Record not found</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger text-center'>No ID provided</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Record</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #faedb5ff, #a4ffb8ff);
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-size: cover;
            background-attachment: fixed;
        }
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .btn-success:hover {
            background-color: #28a745;
            transform: scale(1.03);
            transition: 0.2s ease;
        }
    </style>
</head>

<body>
    <div class="container mt-5 d-flex justify-content-center">
        <div class="card shadow-lg p-4 rounded-4" style="max-width: 500px; width: 100%;">
            <h2 class="text-center mb-4 fw-bold text-success">Update Record</h2>

            <form method="POST">
                <input type="hidden" name="student_id" value="<?= $row->student_id ?>">

                <div class="mb-3">
                    <label class="form-label">First Name:</label>
                    <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($row->first_name) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Last Name:</label>
                    <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($row->last_name) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row->email) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Grade Level:</label>
                    <select name="grade_level" class="form-control" required>
                        <option value="Grade 1"  <?= ($row->grade_level == 'Grade 1')  ? 'selected' : '' ?>>Grade 1</option>
                        <option value="Grade 2"  <?= ($row->grade_level == 'Grade 2')  ? 'selected' : '' ?>>Grade 2</option>
                        <option value="Grade 3"  <?= ($row->grade_level == 'Grade 3')  ? 'selected' : '' ?>>Grade 3</option>
                        <option value="Grade 4"  <?= ($row->grade_level == 'Grade 4')  ? 'selected' : '' ?>>Grade 4</option>
                        <option value="Grade 5"  <?= ($row->grade_level == 'Grade 5')  ? 'selected' : '' ?>>Grade 5</option>
                        <option value="Grade 6"  <?= ($row->grade_level == 'Grade 6')  ? 'selected' : '' ?>>Grade 6</option>
                        <option value="Grade 7"  <?= ($row->grade_level == 'Grade 7')  ? 'selected' : '' ?>>Grade 7</option>
                        <option value="Grade 8"  <?= ($row->grade_level == 'Grade 8')  ? 'selected' : '' ?>>Grade 8</option>
                        <option value="Grade 9"  <?= ($row->grade_level == 'Grade 9')  ? 'selected' : '' ?>>Grade 9</option>
                        <option value="Grade 10" <?= ($row->grade_level == 'Grade 10') ? 'selected' : '' ?>>Grade 10</option>
                        <option value="Grade 11" <?= ($row->grade_level == 'Grade 11') ? 'selected' : '' ?>>Grade 11</option>
                        <option value="Grade 12" <?= ($row->grade_level == 'Grade 12') ? 'selected' : '' ?>>Grade 12</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password:</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password (leave blank to keep current)">
                </div>

                <button type="submit" class="btn btn-success w-100">Update</button>
            </form>

            <div class="text-center mt-3">
                <a href="student_table.php" class="btn btn-outline-secondary">Back to Records</a>
            </div>
        </div>
    </div>
</body>
</html>
