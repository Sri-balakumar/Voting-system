<?php
session_start();
include '../config.php';

// Admin authentication
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit();
}

// Initialize messages
$success_message = "";
$error_message = "";

// Check if confirmation was submitted or if we need to show confirmation screen
if (isset($_POST['confirm_reset']) && $_POST['confirm_reset'] === 'yes') {
    // Reset votes in the candidates table
    $resetCandidates = mysqli_query($conn, "UPDATE candidates SET votes = 0");
    
    // Check if votes column exists in candidates table, if not, might need to clear votes table
    $votesTableExists = mysqli_query($conn, "SHOW TABLES LIKE 'votes'");
    if (mysqli_num_rows($votesTableExists) > 0) {
        $resetVotesTable = mysqli_query($conn, "TRUNCATE TABLE votes");
    } else {
        $resetVotesTable = true; // No votes table to reset
    }
    
    // Reset has_voted status in users table
    $usersTableExists = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($usersTableExists) > 0) {
        $resetUsers = mysqli_query($conn, "UPDATE users SET has_voted = 0");
    } else {
        $resetUsers = true; // No users table to reset
    }
    
    // Check if all operations were successful
    if ($resetCandidates && $resetVotesTable && $resetUsers) {
        $success_message = "All votes and voting status have been reset successfully.";
    } else {
        $error_message = "Reset failed: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Votes - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #1a2533;
            --accent-color: #e74c3c;
            --error-color: #e53e3e;
            --success-color: #38a169;
            --warning-color: #dd6b20;
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
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 25px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h2 {
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .header h2 i {
            margin-right: 10px;
        }
        
        .header .admin-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-right: 10px;
        }
        
        .content-container {
            background-color: white;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .section-heading {
            color: var(--secondary-color);
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        
        .section-heading i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #c0392b;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #0f172a;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #c05621;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        .alert-success {
            background-color: #f0fff4;
            color: var(--success-color);
            border: 1px solid #c6f6d5;
        }
        
        .alert-error {
            background-color: #fff5f5;
            color: var(--error-color);
            border: 1px solid #fed7d7;
        }
        
        .alert-warning {
            background-color: #fffaf0;
            color: var(--warning-color);
            border: 1px solid #feebc8;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            color: #718096;
            font-size: 14px;
        }
        
        .reset-info {
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--warning-color);
        }
        
        .reset-info h4 {
            color: var(--warning-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .reset-info h4 i {
            margin-right: 8px;
        }
        
        .reset-info p {
            color: #4a5568;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        
        .reset-info ul {
            margin: 10px 0 10px 20px;
            color: #4a5568;
        }
        
        .reset-info li {
            margin-bottom: 5px;
        }
        
        .reset-success {
            text-align: center;
            padding: 30px 0;
        }
        
        .reset-success i {
            font-size: 48px;
            color: var(--success-color);
            margin-bottom: 15px;
        }
        
        .reset-success h3 {
            color: var(--success-color);
            margin-bottom: 10px;
        }
        
        .reset-success p {
            color: #4a5568;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-redo-alt"></i> Reset Votes</h2>
            <div class="admin-info">
                <span class="admin-badge"><i class="fas fa-user-shield"></i> Administrator</span>
            </div>
        </div>
        
        <div class="content-container">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
                
                <div class="reset-success">
                    <i class="fas fa-check-circle"></i>
                    <h3>Reset Complete</h3>
                    <p>All votes have been cleared and user voting status has been reset.</p>
                    <p>The voting system is now ready for a new election.</p>
                </div>
            <?php elseif (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($success_message) && empty($error_message)): ?>
                <h3 class="section-heading"><i class="fas fa-exclamation-triangle"></i> Reset Confirmation</h3>
                
                <div class="reset-info">
                    <h4><i class="fas fa-exclamation-triangle"></i> Warning: This action cannot be undone</h4>
                    <p>You are about to reset all votes in the system. This will:</p>
                    <ul>
                        <li>Set all candidate vote counts to zero</li>
                        <li>Clear all voting records</li>
                        <li>Reset all user voting status flags</li>
                    </ul>
                    <p>This action is permanent and cannot be reversed. Please confirm that you want to proceed.</p>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Make sure to export any voting data you wish to keep before resetting.
                </div>
                
                <form method="post" action="">
                    <div class="actions">
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel & Back to Dashboard
                        </a>
                        <button type="submit" name="confirm_reset" value="yes" class="btn btn-warning">
                            <i class="fas fa-redo-alt"></i> Confirm Reset
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="actions" style="justify-content: center;">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Voting System Administration Panel</p>
        </div>
    </div>
</body>
</html>