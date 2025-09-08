<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="MLE Inventory Tool">
    <meta name="author" content="Evidence Action">
    <title>MLE-Inventory Tool</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link href="<?php echo URL; ?>css/style.css" rel="stylesheet">
    <link rel="icon" type="image/jpg" sizes="16x16" href="<?php echo URL; ?>img/icon.jpg">

    <style>
        @font-face {
            font-family: ArchivoBlack;
            src: url("<?php echo URL; ?>/fonts/ArchivoBlack-Regular.ttf");
        }

        .bt-logout {
            background-color: #20253a;
            color: #fff;
            border-style: none;
            padding: .5rem 1rem;
            font-size: 16px;
            border-radius: 5px;
            margin-right: 2rem;
            text-decoration: none;
        }

        .dfaicjcsbg2 {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .nav-option {
            text-align: center;
            text-decoration: none;
            font-size: 15px;
            padding: .8rem .6rem;
            color: #20253a;
            margin-left: 1rem;
            font-family: ArchivoBlack;
        }

        .nav-option:hover,
        .dropdown-item:hover {
            background-color: #20253a !important;
            color: #ffffff !important;
        }

        .active,
        .nav-item.active,
        .nav-item.active .nav-link,
        .dropdown-item.active {
            background-color: #e600a0 !important;
            font-weight: 600;
            color: #ffffff !important;
        }

        .nav-item.active .nav-link {
            font-weight: bold;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-toggle {
            cursor: pointer;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1000;
        }

        .dropdown-item {
            padding: 10px;
            font-size: 14px;
            color: #20253a;
            text-decoration: none;
            display: block;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown .active {
            font-weight: bold;
        }
    </style>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0"/>
</head>

<?php
$uri = $_SERVER['REQUEST_URI'];
$uri = strtok($uri, '?'); 
$current_page = trim(basename($uri), '/');

function isActive($pages, $current_page) {
    if (is_array($pages)) {
        return in_array($current_page, $pages) ? 'active' : '';
    }
    return $current_page === $pages ? 'active' : '';
}

function isDropdownActive($pages, $current_page) {
    return in_array($current_page, $pages) ? 'show' : '';
}
?>


<div class="top-nav navbar navbar-expand-lg navbar-light px-3" style="background-color: #d5f4f7;">
    <a href="<?php echo URL; ?>home" class="navbar-brand">
        <img src="<?php echo URL; ?>img/ea_logo.png" alt="logo" style="padding: 10px; width: 150px; height: auto;">
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <div class="navbar-nav mx-auto d-flex align-items-center gap-3">
            <?php if ($role === 'admin' || $role === 'staff' || $role === 'super_admin'): ?>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo isActive(['inventoryreturn', 'myreturns'], $current_page); ?>"
                       href="#" data-bs-toggle="dropdown">MY ITEMS</a>
                    <div class="dropdown-menu">
                        <a href="<?php echo URL; ?>inventoryreturn"
                           class="dropdown-item <?php echo isActive('inventoryreturn', $current_page); ?>">Assigned Items</a>
                        <a href="<?php echo URL; ?>inventoryreturn/myreturns"
                           class="dropdown-item <?php echo isActive('myreturns', $current_page); ?>">Returned Items</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'super_admin'): ?>
                <a href="<?php echo URL; ?>inventoryassignment"
                   class="nav-item nav-link <?php echo isActive('inventoryassignment', $current_page); ?>">ASSIGNMENTS</a>
            <?php endif; ?>


            <?php if ($role === 'admin' || $role === 'staff' || $role === 'super_admin'): ?>
                <a href="<?php echo URL; ?>inventoryassignment/pending"
                   class="nav-item nav-link <?php echo isActive('pending', $current_page); ?>">PENDING ASSIGNMENTS</a>
            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'super_admin'): ?>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo isActive(['unassignedItems', 'assignedItems','dashboard','reconfirmationReport'], $current_page); ?>"
                       href="#" data-bs-toggle="dropdown">ASSETS</a>
                    <div class="dropdown-menu">
                        <a href="<?php echo URL; ?>inventoryreturn/unassignedItems"
                           class="dropdown-item <?php echo isActive('unassignedItems', $current_page); ?>">In-Stock</a>
                        <a href="<?php echo URL; ?>inventoryreturn/assignedItems"
                           class="dropdown-item <?php echo isActive('assignedItems', $current_page); ?>">In-Use</a>
                        <a href="<?php echo URL; ?>dashboard"
                           class="dropdown-item <?php echo isActive('dashboard', $current_page); ?>">Staff
                            Dashboard</a>
                        <a href="<?php echo URL; ?>inventoryassignment/reconfirmationReport"
                           class="dropdown-item <?php echo isActive('reconfirmationReport', $current_page); ?>">Confirmation
                            Reports</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'super_admin'): ?>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo isActive(['approve', 'lostItems','damagedItems','disposedItems','disapprovedItems'], $current_page); ?>" href="#"
                       data-bs-toggle="dropdown">COLLECTIONS</a>
                    <div class="dropdown-menu">
                        <a href="<?php echo URL; ?>inventoryreturn/approve"
                           class="dropdown-item <?php echo isActive('approve', $current_page); ?>">Pending Approvals</a>
                        <a href="<?php echo URL; ?>inventoryreturn/lostItems"
                           class="dropdown-item <?php echo isActive('lostItems', $current_page); ?>">Lost Inventory</a>
                        <a href="<?php echo URL; ?>inventoryreturn/damagedItems"
                           class="dropdown-item <?php echo isActive('damagedItems', $current_page); ?>">Repairs</a>
                        <a href="<?php echo URL; ?>inventoryreturn/disposedItems"
                           class="dropdown-item <?php echo isActive('disposedItems', $current_page); ?>">Disposed</a>
                           <a href="<?php echo URL; ?>inventoryreturn/disapprovedItems"
                           class="dropdown-item <?php echo isActive('disapprovedItems', $current_page); ?>">Disapproved</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'staff' || $role === 'super_admin'): ?>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo isActive(['staffassignments', 'staffreturneditems'], $current_page); ?>"
                       href="#" data-bs-toggle="dropdown">REPORTS</a>
                    <div class="dropdown-menu">
                        <a href="<?php echo URL; ?>inventoryassignment/staffassignments"
                           class="dropdown-item <?php echo isActive('staffassignments', $current_page); ?>">Staff
                            Assignments</a>
                        <a href="<?php echo URL; ?>inventoryreturn/staffreturneditems"
                           class="dropdown-item <?php echo isActive('staffreturneditems', $current_page); ?>">Staff
                            Returned Items</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'super_admin'): ?>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo isActive(['inventory', 'getCategory','getUsers'], $current_page); ?>" href="#"
                       data-bs-toggle="dropdown">CONFIGURATIONS</a>
                    <div class="dropdown-menu">
                        <a href="<?php echo URL; ?>inventory"
                           class="dropdown-item <?php echo isActive('inventory', $current_page); ?>">Inventory</a>
                        <a href="<?php echo URL; ?>categories/getCategory"
                           class="dropdown-item <?php echo isActive('getCategory', $current_page); ?>">Categories</a>
                        <a href="<?php echo URL; ?>users/getUsers"
                           class="dropdown-item <?php echo isActive('getUsers', $current_page); ?>">Users</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($role === 'super_admin'): ?>
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo isActive(['getPositions', 'getDepartments','getOffices','getLocations'], $current_page); ?>" href="#"
                       data-bs-toggle="dropdown">ADMIN</a>
                    <div class="dropdown-menu">
                        <a href="<?php echo URL; ?>positions/getPositions"
                           class="dropdown-item <?php echo isActive('getPositions', $current_page); ?>">Positions</a>
                        <a href="<?php echo URL; ?>department/getDepartments"
                           class="dropdown-item <?php echo isActive('getDepartments', $current_page); ?>">Departments</a>
                        <a href="<?php echo URL; ?>office/getOffices"
                           class="dropdown-item <?php echo isActive('getOffices', $current_page); ?>">Office</a>
                        <a href="<?php echo URL; ?>location/getLocations"
                           class="dropdown-item <?php echo isActive('getLocations', $current_page); ?>">Location</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-3">
            <?php 
            if (isset($_SESSION['user_email'])) {
                $user_email = $_SESSION['user_email'];
               $user_name = ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $user_email)[0]));
                echo "
                    <div style='position: relative; display: inline-block;'>
                        <span id='profileTrigger' class='mb-0' style='cursor: pointer;' onclick='toggleProfileCard()'>
                            <i class='fas fa-user-circle' style='font-size: 22px; color: #e600a0;'></i>
                            <b style='margin-left: 6px; font-size: 16px;'>" . htmlspecialchars($user_name) . "</b>
                        </span>

                        <div id='profileCard' style='display: none; position: absolute; top: 40px; right: 0; background: #fff; border: 1px solid #ddd; border-radius: 16px; padding: 25px; width: 380px; box-shadow: 0px 8px 20px rgba(0,0,0,0.15); z-index: 999; font-size: 15px; line-height: 1.7;'>
                            <p><i class='fas fa-smile text-warning'></i> <strong>Name:</strong> <span id='cardName'></span></p>
                            <p><i class='fas fa-envelope text-danger'></i> <strong>Email:</strong> <span id='cardEmail'></span></p>
                            <p><i class='fas fa-building text-info'></i> <strong>Department:</strong> <span id='cardDepartment'></span></p>
                            <p><i class='fas fa-briefcase text-success'></i> <strong>Position:</strong> <span id='cardPosition'></span></p>
                            <p><i class='fas fa-map-marker-alt text-muted'></i> <strong>Duty Station:</strong> <span id='cardDuty'></span></p>
                            <div style='text-align: center; margin-top: 20px;'>
                                <a href='" . URL . "login/logout' class='bt-logout' style='padding: 8px 18px;  color: white; text-decoration: none; border-radius: 8px; font-weight: 500;'>LOGOUT</a>
                            </div>
                        </div>
                    </div>

                    <script>
                        function toggleProfileCard() {
                            const card = document.getElementById('profileCard');
                            if (card.style.display === 'none') {
                                fetchUserProfile();
                                card.style.display = 'block';
                            } else {
                                card.style.display = 'none';
                            }
                        }

                        function fetchUserProfile() {
                            fetch('" . URL . "users/getUserProfile')
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('cardName').innerText = data.name;
                                document.getElementById('cardEmail').innerText = data.email;
                                document.getElementById('cardDepartment').innerText = data.department;
                                document.getElementById('cardPosition').innerText = data.position;
                                document.getElementById('cardDuty').innerText = data.dutystation;
                            });
                        }

                        // Hide card when clicking outside
                        document.addEventListener('click', function(e) {
                            const card = document.getElementById('profileCard');
                            const trigger = document.getElementById('profileTrigger');
                            if (!card.contains(e.target) && !trigger.contains(e.target)) {
                                card.style.display = 'none';
                            }
                        });
                    </script>
                ";
            }
            ?>
        </div>
    </div>
</div>

<body>
<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>      
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js" crossorigin="anonymous"></script>
</body>
</html>
