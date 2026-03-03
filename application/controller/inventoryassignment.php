<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class inventoryassignment extends Controller
{
    // Display all assignments
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
        $assignments = $this->model->getAllAssignments();
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory_assignments/index.php';
    }

    // Add new assignment
    public function add()
    {
        if ($this->model === null) {
            error_log("Model not loaded properly!");
            http_response_code(500);
            echo "Internal Server Error";
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session_start();

            $user_id = intval($_POST['user_id']);
            $item_ids = $_POST['inventory_id']; 
            $date_assigned = $_POST['date_assigned'];
            $manager_email = $_POST['managed_by'];

            // Call model to perform assignment
            $result = $this->model->addAssignment($user_id, $item_ids, $date_assigned, $manager_email);

            if (strpos($result, 'successfully') !== false) {
                // Prepare recipient info
                $recipientData = $this->model->getUserById($user_id); // expects ['email' => ...]
                $recipientEmail = $recipientData['email'];

                // Format recipient name: "rita.kogi" -> "Rita Kogi"
                $emailPrefix = explode('@', $recipientEmail)[0];
                $recipientName = implode(' ', array_map('ucfirst', explode('.', $emailPrefix)));

                error_log("Recipient Email: $recipientEmail");
                error_log("Recipient Name: $recipientName");

                // Get assigner info from session
                $assignerEmail = $_SESSION['user_email'] ?? '';
                $assignerProfile = $this->model->getUserProfileByEmail($assignerEmail); 
                // Expected keys: ['email' => ..., 'position' => ..., 'department' => ...]

                // Derive assigner name from email prefix
                if (!empty($assignerProfile['email'])) {
                    $assignerEmailPrefix = explode('@', $assignerProfile['email'])[0];
                    $assignerProfile['name'] = implode(' ', array_map('ucfirst', explode('.', $assignerEmailPrefix)));
                } else {
                    $assignerProfile['name'] = 'N/A';
                }

                error_log("Assigner Profile: " . print_r($assignerProfile, true));

                // Get item summary list with serial, tag, description
                $itemList = $this->model->getItemSummariesByIds($item_ids);

                // Send acknowledgment email to user
                $this->sendAcknowledgmentEmailToUser($recipientEmail, $recipientName, $itemList, $assignerProfile);

                // Send notification to manager
                $managerName = implode(' ', array_map('ucfirst', explode('.', explode('@', $manager_email)[0])));
                $this->sendAssignmentNotificationToManager($manager_email, $managerName, $recipientName, $itemList);

                // Redirect with success
                header("Location: " . URL . "inventoryassignment?success=" . urlencode($result));
            } else {
                // Redirect with error
                header("Location: " . URL . "inventoryassignment?error=" . urlencode($result));
            }

            exit();
        } else {
            $unassignedItems = $this->model->getUnassignedItems();
            $users = $this->model->getAllUsers();
            $offices = $this->model->getOffices();

            require APP . 'view/_templates/sessions.php';
            require APP . 'view/_templates/header.php';
            require APP . 'view/inventory_assignments/add_assignment.php';
        }
    }

    //email to user to acknowledge assigned items
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

            // Format item list using pre-formatted strings
            $itemListHtml = "<ul>";
            foreach ($itemList as $itemString) {
                $itemListHtml .= "<li>" . htmlspecialchars($itemString) . "</li>";
            }
            $itemListHtml .= "</ul>";

            $assignerName = htmlspecialchars($assigner['name'] ?? 'N/A');
            $assignerPosition = htmlspecialchars($assigner['position'] ?? 'N/A');
            $assignerDepartment = htmlspecialchars($assigner['department'] ?? 'N/A');

            $mail->Body = "
                <p>Dear {$recipientName},</p>

                <p>This is to inform you that item(s) have been issued to you through the MLE Inventory System. 
                You are required to log in and acknowledge receipt of the item(s) within the next <strong>two (2) working days</strong>.</p>

                {$itemListHtml}

                <p>You can log in here: <a href='https://mleinventory.evidenceaction.org'>https://mleinventory.evidenceaction.org</a></p>

                <p><strong>Please note:</strong> Failure to acknowledge may affect future inventory tracking and accountability.</p>

                <p>If you experience any issues accessing the system or identifying the issued item(s), kindly reach out to the <strong>IT Department</strong> for assistance.</p>

                <p>Thank you for your prompt attention.</p>

                <p>Best regards,<br>
                {$assignerName}<br>
                {$assignerPosition}<br>
                {$assignerDepartment}<br>
                Evidence Action</p>
            ";

            $mail->AltBody = "You have been assigned items. Please log in to the MLE Inventory Tool and acknowledge receipt within 2 working days: https://mleinventory.evidenceaction.org";

            $mail->CharSet = 'UTF-8';

            $mail->send();
            error_log("Acknowledgment email sent to: {$recipientEmail}");
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
        }
    }

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

                <p>This is to notify you that your supervisee <strong>{$recipientName}</strong> has been assigned the following inventory item(s):</p>

                {$itemListHtml}

                <p>If you believe this assignment is incorrect, kindly contact the IT department immediately.</p>

                <p>Regards,<br>MLE Inventory Tool</p>
            ";

            $mail->AltBody = "{$recipientName} has been assigned inventory item(s).";

            $mail->CharSet = 'UTF-8';
            $mail->send();
            error_log("Manager notification sent to: {$managerEmail}");
        } catch (Exception $e) {
            error_log("PHPMailer Manager Error: " . $mail->ErrorInfo);
        }
    }

