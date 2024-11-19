<?php

include "connection.php";

header('Content-Type: application/json');

$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;

if ($student_id === null) {
    echo json_encode(["error" => "No student_id provided"]);
    exit;
}

$sql = "
    SELECT c.course_id, c.course_name, c.course_description
    FROM courses c
    JOIN course_enrollments ce ON c.course_id = ce.course_id
    WHERE ce.student_id = ?
";

$stmt = $connection->prepare($sql);

$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = [
            'course_id' => $row['course_id'],
            'course_name' => $row['course_name'],
            'course_description' => $row['course_description']
        ];
    }
    echo json_encode($courses);
} else {
    echo json_encode(["message" => "No courses found for this student"]);
}

$stmt->close();
$connection->close();

?>
