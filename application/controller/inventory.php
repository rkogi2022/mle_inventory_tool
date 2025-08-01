<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Inventory extends Controller
{
    // Fetch all inventory items
    public function index()
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
    
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
    
        $search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
        $items = !empty($search_query) ? $this->model->searchItems($search_query) : $this->model->getItems();
    
        // Fetch users and managers for dropdowns
        $users = $this->model->getAllUsers();  
        $managers = $this->model->getManagers();  
    
    
        // Fetch users and managers for dropdowns
        $users = $this->model->getAllUsers();  
        $managers = $this->model->getManagers();  
    
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory/index.php';
    }
    
    // Add a new item
    public function add()
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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $category_id = intval($_POST['category_id']);
            $description = trim($_POST['description']);
            $serial_number = trim($_POST['serial_number']);
            $tag_number = trim($_POST['tag_number']);
            $acquisition_date = trim($_POST['acquisition_date']);
            $acquisition_cost = trim($_POST['acquisition_cost']);
            $warranty_date = trim($_POST['warranty_date']);
            $custodian_id = intval($_POST['custodian_id']);
            $location_id = trim($_POST['location_id']);
            $status = 'instock';

            if ($warranty_date === '') {
                $warranty_date = null;
            }

            $success = $this->model->addItem(
                $category_id,
                $description,
                $serial_number,
                $tag_number,
                $acquisition_date,
                $acquisition_cost,
                $warranty_date,
                $custodian_id,
                $status,
                $location_id 
            );

            if ($success) {
                header("Location: " . URL . "inventory/index?success=Item added successfully!");
                exit();
            } else {
                header("Location: " . URL . "inventory/add?error=Failed to add item. Please try again.");
                exit();
            }
        } else {
            $categories = $this->model->getCategories() ?? [];
            $positions = $this->model->get_positions() ?? [];
            $positionMap = [];
            foreach ($positions as $pos) {
                $positionMap[$pos->id] = $pos->position_name;
            }

            $custodians_raw = $this->model->get_users() ?? [];

            $custodians = [];
            foreach ($custodians_raw as $c) {
                if ($c->role !== 'admin') {
                    continue;
                }

                $positionName = isset($positionMap[$c->position]) ? $positionMap[$c->position] : 'Unknown Position';
                $email_prefix = strstr($c->email, '@', true);
                $name = ucfirst($email_prefix);

                $custodians[] = (object)[
                    'id' => $c->id,
                    'email' => $c->email,
                    'location_name' => $c->dutystation, 
                    'position_name' => $positionName,
                    'name' => $name,
                    'dutystation' => $c->dutystation,   
                ];
            }

            require APP . 'view/_templates/sessions.php';
            require APP . 'view/_templates/header.php';
            require APP . 'view/inventory/add_inventory_item.php';
        }
    }

    // Edit an existing inventory item
    public function edit($id)
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

        $item = $this->model->getItemById($id);
        if (!$item) {
            header("Location: " . URL . "inventory/index?error=Item not found.");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Retrieve form values
            $category_id = intval($_POST['category_id']);
            $description = trim($_POST['description']);
            $serial_number = trim($_POST['serial_number']);
            $tag_number = trim($_POST['tag_number']);
            $acquisition_date = trim($_POST['acquisition_date']);
            $acquisition_cost = trim($_POST['acquisition_cost']);
            $warranty_date = trim($_POST['warranty_date']);
            $warranty_date = $warranty_date === '' ? null : $warranty_date;
            $custodian = intval($_POST['custodian_id']);

            // Try to update item
            $success = $this->model->updateItem(
                $id,
                $category_id,
                $description,
                $serial_number,
                $tag_number,
                $acquisition_date,
                $acquisition_cost,
                $warranty_date,
                $custodian
            );

            // Preserve search query string if available
            $search = isset($_POST['search']) ? $_POST['search'] : '';
            if ($success) {
                $redirectUrl = URL . "inventory/index?success=" . urlencode("Item updated successfully!");
                if (!empty($search)) {
                    $redirectUrl .= "&search=" . urlencode($search);
                }
                header("Location: " . $redirectUrl);
                exit();
            } else {
                $redirectUrl = URL . "inventory/edit/$id?error=" . urlencode("Failed to update item.");
                if (!empty($search)) {
                    $redirectUrl .= "&search=" . urlencode($search);
                }
                header("Location: " . $redirectUrl);
                exit();
            }
        } else {
            // GET request — load form
            $categories = $this->model->getCategories();

            $positions = $this->model->get_positions() ?? [];
            $positionMap = [];
            foreach ($positions as $pos) {
                $positionMap[$pos->id] = $pos->position_name;
            }

            $custodians_raw = $this->model->get_users() ?? [];

            $custodians = [];
            foreach ($custodians_raw as $c) {
                if ($c->role !== 'admin') {
                    continue;
                }

                $positionName = isset($positionMap[$c->position]) ? $positionMap[$c->position] : 'Unknown Position';

                $email_prefix = strstr($c->email, '@', true);
                $parts = preg_split('/[._\-]+/', $email_prefix);
                $nameParts = array_map(function($part) {
                    return ucfirst(strtolower($part));
                }, $parts);

                $name = implode(' ', $nameParts);

                $custodians[] = (object)[
                    'id' => $c->id,
                    'email' => $c->email,
                    'location_name' => $c->dutystation,
                    'position_name' => $positionName,
                    'name' => $name,
                    'dutystation' => $c->dutystation,
                ];
            }

            require APP . 'view/_templates/sessions.php';
            require APP . 'view/_templates/header.php';
            require APP . 'view/inventory/edit_inventory_item.php';
        }
    }

    // Delete an item
    public function delete()
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
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
            $id = intval($_POST['id']);
            $this->model->deleteItem($id);
            header("Location: " . URL . "inventory/index?success=Item deleted successfully!");
            exit();
        } else {
            header("Location: " . URL . "inventory/index?error=Invalid delete request.");
            exit();
        }
    }

    //single item assigning
    public function assignSingle()
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = intval($_POST['user_id']);
            $item_id = intval($_POST['item_id']);
            $date_assigned = $_POST['date_assigned'];
            $manager_email = $_POST['manager_email'];

            $result = $this->model->assignSingleItem($user_id, $item_id, $date_assigned, $manager_email);

            if (isset($result['type']) && $result['type'] === 'success') {
                // ✅ Get recipient info
                $recipientData = $this->model->getUserById($user_id);
                $recipientEmail = $recipientData['email'];
                $emailPrefix = explode('@', $recipientEmail)[0];
                $recipientName = implode(' ', array_map('ucfirst', explode('.', $emailPrefix)));

                // ✅ Get assigner info
                $assignerEmail = $_SESSION['user_email'] ?? '';
                $assignerProfile = $this->model->getUserProfileByEmail($assignerEmail);
                if (!empty($assignerProfile['email'])) {
                    $assignerEmailPrefix = explode('@', $assignerProfile['email'])[0];
                    $assignerProfile['name'] = implode(' ', array_map('ucfirst', explode('.', $assignerEmailPrefix)));
                } else {
                    $assignerProfile['name'] = 'N/A';
                }

                // ✅ Get item summary
                $assignmentId = $this->model->getLatestAssignmentIdByItem($item_id); 
                $itemList = $this->model->getItemSummariesByIds([$assignmentId]);

                // ✅ Send email to recipient
                $this->sendAcknowledgmentEmailToUser($recipientEmail, $recipientName, $itemList, $assignerProfile);

                // ✅ Send email to manager
                $managerName = implode(' ', array_map('ucfirst', explode('.', explode('@', $manager_email)[0])));
                $this->sendAssignmentNotificationToManager($manager_email, $managerName, $recipientName, $itemList);

                // ✅ Redirect with success
                header("Location: " . URL . "inventory/index?success=" . urlencode($result['message']));
                exit();
            } else {
                $message = urlencode($result['message'] ?? 'Unknown error');
                header("Location: " . URL . "inventory/index?error=" . $message);
                exit();
            }
        }
    }
    // email to the user to acknowledge items
    protected function sendAcknowledgmentEmailToUser($recipientEmail, $recipientName, $itemList, $assigner)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'information.systems@evidenceaction.org';
            $mail->Password   = 'rtnbqnbajjhcifbr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('information.systems@evidenceaction.org', 'MLE Inventory Tool');
            $mail->addAddress($recipientEmail, $recipientName);
            $mail->addBCC('information.systems@evidenceaction.org');

            $mail->isHTML(true);
            $mail->Subject = 'Acknowledgment Required: Issued Item(s) – Action Needed Within 2 Working Days';

            // item list HTML
            $itemListHtml = "<ul>";
            foreach ($itemList as $item) {
                $parts = explode(',', $item);
                $formattedParts = [];

                foreach ($parts as $part) {
                    // trim whitespace
                    $part = trim($part);

                    // Split label and value by first colon
                    $labelValue = explode(':', $part, 2);

                    if (count($labelValue) == 2) {
                        $label = htmlspecialchars(trim($labelValue[0]));
                        $value = htmlspecialchars(trim($labelValue[1]));
                        $formattedParts[] = "<strong>{$label}:</strong> {$value}";
                    } else {
                        // fallback if no colon
                        $formattedParts[] = htmlspecialchars($part);
                    }
                }

                $itemListHtml .= "<li>" . implode(', ', $formattedParts) . "</li>";
            }
            $itemListHtml .= "</ul>";


            $assignerName = htmlspecialchars($assigner['name'] ?? 'N/A');
            $assignerPosition = htmlspecialchars($assigner['position'] ?? 'N/A');
            $assignerDepartment = htmlspecialchars($assigner['department'] ?? 'N/A');

            $mail->Body = "
                <p>Dear {$recipientName},</p>

                <p>This is to inform you that item(s) have been issued to you through the MLE Inventory System. 
                You are required to log in and acknowledge receipt of the item(s) within the next <strong>two (2) working days</strong>.</p>

                <p>You can log in here: <a href='https://mleinventory.evidenceaction.org'>https://mleinventory.evidenceaction.org</a></p>

                {$itemListHtml}

                <p><strong>Please note:</strong> Failure to acknowledge may affect future inventory tracking and accountability.</p>

                <p>If you experience any issues accessing the system or identifying the issued item(s), kindly reach out to the <strong>IT Department</strong> for assistance.</p>

                <p>Thank you for your prompt attention.</p>

                <p>Best regards,<br>
                {$assignerName}<br>
                {$assignerPosition}<br>
                {$assignerDepartment}<br>
                Evidence Action</p>
            ";

            $mail->AltBody = "You have been assigned items. Please log in to the MLE Inventory Tool and acknowledge receipt within 2 working days.";

            $mail->CharSet = 'UTF-8';
            $mail->send();
            error_log("Acknowledgment email sent to: {$recipientEmail}");
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
        }
    }
    // email to the manager after item assignments
    protected function sendAssignmentNotificationToManager($managerEmail, $managerName, $recipientName, $itemList)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'information.systems@evidenceaction.org';
            $mail->Password   = 'rtnbqnbajjhcifbr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('information.systems@evidenceaction.org', 'MLE Inventory Tool');
            $mail->addAddress($managerEmail, $managerName);
            $mail->addBCC('information.systems@evidenceaction.org');

            $mail->isHTML(true);
            $mail->Subject = "Notification: {$recipientName} Assigned Inventory Item(s)";

            // Build item list HTML
            $itemListHtml = "<ul>";
            foreach ($itemList as $item) {
                $parts = explode(',', $item);
                $formattedParts = [];

                foreach ($parts as $part) {
                    $labelValue = explode(':', $part, 2);
                    if (count($labelValue) == 2) {
                        $label = htmlspecialchars(trim($labelValue[0]));
                        $value = htmlspecialchars(trim($labelValue[1]));
                        $formattedParts[] = "<strong>{$label}:</strong> {$value}";
                    } else {
                        $formattedParts[] = htmlspecialchars($part);
                    }
                }

                $itemListHtml .= "<li>" . implode(', ', $formattedParts) . "</li>";
            }
            $itemListHtml .= "</ul>";

            $mail->Body = "
                <p>Dear {$managerName},</p>

                <p>This is to inform you that your supervisee <strong>{$recipientName}</strong> has been assigned the following item(s):</p>

                {$itemListHtml}

                <p>Please reach out to IT if you have any concerns or need clarification.</p>

                <p>Best regards,<br>MLE Inventory Tool</p>
            ";

            $mail->AltBody = "{$recipientName} has been assigned inventory items.";

            $mail->CharSet = 'UTF-8';
            $mail->send();
        } catch (Exception $e) {
            error_log("Manager Email Error: " . $mail->ErrorInfo);
        }
    }

    // Bulk uploading
    public function bulkUpdate()
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

        if (isset($_FILES['bulk_file']) && $_FILES['bulk_file']['error'] == 0) {
            $file = $_FILES['bulk_file']['tmp_name'];
            $itemsToInsert = [];
            $errors = [];
            $successCount = 0;

            if (($handle = fopen($file, "r")) !== FALSE) {
                // Skip the first 10 rows
                for ($i = 0; $i < 10; $i++) {
                    fgetcsv($handle);
                }

                $rowNumber = 10; 

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $rowErrors = [];

                    $category_name = trim($data[0] ?? '');
                    $description = trim($data[1] ?? '');
                    $serial_number = trim($data[2] ?? '');
                    $tag_number = !empty(trim($data[3] ?? '')) ? trim($data[3]) : null;
                    $acquisition_date = trim($data[4] ?? '');
                    $acquisition_cost = trim($data[5] ?? '');
                    $warranty_date = isset($data[6]) ? trim($data[6]) : null;
                    $location = isset($data[7]) ? trim($data[7]) : null;      
                    $custodian = isset($data[8]) ? trim($data[8]) : null;     

                    // Validate fields
                    if (empty($category_name)) {
                        $rowErrors[] = "Missing category name";
                    } else {
                        $category_id = $this->model->getCategoryIdByName($category_name);
                        if ($category_id === null) {
                            $rowErrors[] = "Invalid category: $category_name";
                        }
                    }

                    if (empty($description)) $rowErrors[] = "Missing description";
                    if (empty($serial_number)) {
                        $rowErrors[] = "Missing serial number";
                    } else if ($this->model->isSerialNumberExists($serial_number)) {
                        $rowErrors[] = "Duplicate serial number: $serial_number";
                    }

                    if (empty($acquisition_date)) $rowErrors[] = "Missing acquisition date";
                    if (empty($acquisition_cost)) $rowErrors[] = "Missing acquisition cost";

                    if (empty($rowErrors)) {
                        $itemsToInsert[] = [
                            'category_id' => $category_id,
                            'description' => $description,
                            'serial_number' => $serial_number,
                            'tag_number' => $tag_number,
                            'acquisition_date' => $acquisition_date,
                            'acquisition_cost' => $acquisition_cost,
                            'warranty_date' => $warranty_date,
                            'location' => $location,        
                            'custodian' => $custodian,      
                        ];
                        $successCount++;
                    } else {
                        $errors[] = "Row $rowNumber: " . implode(", ", $rowErrors);
                    }

                    $rowNumber++;
                }

                fclose($handle);
            }

            // Insert valid items
            if (!empty($itemsToInsert)) {
                $this->model->bulkInsertItems($itemsToInsert);
            }

            // Create feedback message
            $message = "$successCount items uploaded successfully.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " items failed: " . implode(" | ", $errors);
            }
        
            $updateStatus = $successCount > 0 ? 'success' : 'fail';

            header('Location: ' . URL . 'inventory/index?' . http_build_query([
                'update' => $updateStatus,
                'message' => $message
            ]));

        } else {
            $message = 'Error with the uploaded file.';
            header('Location: ' . URL . 'inventory/index?' . http_build_query([
                'update' => 'fail',
                'message' => $message
            ]));
        }

        exit();
    }

    // Download inventory template
    public function downloadInventoryTemplate()
    {
        $filename = "inventory_template.csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header row 
        fputcsv($output, ['category', 'description', 'serial_number', 'tag_number', 'acquisition_date', 'acquisition_cost', 'warranty_date', 'location', 'custodian']);

        // Static sample rows 
        fputcsv($output, ['Mouse', 'hp', 'sn100', 'tag-001', '2025-03-05', '20.00', '2025-03-08', 'Awendo', 'Terence']);
        fputcsv($output, ['Laptop', 'Macbook', '4t5rfr5', 'tg-003', '2025-03-03', '100.00', '2025-04-24', 'Chavakali', 'Farida']);
        fputcsv($output, ['Printer', 'Laser Printers', '234ede4', 'ea-111', '2025-03-14', '123.00', '2025-05-01', 'Nairobi', 'JohnMark']);
        fputcsv($output, ['Smart Phone', 'Samsung A52', '1q234', '1q2ws', '2025-03-14', '125.00', '2025-04-30', 'Awendo', 'Terence']);
        fputcsv($output, ['Monitor', 'Asus ROG', 'r71083', 'ea-k011', '2025-04-01', '123.00', '2028-10-25', 'Nairobi', 'JohnMark']);
        fputcsv($output, ['CPU', 'Intel', 't5t5', 'rd4w3', '2025-05-05', '23390.00', '2025-06-20', 'Nairobi', 'JohnMark']);

        // Instruction rows
        fputcsv($output, ['// - Kindly follow the naming convention above, especially for the category.']);
        fputcsv($output, ['// - Use yyyy-mm-dd format for all dates (e.g., 2025-03-05), and ensure acquisition_cost is a number with 2 decimal places (e.g., 123.00).']);
        fputcsv($output, ['// - NB: Do not alter anything in the header or example rows.']);

        fclose($output);
        exit();
    }

    //export inventory
    public function exportInventoryCSV() {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }

        session_start();

        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
    $items = $this->model->getAllInventoryItems();

    // CSV headers
    $csvHeaders = [
        'Category ID', 'Description', 'Serial Number', 'Tag Number',
        'Acquisition Date', 'Acquisition Cost', 'Warranty Date',
        'Location', 'Custodian', 'Created At'
    ];

    // Output headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inventory_export_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    // Output column headings
    fputcsv($output, $csvHeaders);

    // Output data rows
    foreach ($items as $item) {
        fputcsv($output, [
            $item['category_id'],
            $item['description'],
            $item['serial_number'],
            $item['tag_number'],
            $item['acquisition_date'],
            $item['acquisition_cost'],
            $item['warranty_date'],
            $item['location'],
            $item['custodian'],
            $item['created_at']
        ]);
    }
    fclose($output);
    exit;  // Stop further output after CSV download
}

