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

// Receive the JSON data from the POST request
$jsonData = file_get_contents('php://input');

// Decode the JSON data into a PHP associative array
$data = json_decode($jsonData, true);

// Check if the data was decoded successfully
if ($data === null) {
    // If decoding fails, return an error
    http_response_code(400); // Set status code to 400 (Bad Request)
    echo json_encode(['error' => 'Invalid JSON data received']);
    exit();
}

// Check if 'penilai_id' and 'student_data' are provided and are not null
if (empty($data['penilai_id']) || empty($data['student_data'])) {
    http_response_code(400); // Set status code to 400 (Bad Request)
    echo json_encode(['error' => 'penilai_id and student_data are required and cannot be null']);
    exit();
}

$studentData = $data["student_data"];
$penilaiId = $data["penilai_id"];

// echo json_encode($studentData);

// Prepare an array to collect the responses
$responses = [];

foreach ($studentData as $studentId => $studentScores) {
    // Check if values are missing or empty, and set to NULL if true
    $presentasi = !empty($studentScores['presentasi']) ? (int)$studentScores['presentasi'] : 'NULL';
    $tanjaw = !empty($studentScores['tanjaw']) ? (int)$studentScores['tanjaw'] : 'NULL';
    $total = isset($studentScores['total']) ? (float)$studentScores['total'] : 'NULL';

    // SQL query to insert or update data into your table
    $sql = "
        INSERT INTO student_scores (student_id, penilai_id, presentasi, tanjaw, total) 
        VALUES ('$studentId', '$penilaiId', $presentasi, $tanjaw, $total)
        ON DUPLICATE KEY UPDATE
        presentasi = VALUES(presentasi),
        tanjaw = VALUES(tanjaw),
        total = VALUES(total);
    ";

    if ($conn->query($sql) === TRUE) {
        // Store success response for this student
        $responses[] = [
            'student_id' => $studentId,
            'presentasi' => $presentasi,
            'tanjaw' => $tanjaw,
            'total' => $total
        ];
    }
    else {
        // Error occurred while inserting data
        http_response_code(500); // Set status code to 500 (Internal Server Error)
        echo json_encode(['error' => 'Error inserting data for student ' . $studentId . ': ' . $conn->error]);
        break; // Break the loop on error
    }
}


/// If no error occurred, return a success response with the new values
if (empty($responses)) {
    http_response_code(400); // Set status code to 400 (Bad Request)
    echo json_encode(['error' => 'No data to insert.']);
} else {
    // Success response
    http_response_code(200); // Set status code to 200 (OK)
    echo json_encode([
        'status' => 'Data inserted/updated successfully',
        'students' => $responses
    ]);
}

// Close the connection
$conn->close();
?>
