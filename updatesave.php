<?php
$owner_name = $_POST['owner_name'];
$vehicle_name = $_POST['vehicle_name'];
$vehicle_number = $_POST['vehicle_number'];
$entry_date = $_POST['entry_date'];
$exit_date = $_POST['exit_date'];
$token_number = $_POST['Token'];

$conn = mysqli_connect("localhost", "root", "", "parking_project") or die("Connection failed!");

// Update the existing row instead of inserting a new one
$sql = "UPDATE vehicle_info SET Exit_date = '{$exit_date}' WHERE Token_number = '{$token_number}'";
$result = mysqli_query($conn, $sql) or die("Query failed");

// Update the Status in booking to "Out"
$sql2 = "UPDATE booking SET Status = 'Out' WHERE Slot_number = '{$token_number}'";
$result2 = mysqli_query($conn, $sql2) or die("Booking status update failed!");


header("location: http://localhost:8000/project/index.php");
mysqli_close($conn);
?>
