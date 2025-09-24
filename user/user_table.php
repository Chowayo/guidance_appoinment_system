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
    <link rel="stylesheet" href="../css/jquery.dataTables.min.css">
    <script src="../js/jquery-3.6.0.min.js"></script>
  <script src="../js/jquery.dataTables.min.js"></script>
  <script src="../js/sweetalert2@11.js"></script>
</head>
<body>
    <div class="container mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?>!</h2>
            <a href="../counselor/counselor_logout.php" class="btn btn-danger">Logout</a>
        </div>

        <h3>Students - <?= htmlspecialchars($grade_level); ?></h3>

        <?php if ($result->num_rows > 0): ?>
            

            <table class="table table-striped table-bordered table-hover text-center shadow-sm" id=studentTable>
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
                   
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning text-center">No records found for your grade level.</div>
        <?php endif; ?>

        <?php $conn->close(); ?>
    </div>

            <script>
$(document).ready(function(){
  var table = $('#studentTable').DataTable({
    "ajax": "fetch.php",
    "columns": [
      { "data": "student_id" },
      { "data": "first_name" },
      { "data": "last_name" },
      { "data": "email" },
      { "data": "grade_level" },
      {
        "data": null,
        "render": function(data, type, row){
          return `
            <a href="user_update.php?student_id=${row.student_id}" class="btn btn-sm btn-primary">Update</a>
            <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.student_id}">Delete</button>
          `;
        }
      }
    ]
  });

  // Delete button handler
  $('#studentTable').on('click', '.deleteBtn', function(){
    var student_id = $(this).data('id');
    Swal.fire({
      title: 'Are you sure?',
      text: "This will delete the record!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if(result.isConfirmed){
        $.ajax({
          url: "delete.php",
          type: "POST",
          data: { student_id: student_id },
          success: function(res){
            Swal.fire('Deleted!', res, 'success');
            table.ajax.reload();
          }
        });
      }
    });
  });
});

    </script>
</body>
</html>
