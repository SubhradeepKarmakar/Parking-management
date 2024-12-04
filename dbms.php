<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Project</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('https://imgs.search.brave.com/Eobga7BOY_8PHiftZYbWCIep6peDhmunAtA90WM0W8c/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWcu/ZnJlZXBpay5jb20v/cHJlbWl1bS1waG90/by9wcm9mZXNzaW9u/YWwtcGFya2luZy1s/b3Qtd2l0aC1uZWF0/bHktcGFya2VkLWNh/cnMtYXV0dW1uLWxh/bmRzY2FwaW5nLWNv/cnBvcmF0ZS1vZmZp/Y2UtcGFya3NfNDE2/MjU2LTc4MzM1Lmpw/Zz9zZW10PWFpc19o/eWJyaWQ'); /* Replace with a car background image URL */
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6); /* Dark overlay to improve text readability */
        }
        .container {
            position: relative;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.85); /* Semi-transparent background */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            z-index: 1;
        }
        .container h1 {
            font-size: 2.5rem;
            color: #343a40;
            margin-bottom: 20px;
        }
        .btn {
            width: 100%;
            font-size: 1.2rem;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }
        .btn-user {
            background-color: #007bff;
            color: white;
        }
        .btn-user:hover {
            background-color: #0056b3;
        }
        .btn-admin {
            background-color: #28a745;
            color: white;
        }
        .btn-admin:hover {
            background-color: #218838;
        }
        footer {
            position: absolute;
            bottom: 10px;
            width: 100%;
            text-align: center;
            color: #ffffff;
            font-size: 1rem;
            font-weight: bold;
            z-index: 1;
        }
        footer span {
            color: #ffeb3b;
        }
    </style>
</head>
<body>
    <div class="overlay"></div> <!-- Dark overlay for the background -->

    <div class="container">
        <h1>Welcome to Parking Management System</h1>
        <button class="btn btn-user" onclick="window.location.href='userlogin.php'">User Login/Register</button>
        <button class="btn btn-admin" onclick="window.location.href='login.php'">Admin Login</button>
    </div>

    <footer>
        Developed by <span>Subhradeep Karmakar</span> & <span>Imran Hussain</span>
    </footer>
</body>
</html>