//automated reminder
protected function sendReminderToUnacknowledgedUsers($usersWithPendingAssignments)
{
    $batchSize = 10;
    $totalUsers = count($usersWithPendingAssignments);
    $batches = array_chunk($usersWithPendingAssignments, $batchSize);

    foreach ($batches as $index => $batch) {
        foreach ($batch as $user) {
            $recipientEmail = $user['email'];
            $recipientName = ucwords(str_replace('.', ' ', explode('@', $recipientEmail)[0]));

            // Get reminder count for this user
            $reminderCount = $this->getReminderCountForUser($recipientEmail);
            
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'information.systems@evidenceaction.org';
                $mail->Password   = 'rtnbqnbajjhcifbr'; // Consider using env var
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->SMTPKeepAlive = true;

                $mail->setFrom('information.systems@evidenceaction.org', 'MLE Inventory Tool');

                // For testing:
                // $mail->addAddress('rita.kogi@evidenceaction.org');
                // For production:
                $mail->addAddress($recipientEmail, $recipientName);
                $mail->addBCC('information.systems@evidenceaction.org');

                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                
                // Customize subject based on reminder count
                if ($reminderCount == 0) {
                    $mail->Subject = 'Reminder: Please Acknowledge Your Assigned Item(s) in MLE Inventory Tool';
                } else {
                    $mail->Subject = 'Follow-up Reminder #' . ($reminderCount + 1) . ': Pending Item Acknowledgment Required';
                }

                // Get days pending
                $daysPending = $this->getDaysPendingForUser($recipientEmail);
                
                $mail->Body = "
                    <p>Dear {$recipientName},</p>

                    <p>Our records show that you have been assigned one or more inventory items that are now <strong>{$daysPending} days overdue</strong> for acknowledgment in the <strong>MLE Inventory Tool</strong>.</p>
                    
                    <p><strong>Reminder #" . ($reminderCount + 1) . "</strong></p>

                    <p><strong>Action Required:</strong> Kindly log in to the system and acknowledge the item(s) assigned to you as soon as possible. Items must be acknowledged within 30 days of assignment.</p>

                    <p><a href='https://mleinventory.evidenceaction.org/login/index' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Click here to log in</a></p>

                    <p>If there's a mismatch between the listed items and what you actually received, or if you're unable to access your account, please contact:</p>
                    <ul>
                        <li><a href='mailto:johnmark.oyugi@evidenceaction.org'>johnmark.oyugi@evidenceaction.org</a></li>
                        <li><a href='mailto:terence.wandera@evidenceaction.org'>terence.wandera@evidenceaction.org</a></li>
                    </ul>

                    <p>If you're experiencing navigation or technical difficulties, feel free to reach out to anyone in the IS department.</p>

                    <p><em>Note: This is an automated reminder. You will continue to receive reminders every 30 days until the items are acknowledged.</em></p>

                    <p>Thank you for your cooperation.</p>

                    <p>Best regards,<br>
                    MLE-D DEPARTMENT<br>
                    Evidence Action</p>
                ";

                $mail->AltBody = "Dear {$recipientName},\n\nYou have unacknowledged inventory items that are {$daysPending} days overdue. Reminder #" . ($reminderCount + 1) . ". Please log in to https://mleinventory.evidenceaction.org/login/index to acknowledge. For support, contact johnmark.oyugi@evidenceaction.org or terence.wandera@evidenceaction.org.\n\nYou will receive reminders every 30 days until acknowledgment.\n\nRegards,\nRita Kogi";

                $mail->send();
                
                // Update reminder tracking in database
                $this->model->updateReminderTracking($recipientEmail);

                error_log("Reminder #" . ($reminderCount + 1) . " email sent to: {$recipientEmail} (Days pending: {$daysPending})");
            } catch (Exception $e) {
                error_log("PHPMailer Error (Reminder): {$mail->ErrorInfo} for email: {$recipientEmail}");
            }
        }

        // Pause briefly between batches to reduce SMTP load or avoid timeouts
        sleep(1); // pause 1 second after each batch
    }

    error_log("✅ Finished sending reminder emails to {$totalUsers} users in " . count($batches) . " batches.");
}

