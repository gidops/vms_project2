<?php
session_start();
require_once 'db_connection.php';

// Fetch Security Manager's Data
// Assuming user is logged in and we have the security manager's ID stored in session.
$manager_id = $_SESSION['manager_id'];  // Make sure this is set when the user logs in.

$errors = [];
$success = '';

// Fetch Pending Approvals
$pendingQuery = "SELECT * FROM visitor_requests WHERE status = 'pending' ORDER BY created_at DESC";
$pendingResult = $conn->query($pendingQuery);

// Fetch Previous Approvals
$approvedQuery = "SELECT * FROM visitor_requests WHERE status = 'approved' ORDER BY created_at DESC";
$approvedResult = $conn->query($approvedQuery);

// Fetch Profile Data
$profileQuery = "SELECT * FROM security_managers WHERE id = ?";
$profileStmt = $conn->prepare($profileQuery);
$profileStmt->bind_param("i", $manager_id);
$profileStmt->execute();
$profileResult = $profileStmt->get_result();
$profile = $profileResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Manager Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            padding: 12px 15px;
            text-decoration: none;
            display: block;
        }
        .sidebar a:hover {
            background-color: #575d63;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        .card {
            border-radius: 10px;
        }
        .card-header {
            background-color: #343a40;
            color: white;
            font-weight: bold;
        }
        .btn-profile {
            background-color: #28a745;
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h4 class="text-center text-white">Security Manager</h4>
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">View Profile</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <div class="container">
            <h1 class="mb-4">Security Manager Dashboard</h1>
            
            <!-- Profile Section -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-user"></i> Profile
                        </div>
                        <div class="card-body">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($profile['name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
                            <p><strong>Role:</strong> <?php echo htmlspecialchars($profile['role']); ?></p>
                            <a href="edit_profile.php" class="btn btn-profile">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-clock"></i> Pending Approvals
                        </div>
                        <div class="card-body">
                            <?php if ($pendingResult->num_rows > 0): ?>
                                <ul class="list-group">
                                    <?php while($row = $pendingResult->fetch_assoc()): ?>
                                        <li class="list-group-item">
                                            <strong><?php echo htmlspecialchars($row['visitor_name']); ?></strong> 
                                            - <?php echo htmlspecialchars($row['created_at']); ?>
                                            <a href="approve_request.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm float-end">Approve</a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p>No pending approvals.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Previous Approvals Section -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-check-circle"></i> Previous Approvals
                        </div>
                        <div class="card-body">
                            <?php if ($approvedResult->num_rows > 0): ?>
                                <ul class="list-group">
                                    <?php while($row = $approvedResult->fetch_assoc()): ?>
                                        <li class="list-group-item">
                                            <strong><?php echo htmlspecialchars($row['visitor_name']); ?></strong> 
                                            - <?php echo htmlspecialchars($row['created_at']); ?>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p>No previous approvals.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
