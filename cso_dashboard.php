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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .visitor-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 1.5rem;
        }

        .visitor-item {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eaeaea;
        }

        .visitor-item:last-child {
            border-bottom: none;
        }

        .visitor-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 1rem;
        }

        .visitor-info {
            flex-grow: 1;
            min-width: 200px;
        }

        .visitor-info h5 {
            margin-bottom: 0.3rem;
            font-weight: 600;
            color: #007570;
        }

        .visitor-info small {
            display: block;
            color: #555;
        }

        .badge-pending {
            background-color: #fd7e14;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        @media (min-width: 768px) {
            .action-buttons {
                margin-top: 0;
            }
        }

        .empty-state {
            text-align: center;
            padding: 4rem 1rem;
            color: #777;
        }

        .empty-state i {
            color: #ccc;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .header-bar h1 {
            font-size: 1.5rem;
            color: #007570;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="visitor-list">
        <div class="header-bar">
            <h1>
                Visitor Approvals
                <span class="badge bg-primary ms-2">
                    <?php echo $result->num_rows; ?> Pending
                </span>
            </h1>
            <button class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="visitor-item">
    <!-- <img src="<?php echo htmlspecialchars($row['picture']); ?>" alt="Visitor" class="visitor-img"> -->

    <div class="visitor-info">
        <h5><?php echo htmlspecialchars($row['name']); ?></h5>
        <span class="badge bg-warning text-dark mb-1">Pending</span>
        <small><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($row['phone']); ?></small>
        <small><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?></small>
        <small><i class="fas fa-user-shield me-1"></i> Visiting: <?php echo htmlspecialchars($row['host_name']); ?></small>
        <?php if (!empty($row['visit_date'])): ?>
            <small><i class="fas fa-calendar-day me-1"></i> <?php echo htmlspecialchars($row['visit_date']); ?></small>
        <?php endif; ?>
        <!-- New fields -->
        <?php if (!empty($row['organization'])): ?>
            <small><i class="fas fa-building me-1"></i> Organization: <?php echo htmlspecialchars($row['organization']); ?></small>
        <?php endif; ?>
        <?php if (!empty($row['reason'])): ?>
            <small><i class="fas fa-info-circle me-1"></i> Reason: <?php echo htmlspecialchars($row['reason']); ?></small>
        <?php endif; ?>
    </div>

    <div class="action-buttons ms-auto">
        <a href="approve.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">
            <i class="fas fa-check me-1"></i> Approve
        </a>
        <a href="deny.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">
            <i class="fas fa-times me-1"></i> Deny
        </a>
    </div>
</div>

            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-check fa-4x mb-3"></i>
                <h4>No pending approvals</h4>
                <p>All visitor requests have been processed</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