// Helper method
protected function getReminderCountForUser($email)
{
    $sql = "SELECT MAX(reminder_count) as count 
            FROM inventory_assignment 
            WHERE email = :email 
            AND acknowledgment_status = 'pending'";
    
    $query = $this->db->prepare($sql);
    $query->bindValue(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    
    return $result ? (int)$result['count'] : 0;
}

protected function getDaysPendingForUser($email)
{
    $sql = "SELECT DATEDIFF(CURDATE(), MIN(date_assigned)) as days_pending 
            FROM inventory_assignment 
            WHERE email = :email 
            AND acknowledgment_status = 'pending'";
    
    $query = $this->db->prepare($sql);
    $query->bindValue(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['days_pending'] : 30;
}

public function triggerAcknowledgmentReminders()
{
    session_start();

    if ($this->model === null) {
        $_SESSION['reminder_success'] = "Model not loaded.";
        header("Location: " . URL . "inventoryassignment/manage");
        exit();
    }

    // Use the NEW method that gets ALL users
    $users = $this->model->getAllUsersWithPendingAcknowledgment();

    if (!empty($users)) {
        // This function already handles batching internally
        $this->sendReminderToUnacknowledgedUsers($users);
        
        $_SESSION['reminder_success'] = "✅ All reminder emails sent to " . count($users) . " users.";
        header("Location: " . URL . "inventoryassignment/index");
        exit();
    } else {
        $_SESSION['reminder_success'] = "No users with pending acknowledgments found.";
        header("Location: " . URL . "inventoryassignment/index");
        exit();
    }
}


    //edit assignment
    public function edit($id) 
    {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }

        $assignment = $this->model->getAssignmentById($id);

        if (!$assignment) {
            echo "Assignment not found.";
            return;
        }

        // Prevent editing if already acknowledged
        if ($assignment['acknowledgment_status'] !== 'pending') {
            echo "Editing not allowed. The assignment has been acknowledged.";
            return;
        }

        // Fetch necessary data
        $unassignedItems = $this->model->getUnassignedItems();
        $users = $this->model->getAllUsers();
        $offices = $this->model->getOffices(); 

        // ✅ Merge already assigned items to the unassigned list for dropdown
        foreach ($assignment['items'] as $assignedItem) {
            $found = false;
            foreach ($unassignedItems as $item) {
                if ($item['id'] == $assignedItem['id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $unassignedItems[] = $assignedItem;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updatedData = [
                'user_id' => $_POST['user_id'],
                'date_assigned' => $_POST['date_assigned'],
                'managed_by' => $_POST['managed_by']
            ];

            if (!empty($_POST['inventory_id']) && is_array($_POST['inventory_id'])) {
                $inventory_ids = $_POST['inventory_id'];
            } else {
                header("Location: " . URL . "inventoryassignment/edit/$id?error=Invalid+inventory+selection");
                exit();
            }

            $result = $this->model->updateAssignment($id, $updatedData, $inventory_ids);

            if ($result) {
                // === NEW: Send emails ===
                $recipientData = $this->model->getUserById($updatedData['user_id']);
                $recipientEmail = $recipientData['email'];
                $emailPrefix = explode('@', $recipientEmail)[0];
                $recipientName = implode(' ', array_map('ucfirst', explode('.', $emailPrefix)));

                session_start();
                $assignerEmail = $_SESSION['user_email'] ?? '';
                $assignerProfile = $this->model->getUserProfileByEmail($assignerEmail);
                if (!empty($assignerProfile['email'])) {
                    $emailPrefix = explode('@', $assignerProfile['email'])[0];
                    $assignerProfile['name'] = implode(' ', array_map('ucfirst', explode('.', $emailPrefix)));
                } else {
                    $assignerProfile['name'] = 'N/A';
                }

                $itemList = $this->model->getItemSummariesByIds($inventory_ids);

                $this->sendAcknowledgmentEmailToUser($recipientEmail, $recipientName, $itemList, $assignerProfile);

                $managerName = implode(' ', array_map('ucfirst', explode('.', explode('@', $updatedData['managed_by'])[0])));
                $this->sendAssignmentNotificationToManager($updatedData['managed_by'], $managerName, $recipientName, $itemList);
                // === END email ===

                header("Location: " . URL . "inventoryassignment?success=" . urlencode("Assignment Updated Successfully"));
                exit();
            } else {
                header("Location: " . URL . "inventoryassignment/edit/$id?error=Update+Failed");
                exit();
            }
        } else {
            require APP . 'view/_templates/sessions.php';
            require APP . 'view/_templates/header.php';
            require APP . 'view/inventory_assignments/edit_assignment.php';
        }
    }
    
    //delete assignment
    public function delete() {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment_id'])) {
            $id = $_POST['assignment_id'];
            $success = $this->model->deleteAssignment($id);
            if ($success) {
                header("Location: " . URL . "inventoryassignment/index?success=deleted");
                exit();
            } else {
                echo "Deletion failed. The assignment may not be pending.";
            }
        }
    }
    
    // Show pending assignments for the logged-in user
    public function pending()
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

        $user_email = $_SESSION['user_email']; 
        $pendingAssignments = $this->model->getPendingAssignmentsByLoggedInUser($user_email);
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory_assignments/pending_assignments.php';
    }

    // Acknowledge selected assignments
    public function acknowledge()
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

        $user_email = $_SESSION['user_email'];
        $assignment_id = $_POST['assignment_id'] ?? null;
        $device_state = trim($_POST['device_state'] ?? '');

        if (!$assignment_id || empty($device_state)) {
            $_SESSION['error_message'] = "Assignment ID and device state are required.";
            header("Location: " . URL . "inventoryassignment/pending");
            exit();
        }

        $updated_rows = $this->model->acknowledgeAssignment($assignment_id, $user_email, $device_state);

        if ($updated_rows > 0) {
            $_SESSION['success_message'] = "Item acknowledged successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to acknowledge item. Please try again.";
        }

        header("Location: " . URL . "inventoryassignment/pending");
        exit();
    }

    //report
    //managers reports of assigned items
    public function staffassignments()
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
        $assignments = $this->model->getAssignmentsByHierarchy($loggedInEmail);

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory_assignments/assignments_hierarchy.php';
    }

    //download reports
    public function downloadAssignments()
    {
        session_start();
    
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
        if ($this->model === null) {
            $_SESSION['error_message'] = "Model not loaded properly!";
            header("Location: " . URL . "inventoryassignment/staffassignments"); // Redirect to staff assignments page
            exit();
        }
    
        $loggedInEmail = $_SESSION['user_email'];
        $assignments = $this->model->getAssignmentsForDownload($loggedInEmail);
    
        if (empty($assignments)) {
            $_SESSION['error_message'] = "No data available for download.";
            header("Location: " . URL . "inventoryassignment/staffassignments"); // Redirect to staff assignments page
            exit();
        }
    
        // Define the exact column headers you want
        $headers = [
            'user_email', 'department', 'position', 'location',
            'description', 'serial_number', 'tag_number',
            'date_assigned', 'managed_by', 'acknowledgment_status'
        ];
    
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="assignments.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
    
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers); // Write column headers
    
        foreach ($assignments as $row) {
            $filteredRow = [];
            foreach ($headers as $column) {
                $filteredRow[] = $row[$column] ?? ''; // Ensure columns match headers
            }
            fputcsv($output, $filteredRow);
        }
    
        fclose($output);
        exit();
    }
    
    //button for admis
    // Toggle reconfirm_enabled on all inventory_assignment records
    public function toggleReconfirmation()
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

        $adminEmail = $_SESSION['user_email'];
        $enabled = isset($_POST['enable_reconfirm']) && $_POST['enable_reconfirm'] == '1';

        // Check for existing active session
        $activeSession = $this->model->getActiveReconfirmationSession();

        // Helper to calculate working days
        function addWorkingDays(DateTime $date, int $days): DateTime {
            $count = 0;
            while ($count < $days) {
                $date->modify('+1 day');
                if ($date->format('N') < 6) { // Mon-Fri
                    $count++;
                }
            }
            return $date;
        }

        if ($enabled) {
            if ($activeSession) {
                $_SESSION['error'] = "Reconfirmation is already active. Only {$activeSession['initiated_by']} can deactivate it.";
            } else {
                // Start new session
                $sessionId = $this->model->startNewReconfirmationSession($adminEmail);

                if ($sessionId && is_numeric($sessionId)) {
                    $this->model->assignSessionToUnconfirmed((int)$sessionId);
                    $_SESSION['success'] = "Reconfirmation session started successfully.";

                    // Initial confirmation email
                    $groupedAssignments = $this->model->getAssignmentsNeedingConfirmation();

                    foreach ($groupedAssignments as $email => $data) {
                        $rawName = $data['name'];
                        $parts = explode('.', $rawName);
                        $prettyName = '';
                        foreach ($parts as $part) {
                            $prettyName .= ucfirst(strtolower($part)) . ' ';
                        }
                        $prettyName = trim($prettyName);

                        $tableRows = '';
                        foreach ($data['assignments'] as $item) {
                            $tableRows .= "<tr>
                                <td>{$item['description']}</td>
                                <td>{$item['tag_number']}</td>
                                <td>{$item['serial_number']}</td>
                            </tr>";
                        }

                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'information.systems@evidenceaction.org';
                            $mail->Password   = 'rtnbqnbajjhcifbr';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;
                            $mail->SMTPKeepAlive = true;

                            $mail->setFrom('information.systems@evidenceaction.org', 'MLE Inventory Tool');
                            $mail->addAddress($email, $prettyName);
                            $mail->addBCC('information.systems@evidenceaction.org');

                            $mail->isHTML(true);
                            $mail->Subject = 'Action Required: Confirm Your Assigned Item(s)';
                            $mail->Body    = "Hi {$prettyName},<br><br>"
                                . "You have been assigned the following item(s). Please log into the MLE Inventory Tool and confirm receipt within 5 working days.<br><br>"
                                . "Access the system here: <a href='https://mleinventory.evidenceaction.org'>https://mleinventory.evidenceaction.org</a><br><br>"
                                . "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse: collapse;'>"
                                . "<tr><th>Item</th><th>Tag Number</th><th>Serial Number</th></tr>"
                                . $tableRows
                                . "</table><br>"
                                . "Regards,<br>MLE Inventory Tool";

                            $mail->send();
                        } catch (Exception $e) {
                            error_log("Initial email could not be sent to {$email}. Error: {$mail->ErrorInfo}");
                        }
                    }
                } else {
                    $_SESSION['error'] = "Failed to start reconfirmation session. Please try again.";
                }
            }
        } else {
            if (!$activeSession) {
                $_SESSION['error'] = "No active reconfirmation session found.";
            } elseif ($activeSession['initiated_by'] !== $adminEmail) {
                $_SESSION['error'] = "Only {$activeSession['initiated_by']} can end this session.";
            } else {
                // Send reminders if needed
                $startDate = new DateTime($activeSession['start_date']);
                $now = new DateTime();
                $reminderDate = addWorkingDays(clone $startDate, 5);

                if ($now >= $reminderDate) {
                    $groupedAssignments = $this->model->getAssignmentsNeedingConfirmation();

                    foreach ($groupedAssignments as $email => $data) {
                        $rawName = $data['name'];
                        $parts = explode('.', $rawName);
                        $prettyName = '';
                        foreach ($parts as $part) {
                            $prettyName .= ucfirst(strtolower($part)) . ' ';
                        }
                        $prettyName = trim($prettyName);

                        $tableRows = '';
                        foreach ($data['assignments'] as $item) {
                            $tableRows .= "<tr>
                                <td>{$item['description']}</td>
                                <td>{$item['tag_number']}</td>
                                <td>{$item['serial_number']}</td>
                            </tr>";
                        }

                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'information.systems@evidenceaction.org';
                            $mail->Password   = 'rtnbqnbajjhcifbr';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;
                            $mail->SMTPKeepAlive = true;

                            $mail->setFrom('rita.kogi@evidenceaction.org', 'MLE Inventory Tool');
                            $mail->addAddress($email, $prettyName);
                            $mail->addBCC('information.systems@evidenceaction.org');

                            $mail->isHTML(true);
                            $mail->Subject = 'Reminder: Confirm Your Assigned Item(s)';
                            $mail->Body    = "Hi {$prettyName},<br><br>"
                                . "This is a reminder to confirm receipt of the following item(s). Please log into the MLE Inventory Tool to confirm.<br><br>"
                                . "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse: collapse;'>"
                                . "<tr><th>Item</th><th>Tag Number</th><th>Serial Number</th></tr>"
                                . $tableRows
                                . "</table><br>"
                                . "Regards,<br>MLE Inventory Tool";

                            $mail->send();
                        } catch (Exception $e) {
                            error_log("Reminder email could not be sent to {$email}. Error: {$mail->ErrorInfo}");
                        }
                    }
                }

                // Deactivate session and reset toggles
                $this->model->deactivateReconfirmationSession($activeSession['id']);
                $this->model->resetReconfirmToggle();
                $_SESSION['success'] = "Reconfirmation session ended.";
            }
        }

        header("Location: " . URL . "users/getUsers?");
        exit;
    }

    //btns for users to recinfirm
    public function confirm() 
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
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignmentId = $_POST['assignment_id'];
        
            $activeSession = $this->model->getActiveReconfirmationSession();
        
            if (!$activeSession) {
                $_SESSION['error'] = "No active reconfirmation session.";
                header("Location: " . URL . "inventoryreturn");
                exit();
            }
        
            if ($this->model->confirmAssignment($assignmentId, $activeSession['id'])) {

                $status = 'confirmed';
                $confirmedBy = $_SESSION['user_email']; 
                $this->model->recordConfirmation($assignmentId, $status, $confirmedBy);
        
                // Check if all items have been confirmed
                if ($this->model->allAssignmentsConfirmed()) {
                    $this->model->resetReconfirmToggle();
                }
        
                $_SESSION['success'] = "Item confirmed successfully.";
            } else {
                $_SESSION['error'] = "Failed to confirm item.";
            }
        
            header("Location: " . URL . "inventoryreturn");
            exit();
        }
    }
    
      
    //geetting annual reports
    public function reconfirmationReport()
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
    
        $year = isset($_GET['year']) ? (int)$_GET['year'] : null;
        $month = isset($_GET['month']) ? (int)$_GET['month'] : null;
    
        $reportData = $this->model->getReconfirmationReport($year, $month);
    
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/inventory_assignments/reconfirmation_report.php';
    }
    

}    

?>