//re-uploading
public function reuploadFromExportCSV()
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

    if (!isset($_FILES['upload_file']) || $_FILES['upload_file']['error'] !== UPLOAD_ERR_OK) {
        header("Location: " . URL . "inventory/index?" . http_build_query([
            'status' => 'fail',
            'message' => 'Upload failed'
        ]));
        exit();
    }

    $file = $_FILES['upload_file']['tmp_name'];
    $handle = fopen($file, "r");
    if (!$handle) {
        header("Location: " . URL . "inventory/index?" . http_build_query([
            'status' => 'fail',
            'message' => 'Cannot open file'
        ]));
        exit();
    }

    $rowNumber = 1;
    $insertCount = 0;
    $updateCount = 0;
    $errors = [];

    $header = fgetcsv($handle); // Skip header row

    while (($data = fgetcsv($handle)) !== false) {
        $rowNumber++;

        $category_id = trim($data[0] ?? '');
        $description = trim($data[1] ?? '');
        $serial_number = trim($data[2] ?? '');
        $tag_number = trim($data[3] ?? '');
        $acquisition_date = trim($data[4] ?? '');
        $acquisition_cost = trim($data[5] ?? '');
        $warranty_date = trim($data[6] ?? '');
        $location = trim($data[7] ?? '');
        $custodian = trim($data[8] ?? '');
        $created_at = trim($data[9] ?? '');

        $rowErrors = [];

        // Validate required fields
        if (empty($category_id) || empty($description) || empty($serial_number) ||
            empty($acquisition_date) || empty($acquisition_cost) ||
            empty($location) || empty($custodian) || empty($created_at)) {
            $rowErrors[] = "Missing required field(s)";
        }

        // Prepare item
        $item = [
            'category_id' => $category_id,
            'description' => $description,
            'serial_number' => $serial_number,
            'tag_number' => $tag_number,
            'acquisition_date' => $acquisition_date,
            'acquisition_cost' => $acquisition_cost,
            'warranty_date' => $warranty_date,
            'location' => $location,
            'custodian' => $custodian,
            'created_at' => $created_at
        ];

        if (empty($rowErrors)) {
            if ($this->model->isSerialNumberExists($serial_number)) {
                $this->model->updateItemBySerialNumber($serial_number, $item);
                $updateCount++;
            } else {
                $this->model->bulkInsertFromExport([$item]);
                $insertCount++;
            }
        } else {
            $errors[] = "Row $rowNumber: " . implode(', ', $rowErrors);
        }
    }

    fclose($handle);

    $message = "$insertCount new item(s) added. $updateCount item(s) updated.";
    if (!empty($errors)) {
        $message .= " " . count($errors) . " row(s) failed: " . implode(" | ", $errors);
    }

    $status = ($insertCount + $updateCount > 0) ? 'success' : 'fail';

    header("Location: " . URL . "inventory/index?" . http_build_query([
        'status' => $status,
        'message' => $message
    ]));
    exit();
}


}
