<?php

class nonconsumables extends Controller
{
    
    // ------------------ SERIALISED NONCONSUMABLES ------------------
    public function index()
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

        // Fetch all serialized nonconsumables
        $allSerialised = $this->model->getAllSerialised();
        // Fetch all staff for recipient dropdown
        $recipients = $this->model->getAllStaffNames();

        // Pass to view
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/labheader.php';
        require APP . 'view/lab/nonconsumables.php';
    }


    public function addSerialised() {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'item_name'         => $_POST['item_name'] ?? '',
                'description'       => $_POST['description'] ?? '',
                'serial_number'     => $_POST['serial_number'] ?? '',
                'specification'     => $_POST['specification'] ?? '',
                'accessories'       => $_POST['accessories'] ?? '',
                'quantity_received' => $_POST['quantity_received'] ?? 1,
                'quantity_in_store' => $_POST['quantity_in_store'] ?? 1,
                'date_received'     => $_POST['date_received'] ?? date('Y-m-d'),
                'recipient_name'    => $_POST['recipient_name'] ?? '',
                'condition_status'  => $_POST['condition_status'] ?? 'good',
                'current_status'    => $_POST['current_status'] ?? 'instock',
                'remarks'           => $_POST['remarks'] ?? ''
            ];

            $result = $this->model->addSerialised($data);

            $_SESSION['message'] = $result ? 'Serialized item added successfully.' : 'Failed to add serialized item.';
            header("Location: " . URL . "nonconsumables");
            exit();
        }
    }

    public function getSerialisedById($id)
    {
        if ($this->model === null) { exit("Model not loaded!"); }
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        return $this->model->getSerialisedById($id);
    }

    // Update Serialized Item
    public function updateSerialised($id) {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id'                => $id,
                'item_name'         => $_POST['item_name'],
                'description'       => $_POST['description'],
                'serial_number'     => $_POST['serial_number'],
                'specification'     => $_POST['specification'],
                'accessories'       => $_POST['accessories'],
                'quantity_received' => $_POST['quantity_received'],
                'quantity_in_store' => $_POST['quantity_in_store'],
                'date_received'     => $_POST['date_received'],
                'recipient_name'    => $_POST['recipient_name'],
                'condition_status'  => $_POST['condition_status'],
                'current_status'    => $_POST['current_status'],
                'remarks'           => $_POST['remarks']
            ];

            $this->model->updateSerialised($data);

            $_SESSION['message'] = 'Serialized item updated successfully.';
            header("Location: " . URL . "nonconsumables");
            exit();
        }
    }

    public function deleteSerialised($id)
    {
        if ($this->model === null) { exit("Model not loaded!"); }
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        return $this->model->deleteSerialised($id);
    }

    public function addSerialisedMovement($data)
    {
        if ($this->model === null) { exit("Model not loaded!"); }
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        return $this->model->addSerialisedMovement($data);
    }

    public function getSerialisedMovements($serialized_id)
    {
        if ($this->model === null) { exit("Model not loaded!"); }
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        return $this->model->getSerialisedMovements($serialized_id);
    }

    public function addSerialisedConditionLog()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'serialized_id'    => $_POST['serialized_id'],
                'condition_status' => $_POST['condition_status'],
                'remarks'          => $_POST['remarks'] ?? '',
                'checked_by'       => $_POST['checked_by'] ?? $_SESSION['user_name'] ?? '',
                'date_checked'     => $_POST['date_checked'] ?? date('Y-m-d')
            ];

            $result = $this->model->addSerialisedConditionLog($data);
            $_SESSION['message'] = $result ? "Condition log saved." : "Failed to save condition log.";
            header("Location: " . URL . "nonconsumables");
            exit();
        }
    }

    public function addMovement()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'serialized_id'  => $_POST['serialized_id'],
                'movement_type'  => $_POST['movement_type'],
                'movement_date'  => $_POST['movement_date'],
                'destination'    => $_POST['destination'] ?? '',
                'remarks'        => $_POST['remarks'] ?? '',
                'recorded_by'    => $_POST['recorded_by'] ?? $_SESSION['user_name'] ?? ''
            ];

            $result = $this->model->addSerialisedMovement($data);
            $_SESSION['message'] = $result ? "Movement recorded." : "Failed to record movement.";
            header("Location: " . URL . "nonconsumables");
            exit();
        }
    }


    public function getLogs($serialized_id)
    {
        if ($this->model === null) { exit("Model not loaded!"); }
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        $conditionLogs = $this->model->getConditionLogs($serialized_id);
        $movements = $this->model->getSerialisedMovements($serialized_id);

        // Render a partial HTML view for the modal
        require APP . 'view/lab/nonconsumables_tracking.php';
    }


    // ------------------ BULK NONCONSUMABLES ------------------

