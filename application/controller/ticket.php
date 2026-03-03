<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ticket extends controller {

    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    //Initialize PHPMailer

    private function initMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'information.systems@evidenceaction.org';
            $mail->Password   = 'rtnbqnbajjhcifbr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Default from address
            $mail->setFrom('information.systems@evidenceaction.org', 'MLE Inventory Tool');
            
            return $mail;
            
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return null;
        }
    }

    //Get recipients based on ticket category

    private function getRecipientsForCategory($category) {
        $emails = [];
        
        // Hardware tickets go to IT department
        if ($category === 'hardware') {
            $stmt = $this->db->prepare("
                SELECT s.email 
                FROM staff_login s
                JOIN departments d ON s.department = d.id 
                WHERE (d.department_name LIKE '%IT%' 
                       OR d.department_name LIKE '%Information Technology%')
                   AND s.email IS NOT NULL
                   AND s.email != ''
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as $row) {
                $email = trim($row['email']);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
            
            // Fallback if no IT staff found
            if (empty($emails)) {
                $emails[] = 'information.systems@evidenceaction.org';
            }
        } 
        // All other tickets go to Information Systems department
        else {
            $stmt = $this->db->prepare("
                SELECT s.email 
                FROM staff_login s
                JOIN departments d ON s.department = d.id 
                WHERE (d.department_name LIKE '%Information Systems%'
                       OR d.department_name LIKE '%Information System%')
                   AND s.email IS NOT NULL
                   AND s.email != ''
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($results as $row) {
                $email = trim($row['email']);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
            
            // Fallback if no IS staff found
            if (empty($emails)) {
                $emails[] = 'information.systems@evidenceaction.org';
            }
        }
        
        return array_unique($emails);
    }

    //Send new ticket notification

    // private function sendNewTicketNotification($ticketId) {
    //     try {
    //         // Get ticket details
    //         $stmt = $this->db->prepare("
    //             SELECT t.*, 
    //                 u.email as requester_email,
    //                 u.email as requester_name, -- Use email as name since no first/last name
    //                 d.department_name as requester_department
    //             FROM tickets t
    //             JOIN staff_login u ON t.user_id = u.id
    //             LEFT JOIN departments d ON u.department = d.id
    //             WHERE t.id = ?
    //         ");
    //         $stmt->execute([$ticketId]);
    //         $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
    //         // Format name from email
    //         if ($ticket) {
    //             $email_parts = explode('@', $ticket['requester_email']);
    //             $ticket['requester_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $email_parts[0]));
    //         }
            
    //         // Get recipients
    //         $recipients = $this->getRecipientsForCategory($ticket['category']);
            
    //         // Initialize mailer
    //         $mail = $this->initMailer();
    //         if (!$mail) {
    //             return false;
    //         }
            
    //         // Add recipients
    //         $toRecipients = array_slice($recipients, 0, 10); // Limit to 10
    //         foreach ($toRecipients as $email) {
    //             $mail->addAddress($email);
    //         }
            
    //         // Always BCC information.systems
    //         $mail->addBCC('information.systems@evidenceaction.org');
            
    //         // Set subject and body
    //         $mail->Subject = "[Ticket #{$ticket['ticket_number']}] {$ticket['subject']}";
    //         $mail->Body = $this->createNewTicketEmailBody($ticket);
    //         $mail->AltBody = $this->createPlainTextBody($ticket);
            
    //         // Send email
    //         $sent = $mail->send();
            
    //         // Log the email send
    //         if ($sent) {
    //             $this->logStatusChange($ticketId, $ticket['status'], $ticket['status'], 
    //                 $ticket['user_id'], "Email notification sent to " . count($toRecipients) . " recipients");
    //         }
            
    //         return $sent;
            
    //     } catch (Exception $e) {
    //         error_log("Failed to send ticket notification: " . $e->getMessage());
    //         return false;
    //     }
    // }
    private function sendNewTicketNotification($ticketId) {
    try {
        // Get ticket details (same as before)
        $stmt = $this->db->prepare("
            SELECT t.*, 
                u.email as requester_email,
                u.email as requester_name,
                d.department_name as requester_department
            FROM tickets t
            JOIN staff_login u ON t.user_id = u.id
            LEFT JOIN departments d ON u.department = d.id
            WHERE t.id = ?
        ");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ticket) {
            $email_parts = explode('@', $ticket['requester_email']);
            $ticket['requester_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $email_parts[0]));
        }
        
        // Initialize mailer
        $mail = $this->initMailer();
        if (!$mail) {
            return false;
        }
        
        // ===== TEST MODE: OVERRIDE ALL RECIPIENTS =====
        // Comment out the original recipient logic and use this:
        
        // Clear any existing addresses
        $mail->clearAddresses();
        $mail->clearBCCs();
        $mail->clearCCs();
        
        // Send ONLY to your personal email
        $mail->addAddress('rhyttahkogi@gmail.com', 'Test User');
        
        // Add test indicators
        $mail->Subject = "[TEST - Ticket #{$ticket['ticket_number']}] {$ticket['subject']}";
        $mail->Body = '<div style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">' .
                      '<strong>🔬 TEST MODE:</strong> This ticket was sent to rhyttahkogi@gmail.com for testing</div>' . 
                      $this->createNewTicketEmailBody($ticket);
        $mail->AltBody = "[TEST MODE] " . $this->createPlainTextBody($ticket);
        
        // ===== END TEST MODE =====
        
        // Send email
        $sent = $mail->send();
        
        if ($sent) {
            $this->logStatusChange($ticketId, $ticket['status'], $ticket['status'], 
                $ticket['user_id'], "TEST MODE: Email sent to rhyttahkogi@gmail.com");
            error_log("TEST: Ticket {$ticket['ticket_number']} sent to rhyttahkogi@gmail.com");
        }
        
        return $sent;
        
    } catch (Exception $e) {
        error_log("TEST - Failed to send ticket notification: " . $e->getMessage());
        return false;
    }
}

    //Send resolution notification

    // private function sendResolutionNotification($ticketId, $resolverId, $resolutionNotes = '') {
    //     try {
    //         // Get ticket and resolver details
    //     $stmt = $this->db->prepare("
    //         SELECT t.*, 
    //                u.email as requester_email,
    //                u.email as requester_name,
    //                r.email as resolver_email,
    //                r.email as resolver_name
    //         FROM tickets t
    //         JOIN staff_login u ON t.user_id = u.id
    //         JOIN staff_login r ON r.id = ?
    //         WHERE t.id = ?
    //     ");
    //     $stmt->execute([$resolverId, $ticketId]);
    //     $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
    //     // Format names from emails
    //     if ($data) {
    //         $requester_parts = explode('@', $data['requester_email']);
    //         $data['requester_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $requester_parts[0]));
            
    //         $resolver_parts = explode('@', $data['resolver_email']);
    //         $data['resolver_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $resolver_parts[0]));
    //     }
            
    //         // Initialize mailer
    //         $mail = $this->initMailer();
    //         if (!$mail) {
    //             return false;
    //         }
            
    //         // Send to requester
    //         $mail->addAddress($data['requester_email'], $data['requester_name']);
    //         $mail->addBCC('information.systems@evidenceaction.org');
            
    //         // Set subject and body
    //         $mail->Subject = "[RESOLVED - Ticket #{$data['ticket_number']}] {$data['subject']}";
    //         $mail->Body = $this->createResolutionEmailBody($data, $resolutionNotes);
    //         $mail->AltBody = strip_tags($this->createResolutionEmailBody($data, $resolutionNotes));
            
    //         $sent = $mail->send();
            
    //         // Log the resolution email
    //         if ($sent) {
    //             $this->logStatusChange($ticketId, $data['status'], $data['status'], 
    //                 $resolverId, "Resolution email sent to requester");
    //         }
            
    //         return $sent;
            
    //     } catch (Exception $e) {
    //         error_log("Failed to send resolution notification: " . $e->getMessage());
    //         return false;
    //     }
    // }
    private function sendResolutionNotification($ticketId, $resolverId, $resolutionNotes = '') {
    try {
        // Get ticket and resolver details
        $stmt = $this->db->prepare("
            SELECT t.*, 
                   u.email as requester_email,
                   u.email as requester_name,
                   r.email as resolver_email,
                   r.email as resolver_name
            FROM tickets t
            JOIN staff_login u ON t.user_id = u.id
            JOIN staff_login r ON r.id = ?
            WHERE t.id = ?
        ");
        $stmt->execute([$resolverId, $ticketId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Format names from emails
        if ($data) {
            $requester_parts = explode('@', $data['requester_email']);
            $data['requester_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $requester_parts[0]));
            
            $resolver_parts = explode('@', $data['resolver_email']);
            $data['resolver_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $resolver_parts[0]));
        }
        
        // Initialize mailer
        $mail = $this->initMailer();
        if (!$mail) {
            return false;
        }
        
        // ===== TEST MODE: OVERRIDE ALL RECIPIENTS =====
        // Just like in sendNewTicketNotification, send to your test email
        
        // Clear any existing addresses
        $mail->clearAddresses();
        $mail->clearBCCs();
        $mail->clearCCs();
        
        // Send ONLY to your personal email
        $mail->addAddress('rhyttahkogi@gmail.com', 'Test User');
        
        // Add test indicators
        $mail->Subject = "[TEST - RESOLVED - Ticket #{$data['ticket_number']}] {$data['subject']}";
        $mail->Body = '<div style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">' .
                      '<strong>🔬 TEST MODE:</strong> This resolution notification was sent to rhyttahkogi@gmail.com for testing</div>' . 
                      $this->createResolutionEmailBody($data, $resolutionNotes);
        $mail->AltBody = "[TEST MODE] " . strip_tags($this->createResolutionEmailBody($data, $resolutionNotes));
        
        // ===== END TEST MODE =====
        
        $sent = $mail->send();
        
        // Log the resolution email
        if ($sent) {
            $this->logStatusChange($ticketId, $data['status'], $data['status'], 
                $resolverId, "TEST MODE: Resolution email sent to rhyttahkogi@gmail.com");
            error_log("TEST: Resolution email for Ticket {$data['ticket_number']} sent to rhyttahkogi@gmail.com");
        }
        
        return $sent;
        
    } catch (Exception $e) {
        error_log("TEST - Failed to send resolution notification: " . $e->getMessage());
        return false;
    }
}

    // Create HTML email body for new ticket

    private function createNewTicketEmailBody($ticket) {
        // Detect the protocol dynamically
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . '://' . $host . '/mle_inventory_tool';
        
        $viewUrl = $baseUrl . "/ticket/view/" . $ticket['id'];
        
        $priorityColors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark'
        ];
        
        $priorityClass = $priorityColors[$ticket['priority']] ?? 'secondary';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; border-radius: 5px; }
                .priority-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; color: white; font-size: 12px; }
                .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: white; 
                    text-decoration: none; border-radius: 6px; font-weight: bold; }
                .btn-login { background: #28a745; margin-left: 10px; }
                .details-box { background: white; border: 1px solid #ddd; border-radius: 5px; padding: 20px; margin: 20px 0; }
                .bg-success { background: #28a745; }
                .bg-warning { background: #ffc107; color: #212529; }
                .bg-danger { background: #dc3545; }
                .bg-dark { background: #343a40; }
                .login-note { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin: 0 0 10px 0;'>📋 New Support Ticket</h2>
                    <h3 style='color: #666; margin: 0;'>#{$ticket['ticket_number']}</h3>
                </div>
                
                <div class='login-note'>
                    <strong>🔐 Note:</strong> You will be redirected to the login page first if you are not already logged in. 
                    After logging in, you'll be taken directly to this ticket.
                </div>
                
                <div class='details-box'>
                    <h3 style='margin-top: 0;'>{$ticket['subject']}</h3>
                    
                    <div style='display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;'>
                        <div>
                            <strong>Category:</strong><br>
                            " . ucfirst($ticket['category']) . 
                            ($ticket['subcategory'] ? "<br><small>{$ticket['subcategory']}</small>" : "") . "
                        </div>
                        <div>
                            <strong>Priority:</strong><br>
                            <span class='priority-badge bg-{$priorityClass}'>
                                " . strtoupper($ticket['priority']) . "
                            </span>
                        </div>
                        <div>
                            <strong>Status:</strong><br>
                            <span style='color: #dc3545; font-weight: bold;'>OPEN</span>
                        </div>
                    </div>
                    
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <strong>Submitted By:</strong> {$ticket['requester_name']}<br>
                        <strong>Email:</strong> {$ticket['requester_email']}<br>
                        <strong>Department:</strong> {$ticket['requester_department']}<br>
                        <strong>Date:</strong> " . date('F j, Y g:i A', strtotime($ticket['created_at'])) . "
                    </div>
                    
                    <h4>Description:</h4>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;'>
                        " . nl2br(htmlspecialchars($ticket['description'])) . "
                    </div>
                    
                    " . (!empty($ticket['page_url']) ? "
                    <div style='margin-top: 15px;'>
                        <strong>Page URL:</strong><br>
                        <a href='{$ticket['page_url']}' style='color: #007bff;'>
                            {$ticket['page_url']}
                        </a>
                    </div>
                    " : "") . "
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$viewUrl}' class='btn' style='background: #007bff;'>
                        👉 View & Respond to Ticket
                    </a>
                    <p style='color: #666; margin-top: 15px; font-size: 14px;'>
                        <strong>How it works:</strong><br>
                        1. Click the button above<br>
                        2. Login with your credentials (if not already logged in)<br>
                        3. You'll be automatically redirected to this ticket<br>
                        4. Update status, assign staff, or add notes
                    </p>
                </div>
                
                <div style='font-size: 12px; color: #6c757d; text-align: center; border-top: 1px solid #dee2e6; padding-top: 20px;'>
                    <p>This is an automated notification from MLE Inventory Tool.</p>
                    <p>Ticket ID: {$ticket['id']} | Category: {$ticket['category']} | Status: Open</p>
                    <p style='margin-top: 10px;'>
                        <small>🔗 Direct link: <a href='{$viewUrl}' style='color: #6c757d;'>{$viewUrl}</a></small>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    //Create resolution email body

    private function createResolutionEmailBody($data, $resolutionNotes = '') {
        $feedbackUrl = URL . "ticket/feedback/" . $data['id'];
        $ticketUrl = URL . "ticket/view/" . $data['id'];
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 700px; margin: 0 auto; padding: 20px; }
                .header { background: #d4edda; padding: 20px; border-radius: 5px; color: #155724; }
                .btn { display: inline-block; padding: 12px 24px; margin: 8px; color: white; 
                       text-decoration: none; border-radius: 6px; font-weight: bold; }
                .btn-primary { background: #007bff; }
                .btn-success { background: #28a745; }
                .info-box { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2 style='margin: 0;'>✅ Ticket Resolved</h2>
                    <p style='margin: 10px 0 0 0; font-size: 18px;'>
                        Ticket #{$data['ticket_number']}: {$data['subject']}
                    </p>
                </div>
                
                <div class='info-box'>
                    <h3 style='margin-top: 0;'>{$data['subject']}</h3>
                    
                    <div style='display: flex; gap: 20px; margin-bottom: 15px; flex-wrap: wrap;'>
                        <div>
                            <strong>Resolved By:</strong><br>
                            {$data['resolver_name']}<br>
                            <small>{$data['resolver_email']}</small>
                        </div>
                        <div>
                            <strong>Resolved On:</strong><br>
                            " . date('F j, Y g:i A') . "
                        </div>
                        <div>
                            <strong>Category:</strong><br>
                            " . ucfirst($data['category']) . "
                        </div>
                    </div>
                    
                    " . (!empty($resolutionNotes) ? "
                    <div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
                        <strong>Resolution Notes:</strong><br>
                        " . nl2br(htmlspecialchars($resolutionNotes)) . "
                    </div>
                    " : "") . "
                    
                    <p style='color: #666;'>
                        This ticket has been marked as resolved. If your issue is not fully resolved, 
                        please reply to this email or visit the ticket page to reopen it.
                    </p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <p style='font-size: 16px; margin-bottom: 20px;'>Was your issue resolved satisfactorily?</p>
                    
                    <a href='{$ticketUrl}' class='btn btn-primary'>
                        📄 View Ticket Details
                    </a>
                    
                    <a href='{$feedbackUrl}' class='btn btn-success'>
                        ⭐ Provide Feedback
                    </a>
                    
                    <p style='color: #666; margin-top: 20px;'>
                        Your feedback helps us improve our service.
                    </p>
                </div>
                
                <div style='font-size: 12px; color: #6c757d; text-align: center; border-top: 1px solid #dee2e6; padding-top: 20px;'>
                    <p>This is an automated notification from MLE Inventory Tool.</p>
                    <p>If you have any questions, contact information.systems@evidenceaction.org</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    //Create plain text email body
    private function createPlainTextBody($ticket) {
        return "NEW SUPPORT TICKET\n" . str_repeat("=", 50) . "\n\n" .
               "Ticket #: {$ticket['ticket_number']}\n" .
               "Subject: {$ticket['subject']}\n" .
               "Category: {$ticket['category']}\n" .
               "Priority: {$ticket['priority']}\n" .
               "Submitted By: {$ticket['requester_name']} ({$ticket['requester_email']})\n" .
               "Submitted: " . date('F j, Y g:i A', strtotime($ticket['created_at'])) . "\n\n" .
               "DESCRIPTION:\n{$ticket['description']}\n\n" .
               ($ticket['page_url'] ? "Page URL: {$ticket['page_url']}\n\n" : "") .
               "View Ticket: " . URL . "ticket/view/{$ticket['id']}\n\n" .
               str_repeat("-", 50) . "\n" .
               "Automated notification from MLE Inventory Tool";
    }

    // status change
    private function logStatusChange($ticketId, $oldStatus, $newStatus, $changedBy, $notes = '') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ticket_status_log 
                (ticket_id, old_status, new_status, changed_by, notes, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$ticketId, $oldStatus, $newStatus, $changedBy, $notes]);
            return true;
        } catch (Exception $e) {
            error_log("Failed to log status change: " . $e->getMessage());
            return false;
        }
    }

    // AJAX: Get subcategories for a category
    public function getSubcategoriesAjax() {
        $category = trim($_GET['category'] ?? '');
        $category = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');

        $allSubcategories = [
            'hardware' => ['Laptop', 'Printer', 'Monitor', 'Phone', 'Desktop'],
            'training' => ['Orientation', 'Refresher Training'],
            'other'    => ['General Inquiry', 'Feedback', 'Bug Report', 'Feature Request']
        ];

        $subcategories = $allSubcategories[$category] ?? [];

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'subcategories' => $subcategories]);
    }

    //AJAX: Create ticket 
    public function createAjax()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Debug logging
        error_log("DEBUG - Ticket creation started");
        error_log("DEBUG - Session data: " . print_r($_SESSION, true));
        
        // Get form data
        $subject = trim($_POST['subject'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $subcategory = trim($_POST['subcategory'] ?? '');
        $priority = trim($_POST['priority'] ?? 'medium');
        $description = trim($_POST['description'] ?? '');
        $page_url = trim($_POST['current_url'] ?? '');
        
        // Debug form data
        error_log("DEBUG - Form data:");
        error_log("  Subject: " . $subject);
        error_log("  Category: " . $category);
        error_log("  Subcategory: " . $subcategory);
        error_log("  Priority: " . $priority);
        error_log("  Description length: " . strlen($description));
        error_log("  Page URL: " . $page_url);
        
        // Validate required fields
        if (empty($subject) || empty($category) || empty($description)) {
            error_log("ERROR - Missing required fields");
            echo json_encode(['success' => false, 'message' => 'Subject, category, and description are required']);
            return;
        }
        
        // Validate category
        $validCategories = ['hardware', 'training', 'other'];
        if (!in_array($category, $validCategories)) {
            error_log("ERROR - Invalid category: " . $category);
            echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
            return;
        }
        
        // Get user info
        $user_email = $_SESSION['user_email'] ?? '';
        
        if (empty($user_email)) {
            error_log("ERROR - No user_email in session");
            echo json_encode(['success' => false, 'message' => 'You must be logged in to create a ticket.']);
            return;
        }
        
        // Look up user ID
        $user_id = $this->getUserIdByEmail($user_email);
        
        if (empty($user_id)) {
            error_log("ERROR - User not found for email: " . $user_email);
            echo json_encode(['success' => false, 'message' => 'User account not found.']);
            return;
        }
        
        error_log("DEBUG - User ID found: " . $user_id);
        
        // Handle file upload
        $attachment_path = null;
        $attachment_name = null;
        
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../uploads/tickets/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 
                            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if ($_FILES['attachment']['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
                return;
            }

            if (!in_array($_FILES['attachment']['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type']);
                return;
            }

            // Generate unique filename
            $fileExt = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $fileName = 'ticket_' . time() . '_' . uniqid() . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $filePath)) {
                $attachment_path = $filePath;
                $attachment_name = $_FILES['attachment']['name'];
                error_log("DEBUG - File uploaded: " . $attachment_name);
            }
        }
        
        $model = new Model($this->db);
        
        // Generate ticket number
        $ticket_number = $model->generateTicketNumber();
        
        // Prepare ticket data
        $ticket_data = [
            'ticket_number' => $ticket_number,
            'user_id' => $user_id,
            'subject' => $subject,
            'category' => $category,
            'subcategory' => $subcategory,
            'priority' => $priority,
            'description' => $description,
            'page_url' => $page_url,
            'attachment_path' => $attachment_path,
            'attachment_name' => $attachment_name,
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        error_log("DEBUG - Ticket data ready for insertion");
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Create ticket
            $ticket_id = $model->createTicket($ticket_data);
            
            if (!$ticket_id) {
                throw new Exception("Failed to create ticket in database");
            }
            
            // Log initial status
            $this->logStatusChange($ticket_id, null, 'open', $user_id, 'Ticket created');
            
            // Send email notification
            $emailSent = $this->sendNewTicketNotification($ticket_id);
            error_log("DEBUG - Email notification sent: " . ($emailSent ? 'Yes' : 'No'));
            
            // Commit transaction
            $this->db->commit();
            
            error_log("SUCCESS - Ticket created with ID: " . $ticket_id . " Number: " . $ticket_number);
            
            echo json_encode([
                'success' => true,
                'message' => 'Ticket created successfully!' . ($emailSent ? '' : ' Email notification may be delayed.'),
                'ticket_id' => $ticket_id,
                'ticket_number' => $ticket_number,
                'email_sent' => $emailSent
            ]);
            
        } catch (Exception $e) {
            $this->db->rollBack();
            
            // Clean up uploaded file
            if ($attachment_path && file_exists($attachment_path)) {
                unlink($attachment_path);
            }
            
            error_log("ERROR - Ticket creation failed: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ]);
        }
    }

    // AJAX: Submit feedback
    public function submitFeedbackAjax() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $ticket_id = $_POST['ticket_id'] ?? 0;
        $rating = $_POST['rating'] ?? 0;
        $satisfaction = $_POST['satisfaction'] ?? '';
        $comments = trim($_POST['comments'] ?? '');
        $user_id = $_SESSION['user_id'] ?? 0;
        
        // Verify ticket belongs to user
        $stmt = $this->db->prepare("SELECT user_id FROM tickets WHERE id = ?");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket || $ticket['user_id'] != $user_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ticket']);
            return;
        }
        
        try {
            // Insert feedback
            $stmt = $this->db->prepare("
                INSERT INTO ticket_feedback 
                (ticket_id, rating, satisfaction, comments, submitted_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$ticket_id, $rating, $satisfaction, $comments]);
            
            // Update ticket
            $stmt = $this->db->prepare("
                UPDATE tickets SET feedback_submitted = 1 WHERE id = ?
            ");
            $stmt->execute([$ticket_id]);
            
            echo json_encode(['success' => true, 'message' => 'Feedback submitted successfully']);
            
        } catch (Exception $e) {
            error_log("Feedback error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Submission failed']);
        }
    }

    // View single ticket with management features
    public function view($ticket_id = null)
    {
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check login - FIX: REDIRECT instead of echo
        if (empty($_SESSION['user_email'])) {
            // Store the ticket ID to redirect back after login
            if (!empty($ticket_id)) {
                $_SESSION['redirect_after_login'] = 'ticket/view/' . $ticket_id;
            } elseif (!empty($_GET['id'])) {
                $_SESSION['redirect_after_login'] = 'ticket/view/' . $_GET['id'];
            }
            
            // Redirect to login page
            header("Location: " . URL . "login");
            exit();
        }
        
        // Get current user info
        $user_email = $_SESSION['user_email'];
        $user_id = $_SESSION['user_id'] ?? $this->getUserIdByEmail($user_email);
        $user_name = ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $user_email)[0]));
        $user_role = $_SESSION['role'] ?? 'staff';
        
        // Get ticket ID
        if (empty($ticket_id)) {
            $ticket_id = $_GET['id'] ?? null;
        }
        
        if (empty($ticket_id)) {
            // Store error message in session and redirect
            $_SESSION['error_message'] = "Ticket ID is required";
            header("Location: " . URL . "ticket/viewall");
            exit();
        }
        
        // Convert to integer
        $ticket_id = (int)$ticket_id;
        
        // Get ticket details
        $stmt = $this->db->prepare("
            SELECT t.*, 
                u.email as requester_email,
                u.email as requester_name,
                d.department_name,
                a.email as assigned_email,
                a.email as assigned_name,
                r.email as resolver_email,
                r.email as resolver_name
            FROM tickets t
            JOIN staff_login u ON t.user_id = u.id
            LEFT JOIN departments d ON u.department = d.id
            LEFT JOIN staff_login a ON t.assigned_to = a.id
            LEFT JOIN staff_login r ON t.resolved_by = r.id
            WHERE t.id = ?
        ");
        $stmt->execute([$ticket_id]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$ticket) {
            $_SESSION['error_message'] = "Ticket ID " . $ticket_id . " not found";
            header("Location: " . URL . "ticket/viewall");
            exit();
        }
        
        // Format names from emails
        $requester_parts = explode('@', $ticket['requester_email']);
        $ticket['requester_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $requester_parts[0]));
        
        if ($ticket['assigned_email']) {
            $assigned_parts = explode('@', $ticket['assigned_email']);
            $ticket['assigned_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $assigned_parts[0]));
        }
        
        if ($ticket['resolver_email']) {
            $resolver_parts = explode('@', $ticket['resolver_email']);
            $ticket['resolver_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $resolver_parts[0]));
        }
        
        // Get status history
        $stmt = $this->db->prepare("
            SELECT l.*, 
                u.email as changed_by_name
            FROM ticket_status_log l
            JOIN staff_login u ON l.changed_by = u.id
            WHERE l.ticket_id = ?
            ORDER BY l.created_at DESC
        ");
        $stmt->execute([$ticket_id]);
        $statusHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format changed_by names from emails
        foreach ($statusHistory as &$history) {
            $email_parts = explode('@', $history['changed_by_name']);
            $history['changed_by_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $email_parts[0]));
        }
        
        // Get feedback if exists
        $feedback = null;
        if ($ticket['feedback_submitted']) {
            $stmt = $this->db->prepare("SELECT * FROM ticket_feedback WHERE ticket_id = ?");
            $stmt->execute([$ticket_id]);
            $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Check permissions
        $is_requester = ($ticket['user_id'] == $user_id);
        $is_in_support = $this->isInSupportDepartment($user_id);
        $can_manage = $user_role === 'admin' || $user_role === 'super_admin' || $is_in_support;
        
        // Get staff for assignment dropdown
        $staff_list = [];
        if ($can_manage) {
            $staff_list = $this->getSupportStaff();
        }
        
        // Display the ticket view
        $this->renderTicketView($ticket, $user_name, $user_email, $user_id, $user_role, 
                            $statusHistory, $feedback, $can_manage, $is_requester, $staff_list);
    }

    //Render ticket view with all features
    private function renderTicketView($ticket, $user_name, $user_email, $user_id, $user_role, 
                                    $statusHistory, $feedback, $can_manage, $is_requester, $staff_list) {
        // Only show this view to the ticket creator
        if (!$is_requester) {
            return;
        }
        
        // Display the ticket
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?> - MLE Inventory Tool</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    margin: 0; 
                    padding: 20px; 
                    background-color: #f5f5f5; 
                }
                .ticket-container { 
                    max-width: 800px; 
                    margin: 0 auto; 
                    background: white; 
                    border-radius: 10px; 
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
                    padding: 30px; 
                }
                .ticket-header { 
                    border-bottom: 3px solid #007bff; 
                    padding-bottom: 20px; 
                    margin-bottom: 30px; 
                }
                .ticket-header h1 { 
                    color: #333; 
                    margin-bottom: 5px; 
                    font-size: 28px; 
                }
                .ticket-number { 
                    color: #666; 
                    font-size: 18px; 
                    font-weight: bold; 
                }
                .ticket-details { 
                    display: grid; 
                    grid-template-columns: repeat(2, 1fr); 
                    gap: 15px; 
                    margin-bottom: 30px; 
                }
                .detail-item { 
                    background: #f8f9fa; 
                    padding: 15px; 
                    border-radius: 5px; 
                    border-left: 4px solid #007bff; 
                }
                .detail-label { 
                    font-weight: bold; 
                    color: #666; 
                    margin-bottom: 5px; 
                    font-size: 14px; 
                }
                .detail-value { 
                    color: #333; 
                    font-size: 16px; 
                }
                .ticket-description { 
                    background: #f8f9fa; 
                    padding: 25px; 
                    border-radius: 5px; 
                    margin: 20px 0; 
                    line-height: 1.6; 
                    white-space: pre-wrap; 
                    border: 1px solid #e0e0e0; 
                }
                .btn-back { 
                    background: #6c757d; 
                    color: white; 
                    border: none; 
                    padding: 12px 25px; 
                    border-radius: 5px; 
                    cursor: pointer; 
                    font-size: 16px; 
                    margin-top: 20px; 
                    transition: background 0.3s; 
                }
                .btn-back:hover { 
                    background: #5a6268; 
                }
                .user-display { 
                    display: flex; 
                    align-items: center; 
                    gap: 10px; 
                }
                .user-icon { 
                    color: #e600a0; 
                    font-size: 20px; 
                }
                .user-email { 
                    font-size: 14px; 
                    color: #666; 
                    margin-top: 3px; 
                }
                .priority-low { color: #28a745; font-weight: bold; }
                .priority-medium { color: #ffc107; font-weight: bold; }
                .priority-high { color: #dc3545; font-weight: bold; }
                .priority-critical { color: #dc3545; font-weight: bold; }
                .status-open { color: #28a745; font-weight: bold; }
                .status-ongoing { color: #007bff; font-weight: bold; }
                .status-resolved { color: #20c997; font-weight: bold; }
                .status-closed { color: #6c757d; font-weight: bold; }
            </style>
            <!-- Font Awesome for icons -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        </head>
        <body>
            <div class="ticket-container">
                <div class="ticket-header">
                    <h1><?php echo htmlspecialchars($ticket['subject']); ?></h1>
                    <div class="ticket-number">Ticket #<?php echo htmlspecialchars($ticket['ticket_number']); ?></div>
                </div>
                
                <div class="ticket-details">
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value status-<?php echo strtolower($ticket['status']); ?>">
                            <?php echo htmlspecialchars(ucfirst($ticket['status'])); ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Priority</div>
                        <div class="detail-value priority-<?php echo strtolower($ticket['priority']); ?>">
                            <?php echo htmlspecialchars(ucfirst($ticket['priority'])); ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Category</div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars(ucfirst($ticket['category'])); ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Subcategory</div>
                        <div class="detail-value">
                            <?php echo $ticket['subcategory'] ? htmlspecialchars($ticket['subcategory']) : '—'; ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Created By</div>
                        <div class="detail-value">
                            <div class="user-display">
                                <i class="fas fa-user-circle user-icon"></i>
                                <div>
                                    <b><?php echo htmlspecialchars($ticket['requester_name']); ?></b>
                                    <div class="user-email"><?php echo htmlspecialchars($ticket['requester_email']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Created Date</div>
                        <div class="detail-value">
                            <?php echo date('F j, Y g:i a', strtotime($ticket['created_at'])); ?>
                        </div>
                    </div>
                </div>
                
                <h3><i class="fas fa-file-alt"></i> Issue Description</h3>
                <div class="ticket-description">
                    <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                </div>
                
                <?php if (!empty($ticket['page_url'])): ?>
                <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                    <strong><i class="fas fa-link"></i> Related Page:</strong>
                    <a href="<?php echo htmlspecialchars($ticket['page_url']); ?>" target="_blank" style="margin-left: 10px;">
                        <?php 
                        $display_url = str_replace('http://localhost/mle_inventory_tool/', '', $ticket['page_url']);
                        echo htmlspecialchars($display_url ?: $ticket['page_url']); 
                        ?>
                        <i class="fas fa-external-link-alt" style="font-size: 12px; margin-left: 5px;"></i>
                    </a>
                </div>
                <?php endif; ?>
                
                <div style="text-align: center;">
                    <button class="btn-back" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px; text-align: center;">
                    <i class="fas fa-info-circle"></i> 
                    Ticket ID: <?php echo $ticket['id']; ?> | 
                    Created: <?php echo date('Y-m-d H:i:s', strtotime($ticket['created_at'])); ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

    // Helper methods
    private function getUserIdByEmail($email) {
        try {
            $sql = "SELECT id FROM staff_login WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch (PDOException $e) {
            error_log("ERROR in getUserIdByEmail: " . $e->getMessage());
            return null;
        }
    }
    
    private function canManageTicket($ticketId, $userId) {
        $stmt = $this->db->prepare("SELECT assigned_to FROM tickets WHERE id = ?");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if user is assigned
        if ($ticket['assigned_to'] == $userId) {
            return true;
        }
        
        // Check if user is in support department
        return $this->isInSupportDepartment($userId);
    }
    
    private function isInSupportDepartment($userId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM staff_login s
            JOIN departments d ON s.department = d.id
            WHERE s.id = ? 
            AND (d.department_name LIKE '%Information Systems%'
                OR d.department_name LIKE '%Information System%'
                OR d.department_name LIKE '%IT%'
                OR d.department_name LIKE '%Information Technology%')
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    private function getSupportStaff() {
        $stmt = $this->db->prepare("
            SELECT s.id, s.email, 
                s.email as name
            FROM staff_login s
            JOIN departments d ON s.department = d.id
            WHERE (d.department_name LIKE '%Information Systems%'
                OR d.department_name LIKE '%Information System%'
                OR d.department_name LIKE '%IT%'
                OR d.department_name LIKE '%Information Technology%')
            AND s.email IS NOT NULL
            ORDER BY s.email
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format names from emails
        foreach ($results as &$staff) {
            $email_parts = explode('@', $staff['email']);
            $staff['name'] = ucwords(str_replace(['.', '_', '-'], ' ', $email_parts[0]));
        }
        
        return $results;
    }
        
    
    private function getStaffName($staff_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT CONCAT(first_name, ' ', last_name) as full_name,
                    email
                FROM staff_login 
                WHERE id = ?
            ");
            $stmt->execute([$staff_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['full_name']) && trim($result['full_name']) !== '') {
                return $result['full_name'];
            } elseif ($result && !empty($result['email'])) {
                // Fallback to formatted email
                $parts = explode('@', $result['email']);
                return ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
            }
            
            return 'Staff Member';
        } catch (PDOException $e) {
            error_log("Error getting staff name: " . $e->getMessage());
            return 'Staff Member';
        }
    }
    
    private function getPriorityColor($priority) {
        $colors = [
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark'
        ];
        return $colors[$priority] ?? 'secondary';
    }
    
    private function getStatusColor($status) {
        $colors = [
            'open' => 'success',
            'ongoing' => 'primary',
            'resolved' => 'success',
            'closed' => 'secondary'
        ];
        return $colors[$status] ?? 'secondary';
    }

    // View all tickets - ordered by most recent first
    public function viewall() {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
                
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }
        
        $user_email = $_SESSION['user_email'];
        $user_id = $_SESSION['user_id'] ?? $this->getUserIdByEmail($user_email);
        $user_role = $_SESSION['role'] ?? 'staff';
        $is_support = $this->isInSupportDepartment($user_id);
        
        // Get tickets based on role
        if ($user_role === 'admin' || $user_role === 'super_admin' || $is_support) {
            $tickets = $this->model->getAllTickets();
        } else {
            $tickets = $this->model->getUserTickets($user_id);
        }
        
        // Get staff list for assignment dropdown (support only)
        $staff_list = [];
        if ($is_support) {
            $staff_list = $this->model->getSupportStaff();
        }
        
        // Get feedback for each ticket
        foreach ($tickets as &$ticket) {
            $ticket['feedback'] = $this->model->getTicketFeedback($ticket['id']);
        }
        
        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/configurations/view_ticket.php';
    }

    // AJAX: Update ticket status
    public function updateStatusAjax() {
        header('Content-Type: application/json');
        
        // Check authentication
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Check if user is support staff
        if (!$this->isInSupportDepartment($user_id)) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        // Get POST data
        $ticket_id = $_POST['ticket_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $notes = trim($_POST['notes'] ?? '');
        
        if (!$ticket_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }
        
        // Validate status
        $validStatuses = ['open', 'ongoing', 'resolved', 'closed'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit();
        }
        
        // Load model
        if ($this->model === null) {
            $this->loadModel();
        }
        
        // Get current status before update
        $currentStatus = $this->model->getTicketStatus($ticket_id);
        
        // Update status
        $result = $this->model->updateTicketStatus($ticket_id, $status, $user_id, $notes);
        
        if ($result) {
            // If status changed to resolved, send notification
            if ($status === 'resolved' && $currentStatus !== 'resolved') {
                $this->sendResolutionNotification($ticket_id, $user_id, $notes);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Status updated successfully',
                'status' => $status
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status']);
        }
    }

    // AJAX: Assign ticket
    public function assignTicketAjax() {
        header('Content-Type: application/json');
        
        // Check authentication
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        
        // Check if user is support staff
        if (!$this->isInSupportDepartment($user_id)) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit();
        }
        
        // Get POST data
        $ticket_id = $_POST['ticket_id'] ?? 0;
        $assigned_to = $_POST['assigned_to'] ?? null;
        
        if (!$ticket_id) {
            echo json_encode(['success' => false, 'message' => 'Missing ticket ID']);
            exit();
        }
        
        // Handle empty assignment (unassign)
        if ($assigned_to === '' || $assigned_to === 'null' || $assigned_to === null) {
            $assigned_to = null;
        }
        
        // Load model
        if ($this->model === null) {
            $this->loadModel();
        }
        
        // Update assignment
        $result = $this->model->quickAssignTicket($ticket_id, $assigned_to);
        
        if ($result) {
            // Log the assignment in status history
            if ($assigned_to) {
                $staff_name = $this->getStaffName($assigned_to);
                $notes = "Ticket assigned to " . $staff_name;
            } else {
                $notes = "Ticket unassigned";
            }
            
            $this->logStatusChange(
                $ticket_id, 
                null, // No old status change
                null, // No new status change
                $user_id, 
                $notes
            );
            
            echo json_encode([
                'success' => true, 
                'message' => $assigned_to ? 'Ticket assigned successfully' : 'Ticket unassigned',
                'assigned_to' => $assigned_to
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update assignment']);
        }
    }

    // AJAX: Get status history
    public function getStatusHistoryAjax() {
        header('Content-Type: application/json');
        
        // Check authentication
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_email'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated', 'history' => []]);
            exit();
        }
        
        $ticket_id = $_GET['ticket_id'] ?? 0;
        
        if (!$ticket_id) {
            echo json_encode(['success' => false, 'message' => 'Missing ticket ID', 'history' => []]);
            exit();
        }
        
        // Load model
        if ($this->model === null) {
            $this->loadModel();
        }
        
        // Get formatted history
        $history = $this->model->getFormattedStatusHistory($ticket_id);
        
        echo json_encode(['success' => true, 'history' => $history]);
    }

    // AJAX: Get ticket details for management modal
    public function getTicketDetailsAjax() {
        header('Content-Type: application/json');
        
        // Check authentication
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_email'])) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit();
        }
        
        $user_id = $_SESSION['user_id'] ?? $this->getUserIdByEmail($_SESSION['user_email']);
        $ticket_id = $_GET['ticket_id'] ?? 0;
        
        if (!$ticket_id) {
            echo json_encode(['success' => false, 'message' => 'Missing ticket ID']);
            exit();
        }
        
        try {
            // Load model for support staff list
            if ($this->model === null) {
                $this->loadModel();
            }
            
            // Get ticket details
            $stmt = $this->db->prepare("
                SELECT t.*, 
                    u.email as requester_email,
                    u.first_name as requester_first_name,
                    u.last_name as requester_last_name,
                    d.department_name,
                    a.email as assigned_email,
                    a.first_name as assigned_first_name,
                    a.last_name as assigned_last_name,
                    r.email as resolver_email,
                    r.first_name as resolver_first_name,
                    r.last_name as resolver_last_name
                FROM tickets t
                JOIN staff_login u ON t.user_id = u.id
                LEFT JOIN departments d ON u.department = d.id
                LEFT JOIN staff_login a ON t.assigned_to = a.id
                LEFT JOIN staff_login r ON t.resolved_by = r.id
                WHERE t.id = ?
            ");
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ticket) {
                echo json_encode(['success' => false, 'message' => 'Ticket not found']);
                exit();
            }
            
            // Format requester name
            if (!empty($ticket['requester_first_name']) && !empty($ticket['requester_last_name'])) {
                $ticket['requester_name'] = $ticket['requester_first_name'] . ' ' . $ticket['requester_last_name'];
            } else {
                $parts = explode('@', $ticket['requester_email']);
                $ticket['requester_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
            }
            
            // Format assigned name
            if ($ticket['assigned_to']) {
                if (!empty($ticket['assigned_first_name']) && !empty($ticket['assigned_last_name'])) {
                    $ticket['assigned_name'] = $ticket['assigned_first_name'] . ' ' . $ticket['assigned_last_name'];
                } else {
                    $parts = explode('@', $ticket['assigned_email']);
                    $ticket['assigned_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                }
            }
            
            // Format resolver name
            if ($ticket['resolved_by']) {
                if (!empty($ticket['resolver_first_name']) && !empty($ticket['resolver_last_name'])) {
                    $ticket['resolver_name'] = $ticket['resolver_first_name'] . ' ' . $ticket['resolver_last_name'];
                } else {
                    $parts = explode('@', $ticket['resolver_email']);
                    $ticket['resolver_name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                }
            }
            
            // Get support staff list for assignment dropdown
            $staff_list = $this->model->getSupportStaff();
            
            // Format staff names
            foreach ($staff_list as &$staff) {
                if (empty($staff['name'])) {
                    $parts = explode('@', $staff['email']);
                    $staff['name'] = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                }
            }
            
            // Return both ticket and staff_list
            echo json_encode([
                'success' => true, 
                'ticket' => $ticket,
                'staff_list' => $staff_list,
                'current_user_id' => $user_id
            ]);
            
        } catch (Exception $e) {
            error_log("Error in getTicketDetailsAjax: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}