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

// SQL query to fetch data
$sql = "
    SELECT g.group_number, s.student_id, s.student_name, s.angkatan, s.class
    FROM `student_groups` as g
    JOIN students as s ON g.student_id = s.student_id
    ORDER BY s.angkatan, s.class, g.group_number, s.student_id
";
$result = $conn->query($sql);

// Check for query errors
if ($result === false) {
    // Return error in JSON format if query fails
    echo json_encode(["error" => "Error in SQL query: " . $conn->error]);
    $conn->close(); // Close the connection after query failure
    exit();
}

// Initialize an array to store the grouped data
$groupedData = [];

// Fetch and group the data
while ($row = $result->fetch_assoc()) {
    $angkatan = $row['angkatan'];
    $class = $row['class'];
    $group_number = $row['group_number'];

    $student = [
        'student_id' => $row['student_id'],
        'student_name' => $row['student_name']
    ];

    // If the angkatan doesn't exist, create it
    if (!isset($groupedData[$angkatan])) {
        $groupedData[$angkatan] = [];
    }

    // If the class doesn't exist under this angkatan, create it as an array
    if (!isset($groupedData[$angkatan][$class])) {
        $groupedData[$angkatan][$class] = [];
    }

    // If the group number doesn't exist under this angkatan and class, create it
    $foundGroup = false;
    foreach ($groupedData[$angkatan][$class] as &$group) {
        if ($group['group_number'] == $group_number) {
            // Append student to this group
            $group['students'][] = $student;
            $foundGroup = true;
            break;
        }
    }

    // If group number is not found, create a new group
    if (!$foundGroup) {
        $groupedData[$angkatan][$class][] = [
            'group_number' => $group_number,
            'students' => [$student]
        ];
    }
}

// Close the connection
$conn->close();

// Result
if ($result->num_rows > 0) {
    // Set content type to JSON
    header('Content-Type: application/json');
    
    // Encode data as JSON and output
    echo json_encode($groupedData);
} else {
    // If no results, return an empty array
    echo json_encode([]);
}
?>
