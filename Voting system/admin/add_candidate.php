<?php
session_start();
include '../config.php';

// Admin authentication check
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit(); // Use exit instead of die for clean exit
}

// Initialize variables
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if name and party are set in the POST request
    if (isset($_POST['name']) && isset($_POST['party']) && !empty($_POST['name'])) {
        $name = $_POST['name'];
        $party = $_POST['party'];

        // Sanitize inputs to prevent SQL injection
        $name = mysqli_real_escape_string($conn, $name);
        $party = mysqli_real_escape_string($conn, $party);
        
        // Insert candidate with party into the database
        $query = "INSERT INTO candidates (name, party) VALUES ('$name', '$party')";
        if (mysqli_query($conn, $query)) {
            $success_message = "Candidate '$name' added successfully!";
        } else {
            $error_message = "Error adding candidate: " . mysqli_error($conn);
        }
    } else {
        $error_message = "Please fill in both the name and party fields.";
    }
}

// Get available parties for dropdown (optional enhancement)
$parties = array();
$party_query = "SELECT DISTINCT party FROM candidates WHERE party != '' ORDER BY party";
$party_result = mysqli_query($conn, $party_query);
if ($party_result) {
    while ($row = mysqli_fetch_assoc($party_result)) {
        $parties[] = $row['party'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Candidate - Voting System</title>
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }
        
        .input-with-icon .form-control {
            padding-left: 40px;
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
        
        .btn i {
            margin-right: 8px;
        }
        
        .form-actions {
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
        
        .footer {
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            color: #718096;
            font-size: 14px;
        }
        
        .party-suggestion {
            margin-top: 10px;
            font-size: 14px;
            color: #718096;
        }
        
        .party-tags {
            display: flex;
            flex-wrap: wrap;
            margin-top: 8px;
            gap: 8px;
        }
        
        .party-tag {
            background-color: #edf2f7;
            color: #4a5568;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .party-tag:hover {
            background-color: #e2e8f0;
        }
        
        .form-note {
            margin-top: 5px;
            font-size: 13px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-user-plus"></i> Add Candidate</h2>
            <div class="admin-info">
                <span class="admin-badge"><i class="fas fa-user-shield"></i> Administrator</span>
            </div>
        </div>
        
        <div class="content-container">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <h3 class="section-heading"><i class="fas fa-clipboard-list"></i> Candidate Information</h3>
            
            <form method="post" autocomplete="off">
                <div class="form-group">
                    <label for="name">Candidate Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" class="form-control" required 
                               placeholder="Enter candidate's full name">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="party">Political Party</label>
                    <div class="input-with-icon">
                        <i class="fas fa-flag"></i>
                        <input type="text" id="party" name="party" class="form-control" required 
                               placeholder="Enter political party name" list="party-list">
                        <datalist id="party-list">
                            <?php foreach ($parties as $party): ?>
                                <option value="<?php echo htmlspecialchars($party); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <p class="form-note">The party name will be displayed next to the candidate's name on the ballot.</p>
                    
                    <?php if (!empty($parties)): ?>
                        <div class="party-suggestion">
                            <small>Existing parties in the system:</small>
                            <div class="party-tags">
                                <?php foreach (array_slice($parties, 0, 5) as $party): ?>
                                    <span class="party-tag" onclick="document.getElementById('party').value='<?php echo htmlspecialchars($party); ?>'">
                                        <?php echo htmlspecialchars($party); ?>
                                    </span>
                                <?php endforeach; ?>
                                <?php if (count($parties) > 5): ?>
                                    <span class="party-tag">+<?php echo count($parties) - 5; ?> more</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-actions">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Candidate
                    </button>
                </div>
            </form>
        </div>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Voting System Administration Panel</p>
        </div>
    </div>
    
    <script>
        // Optional enhancement: Auto-capitalize first letter of each word
        document.getElementById('name').addEventListener('input', function(e) {
            let words = e.target.value.split(' ');
            for (let i = 0; i < words.length; i++) {
                if (words[i].length > 0) {
                    words[i] = words[i][0].toUpperCase() + words[i].substring(1);
                }
            }
            e.target.value = words.join(' ');
        });
    </script>
</body>
</html>