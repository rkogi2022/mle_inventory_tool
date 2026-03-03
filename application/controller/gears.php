<?php

class gears extends Controller
{
    // Show all gear inventory and issued items
    public function index()
    {
        session_start();
        
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        // Fetch all inventory items
        $items = $this->model->getAllInventory(); // <-- renamed to $items

        // Fetch all issued gear
        $issuedGear = $this->model->getAllIssuedGear(); // optional for separate table if you display issued gear

        // Fetch all staff for issue modal
        $staffs = $this->model->getAllStaffNames();

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/labheader.php';
        require APP . 'view/lab/gears.php';
    }

    // Add new inventory item
    public function addInventory()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) header("Location: " . URL . "login");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $item_name = $_POST['item_name'] ?? '';
            $number_procured = $_POST['number_procured'] ?? 0;
            $comments = $_POST['comments'] ?? '';

            $success = $this->model->addInventoryItem($item_name, $number_procured, $comments);
            if ($success) {
                header("Location: " . URL . "gears");
            } else {
                echo "Failed to add inventory item.";
            }
        }
    }

        // Edit Inventory Item
    public function editInventory()
    {
        $id = $_POST['id'] ?? null;
        $number_procured = $_POST['number_procured'] ?? 0;
        $comments = $_POST['comments'] ?? '';

        if (!$id || $number_procured < 0) {
            $_SESSION['error'] = "Invalid input for editing gear.";
            header("Location: " . URL . "gears");
            exit();
        }

        $currentItem = $this->model->getInventoryById($id);
        if (!$currentItem) {
            $_SESSION['error'] = "Gear item not found.";
            header("Location: " . URL . "gears");
            exit();
        }

        $number_issued = $currentItem['number_issued'] ?? 0;
        $this->model->updateInventoryItem($id, $number_procured, $number_issued, $comments);

        $_SESSION['message'] = "Gear item updated successfully.";
        header("Location: " . URL . "gears");
    }

        // Delete Inventory Item
    public function deleteItem()
    {
        $id = $_GET['delete'] ?? null;
        if (!$id) {
            $_SESSION['error'] = "Invalid gear item ID.";
            header("Location: " . URL . "gears");
            exit();
        }

        $this->model->deleteInventoryItem($id);

        $_SESSION['message'] = "Gear item deleted successfully.";
        header("Location: " . URL . "gears");
    }

    // Issue gear to staff
    public function issueGear()
    {
        session_start();

        $staff_id     = $_POST['staff_id'] ?? null; // integer
        $item_id      = $_POST['item_id'] ?? null;
        $quantity     = $_POST['quantity'] ?? 1;
        $date_issued  = $_POST['date_issued'] ?? date('Y-m-d');
        $item_condition = $_POST['item_condition'] ?? 'Good';

        // Guard against missing values
        if (empty($staff_id) || empty($item_id) || empty($quantity)) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: " . URL . "gears");
            exit();
        }

        if (!$staff_id || !$item_id || !$quantity) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: " . URL . "gears");
            exit();
        }

        // Pass arguments in correct order to match model
        $this->model->issueGear($staff_id, $item_id, $quantity, $date_issued, $item_condition);

        $_SESSION['message'] = "Gear issued successfully!";
        header("Location: " . URL . "gears");
    }
    

    // View issued gear per staff
    public function issuedPerStaff($staff_id)
    {
        session_start();
        if (!isset($_SESSION['user_email'])) header("Location: " . URL . "login");

        $issued = $this->model->getIssuedByStaff($staff_id);

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/labheader.php';
        require APP . 'view/lab/issued_per_staff.php';
    }

    public function issuedStaffList()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        $staffList = $this->model->getStaffWithIssuedGear();

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/labheader.php';
        require APP . 'view/lab/issued_staff_list.php';
    }



}
