<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $owner_name = $_POST['owner_name'];
    $vehicle_name = $_POST['vehicle_name'];
    $vehicle_number = $_POST['vehicle_number'];
    $estimate_time = $_POST['estimate_time'];

    // Database connection
    $conn = mysqli_connect("localhost", "root", "", "parking_project") or die("Connection failed!");

    // Get the next available slot
    $slot_query = "SELECT Slot_number FROM booking WHERE Slot_number IS NOT NULL ORDER BY Slot_number DESC LIMIT 1";
    $slot_result = mysqli_query($conn, $slot_query);
    $slot_number = 1; // Default slot number
    if ($slot_result && mysqli_num_rows($slot_result) > 0) {
        $row = mysqli_fetch_assoc($slot_result);
        $slot_number = $row['Slot_number'] + 1; // Increment the last slot number
    }

    // Insert the data into the booking table
    $sql = "INSERT INTO booking (Owner_name, Vehicle_name, Vehicle_number, Estimate_time, Status, Slot_number) 
            VALUES ('{$owner_name}', '{$vehicle_name}', '{$vehicle_number}', '{$estimate_time}', 'Pending', '{$slot_number}')";

    // Execute the query
    $result = mysqli_query($conn, $sql) or die("Query Failed");

    // Redirect back to user homepage
    header("Location: http://localhost:8000/project/userhomepage.php");
    mysqli_close($conn);
}
?>
