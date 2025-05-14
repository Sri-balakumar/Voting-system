<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Status messages
$success_message = "";
$error_message = "";

// Check if 'has_voted' column exists
$check_column_query = "SHOW COLUMNS FROM users LIKE 'has_voted'";
$column_result = mysqli_query($conn, $check_column_query);

if (mysqli_num_rows($column_result) == 0) {
    // Column doesn't exist, add it
    $add_column_query = "ALTER TABLE users ADD has_voted TINYINT(1) DEFAULT 0";
    if (!mysqli_query($conn, $add_column_query)) {
        $error_message = "System error: Failed to set up voting system. Please contact support.";
    }
}

// Fetch updated user info from DB to check has_voted status accurately
$stmt = mysqli_prepare($conn, "SELECT has_voted FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    $error_message = "System error: Unable to verify voting status.";
} else {
    $row = mysqli_fetch_assoc($result);
    $has_voted = $row['has_voted'];
    
    // Update session with current voting status
    $_SESSION['user']['has_voted'] = $has_voted;
}

// Process vote submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error_message) && !$has_voted) {
    if (isset($_POST['candidate_id'])) {
        $cid = intval($_POST['candidate_id']);

        // Check if candidate ID is valid using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id FROM candidates WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $cid);
        mysqli_stmt_execute($stmt);
        $check = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check) === 0) {
            $error_message = "Invalid candidate selected.";
        } else {
            // Start transaction for vote integrity
            mysqli_begin_transaction($conn);
            
            try {
                // Update candidate votes using prepared statement
                $vote_stmt = mysqli_prepare($conn, "UPDATE candidates SET votes = votes + 1 WHERE id = ?");
                mysqli_stmt_bind_param($vote_stmt, "i", $cid);
                $vote_result = mysqli_stmt_execute($vote_stmt);
                
                // Update user voting status using prepared statement
                $user_stmt = mysqli_prepare($conn, "UPDATE users SET has_voted = 1 WHERE id = ?");
                mysqli_stmt_bind_param($user_stmt, "i", $user_id);
                $user_result = mysqli_stmt_execute($user_stmt);
                
                if ($vote_result && $user_result) {
                    mysqli_commit($conn);
                    // Update session to reflect voting status
                    $_SESSION['user']['has_voted'] = 1;
                    $has_voted = 1;
                    $success_message = "Thank you for voting! Your vote has been recorded successfully.";
                } else {
                    throw new Exception("Vote update failed");
                }
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error_message = "Voting failed. Please try again later.";
            }
        }
    } else {
        $error_message = "Please select a candidate to vote.";
    }
}

// Fetch candidates using prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM candidates ORDER BY name ASC");
mysqli_stmt_execute($stmt);
$candidates_result = mysqli_stmt_get_result($stmt);

if (!$candidates_result) {
    $error_message = "System error: Unable to retrieve candidate list.";
}

// Get candidate count for UI
$candidate_count = mysqli_num_rows($candidates_result);

