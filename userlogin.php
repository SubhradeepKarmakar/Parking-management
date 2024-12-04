<?php
// userlogin.php
session_start();
$loginError = '';
$loginSuccess = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'parking_project');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, password FROM user_info WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $loginSuccess = true;
        } else {
            $loginError = "Incorrect password.";
        }
    } else {
        $loginError = "Username not found.";
    }
    $stmt->close();
    $conn->close();
    
    // Redirect if login is successful
    if ($loginSuccess) {
        echo '<script>
                setTimeout(function() {
                    window.location.href = "userhomepage.php";
                }, 2000); // 2 seconds delay
              </script>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-image: url('https://imgs.search.brave.com/pw4gwXlyS3zJk4EkeuFW9-cZoGRpqHBoCLBVo9ePUnY/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMudW5zcGxhc2gu/Y29tL3Bob3RvLTE1/OTMyODA0MDUxMDYt/ZTQzOGViZTkzZjVi/P2ZtPWpwZyZxPTYw/Jnc9MzAwMCZpeGxp/Yj1yYi00LjAuMyZp/eGlkPU0zd3hNakEz/ZkRCOE1IeHpaV0Z5/WTJoOE5ueDhjR0Z5/YTJsdVp5VXlNR3h2/ZEh4bGJud3dmSHd3/Zkh4OE1BPT0'); /* Replace with your background image URL */
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6); /* Dark overlay */
            z-index: 0;
        }
        .login-container {
            position: relative;
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 1;
        }
        .login-container h2 {
            font-size: 2rem;
            color: #343a40;
            margin-bottom: 20px;
        }
        .form-group label {
            float: left;
            font-weight: bold;
            color: #495057;
        }
        .form-control {
            border-radius: 5px;
        }
        .btn-primary {
            width: 100%;
            padding: 10px;
            font-size: 1.1rem;
            border-radius: 5px;
            margin-top: 10px;
        }
        .btn-secondary {
            width: 100%;
            padding: 10px;
            font-size: 1.1rem;
            border-radius: 5px;
            background-color: #6c757d;
            color: #fff;
            border: none;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .error-message, .success-message {
            font-weight: bold;
            margin-top: 10px;
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>
    
    <div class="login-container">
        <h2>User Login</h2>
        
        <?php if ($loginError): ?>
            <p class="error-message"><?php echo $loginError; ?></p>
        <?php endif; ?>
        
        <?php if ($loginSuccess): ?>
            <p class="success-message">Login successful! Redirecting...</p>
        <?php else: ?>
            <form action="userlogin.php" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <input type="submit" name="login" value="Login" class="btn btn-primary">
            </form>
            <button onclick="window.location.href='userregister.php'" class="btn btn-secondary">Register</button>
        <?php endif; ?>
    </div>
</body>
</html>
