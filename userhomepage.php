<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: userlogin.php'); // Redirects to the user login page if not logged in
    exit;
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "parking_project") or die("Connection failed!");

// Retrieve logged-in user's username
$username = $_SESSION['username'];

// Fetch booking information for the logged-in user
$sql = "SELECT * FROM booking WHERE Owner_name = '{$username}'";
$result = mysqli_query($conn, $sql) or die("Query Failed");

// Get total and available slots
$sql_booked_slots = "SELECT COUNT(*) AS booked_count FROM booking WHERE Status = 'Confirmed'";
$result_booked_slots = mysqli_query($conn, $sql_booked_slots) or die("Query Failed.");
$row_booked_slots = mysqli_fetch_assoc($result_booked_slots);
$booked_slots = $row_booked_slots['booked_count'];
$total_slots = 50; // Assuming total slots are 50
$available_slots = $total_slots - $booked_slots;

// Fetch previously used vehicles with status 'Out' for the logged-in user
$vehicles_sql = "SELECT DISTINCT Vehicle_name, Vehicle_number FROM booking WHERE Owner_name = '{$username}' AND Status = 'Out'";
$vehicles_result = mysqli_query($conn, $vehicles_sql) or die("Query Failed");

// Handle booking form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $previous_vehicle = $_POST['previous_vehicle'];
    if ($previous_vehicle == 'new') {
        // New vehicle booking request
        $vehicle_name = mysqli_real_escape_string($conn, $_POST['vehicle_name']);
        $vehicle_number = mysqli_real_escape_string($conn, $_POST['vehicle_number']);
    } else {
        // Previously used vehicle selected
        list($vehicle_name, $vehicle_number) = explode('|', $previous_vehicle);
        $vehicle_name = mysqli_real_escape_string($conn, $vehicle_name);
        $vehicle_number = mysqli_real_escape_string($conn, $vehicle_number);
    }

    $estimate_time = (int)$_POST['estimate_time'];

    // Check if an active booking exists for this vehicle with status 'Out'
    $check_sql = "SELECT * FROM booking WHERE Owner_name = '{$username}' AND Vehicle_number = '{$vehicle_number}' AND Status = 'Out'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        // Vehicle already has a booking, so update it
        $update_sql = "UPDATE booking 
                       SET Estimate_time = {$estimate_time}, Status = 'Pending', Booking_time = CURRENT_TIMESTAMP 
                       WHERE Owner_name = '{$username}' AND Vehicle_number = '{$vehicle_number}'";
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['message'] = 'Booking updated successfully!';
            header('Location: userhomepage.php'); // Refresh to avoid multiple form submissions
            exit;
        } else {
            $_SESSION['message'] = 'Error updating booking.';
        }
    } else {
        // No existing 'Out' booking for this vehicle, insert a new record if available slots exist
        if ($available_slots > 0) {
            $booking_sql = "INSERT INTO booking (Owner_name, Vehicle_name, Vehicle_number, Estimate_time, Status, Slot_number) 
                            VALUES ('{$username}', '{$vehicle_name}', '{$vehicle_number}', {$estimate_time}, 'Pending', NULL)";
            if (mysqli_query($conn, $booking_sql)) {
                $_SESSION['message'] = 'Booking request submitted!';
                header('Location: userhomepage.php'); // Refresh to avoid multiple form submissions
                exit;
            } else {
                $_SESSION['message'] = 'Error submitting booking request.';
            }
        } else {
            $_SESSION['message'] = 'No available slots!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Booking Status</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background: url('https://plus.unsplash.com/premium_photo-1661916866784-cdea580d93f7?w=900&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8cGFya2luZyUyMGxvdHxlbnwwfHwwfHx8MA%3D%3D') no-repeat center center fixed;
            background-size: cover;
            color: #ffffff;
        }
        .container {
            margin-top: 40px;
            background: rgba(0, 0, 0, 0.7); /* Semi-transparent background for readability */
            padding: 20px;
            border-radius: 8px;
        }
         /* Styling for headers */
         h1, h3 {
            color: #f8f9fa;
        }
        .card {
            background: rgba(255, 255, 255, 0.1); /* Transparent card background */
            color: #ffffff;
        }
             
        /* Table styling */
        .status-table th, .status-table td {
            color: #f8f9fa;
        }
        
        /* Style for error and success messages */
        .success-message {
            color: #28a745;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }
        
        /* Style logout button */
        .btn-logout {
            float: right;
            background-color: #dc3545;
            color: #ffffff;
            border: none;
        }
        
        /* Form styling */
        form label {
            color: #f8f9fa;
        }
        
        .form-control {
            background-color: rgba(255, 255, 255, 0.8);
            color: #343a40;
        }
        
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
    </style>
    <script>
        function toggleVehicleFields() {
            const selectedOption = document.getElementById('previous_vehicle').value;
            const newVehicleFields = document.getElementById('new_vehicle_fields');
            newVehicleFields.style.display = selectedOption === 'new' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

        <!-- Display success or error message -->
        <?php if (isset($_SESSION['message'])): ?>
            <p class="success-message"><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-header">Parking Slots Overview</div>
                    <div class="card-body">
                        <h3>Total space: <span>50</span></h3>
                        <?php if ($booked_slots != 50) { ?>
                            <h3>Parking Booked space: <span><?php echo $booked_slots; ?></span></h3>
                            <h3>Total Available space: <span><?php echo (50 - $booked_slots); ?></span></h3>
                        <?php } else { ?>
                            <h3>Sorry, no parking space available</h3>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h3>Your Booking Status</h3>
                <table class="table table-striped status-table">
                    <thead>
                        <tr>
                            <th>Vehicle Name</th>
                            <th>Vehicle Number</th>
                            <th>Slot Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Vehicle_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Vehicle_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Slot_number'] ? $row['Slot_number'] : 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['Status']); ?></td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="4" class="text-center">No bookings found.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="card mt-4">
            <div class="card-header">Book a New Parking Slot</div>
            <div class="card-body">
                <form action="" method="post" class="text-left">
                    <div class="form-group">
                        <label for="previous_vehicle">Select Vehicle</label>
                        <select id="previous_vehicle" name="previous_vehicle" class="form-control" onchange="toggleVehicleFields()">
                            <option value="new">New Vehicle</option>
                            <?php while ($vehicle = mysqli_fetch_assoc($vehicles_result)) { ?>
                                <option value="<?php echo htmlspecialchars($vehicle['Vehicle_name'] . '|' . $vehicle['Vehicle_number']); ?>">
                                    <?php echo htmlspecialchars($vehicle['Vehicle_name'] . ' - ' . $vehicle['Vehicle_number']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Show these fields only if "New Vehicle" is selected -->
                    <div id="new_vehicle_fields">
                        <div class="form-group">
                            <label for="vehicle_name">Vehicle Name</label>
                            <input type="text" name="vehicle_name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="vehicle_number">Vehicle Number</label>
                            <input type="text" name="vehicle_number" class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="estimate_time">Estimate Parking Time (minutes)</label>
                        <input type="number" name="estimate_time" class="form-control" required>
                    </div>
                    <input type="submit" class="btn btn-primary" value="Submit Booking">
                </form>
            </div>
        </div>

        <a href="logout.php" class="btn btn-danger btn-logout mt-4">Logout <?php echo htmlspecialchars($username); ?></a>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>

<?php
mysqli_close($conn); // Close the database connection at the end
?>
