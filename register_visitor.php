<?php
session_start();

// DB Connection
$conn = new mysqli("localhost", "root", "", "visitor");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch logged-in employee name
$employee_name = "Employee";
if (isset($_SESSION['employee_id'])) {
    $emp_id = $_SESSION['employee_id'];
    $stmt = $conn->prepare("SELECT name FROM employees WHERE id = ?");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $stmt->bind_result($employee_name);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guests'])) {
    $guests = $_POST['guests'];
    foreach ($guests as $guest) {
        $name = $conn->real_escape_string($guest['name']);
        $host_name = $conn->real_escape_string($guest['host_name']);
        $phone = $conn->real_escape_string($guest['phone']);
        $email = $conn->real_escape_string($guest['email']);
        $organization = $conn->real_escape_string($guest['organization']);
        $visit_date = $conn->real_escape_string($guest['visit_date']);
        $reason = $conn->real_escape_string($guest['reason']);

        $stmt = $conn->prepare("INSERT INTO visitors (name, phone, email, employee_id, host_id, host_name, organization, visit_date, reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisssss", $name, $phone, $email, $emp_id, $emp_id, $host_name, $organization, $visit_date, $reason);
        $stmt->execute();
        $stmt->close();
    }
    $success_message = "Guests registered successfully!";
}

// Fetch upcoming guests
$upcoming_guests = [];
if (isset($_GET['showGuests'])) {
    $sql = "SELECT name, phone, email, host_name FROM visitors WHERE employee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $upcoming_guests[] = $row;
    }
    $stmt->close();
}

// Fetch frequent visitors (visits more than 2 times)
$frequent_visitors = [];
$sql = "SELECT name, COUNT(*) AS visit_count FROM visitors WHERE employee_id = ? GROUP BY name HAVING visit_count > 2";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $frequent_visitors[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visitor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            scroll-behavior: smooth;
        }
        .header-bar {
            background-color: #007570;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-bar img {
            height: 40px;
        }
        .header-bar .employee-name {
            font-size: 1.2rem;
            font-weight: 500;
        }
        .section-title {
            color: #007570;
            margin-top: 2rem;
            text-align: center;
        }
        .card {
            border-radius: 15px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-custom {
            background-color: #007570;
            color: white;
        }
        .btn-custom:hover {
            background-color: #07AF8B;
        }
        .scroll-btn {
            margin: 1rem;
        }
        .guest-form {
            background-color: white;
            border-left: 5px solid #FFCA00;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
        }
        .guest-table {
            margin-top: 2rem;
        }
        .table th {
            background-color: #07AF8B;
            color: white;
        }
        .logout-btn {
            background-color: #dc3545;
            color: white;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<div class="header-bar">
    <img src="assets/logo-green-yellow.png" alt="Logo">
    <div class="employee-name">Welcome, <?= htmlspecialchars($employee_name); ?></div>
    <a href="logout.php" class="btn logout-btn">Logout</a>
</div>

<div class="container">
    <!-- Dashboard Summary Cards -->
    <div class="row text-center mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Today's Visitors</h5>
                    <p class="card-text fs-3">12</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Approved Entries</h5>
                    <p class="card-text fs-3">8</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pending Approvals</h5>
                    <p class="card-text fs-3">4</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Denied Entries</h5>
                    <p class="card-text fs-3">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Dashboard Sections -->
    <ul class="nav nav-tabs mt-5" id="dashboardTabs">
        <li class="nav-item">
            <a class="nav-link active" href="#form-tab" data-bs-toggle="tab">Add Visitors</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#guests-tab" data-bs-toggle="tab">Approved Visitors</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#frequent-tab" data-bs-toggle="tab">Frequent Visitors</a>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="form-tab">
            <h2 class="section-title mt-4">Register Visitors</h2>

            <div class="text-center">
                <button class="btn btn-warning scroll-btn" onclick="scrollToSection('guest-form-section')">Add Visitor</button>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success text-center"><?= $success_message ?></div>
            <?php endif; ?>

            <div id="guest-form-section">
                <form method="POST" action="">
                    <div id="guest-forms">
                        <div class="guest-form">
                            <h5>Visitor 1</h5>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label>Full Name</label>
                                    <input type="text" name="guests[0][name]" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Host Name</label>
                                    <input type="text" name="guests[0][host_name]" class="form-control bg-light" value="<?= htmlspecialchars($employee_name); ?>" readonly>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label>Phone</label>
                                    <input type="tel" name="guests[0][phone]" class="form-control" required>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label>Email</label>
                                    <input type="email" name="guests[0][email]" class="form-control" required>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label>Name of Organization</label>
                                    <input type="text" name="guests[0][organization]" class="form-control" required>
                                </div>
                                <div class="col-md-6 mt-3">
                                    <label>Date of Visit</label>
                                    <input type="date" name="guests[0][visit_date]" class="form-control" required>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <label>Reason for Visit</label>
                                    <textarea name="guests[0][reason]" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-outline-success" onclick="addGuestForm()">Add Another Visitor</button>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-custom btn-lg" type="submit">Register Visitor(s)</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="tab-pane fade" id="guests-tab">
            <?php if (!empty($upcoming_guests)): ?>
                <div id="upcoming-guests" class="guest-table">
                    <h4 class="section-title">Upcoming Visitors</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Host Name</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($upcoming_guests as $guest): ?>
                                <tr>
                                    <td><?= htmlspecialchars($guest['name']) ?></td>
                                    <td><?= htmlspecialchars($guest['phone']) ?></td>
                                    <td><?= htmlspecialchars($guest['email']) ?></td>
                                    <td><?= htmlspecialchars($guest['host_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="frequent-tab">
            <h4 class="section-title">Frequent Visitors</h4>
            <?php if (!empty($frequent_visitors)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Visit Count</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($frequent_visitors as $visitor): ?>
                        <tr>
                            <td><?= htmlspecialchars($visitor['name']) ?></td>
                            <td><?= htmlspecialchars($visitor['visit_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No frequent visitors found.</p>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    let guestCount = 1;

    function scrollToSection(id) {
        document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
    }

    function addGuestForm() {
        const container = document.getElementById('guest-forms');
        const index = guestCount;
        guestCount++;

        const form = document.createElement('div');
        form.classList.add('guest-form');
        form.setAttribute('id', `guest-form-${index}`);
        form.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <h5>Visitor ${index + 1}</h5>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeGuestForm(${index})">Remove</button>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Full Name</label>
                    <input type="text" name="guests[${index}][name]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Host Name</label>
                    <input type="text" name="guests[${index}][host_name]" class="form-control" value="<?= htmlspecialchars($employee_name); ?>" readonly>
                </div>
                <div class="col-md-6 mt-3">
                    <label>Phone</label>
                    <input type="tel" name="guests[${index}][phone]" class="form-control" required>
                </div>
                <div class="col-md-6 mt-3">
                    <label>Email</label>
                    <input type="email" name="guests[${index}][email]" class="form-control" required>
                </div>
                <div class="col-md-6 mt-3">
                    <label>Name of Organization</label>
                    <input type="text" name="guests[${index}][organization]" class="form-control" required>
                </div>
                <div class="col-md-6 mt-3">
                    <label>Date of Visit</label>
                    <input type="date" name="guests[${index}][visit_date]" class="form-control" required>
                </div>
                <div class="col-md-12 mt-3">
                    <label>Reason for Visit</label>
                    <textarea name="guests[${index}][reason]" class="form-control" rows="2" required></textarea>
                </div>
            </div>
        `;
        container.appendChild(form);
    }

    function removeGuestForm(index) {
        document.getElementById(`guest-form-${index}`).remove();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
