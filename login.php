<?php
session_start();
include 'config.php';

// Initialize variables
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input data to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Check if fields are not empty
    if (empty($username) || empty($password)) {
        $error_message = "All fields are required";
    } else {
        // Query the database using prepared statements for better security
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            // Login successful - store user data in session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ];
            
            // Redirect to voting page
            header("Location: vote.php");
            exit;
        } else {
            $error_message = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Voting System</title>
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
            max-width: 400px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }
        
        .header h2 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .header p {
            font-size: 14px;
            margin-top: 5px;
            opacity: 0.8;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 12px 12px 35px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #3d8b40;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #fff5f5;
            color: var(--error-color);
            border: 1px solid #fed7d7;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            background-color: #f9fafb;
            border-top: 1px solid var(--border-color);
        }
        
        .footer p {
            color: #718096;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .footer a {
            color: var(--accent-color);
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me input {
            margin-right: 10px;
            width: 16px;
            height: 16px;
        }
        
        .remember-me label {
            color: #718096;
            font-size: 14px;
            cursor: pointer;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #CBD5E0;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #E2E8F0;
        }
        
        .divider span {
            padding: 0 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-vote-yea"></i> Voting System</h2>
            <p>Enter your credentials to access your account</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <span class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
                
                <div class="divider">
                    <span>or</span>
                </div>
                
                <div class="form-group">
                    <a href="forgot-password.php" style="display: block; text-align: center; color: var(--primary-color); text-decoration: none; font-size: 14px;">
                        <i class="fas fa-key"></i> Forgot your password?
                    </a>
                </div>
            </form>
        </div>
        
        <div class="footer">
            <p>Don't have an account?</p>
            <a href="register.php"><i class="fas fa-user-plus"></i> Create an account</a>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>