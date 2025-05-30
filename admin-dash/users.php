<?php
include('conn.php');
session_start();

if (!isset($_SESSION['admin_users'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$admin_users = $_SESSION['admin_users'];

// جلب قائمة الأحداث
$sql_events = "SELECT id, title FROM events ORDER BY expiry_time DESC";
$events_result = $conn->query($sql_events);
$events = [];
if ($events_result) {
    while ($row = $events_result->fetch_assoc()) {
        $events[] = $row;
    }
}

// جلب بيانات المستخدمين بناءً على الحدث المختار
$selected_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$user_data = [];

if ($selected_event_id > 0) {
    $sql_users = "SELECT eu.user_name, eu.grade, eu.created_at, eu.ip_address, u.fields_data
                  FROM event_users eu
                  LEFT JOIN users u ON eu.ip_address = u.ip_address AND eu.event_id = u.event_id
                  WHERE eu.event_id = ?
                  ORDER BY eu.created_at DESC";
    $stmt = $conn->prepare($sql_users);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $user_data[] = $row;
    }
    $stmt->close();
} else {
    $sql_users = "SELECT eu.user_name, eu.grade, eu.created_at, eu.ip_address, u.fields_data
                  FROM event_users eu
                  LEFT JOIN users u ON eu.ip_address = u.ip_address AND eu.event_id = u.event_id
                  ORDER BY eu.created_at DESC";
    $result = $conn->query($sql_users);
    while ($row = $result->fetch_assoc()) {
        $user_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>ASU Dashboard - Users</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <link href="img/favicon.ico" rel="icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet"> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="./lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid position-relative d-flex p-0">
        <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-secondary navbar-dark">
                <a href="index.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary">ASU Dashboard</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <img class="rounded-circle" src="img/user.png" alt="" style="width: 70px; height: 70px;">
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?php echo htmlspecialchars($admin_users); ?></h6>
                    </div>
                </div>
                <div class="navbar-nav w-100">
                    <a href="index.php" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="events.php" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Events</a>
                    <a href="forms.php" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Forms</a>
                    <a href="users.php" class="nav-item nav-link active"><i class="fa fa-user me-2"></i>Users</a>
                    <a href="about.php" class="nav-item nav-link"><i class="fa fa-info-circle me-2"></i>About</a>
                    <a href="logout.php" class="nav-item nav-link"><i class="fa fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </nav>
        </div>
        <div class="content">
            <nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
                <a href="index.php" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-user-edit"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="img/user.png" alt="" style="width: 50px; height: 50px;">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="index.php" class="dropdown-item">Dashboard</a>
                            <a href="events.php" class="dropdown-item">Events</a>
                            <a href="forms.php" class="dropdown-item">Forms</a>
                            <a href="users.php" class="dropdown-item">Users</a>
                            <a href="about.php" class="dropdown-item">About</a>
                            <a href="logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary text-center rounded p-4">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h6 class="mb-0">Registered Users</h6>
                        <form action="users.php" method="GET">
                            <select name="event_id" onchange="this.form.submit()" class="form-select" style="width: 200px;">
                                <option value="0">All Events</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?php echo $event['id']; ?>" <?php echo $selected_event_id == $event['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($event['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table text-start align-middle table-bordered table-hover mb-0">
                            <thead>
                                <tr class="text-white">
                                    <th scope="col">Name</th>
                                    <th scope="col">Grade</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">IP Address</th>
                                    <th scope="col">Form Responses</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($user_data): ?>
                                    <?php foreach ($user_data as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['user_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['grade'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['created_at'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['ip_address'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($row['fields_data'] ?? 'No responses'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">No registered users for this event</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="container-fluid pt-4 px-4">
                <div class="bg-secondary rounded-top p-4">
                    <div class="row">
                        <div class="col-12 col-sm-6 text-center text-sm-start">
                            &copy; <a href="https://maok3ak.rf.gd">Maok3ak</a>, All Right Reserved. 
                        </div>
                        <div class="col-12 col-sm-6 text-center text-sm-end">
                            Designed By <a href="https://maok3ak.rf.gd">EssamAboElmgd</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>
    </div>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
<?php $conn->close(); ?>