// Get total votes (for showing results when user has voted)
$total_votes_query = mysqli_query($conn, "SELECT SUM(votes) as total FROM candidates");
$total_votes_row = mysqli_fetch_assoc($total_votes_query);
$total_votes = $total_votes_row['total'] ?: 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a5568;
            --secondary-color: #2d3748;
            --accent-color: #4CAF50;
            --accent-dark: #3d8b40;
            --error-color: #e53e3e;
            --success-color: #38a169;
            --light-bg: #f7fafc;
            --border-color: #e2e8f0;
            --highlight-color: #ebf8ff;
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
            max-width: 800px;
            margin: 0 auto;
            padding-top: 20px;
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .card-header h2 {
            font-size: 24px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 30px;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            margin-bottom: 20px;
        }
        
        .navbar .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .navbar .logo i {
            margin-right: 10px;
        }
        
        .navbar .user-menu {
            display: flex;
            align-items: center;
        }
        
        .navbar .user-menu .username {
            margin-right: 20px;
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .navbar .user-menu .logout-btn {
            padding: 8px 15px;
            background-color: #f56565;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
        }
        
        .navbar .user-menu .logout-btn i {
            margin-right: 5px;
        }
        
        .navbar .user-menu .logout-btn:hover {
            background-color: #e53e3e;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 16px;
            text-align: center;
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
        
        .voting-title {
            font-size: 22px;
            color: var(--secondary-color);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .voting-subtitle {
            font-size: 16px;
            color: #718096;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .candidate-list {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .candidate-card {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        
        .candidate-card:hover {
            border-color: var(--accent-color);
            background-color: var(--highlight-color);
        }
        
        .candidate-card.selected {
            border-color: var(--accent-color);
            background-color: var(--highlight-color);
        }
        
        .candidate-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .candidate-info {
            display: flex;
            align-items: center;
        }
        
        .candidate-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary-color);
            margin-right: 15px;
        }
        
        .candidate-details {
            flex-grow: 1;
        }
        
        .candidate-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 5px;
        }
        
        .candidate-party {
            font-size: 14px;
            color: #718096;
        }
        
        .vote-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .vote-btn i {
            margin-right: 10px;
        }
        
        .vote-btn:hover {
            background-color: var(--accent-dark);
        }
        
        .vote-btn:disabled {
            background-color: #cbd5e0;
            cursor: not-allowed;
        }
        
        .results-container {
            margin-top: 20px;
        }
        
        .results-title {
            font-size: 22px;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .result-item {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 15px;
        }
        
        .result-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .result-name {
            font-weight: 600;
            color: var(--secondary-color);
        }
        
        .result-votes {
            color: var(--primary-color);
        }
        
        .progress-bar {
            height: 12px;
            background-color: #edf2f7;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background-color: var(--accent-color);
            border-radius: 6px;
            transition: width 0.6s ease;
        }
        
        .thank-you-card {
            text-align: center;
            padding: 40px 20px;
        }
        
        .thank-you-icon {
            font-size: 50px;
            color: var(--accent-color);
            margin-bottom: 20px;
        }
        
        .thank-you-title {
            font-size: 24px;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }
        
        .thank-you-message {
            font-size: 16px;
            color: #718096;
            margin-bottom: 30px;
        }
        
        .back-to-results {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .back-to-results:hover {
            background-color: var(--secondary-color);
        }
        
        @media (max-width: 768px) {
            .candidate-list {
                grid-template-columns: 1fr;
            }
        }
        
        /* Add this to change the grid based on candidate count */
        <?php if ($candidate_count > 3): ?>
        .candidate-list {
            grid-template-columns: repeat(2, 1fr);
        }
        @media (max-width: 640px) {
            .candidate-list {
                grid-template-columns: 1fr;
            }
        }
        <?php else: ?>
        .candidate-list {
            grid-template-columns: 1fr;
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="#" class="logo">
                <i class="fas fa-vote-yea"></i>
                Voting System
            </a>
            <div class="user-menu">
                <span class="username">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($user['username']); ?>
                </span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </nav>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($has_voted && !empty($success_message)): ?>
            <!-- Thank you card for recent voters -->
            <div class="card">
                <div class="thank-you-card">
                    <div class="thank-you-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="thank-you-title">Thank You for Voting!</h2>
                    <p class="thank-you-message">Your vote has been recorded successfully. Every vote counts!</p>
                    <a href="#results" class="back-to-results">View Election Results</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($has_voted): ?>
            <!-- Display voting results -->
            <div class="card" id="results">
                <div class="card-header">
                    <h2><i class="fas fa-chart-bar"></i> Election Results</h2>
                </div>
                <div class="card-body">
                    <div class="results-container">
                        <p class="voting-subtitle">Current standings based on <?php echo $total_votes; ?> total votes</p>
                        
                        <?php 
                        mysqli_data_seek($candidates_result, 0);
                        while ($candidate = mysqli_fetch_assoc($candidates_result)): 
                            $percentage = $total_votes > 0 ? round(($candidate['votes'] / $total_votes) * 100, 1) : 0;
                        ?>
                            <div class="result-item">
                                <div class="result-header">
                                    <span class="result-name"><?php echo htmlspecialchars($candidate['name']); ?></span>
                                    <span class="result-votes"><?php echo $candidate['votes']; ?> votes (<?php echo $percentage; ?>%)</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Display voting form -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-vote-yea"></i> Cast Your Vote</h2>
                </div>
                <div class="card-body">
                    <h3 class="voting-title">Choose Your Candidate</h3>
                    <p class="voting-subtitle">Select one candidate from the options below</p>
                    
                    <form method="post" id="voting-form">
                        <div class="candidate-list">
                            <?php if ($candidate_count === 0): ?>
                                <p style="text-align: center; color: #718096;">No candidates available at this time.</p>
                            <?php else: ?>
                                <?php while ($candidate = mysqli_fetch_assoc($candidates_result)): ?>
                                    <label class="candidate-card" data-id="<?php echo $candidate['id']; ?>">
                                        <input type="radio" name="candidate_id" value="<?php echo $candidate['id']; ?>" required>
                                        <div class="candidate-info">
                                            <div class="candidate-avatar">
                                                <?php echo strtoupper(substr($candidate['name'], 0, 1)); ?>
                                            </div>
                                            <div class="candidate-details">
                                                <h4 class="candidate-name"><?php echo htmlspecialchars($candidate['name']); ?></h4>
                                                <?php if (isset($candidate['party']) && !empty($candidate['party'])): ?>
                                                    <p class="candidate-party"><?php echo htmlspecialchars($candidate['party']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                        
                        <button type="submit" class="vote-btn" <?php echo $candidate_count === 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-check-circle"></i> Submit Your Vote
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Make the entire candidate card clickable
        document.addEventListener('DOMContentLoaded', function() {
            const candidateCards = document.querySelectorAll('.candidate-card');
            
            candidateCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selected class from all cards
                    candidateCards.forEach(c => c.classList.remove('selected'));
                    
                    // Add selected class to clicked card
                    this.classList.add('selected');
                    
                    // Check the radio button
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });
        });
    </script>
</body>
</html>