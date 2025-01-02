<?php
// Database connection settings
$servername = "localhost"; 
$username = "aspraklabjarkom"; 
$password = "";
$dbname = "student_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Return error in JSON format if connection fails
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// SQL query to fetch student_id, student_name from students table and total from student_scores table
$sql = "
    SELECT 
    s.student_id, 
    s.student_name,

    -- Calculate total_score_avg (sum of ss.total divided by the count of student_scores for the student)
    COALESCE(
        (SELECT SUM(ss.total) FROM student_scores ss WHERE ss.student_id = s.student_id), 0
    ) / 
    NULLIF(
        (SELECT COUNT(*) FROM student_scores ss WHERE ss.student_id = s.student_id), 0
    ) AS total_score_avg,

    -- Calculate total_point_avg (sum of sc.total_point divided by the count of student_groups for the student)
    COALESCE(
        (SELECT SUM(sc.total_point) FROM scoring sc 
         JOIN student_groups sg ON sc.group_number = sg.group_number 
         WHERE sg.student_id = s.student_id), 0
    ) / 
    NULLIF(
        (SELECT COUNT(*) FROM student_groups sg WHERE sg.student_id = s.student_id), 0
    ) AS total_point_avg

FROM students s;
";

$result = $conn->query($sql);

// Check if there are any results
if ($result->num_rows > 0) {
    // Fetch and return the results as JSON
    $students = [];
    while ($row = $result->fetch_assoc()) {
        // Add the student data to the array
        $students[] = [
            'student_id' => $row['student_id'],
            'student_name' => $row['student_name'],
            'total_perorang' => $row['total_score_avg'],
            'total_kelompok' => $row['total_point_avg'],
            'total' => ($row['total_score_avg'] + $rot['total_point_avg']) / 2
        ];
    }

    // Set content type to JSON
    header('Content-Type: application/json');
    echo json_encode($students);
} else {
    // If no students found, return an empty array
    echo json_encode([]);
}

// Close the connection
$conn->close();
?>
    