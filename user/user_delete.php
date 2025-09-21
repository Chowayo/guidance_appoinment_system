<?php
include '../db/dbconn.php';
include 'user_update_delete_function.php';

if (isset($_GET['student_id'])) {
    $id = intval($_GET['student_id']);

    if (deleteRecord($id)) {
        echo "<div class='alert alert-success text-center'>Record deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Error deleting record!</div>";
    }
} else {
    echo "<div class='alert alert-warning text-center'>No ID provided!</div>";
}
?>

<br>
<div class="text-center">
    <a href="user_table.php" class="btn btn-primary">Back to Records</a>
</div>