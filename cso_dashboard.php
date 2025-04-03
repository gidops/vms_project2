<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "visitor");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all pending entries
$result = $conn->query("SELECT * FROM visitors WHERE status='pending'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Approvals</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .visitor-card {
            transition: transform 0.2s;
        }
        .visitor-card:hover {
            transform: translateY(-3px);
        }
        .visitor-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .badge-pending {
            background-color: #fd7e14;
        }
        .action-btn {
            width: 100px;
        }
        .empty-state {
            opacity: 0.7;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        Visitor Approvals
                        <span class="badge bg-primary ms-2">
                            <?php echo $result->num_rows; ?> Pending
                        </span>
                    </h1>
                    <div>
                        <button id="refreshBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card visitor-card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <img src="<?php echo htmlspecialchars($row['picture']); ?>" 
                                         alt="Visitor photo" 
                                         class="visitor-img me-3">
                                    <div>
                                        <h5 class="card-title mb-1"><?php echo htmlspecialchars($row['name']); ?></h5>
                                        <span class="badge bg-warning rounded-pill">Pending</span>
                                    </div>
                                </div>
                                
                                <ul class="list-unstyled mb-4">
                                    <li class="mb-2">
                                        <i class="fas fa-phone me-2 text-muted"></i>
                                        <?php echo htmlspecialchars($row['phone']); ?>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-envelope me-2 text-muted"></i>
                                        <?php echo htmlspecialchars($row['email']); ?>
                                    </li>
                                    <?php if (!empty($row['visit_date'])): ?>
                                    <li>
                                        <i class="fas fa-calendar-day me-2 text-muted"></i>
                                        <?php echo htmlspecialchars($row['visit_date']); ?>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                                
                                <div class="d-flex justify-content-between">
                                    <a href="approve.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-success action-btn">
                                        <i class="fas fa-check me-1"></i> Approve
                                    </a>
                                    <a href="deny.php?id=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger action-btn">
                                        <i class="fas fa-times me-1"></i> Deny
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 empty-state">
                <div class="mb-4">
                    <i class="fas fa-user-check fa-4x text-muted"></i>
                </div>
                <h3 class="h4 text-muted">No pending approvals</h3>
                <p class="text-muted">All visitor requests have been processed</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add click event listener to the refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            // Show loading spinner on the button
            const icon = this.querySelector('i');
            icon.classList.remove('fa-sync-alt');
            icon.classList.add('fa-spinner', 'fa-spin');
            
            // Reload the page after a short delay to show the spinner
            setTimeout(() => {
                location.reload();
            }, 300);
        });
    </script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>