<?php
class inventoryreturn extends Controller
{

    public function index()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
        if ($this->model === null) {
            die("Model not loaded properly!");
        }

        $user_email = $_SESSION['user_email'];
        $approvedAssignments = $this->model->getApprovedAssignmentsByLoggedInUser($user_email);
        $receivers = $this->model->getReceivers();

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventoryreturns/index.php';
    }
    
    public function myreturns()
    {
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
    
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
    
        $user_email = $_SESSION['user_email'];
        $returned_by = $user_email; // Keep full email; use strstr only if you need a username elsewhere
    
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $assignment_ids = $_POST['assignment_ids'] ?? [];
            $return_date = $_POST['return_date'] ?? date('Y-m-d H:i:s');
            $receiver_id = $_POST['receiver_id'] ?? null;
    
            if (empty($assignment_ids)) {
                $_SESSION['error'] = "No Assignment IDs selected!";
                header("Location: " . URL . "inventoryreturn/myreturns");
                exit();
            }
    
            foreach ($assignment_ids as $assignment_id) {
                $result = $this->model->recordReturn($assignment_id, $returned_by, $receiver_id, $return_date);
    
                if (!$result) {
                    $_SESSION['error'] = "Failed to return Assignment ID: $assignment_id";
                }
            }
    
            $_SESSION['success'] = "Return(s) recorded successfully.";
            header("Location: " . URL . "inventoryreturn/myreturns");
            exit();
        }
    
        // GET method - Load data and show form
        $items = $this->model->getApprovedAssignmentsByLoggedInUser($user_email);
        $receivers = $this->model->getReceivers();
        $returnedItems = $this->model->getReturnedItems($user_email);
    
        require APP . 'view/_templates/header.php';
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/inventoryreturns/return_item.php';
    }
      
    public function add()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            die("User not logged in!");
        }
        if ($this->model === null) {
            die("Model not loaded properly!");
        }
    
        $user_email = $_SESSION['user_email'];
        $returned_by = $user_email; 
    
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $items = $this->model->getApprovedAssignmentsByLoggedInUser($user_email);
            $receivers = $this->model->getReceivers();
    
            require APP . 'view/_templates/header.php';
            require APP . 'view/inventoryreturns/return_item_form.php';
            exit();
        }
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignment_ids = $_POST['assignment_ids'] ?? [];
            $return_date = $_POST['return_date'] ?? date('Y-m-d H:i:s');
            $receiver_id = $_POST['receiver_id'] ?? null;
    
            if (empty($assignment_ids)) {
                $_SESSION['error'] = "No items selected for return!";
                header("Location: " . URL . "inventoryreturn");
                exit();
            }
    
            foreach ($assignment_ids as $assignment_id) {
                $this->model->recordReturn($assignment_id, $returned_by, $receiver_id, $return_date);
            }
    
            $_SESSION['success'] = "Items returned successfully!";
            header("Location: " . URL . "inventoryreturn");
            exit();
        }
    }
          
    public function delete()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
    
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header("Location: " . URL . "inventoryreturn/myreturns?error=Invalid request!");
            exit();
        }
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        $id = $_GET['id'];
        $deleted = $this->model->deleteReturn($id);
    
        if ($deleted) {
            header("Location: " . URL . "inventoryreturn/myreturns?success=Item deleted successfully!");
        } else {
            header("Location: " . URL . "inventoryreturn/myreturns?error=Cannot delete approved returned items!");
        }
        exit();
    }
    
    //admins approving returns(fetch items to approve)
    public function approve()
    {
        session_start();

        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }

        $user_email = $_SESSION['user_email'];
        $user_name_raw = explode('@', $user_email)[0]; 

        // Format like "Rita Kogi"
        $parts = explode('.', $user_name_raw);
        $first = isset($parts[0]) ? ucfirst(strtolower($parts[0])) : '';
        $last = isset($parts[1]) ? ucfirst(strtolower($parts[1])) : '';
        $formatted_name = trim("$first $last");

        error_log("Formatted name from session: $formatted_name");

        $receivers = $this->model->getReceivers();

        $receiver_id = null;
        foreach ($receivers as $receiver) {
            if (isset($receiver['name']) && strtolower($receiver['name']) === strtolower($formatted_name)) {
                $receiver_id = $receiver['id'];
                break;
            }
        }

        if ($receiver_id !== null) {
            $_SESSION['user_id'] = $receiver_id; // ✅ Fix: store for approveReturn()
        } else {
            $_SESSION['error'] = "User ID not found for approval.";
            header("Location: " . URL . "error");
            exit;
        }

        $pendingApprovals = $this->model->getPendingApprovalsByUser($receiver_id);

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventoryreturns/pendingapprovals.php';
    }

    //approve returned item
    public function approveReturn() 
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            session_start();
    
            if (!isset($_SESSION['user_email'])) {
                die("Unauthorized access.");
            }
    
            if ($this->model === null) {
                echo "Model not loaded properly!";
                exit();
            }
    
            $return_id  = $_POST['return_id'];
            $item_state = $_POST['item_state'];
            $approved_by = $_SESSION['user_id'];
            $disapproval_comment = null;
    
            // Only get the comment if the item is being disapproved
            if ($item_state === 'disapproved') {
                $disapproval_comment = $_POST['disapproval_comment'] ?? null;
            }
    
            if ($this->model->approveReturn($return_id, $item_state, $approved_by, $disapproval_comment)) {
                $_SESSION['success'] = "Item return approved successfully!";
    
                if ($item_state === 'lost') {
                    header("Location: " . URL . "inventoryreturn/approve");
                } elseif ($item_state === 'damaged') {
                    header("Location: " . URL . "inventoryreturn/approve");
                } else {
                    header("Location: " . URL . "inventoryreturn/approve");
                }
                exit;
            } else {
                $_SESSION['error'] = "Failed to approve item return.";
                header("Location: " . URL . "inventoryreturn/approve");
                exit;
            }
        }
    }
          
    //item classifications....
    public function lostItems() 
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
        $lostItems = $this->model->getLostItems();
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_lost.php';
    }
    //searh i n lost items
    public function lostItemsSearch()
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $lostItems = $this->model->getLostItemsSearch($search);
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_lost.php';
    }

    public function damagedItems()
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
    
        // Fetch damaged items from the model
        $damagedItems = $this->model->getDamagedItems();
    
        // Pass the damagedItems to the view
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_damaged.php';
    }
    
    //search damaged items
    public function searchDamagedItems()
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $damagedItems = $this->model->getDamagedItemsSearch($search);
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_damaged.php'; 
    }

    public function updateRepairStatus()    
    {
        session_start();
    
        if ($this->model === null) {
            die("Model not loaded properly!");
        }
    
        if (!isset($_SESSION['user_email'])) {
            die("User not logged in!");
        }
    
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $item_id = $_POST['item_id'] ?? null; 
            $repair_status = $_POST['repair_status'] ?? null;
        
            if (!$item_id || !$repair_status) {
                die("Missing required fields! Item ID: " . ($item_id ?? 'N/A') . " | Repair Status: " . ($repair_status ?? 'N/A'));
            }
        
            $result = $this->model->updateRepairStatus($item_id, $repair_status);
        
            if (!$result) {
                $_SESSION['error'] = "Failed to update repair status. Check if item exists.";
            } else {
                $_SESSION['success'] = "Repair status updated successfully!";
            }
        
            header("Location: " . URL . "inventoryreturn/damagedItems");
            exit();
        }        
    }
    
    public function unassignedItems()
    {
        session_start();

        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }

        // Get unassigned items (already includes custodian_name)
        $unassignedItems = $this->model->getUnassignedInventory();

        // Optionally get full user list (for location names or other user details)
        $users = $this->model->get_users();

        // Map user ID to user for location lookup
        $usersMap = [];
        foreach ($users as $user) {
            $usersMap[$user->id] = $user;
        }

        // Enrich items with location name, use 'Unassigned' if none
        foreach ($unassignedItems as &$item) {
            $item['location_name'] = 'Unassigned';

            if (!empty($item['custodian']) && isset($usersMap[$item['custodian']])) {
                $custodian = $usersMap[$item['custodian']];
                $item['location_name'] = $custodian->dutystation ?? 'Unknown';
            }
        }

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_instock.php';
    }

    //search unassihnmed
    public function searchUnassignedItems()
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
        if (!empty($search)) {
            $unassignedItems = $this->model->getUnassignedItemsSearch($search);
        } else {
            $unassignedItems = $this->model->getUnassignedItems();
        }
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_instock.php';
    }
   
    public function assignedItems()
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
        $assignedItems = $this->model->getAssignedItems();
        if (isset($_GET['search'])) {
            $search = trim($_GET['search']);
        } 
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/inventory_inuse.php';
    }
    //search assigned items
    public function searchAssignedItems()
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
        if (isset($_GET['search'])) {
            $search = trim($_GET['search']);
            $assignedItems = $this->model->getAssignedItemsSearch($search);
            require APP . 'view/_templates/sessions.php';
            require APP . 'view/_templates/header.php';
            require APP . 'view/inventory/inventory_inuse.php';
        } else {
            header("Location: " . URL . "inventoryreturn/assignedItems");
            exit();
        }
    }
    
    public function disposedItems()
    {
        session_start();

        if (!isset($_SESSION['user_email'])) { 
            die("User not logged in!");
        }
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }

        // Fetch disposed items
        $disposedItems = $this->model->getDisposedItems();
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_disposed.php';
    }
    //search disposred items
    public function searchDisposedItems()
    {
        session_start();

        if (!isset($_SESSION['user_email'])) { 
            die("User not logged in!");
        }

        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $disposedItems = $this->model->getDisposedItemsSearch($search);
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_disposed.php'; 
    }

    //disapproved items]
    public function disapprovedItems()
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        session_start();

        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        $disapprovedItems = $this->model->getDisapprovedItems();
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/items_disapproved.php';
    }

    
    //managers reports
    //returned items for staff
    public function staffreturneditems()
    {
        session_start();

        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }

        $loggedInEmail = $_SESSION['user_email'];

        // Fetch returned items based on hierarchy 
        $returnedItems = $this->model->getReturnedItemsByHierarchy($loggedInEmail);

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventoryreturns/returned_items_hierarchy.php';
    }

    public function downloadReturnedItems()
    {
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
    
        if ($this->model === null) {
            $_SESSION['error_message'] = "Model not loaded properly!";
            header("Location: " . URL . "inventoryreturn/staffreturneditems"); 
            exit();
        }
    
        $loggedInEmail = $_SESSION['user_email'];
        $items = $this->model->getReturnedItemsForDownload($loggedInEmail);
    
        if (empty($items)) {
            $_SESSION['error_message'] = "No returned items available for download.";
            header("Location: " . URL . "inventoryreturn/staffreturneditems"); 
            exit();
        }
    
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="returned_items.csv"');
    
        $output = fopen('php://output', 'w');
    
        fputcsv($output, ['Description', 'Serial Number', 'Returned By', 'Return Date', 'Status', 'Receiver']);
    
        foreach ($items as $item) {
            fputcsv($output, [
                $item['description'],
                $item['serial_number'],
                $item['returned_by_name'],
                $item['return_date'],
                $item['status'],
                $item['receiver_name']
            ]);
        }
    
        fclose($output);
        exit();
    }

        // Email to manager to notify them that their supervisee has returned item(s)

    


} 