// Controller: nonconsumables.php
    public function addBulk()
    {
        if ($this->model === null) { 
            exit("Model not loaded!"); 
        }

        session_start();
        if (!isset($_SESSION['user_email'])) { 
            header("Location: " . URL . "login"); 
            exit(); 
        }

        // Only handle POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Collect and sanitize POST data
            $data = [
                'item_name'        => trim($_POST['item_name'] ?? ''),
                'specification'    => trim($_POST['specification'] ?? null),
                'description'      => trim($_POST['description'] ?? null),
                'condition_status' => $_POST['condition_status'] ?? 'good',
                'last_checked_date'=> $_POST['last_checked_date'] ?? null,
                'remarks'          => trim($_POST['remarks'] ?? null)
            ];

            // Call model to insert
            $result = $this->model->addBulk($data);

            if ($result) {
                $_SESSION['message'] = "Bulk item added successfully!";
            } else {
                $_SESSION['error'] = "Failed to add bulk item.";
            }

            // Redirect to the bulk non-consumables view
            header("Location: " . URL . "nonconsumables/bulkNonconsumables");
            exit();
        }

        // If request is not POST, just redirect to view
        header("Location: " . URL . "nonconsumables/bulkNonconsumables");
        exit();
    }


    public function bulkNonconsumables()
    {
        if ($this->model === null) { 
            exit("Model not loaded!"); 
        }

        session_start();
        if (!isset($_SESSION['user_email'])) { 
            header("Location: " . URL . "login"); 
            exit(); 
        }

        // Fetch all bulk items
        $allBulk = $this->model->getAllBulk();


        // Render the view
            require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/labheader.php';
        require APP . 'view/lab/bulk_nonconsumables.php';
    }

    public function getBulkById($id)
    {
        if ($this->model === null) { exit("Model not loaded!"); }
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        return $this->model->getBulkById($id);
    }

    public function updateBulk($id)
    {
        if ($this->model === null) { 
            exit("Model not loaded!"); 
        }

        session_start();
        if (!isset($_SESSION['user_email'])) { 
            header("Location: " . URL . "login"); 
            exit(); 
        }

        // Only handle POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'item_name'        => $_POST['item_name'] ?? '',
                'specification'    => $_POST['specification'] ?? null,
                'description'      => $_POST['description'] ?? null,
                'condition_status' => $_POST['condition_status'] ?? 'good',
                'last_checked_date'=> $_POST['last_checked_date'] ?? null,
                'remarks'          => $_POST['remarks'] ?? null
            ];

            $result = $this->model->updateBulk($id, $data);

            if ($result) {
                $_SESSION['message'] = "Bulk item updated successfully!";
            } else {
                $_SESSION['error'] = "Failed to update bulk item.";
            }

            header("Location: " . URL . "nonconsumables/bulkNonconsumables");
            exit();
        }

        // Redirect if not POST
        header("Location: " . URL . "nonconsumables/bulkNonconsumables");
        exit();
    }


    public function deleteBulk($id)
    {
        if ($this->model === null) { exit("Model not loaded!"); }
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        return $this->model->deleteBulk($id);
    }

    //Add a bulk movement
    public function addBulkMovement()
    {
        if ($this->model === null) { exit("Model not loaded!"); }
        session_start();
        if (!isset($_SESSION['user_email'])) { header("Location: " . URL . "login"); exit(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'bulk_id'       => $_POST['bulk_id'] ?? null,
                'movement_type' => $_POST['movement_type'] ?? 'received',
                'quantity'      => $_POST['quantity'] ?? 0,
                'movement_date' => $_POST['movement_date'] ?? date('Y-m-d'),
                'destination'   => $_POST['destination'] ?? null,
                'remarks'       => $_POST['remarks'] ?? null,
                'recorded_by'   => $_SESSION['user_email'] ?? null
            ];

            if(!$data['bulk_id']) {
                $_SESSION['error'] = "Please select an item.";
                header("Location: " . URL . "nonconsumables/bulkNonconsumables");
                exit();
            }

            $result = $this->model->addBulkMovement($data);

            if ($result) {
                $_SESSION['message'] = "Bulk movement recorded successfully!";
            } else {
                $_SESSION['error'] = "Failed to record bulk movement.";
            }

            header("Location: " . URL . "nonconsumables/bulkNonconsumables");
            exit();
        }

        header("Location: " . URL . "nonconsumables/bulkNonconsumables");
        exit();
    }
    //Get bulk movements for display (AJAX)
    public function getBulkMovements($bulk_id)
    {
        if ($this->model === null) { exit("Model not loaded!"); }

        $movements = $this->model->getBulkMovements($bulk_id);

        // Simple HTML table for AJAX modal
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr>
                <th>Type</th>
                <th>Quantity</th>
                <th>Date</th>
                <th>Destination</th>
                <th>Remarks</th>
                <th>Recorded By</th>
            </tr></thead><tbody>';

        if (!empty($movements)) {
            foreach ($movements as $m) {
                echo '<tr>
                        <td>' . htmlspecialchars($m['movement_type']) . '</td>
                        <td>' . htmlspecialchars($m['quantity']) . '</td>
                        <td>' . htmlspecialchars($m['movement_date']) . '</td>
                        <td>' . htmlspecialchars($m['destination']) . '</td>
                        <td>' . htmlspecialchars($m['remarks']) . '</td>
                        <td>' . htmlspecialchars($m['recorded_by']) . '</td>
                    </tr>';
            }
        } else {
            echo '<tr><td colspan="6">No movements recorded.</td></tr>';
        }

        echo '</tbody></table>';
    }
}
