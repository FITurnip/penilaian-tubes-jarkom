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
    // Set the HTTP response code to 500 for server error (connection failed)
    http_response_code(500);
    // Return a JSON response with the error details
    echo json_encode(['success' => false, 'message' => 'Connection failed', 'error_code' => $conn->connect_errno]);
    exit();
}

// Get JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Extract values from the received data
$group_number = $data['group_number'];
$penilai_id = $data['penilai_id'];
$ide = $data['ide'];
$visual = $data['visual'];
$konfigurasi_ip_address = $data['konfigurasi_ip_address'];
$desain_router = $data['desain_router'];
$konfigurasi_vlan = $data['konfigurasi_vlan'];
$keamanan = $data['keamanan'];
$kesesuaian_kebutuhan_kabel_dan_perangkat = $data['kesesuaian_kebutuhan_kabel_dan_perangkat'];
$server = $data['server'];
$internet_of_things = $data['internet_of_things'];
$total_point = $data['total']; // Get the total_point

// Prepare the SQL query using ON DUPLICATE KEY UPDATE
$sql = "INSERT INTO scoring 
            (group_number, penilai_id, ide, visual, konfigurasi_ip_address, 
             desain_router, konfigurasi_vlan, keamanan, kesesuaian_kebutuhan_kabel, 
             `server`, internet_of_things, total_point) 
        VALUES ('$group_number', $penilai_id, $ide, $visual, $konfigurasi_ip_address, 
                $desain_router, $konfigurasi_vlan, $keamanan, $kesesuaian_kebutuhan_kabel_dan_perangkat, 
                $server, $internet_of_things, '$total_point')
        ON DUPLICATE KEY UPDATE 
            ide = $ide, 
            visual = $visual, 
            konfigurasi_ip_address = $konfigurasi_ip_address, 
            desain_router = $desain_router, 
            konfigurasi_vlan = $konfigurasi_vlan, 
            keamanan = $keamanan, 
            kesesuaian_kebutuhan_kabel = $kesesuaian_kebutuhan_kabel_dan_perangkat, 
            `server` = $server, 
            internet_of_things = $internet_of_things, 
            total_point = '$total_point'";

// echo json_encode($sql);

// Execute the query
if ($conn->query($sql)) {
    // If successful, set HTTP status code 200 (OK) and return success message
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Record inserted or updated successfully']);
} else {
    // Set HTTP status code to 500 for internal server error
    http_response_code(500);
    // Return error details in JSON response
    echo json_encode(['success' => false, 'message' => 'Failed to insert or update record', 'error_code' => $conn->errno, 'error_detail' => $conn->error]);
}

// Close the connection
$conn->close();
?>
