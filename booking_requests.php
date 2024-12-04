<?php
session_start();

// Redirect if not logged in
if(!isset($_SESSION["username"])){
   header("Location: login.php");
   exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "parking_project") or die("Connection failed!");

// Handle booking approval with slot assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_booking'])) {
    $booking_id = $_POST['booking_id'];
    $slot_number = $_POST['slot_number'];

    // Fetch booking details for the approved request
    $booking_details_query = "SELECT Owner_name, Vehicle_name, Vehicle_number FROM booking WHERE id='$booking_id'";
    $booking_details_result = mysqli_query($conn, $booking_details_query);
    if ($booking_details_result && mysqli_num_rows($booking_details_result) > 0) {
        $booking_details = mysqli_fetch_assoc($booking_details_result);
        $owner_name = $booking_details['Owner_name'];
        $vehicle_name = $booking_details['Vehicle_name'];
        $vehicle_number = $booking_details['Vehicle_number'];


        // // Check if the entry already exists in vehicle_info
        // $check_entry_query = "SELECT * FROM vehicle_info 
        //                       WHERE Token_number = '$slot_number' AND Exit_date = '0000-00-00 00:00:00'";
        // $check_entry_result = mysqli_query($conn, $check_entry_query);

        // if (mysqli_num_rows($check_entry_result) == 0) {
        //     // Only insert if no matching active entry is found
        //     $insert_vehicle_info_sql = "INSERT INTO vehicle_info (Owner_name, Vehicle_name, Vehicle_number, Entry_date, Exit_date, Token_number)
        //                                 VALUES ('$owner_name', '$vehicle_name', '$vehicle_number', NOW(), '0000-00-00 00:00:00', '$slot_number')";
        //     if (mysqli_query($conn, $insert_vehicle_info_sql)) {
        //         echo "<script>alert('Booking approved, slot assigned, and entry added to vehicle info!');</script>";
        //     } else {
        //         echo "<script>alert('Error adding entry to vehicle info.');</script>";
        //     }
        // } else {
        //     echo "<script>alert('Duplicate entry found! This slot is already assigned to an active vehicle.');</script>";
        // }




        // Update the booking status to 'Confirmed' and assign the slot number
        $update_sql = "UPDATE booking SET Status='Confirmed', Slot_number='$slot_number' WHERE id='$booking_id'";
        if (mysqli_query($conn, $update_sql)) {
            // Insert the approved booking into the vehicle_info table
            $insert_vehicle_info_sql = "INSERT INTO vehicle_info (Owner_name, Vehicle_name, Vehicle_number, Entry_date, Exit_date, Token_number)
                                        VALUES ('$owner_name', '$vehicle_name', '$vehicle_number', NOW(), '0000-00-00 00:00:00', '$slot_number')";
            if (mysqli_query($conn, $insert_vehicle_info_sql)) {
                echo "<script>alert('Booking approved, slot assigned, and entry added to vehicle info!');</script>";
            } else {
                echo "<script>alert('Error adding entry to vehicle info.');</script>";
            }
        } else {
            echo "<script>alert('Error updating booking status.');</script>";
        }
    }

    // Redirect to prevent resubmission on page refresh (PRG pattern)
    header("Location: booking_requests.php");
    exit();
}

// Fetch all pending booking requests
$booking_query = "SELECT * FROM booking WHERE Status='Pending'";
$booking_result = mysqli_query($conn, $booking_query);

// Fetch occupied slots
$occupied_slots_query = "SELECT Token_number FROM vehicle_info WHERE Exit_date = '0000-00-00 00:00:00'";
$occupied_slots_result = mysqli_query($conn, $occupied_slots_query);
$occupied_slots = [];
while ($row = mysqli_fetch_assoc($occupied_slots_result)) {
    $occupied_slots[] = $row['Token_number'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Requests</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<style>
        /* Set background image */
        body {
            background: url('https://plus.unsplash.com/premium_photo-1661962915138-c10a03d4ae28?w=1000&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8cGFya2luZyUyMGxvdHxlbnwwfHwwfHx8MA%3D%3D') no-repeat center center fixed;
            background-size: cover;
        }
        .container {
            background: rgba(255, 255, 255, 0.8); /* Transparent white background */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .table {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            overflow: hidden;
        }
        .table thead {
            background: rgba(0, 123, 255, 0.8);
            color: white;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #007bff;
            font-weight: bold;
        }
        .btn {
            background: #007bff;
            border: none;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>






<body>
<div class="container mt-5">
<div class="mb-3">
        <a href="index.php" class="btn btn-primary">Return to Home</a>
    </div>
    <h2>Booking Requests</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Owner Name</th>
                <th>Vehicle Name</th>
                <th>Vehicle Number</th>
                <th>Estimate Time (mins)</th>
                <th>Booking Time</th>
                <th>Status</th>
                <th>Slot Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($booking_result) > 0) {
                while ($row = mysqli_fetch_assoc($booking_result)) { ?>
                    <tr>
                        <td><?php echo $row['Owner_name']; ?></td>
                        <td><?php echo $row['Vehicle_name']; ?></td>
                        <td><?php echo $row['Vehicle_number']; ?></td>
                        <td><?php echo $row['Estimate_time']; ?> mins</td>
                        <td><?php echo $row['Booking_time']; ?></td>
                        <td><?php echo $row['Status']; ?></td>
                        <form action="" method="post">
                        <td>
    <select name="slot_number" required class="form-control">
        <option value="" disabled selected>Select Slot</option>
        <?php 
        // Generate slot numbers from 1 to 50 and exclude occupied slots
        for ($i = 1; $i <= 50; $i++) {
            if (!in_array($i, $occupied_slots)) {
                echo "<option value='$i'>$i</option>";
            }
        }
        ?>
    </select>
</td>

                            <td>
                                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="approve_booking" class="btn btn-success">Approve</button>
                            </td>
                        </form>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="8" class="text-center">No pending booking requests.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h2>All Active Vehicle Entries (Vehicles Not Exited Yet)</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Owner Name</th>
                <th>Vehicle Name</th>
                <th>Vehicle Number</th>
                <th>Entry Date</th>
                <th>Exit Date</th>
                <th>Token Number</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch only active vehicles with Exit_date '0000-00-00 00:00:00'
            $vehicle_info_query = "SELECT * FROM vehicle_info WHERE Exit_date = '0000-00-00 00:00:00'";
            $vehicle_info_result = mysqli_query($conn, $vehicle_info_query);
            if(mysqli_num_rows($vehicle_info_result) > 0) {
                while ($row = mysqli_fetch_assoc($vehicle_info_result)) { ?>
                    <tr>
                        <td><?php echo $row['Owner_name']; ?></td>
                        <td><?php echo $row['Vehicle_name']; ?></td>
                        <td><?php echo $row['Vehicle_number']; ?></td>
                        <td><?php echo $row['Entry_date']; ?></td>
                        <td><?php echo $row['Exit_date']; ?></td>
                        <td><?php echo $row['Token_number']; ?></td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="6" class="text-center">No active vehicle entries found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php mysqli_close($conn); ?>
