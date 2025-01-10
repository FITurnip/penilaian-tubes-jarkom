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
    g.group_number, 
    s.student_id, 
    s.student_name, 
    s.angkatan, 
    s.class, 
    AVG(student_scores.total) AS total_score_avg,
    AVG(scoring.total_point) AS total_point_avg
FROM student_groups AS g
JOIN students AS s ON g.student_id = s.student_id
JOIN student_scores ON s.student_id = student_scores.student_id
JOIN scoring ON g.group_number = scoring.group_number  
GROUP BY g.group_number, s.student_id, s.student_name, s.angkatan, s.class  
ORDER BY s.student_id, s.angkatan, s.class, g.group_number;
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
            'total' => ($row['total_score_avg'] + $row['total_point_avg']) / 2,
            'class' => $row['class'],
            'angkatan' => $row['angkatan'],
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
