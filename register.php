<?php
include 'config.php';
require 'vendor/autoload.php'; // Composer autoload for PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables
$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input data to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $user_email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($username) || empty($user_email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required";
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long";
    } else {
        // Check if email already exists
        $check_email = "SELECT * FROM users WHERE email = '$user_email'";
        $email_result = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($email_result) > 0) {
            $error_message = "Email already exists. Please use a different email.";
        } else {
            // All validations passed, proceed with registration
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password)
                    VALUES ('$username', '$user_email', '$hashed')";

            if (mysqli_query($conn, $sql)) {
                // Email notification using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->SMTPDebug = 0; // Or 3 for more detailed output
                    $mail->Username = 'sribalakumarr@gmail.com'; // replace with your Gmail
                    $mail->Password = 'sozj xzzp odpv aloo';   // replace with your Gmail App Password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    // Recipients
                    $mail->setFrom('sribalakumarr@gmail.com', 'Voting System');
                    $mail->addAddress($user_email, $username);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Registration Successful';
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                            <div style='text-align: center; padding: 10px; background-color: #f9f9f9; margin-bottom: 20px;'>
                                <h2 style='color: #4a5568;'>Welcome to the Voting System!</h2>
                            </div>
                            <p>Hello <b>$username</b>,</p>
                            <p>Thank you for registering with our Voting System. Your account has been successfully created.</p>
                            <p>You can now log in using your email and password.</p>
                            <div style='text-align: center; margin-top: 30px;'>
                                <a href='login.php' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Login Now</a>
                            </div>
                            <p style='margin-top: 30px; font-size: 12px; color: #666; text-align: center;'>
                                If you did not create this account, please contact support immediately.
                            </p>
                        </div>
                    ";

                    $mail->send();
                    $success_message = "Registration successful! Please check your email for confirmation.";
                } catch (Exception $e) {
                    $success_message = "Registration successful! You can now <a href='login.php'>login</a>.<br>Note: Email notification could not be sent.";
                }
            } else {
                $error_message = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Voting System</title>
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
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h2 {
            font-size: 24px;
            font-weight: 600;
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
        
        .alert-success {
            background-color: #f0fff4;
            color: var(--success-color);
            border: 1px solid #c6f6d5;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-vote-yea"></i> Voting System Registration</h2>
        </div>
        
        <div class="form-container">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php else: ?>
                <form method="post" autocomplete="off">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="username" name="username" class="form-control" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
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
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
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