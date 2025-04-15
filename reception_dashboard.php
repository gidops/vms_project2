<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "root", "", "visitor");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch logged-in receptionist name
$receptionist_name = "Receptionist";
if (isset($_SESSION['receptionist_id'])) {
    $rec_id = $_SESSION['receptionist_id'];
    $stmt = $conn->prepare("SELECT name FROM receptionists WHERE id = ?");
    $stmt->bind_param("i", $rec_id);
    $stmt->execute();
    $stmt->bind_result($receptionist_name);
    $stmt->fetch();
    $stmt->close();
}

// Pagination settings
$per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $per_page;

// Fetch approved guests with pagination

$search_term = isset($_GET['search']) ? trim($_GET['search']) : "";

if ($search_term !== "") {
    // Search in both approved and authenticated
    $like_search = "%" . $conn->real_escape_string($search_term) . "%";

    // Search Approved
    $stmt = $conn->prepare("SELECT id, name, phone, email, host_name, visit_date FROM visitors 
                            WHERE status = 'approved' 
                            AND (name LIKE ? OR phone LIKE ? OR host_name LIKE ?) 
                            LIMIT ?, ?");
    $stmt->bind_param("sssii", $like_search, $like_search, $like_search, $start, $per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    $approved_guests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Search Authenticated
    $stmt = $conn->prepare("SELECT id, name, phone, email, host_name, visit_date, check_in_time FROM visitors 
                            WHERE status = 'authenticated' 
                            AND (name LIKE ? OR phone LIKE ? OR host_name LIKE ?) 
                            LIMIT ?, ?");
    $stmt->bind_param("sssii", $like_search, $like_search, $like_search, $start, $per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    $authenticated_guests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Override total counts for pagination (optional)
    $result = $conn->query("SELECT COUNT(*) AS count FROM visitors 
                            WHERE status = 'approved' 
                            AND (name LIKE '$like_search' OR phone LIKE '$like_search' OR host_name LIKE '$like_search')");
    $total_approved = $result->fetch_assoc()['count'];

    $result = $conn->query("SELECT COUNT(*) AS count FROM visitors 
                            WHERE status = 'authenticated' 
                            AND (name LIKE '$like_search' OR phone LIKE '$like_search' OR host_name LIKE '$like_search')");
    $total_authenticated = $result->fetch_assoc()['count'];
} else {
    // Original logic for approved guests
    $approved_guests = [];
    $sql_approved = "SELECT id, name, phone, email, host_name, visit_date FROM visitors WHERE status = 'approved' LIMIT $start, $per_page";
    $result_approved = $conn->query($sql_approved);
    while ($row = $result_approved->fetch_assoc()) {
        $approved_guests[] = $row;
    }

    // Original logic for authenticated guests
    $authenticated_guests = [];
    $sql_authenticated = "SELECT id, name, phone, email, host_name, visit_date, check_in_time FROM visitors WHERE status = 'authenticated' LIMIT $start, $per_page";
    $result_authenticated = $conn->query($sql_authenticated);
    while ($row = $result_authenticated->fetch_assoc()) {
        $authenticated_guests[] = $row;
    }
}



$approved_guests = [];
$sql_approved = "SELECT id, name, phone, email, host_name, visit_date FROM visitors WHERE status = 'approved' LIMIT $start, $per_page";
$result_approved = $conn->query($sql_approved);
while ($row = $result_approved->fetch_assoc()) {
    $approved_guests[] = $row;
}

// Fetch authenticated (scanned-in) guests with pagination
$authenticated_guests = [];
$sql_authenticated = "SELECT id, name, phone, email, host_name, visit_date, check_in_time FROM visitors WHERE status = 'authenticated' LIMIT $start, $per_page";
$result_authenticated = $conn->query($sql_authenticated);
while ($row = $result_authenticated->fetch_assoc()) {
    $authenticated_guests[] = $row;
}

// Handle check-out
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_out_id'])) {
    $check_out_id = $_POST['check_out_id'];
    $stmt = $conn->prepare("UPDATE visitors SET status = 'checked out', check_out_time = NOW() WHERE id = ?");
    $stmt->bind_param("i", $check_out_id);
    if ($stmt->execute()) {
        $success_message = "Guest checked out successfully!";
    } else {
        $error_message = "Error checking out guest.";
    }
    $stmt->close();
}

// Total count for pagination
$total_approved = $conn->query("SELECT COUNT(*) AS count FROM visitors WHERE status = 'approved'")->fetch_assoc()['count'];
$total_authenticated = $conn->query("SELECT COUNT(*) AS count FROM visitors WHERE status = 'authenticated'")->fetch_assoc()['count'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reception Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6f7f4;
            font-family: 'Segoe UI', sans-serif;
        }
        .header-bar {
            background-color: #007570;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-bar img {
            height: 40px;
        }
        .header-bar .receptionist-name {
            font-size: 1.2rem;
            font-weight: 500;
        }
        .section-title {
            color: #007570;
            text-align: center;
            margin-top: 2rem;
        }
        .table th {
            background-color: #07AF8B;
            color: white;
        }
        .btn-custom {
            background-color: #007570;
            color: white;
        }
        .btn-custom:hover {
            background-color: #07AF8B;
        }
        .btn-danger-custom {
            background-color: #f72585;
            color: white;
        }
        .btn-danger-custom:hover {
            background-color: #f1d3e4;
        }
        .guest-table {
            margin-top: 2rem;
        }
        .btn-logout {
            background-color: #FF6347;
            color: white;
        }
        .btn-logout:hover {
            background-color: #ff4500;
        }
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

<div class="header-bar">
    <img src="assets/logo-green-yellow.png" alt="Logo">
    <div class="receptionist-name">Welcome, <?= htmlspecialchars($receptionist_name); ?></div>
    <a href="logout.php" class="btn btn-logout btn-sm">Logout</a>
</div>

<!-- <div class="container">
    <h2 class="section-title">Reception Desk</h2>


    <div class="alert alert-info alert-dismissible fade show text-center m-0 rounded-0" role="alert" style="background-color: #FFCA00; color: #212529;">
    <strong>Search Result:</strong> Showing results for "<strong><?= htmlspecialchars($search_term) ?></strong>"
    <a href="reception_dashboard.php" class="btn btn-sm btn-dark ms-3">Clear Search</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div> -->



    <!-- Search Visitors -->
<!-- <div class="my-4">
    <form method="GET" class="d-flex justify-content-center">
        <input type="text" name="search" class="form-control w-50 me-2" placeholder="Search visitors by name, phone, or host" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit" class="btn btn-custom">Search</button>
    </form>
</div> -->


    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success text-center"><?= $success_message ?></div>
    <?php elseif (!empty($error_message)): ?>
        <div class="alert alert-danger text-center"><?= $error_message ?></div>
    <?php endif; ?>

    <!-- Register Walk-in Button -->
    <div class="text-center my-3">
        <a href="register_walkin.php" class="btn btn-custom">Register Walk-in Visitor</a>
    </div>

    <!-- Approved Guests -->
    <h4 class="section-title">Approved Visitors</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Host Name</th>
                    <th>Visit Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approved_guests as $guest): ?>
                    <tr>
                        <td><?= htmlspecialchars($guest['name']) ?></td>
                        <td><?= htmlspecialchars($guest['phone']) ?></td>
                        <td><?= htmlspecialchars($guest['email']) ?></td>
                        <td><?= htmlspecialchars($guest['host_name']) ?></td>
                        <td><?= htmlspecialchars($guest['visit_date']) ?></td>
                        <td><button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#checkOutModal<?= $guest['id'] ?>">Check Out</button></td>
                    </tr>

                    <!-- Check-Out Modal -->
                    <div class="modal fade" id="checkOutModal<?= $guest['id'] ?>" tabindex="-1" aria-labelledby="checkOutModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="checkOutModalLabel">Check Out Visitors</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to check out <strong><?= htmlspecialchars($guest['name']) ?></strong>?
                                </div>
                                <div class="modal-footer">
                                    <form method="POST">
                                        <input type="hidden" name="check_out_id" value="<?= $guest['id'] ?>">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger-custom">Check Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Authenticated Guests -->
    <h4 class="section-title">Authenticated Visitors</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Host Name</th>
                    <th>Visit Date</th>
                    <th>Check-In Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($authenticated_guests as $guest): ?>
                    <tr>
                        <td><?= htmlspecialchars($guest['name']) ?></td>
                        <td><?= htmlspecialchars($guest['phone']) ?></td>
                        <td><?= htmlspecialchars($guest['email']) ?></td>
                        <td><?= htmlspecialchars($guest['host_name']) ?></td>
                        <td><?= htmlspecialchars($guest['visit_date']) ?></td>
                        <td><?= htmlspecialchars($guest['check_in_time']) ?></td>
                        <td><button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#checkOutModal<?= $guest['id'] ?>">Check Out</button></td>
                    </tr>

                    <!-- Check-Out Modal -->
                    <div class="modal fade" id="checkOutModal<?= $guest['id'] ?>" tabindex="-1" aria-labelledby="checkOutModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="checkOutModalLabel">Check Out Visitor</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to check out <strong><?= htmlspecialchars($guest['name']) ?></strong>?
                                </div>
                                <div class="modal-footer">
                                    <form method="POST">
                                        <input type="hidden" name="check_out_id" value="<?= $guest['id'] ?>">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger-custom">Check Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php
                // Pagination Links
                $total_pages_approved = ceil($total_approved / $per_page);
                $total_pages_authenticated = ceil($total_authenticated / $per_page);
                $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
                ?>
                <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $current_page - 1 ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages_approved; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $current_page >= $total_pages_approved ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $current_page + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
