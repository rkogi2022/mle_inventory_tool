<?php
class ticket extends controller {

    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * AJAX: Get subcategories for a category
     */
    public function getSubcategoriesAjax() {
        $category = trim($_GET['category'] ?? '');
        $category = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');

        $allSubcategories = [
            'hardware' => ['Laptop', 'Printer', 'Monitor', 'Phone'],
            'training' => ['Orientation', 'Refresher Training'],
            'other'    => ['General Inquiry', 'Feedback']
        ];

        $subcategories = $allSubcategories[$category] ?? [];

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'subcategories' => $subcategories]);
    }

    /**
     * AJAX: Get hardware items (all items, not user-specific)
     */
    public function getAllHardwareItemsAjax() {
        $model = new Model($this->db);
        $items = $model->getAllHardwareItems(); 

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'items' => $items]);
    }

    /**
     * AJAX: Create ticket 
     */

    public function createAjax()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Debug: Check what's in session
        error_log("DEBUG - Session data: " . print_r($_SESSION, true));
        
        // Get form data
        $subject = trim($_POST['subject'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $subcategory = trim($_POST['subcategory'] ?? '');
        $priority = trim($_POST['priority'] ?? 'medium');
        $description = trim($_POST['description'] ?? '');
        $page_url = trim($_POST['current_url'] ?? '');
        
        // Debug: Check form data
        error_log("DEBUG - Form data received:");
        error_log("  Subject: " . $subject);
        error_log("  Category: " . $category);
        error_log("  Subcategory: " . $subcategory);
        error_log("  Priority: " . $priority);
        error_log("  Description: " . (strlen($description) > 50 ? substr($description, 0, 50) . "..." : $description));
        error_log("  Page URL: " . $page_url);
        
        // Validate required fields
        if (empty($subject) || empty($category) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Subject, category, and description are required']);
            return;
        }
        
        $model = new Model($this->db);
        
        // Get user email from session (as shown in your navigation)
        $user_email = $_SESSION['user_email'] ?? '';
        
        if (empty($user_email)) {
            error_log("ERROR - No user_email found in session. Session keys: " . implode(', ', array_keys($_SESSION)));
            echo json_encode(['success' => false, 'message' => 'You must be logged in to create a ticket. Please login first.']);
            return;
        }
        
        // Look up user ID from database using email
        $user_id = $this->getUserIdByEmail($user_email);
        
        if (empty($user_id)) {
            error_log("ERROR - Could not find user in database with email: " . $user_email);
            echo json_encode(['success' => false, 'message' => 'User account not found. Please contact administrator.']);
            return;
        }
        
        error_log("DEBUG - Found user ID: " . $user_id . " for email: " . $user_email);
        
        // Validate category
        $validCategories = ['hardware', 'training', 'other'];
        if (!in_array($category, $validCategories)) {
            echo json_encode(['success' => false, 'message' => 'Invalid category selected']);
            return;
        }
        
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
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        error_log("DEBUG - Ticket data to insert: " . print_r($ticket_data, true));
        
        // Create ticket
        $ticket_id = $model->createTicket($ticket_data);
        
        if ($ticket_id) {
            error_log("SUCCESS - Ticket created with ID: " . $ticket_id . " Number: " . $ticket_number);
            echo json_encode([
                'success' => true,
                'message' => 'Ticket created successfully!',
                'ticket_id' => $ticket_id,
                'ticket_number' => $ticket_number
            ]);
        } else {
            error_log("ERROR - Failed to insert ticket into database");
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to create ticket. Database error. Please try again.'
            ]);
        }
    }

    // Helper method to get user ID by email
    private function getUserIdByEmail($email) 
    {
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

    /**
     * View single ticket
     */

    public function view($ticket_id = null)
    {
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check login
        if (empty($_SESSION['user_email'])) {
            echo "You must be logged in to view tickets";
            return;
        }
        
        // Get current user info
        $user_email = $_SESSION['user_email'];
        $user_name = ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $user_email)[0]));
        
        // Get ticket ID
        if (empty($ticket_id)) {
            $ticket_id = $_GET['id'] ?? null;
        }
        
        if (empty($ticket_id)) {
            echo "Ticket ID is required";
            return;
        }
        
        // Convert to integer
        $ticket_id = (int)$ticket_id;
        
        // DIRECT DATABASE QUERY (bypass Model to ensure it works)
        try {
            $sql = "SELECT * FROM tickets WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ticket) {
                echo "Ticket ID " . $ticket_id . " not found";
                return;
            }
            
        } catch (Exception $e) {
            echo "Database error: " . $e->getMessage();
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
                .status-open { color: #28a745; font-weight: bold; }
                .status-closed { color: #dc3545; font-weight: bold; }
                .status-pending { color: #ffc107; font-weight: bold; }
            </style>
            <!-- Font Awesome for icons -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                        <div class="detail-value status-<?php echo strtolower($ticket['status'] ?? 'open'); ?>">
                            <?php echo htmlspecialchars(ucfirst($ticket['status'] ?? 'Open')); ?>
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
                        <div class="detail-value"><?php echo htmlspecialchars(ucfirst($ticket['category'])); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Subcategory</div>
                        <div class="detail-value"><?php echo htmlspecialchars($ticket['subcategory']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Created By</div>
                        <div class="detail-value">
                            <div class="user-display">
                                <i class="fas fa-user-circle user-icon"></i>
                                <div>
                                    <b><?php echo htmlspecialchars($user_name); ?></b>
                                    <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Created Date</div>
                        <div class="detail-value"><?php echo date('F j, Y g:i a', strtotime($ticket['created_at'])); ?></div>
                    </div>
                    
                    <?php if (!empty($ticket['page_url'])): ?>
                    <div class="detail-item" style="grid-column: span 2;">
                        <div class="detail-label">Related Page</div>
                        <div class="detail-value">
                            <a href="<?php echo htmlspecialchars($ticket['page_url']); ?>" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                                <?php 
                                // Display shortened URL
                                $url = $ticket['page_url'];
                                $display_url = str_replace('http://localhost/mle_inventory_tool/', '', $url);
                                echo htmlspecialchars($display_url ?: $url); 
                                ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <h3>Description</h3>
                <div class="ticket-description">
                    <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                </div>
                
                <button class="btn-back" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Back to Previous Page
                </button>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; font-size: 14px;">
                    <i class="fas fa-info-circle"></i> 
                    Ticket ID: <?php echo $ticket['id']; ?> | 
                    Created: <?php echo date('Y-m-d H:i:s', strtotime($ticket['created_at'])); ?>
                </div>
            </div>
        </body>
        </html>
        <?php
    }

}
