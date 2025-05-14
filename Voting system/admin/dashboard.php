<?php
// Start session and output buffering to avoid "headers already sent"
session_start();
ob_start(); // Start output buffering

include '../config.php';

// Admin authentication check
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit(); // Use exit instead of die for clean exit
}

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle Delete action
if (isset($_POST['delete'])) {
    $candidate_id = intval($_POST['candidate_id']);
    
    // 1. Delete the candidate
    $delete_query = "DELETE FROM candidates WHERE id = $candidate_id";
    if (mysqli_query($conn, $delete_query)) {
        // 2. Reorder the IDs to ensure sequential order
        // First, create a temporary table with new IDs
        mysqli_query($conn, "CREATE TEMPORARY TABLE temp_candidates SELECT * FROM candidates ORDER BY id");
        // Then truncate the original table
        mysqli_query($conn, "TRUNCATE TABLE candidates");
        // Reset AUTO_INCREMENT
        mysqli_query($conn, "ALTER TABLE candidates AUTO_INCREMENT = 1");
        // Insert the data back with new sequential IDs
        mysqli_query($conn, "INSERT INTO candidates (name) SELECT name FROM temp_candidates");
        // Drop the temporary table
        mysqli_query($conn, "DROP TEMPORARY TABLE IF EXISTS temp_candidates");
        
        // 3. Redirect to the current page (use the actual file name)
        $current_page = basename($_SERVER['PHP_SELF']);
        header("Location: $current_page");
        exit(); // Ensure no more code executes after redirect
    } else {
        $error_message = "Error deleting candidate: " . mysqli_error($conn);
    }
}

// Fetch candidates and their votes
$query = "SELECT * FROM candidates";
$res = mysqli_query($conn, $query);

if (!$res) {
    echo "Error: " . mysqli_error($conn);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #1a2533;
            --accent-color: #e74c3c;
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
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
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
        
        .header .admin-info {
            display: flex;
            align-items: center;
        }
        
        .header .admin-badge {
            background-color: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-right: 10px;
        }
        
        .dashboard-container {
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
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .data-table th {
            background-color: var(--primary-color);
            color: white;
            text-align: left;
            padding: 12px 15px;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .data-table tr:hover {
            background-color: #edf2f7;
        }
        
        .vote-count {
            font-weight: bold;
            color: var(--primary-color);
            background-color: #edf2f7;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
            min-width: 40px;
            text-align: center;
        }
        
        .actions {
            display: flex;
            justify-content: center;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            margin: 0 5px;
        }
        
        .btn-delete {
            background-color: var(--error-color);
            color: white;
            border: none;
        }
        
        .btn-delete:hover {
            background-color: #c53030;
        }
        
        .btn-primary {
            background-color: var(--accent-color);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #c0392b;
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: white;
            border: none;
        }
        
        .btn-secondary:hover {
            background-color: #0f172a;
        }
        
        .dashboard-actions {
            display: flex;
            justify-content: flex-start;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .dashboard-actions .btn {
            display: flex;
            align-items: center;
        }
        
        .dashboard-actions .btn i {
            margin-right: 8px;
        }
        
        .empty-state {
            padding: 40px;
            text-align: center;
            color: #718096;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #cbd5e0;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 16px;
        }
        
        .alert-error {
            background-color: #fff5f5;
            color: var(--error-color);
            border: 1px solid #fed7d7;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            color: #718096;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
            <div class="admin-info">
                <span class="admin-badge"><i class="fas fa-user-shield"></i> Administrator</span>
                <a href="logout.php" class="btn btn-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="dashboard-container">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <h3 class="section-heading"><i class="fas fa-chart-bar"></i> Voting Results</h3>
            
            <?php if (mysqli_num_rows($res) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Candidate Name</th>
                            <th>Votes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td>
                                    <?php
                                    if (isset($row['votes'])) {
                                        $votes = $row['votes'];
                                    } elseif (isset($row['vote_count'])) {
                                        $votes = $row['vote_count'];
                                    } else {
                                        $candidate_id = $row['id'];
                                        $vote_query = "SELECT COUNT(*) as vote_count FROM votes WHERE candidate_id = $candidate_id";
                                        $vote_result = mysqli_query($conn, $vote_query);

                                        if ($vote_result && $vote_row = mysqli_fetch_assoc($vote_result)) {
                                            $votes = $vote_row['vote_count'];
                                        } else {
                                            $votes = 0;
                                        }
                                    }
                                    ?>
                                    <span class="vote-count"><?php echo $votes; ?></span>
                                </td>
                                <td class="actions">
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="candidate_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-delete" 
                                                onclick="return confirm('Are you sure you want to delete this candidate?');">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <p>No candidates found in the system.</p>
                    <p>Use the "Add Candidate" button below to add candidates to the voting system.</p>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-actions">
                <a href="add_candidate.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add Candidate
                </a>
                <a href="reset_votes.php" class="btn btn-secondary" 
                   onclick="return confirm('This will reset all votes. Are you sure?');" style="margin-left: 10px;">
                    <i class="fas fa-redo"></i> Reset Votes
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Voting System Administration Panel</p>
        </div>
    </div>
</body>
</html>

<?php
// End output buffering and flush the output
ob_end_flush();
?>