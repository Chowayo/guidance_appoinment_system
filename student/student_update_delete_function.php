<?php
function deleteRecord($student_id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM student WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    return $stmt->execute();
}

function updateRecord($student_id, $first_name, $last_name, $email, $grade_level, $password)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE student 
                            SET first_name = ?, last_name = ?, email = ?, grade_level=?, password = ? 
                            WHERE student_id = ?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $grade_level, $password, $student_id);
    return $stmt->execute();
}