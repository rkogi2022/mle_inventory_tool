<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class consumables extends Controller
{
    // Show all consumables
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

        $items = $this->model->getAllConsumableItems();
        $users = $this->model->getAllStaffNames(); 

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/labheader.php';
        require APP . 'view/lab/consumables.php';
    }

    // Add new item
    public function addItem()
    {
        session_start();

        $data = [
            'item_name' => $_POST['item_name'],
            'item_code' => $_POST['item_code'],
            'unit' => $_POST['unit'],
            'reorder_level' => $_POST['reorder_level'],
            'expiry_date' => $_POST['expiry_date'] ?? null 
        ];

        if ($this->model->createConsumableItem($data)) {
            $_SESSION['message'] = "Item added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add item.";
        }

        header("Location: " . URL . "consumables");
        exit();
    }

    // Edit item
    public function editItem()
    {
        session_start();

        $id = $_POST['id'];

        $data = [
            'item_name' => $_POST['item_name'],
            'item_code' => $_POST['item_code'],
            'unit' => $_POST['unit'],
            'reorder_level' => $_POST['reorder_level'],
            'expiry_date' => $_POST['expiry_date'] ?? null 
        ];

        if ($this->model->updateConsumableItem($id, $data)) {
            $_SESSION['message'] = "Item updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update item.";
        }

        header("Location: " . URL . "consumables");
        exit();
    }

    // Delete item
    public function delete()
    {
        session_start();

        $id = $_GET['delete'];

        if ($this->model->deleteConsumableItem($id)) {
            $_SESSION['message'] = "Item deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete item.";
        }

        header("Location: " . URL . "consumables");
        exit();
    }

    // Record receipt
    public function addReceipt()
    {
        session_start();

        // Get logged-in user's ID from email
        if (!isset($_SESSION['user_email'])) {
            $_SESSION['error'] = "Session expired. Please login again.";
            header("Location: " . URL . "login");
            exit();
        }

        $user = $this->model->get_user_by_email($_SESSION['user_email']);
        $created_by = $user ? $user->id : null;

        $data = [
            'item_id' => $_POST['item_id'],
            'transaction_type' => 'receipt',
            'quantity' => $_POST['quantity'],
            'transaction_date' => $_POST['transaction_date'],
            'receiver_name' => $_POST['receiver_name'], // selected from dropdown
            'issuer_name' => null,
            'created_by' => $created_by
        ];

        if ($this->model->createConsumableTransaction($data)) {
            $_SESSION['message'] = "Receipt recorded successfully!";
        } else {
            $_SESSION['error'] = "Failed to record receipt.";
        }

        header("Location: " . URL . "consumables");
        exit();
    }

    // Record issue
    public function addIssue()
    {
        session_start();

        // Get logged-in user's ID from email
        if (!isset($_SESSION['user_email'])) {
            $_SESSION['error'] = "Session expired. Please login again.";
            header("Location: " . URL . "login");
            exit();
        }

        $user = $this->model->get_user_by_email($_SESSION['user_email']);
        $created_by = $user ? $user->id : null;

        $data = [
            'item_id' => $_POST['item_id'],
            'transaction_type' => 'issue',
            'quantity' => $_POST['quantity'],
            'transaction_date' => $_POST['transaction_date'],
            'receiver_name' => null,
            'issuer_name' => $_POST['issuer_name'], // selected from dropdown
            'created_by' => $created_by
        ];

        if ($this->model->createConsumableTransaction($data)) {
            $_SESSION['message'] = "Issue recorded successfully!";
        } else {
            $_SESSION['error'] = "Failed to record issue.";
        }

        header("Location: " . URL . "consumables");
        exit();
    }

    // View Bin Card
    public function viewBinCard($item_id)
    {
        session_start();

        $transactions = $this->model->getConsumableTransactions($item_id);
        $balance = $this->model->getConsumableBalance($item_id);
        $item = $this->model->getConsumableItem($item_id);

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/labheader.php';
        require APP . 'view/lab/bin_card.php';
    }
    //show quatery reports per year
    public function quarterlySummary()
    {
        session_start();

        // Check if year is selected via GET (e.g., ?year=2025)
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

        $items = $this->model->getQuarterlySummary($year);

        $summary = [];
        foreach ($items as $item) {
            $summary[1][] = [
                'item_name' => $item['item_name'],
                'received' => $item['Q1_received'],
                'issued' => $item['Q1_issued'],
            ];
            $summary[2][] = [
                'item_name' => $item['item_name'],
                'received' => $item['Q2_received'],
                'issued' => $item['Q2_issued'],
            ];
            $summary[3][] = [
                'item_name' => $item['item_name'],
                'received' => $item['Q3_received'],
                'issued' => $item['Q3_issued'],
            ];
            $summary[4][] = [
                'item_name' => $item['item_name'],
                'received' => $item['Q4_received'],
                'issued' => $item['Q4_issued'],
            ];
        }

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/labheader.php';
        require APP . 'view/lab/quarterly_summary.php';
    }

    //send email alert for expiring items
    public function sendExpiryNotification($items)
    {
        if (empty($items)) {
            return;
        }

        $mail = new PHPMailer(true);

        try {

            // EMAIL SETTINGS
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'information.systems@evidenceaction.org';
            $mail->Password   = 'rtnbqnbajjhcifbr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // EMAIL HEADERS
            $mail->setFrom('information.systems@evidenceaction.org', 'MLE Inventory Tool');

            // MAIN RECIPIENT
            // $mail->addAddress('information.systems@evidenceaction.org');
            $mail->addAddress('gentrix.obinda@evidenceaction.org');
            //Farida
            $mail->addBCC('rita.kogi@evidenceaction.org');

            // OPTIONAL TEAM BCC
            $mail->addBCC('information.systems@evidenceaction.org');

            $mail->isHTML(true);
            $mail->Subject = 'Consumable Items Expiry Alert';

            // EMAIL BODY
            $body = "<h3>Consumable items expiring within the next 3 months</h3>";
            $body .= "<table border='1' cellpadding='6' cellspacing='0'>";
            $body .= "<tr>
                        <th>Item Name</th>
                        <th>Item Code</th>
                        <th>Expiry Date</th>
                    </tr>";

            foreach ($items as $item) {

                $body .= "<tr>
                            <td>" . htmlspecialchars($item['item_name']) . "</td>
                            <td>" . htmlspecialchars($item['item_code']) . "</td>
                            <td>" . htmlspecialchars($item['expiry_date']) . "</td>
                        </tr>";
            }

            $body .= "</table>";
            $body .= "<br>Please take the necessary action before the expiry date.";

            $mail->Body = $body;

            $mail->send();

        } catch (Exception $e) {
            error_log("Expiry notification failed: " . $mail->ErrorInfo);
        }


    }

    public function checkExpiringItems()
    {
        session_start();

        $items = $this->model->getExpiringItems();

        if (!empty($items)) {

            $this->sendExpiryNotification($items);

            $_SESSION['message'] = "Expiry notification sent successfully.";

        } else {

            $_SESSION['message'] = "No items nearing expiry.";

        }

        header("Location: " . URL . "consumables");
        exit();
    }
}
