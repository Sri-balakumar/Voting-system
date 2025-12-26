<?php
// Start the session (if not already started)
session_start();

// Store username before destroying session (if it exists)
$username = isset($_SESSION['user']['username']) ? $_SESSION['user']['username'] : 'User';

// Clear all session data
session_unset();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Set a flag to show logout message - we'll redirect after showing message
$logged_out = true;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a5568;
            --secondary-color: #2d3748;
            --accent-color: #4CAF50;
            --error-color: #e53e3e;
            --success-color: #38a169;
            --light-bg: #f7fafc;
            --border-color: #e2e8f0;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 450px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            text-align: center;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 25px 20px;
        }
        
        .header h2 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .logout-icon {
            font-size: 60px;
            color: var(--accent-color);
            margin-bottom: 20px;
        }
        
        .logout-title {
            font-size: 24px;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .logout-message {
            font-size: 16px;
            color: #718096;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #3d8b40;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .footer {
            padding: 20px;
            background-color: #f9fafb;
            border-top: 1px solid var(--border-color);
        }
        
        .footer p {
            color: #718096;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-vote-yea"></i> Voting System</h2>
        </div>
        
        <div class="content">
            <div class="logout-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="logout-title">Successfully Logged Out</h2>
            <p class="logout-message">
                Thank you for using our Voting System, <?php echo htmlspecialchars($username); ?>.<br>
                You have been successfully logged out.
            </p>
            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i> Log In Again
            </a>
        </div>
        
        <div class="footer">
            <p>Don't have an account? <a href="register.php" style="color: var(--accent-color); text-decoration: none;">Register here</a></p>
        </div>
    </div>
    
    <script>
        // Redirect to login page after showing message
        setTimeout(function() {
            window.location.href = "login.php";
        }, 3000); // Redirect after 3 seconds
    </script>
</body>
</html>