<?php

include "connection.php";

$sql = "SELECT * FROM courses";
$result = $connection->query($sql);

if ($result->num_rows > 0) {
    $courses = [];
    while($row = $result->fetch_assoc()) {
        $courses[] = [
            'course_id' => $row['course_id'],
            'course_name' => $row['course_name'],
            'course_description' => $row['course_description']
        ];
    }
    echo json_encode($courses);
} else {
    echo json_encode(["message" => "No courses found"]);
}

$connection->close();

?>