<?php
if (session_status() === PHP_SESSION_NONE) session_start();
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

<!-- Bootstrap & Font Awesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

/* ---------- TICKET STYLES ONLY ---------- */
.ticket-floating-btn {
    position: fixed; 
    bottom: 30px; 
    left: 30px; 
    z-index: 1000;
}

.ticket-floating-btn button {
    background: linear-gradient(135deg, #2196F3, #1976D2); 
    color: white; 
    border: none; 
    border-radius: 50px; 
    padding: 15px 25px; 
    font-size: 16px; 
    font-weight: 600; 
    cursor: pointer; 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    transition: all 0.3s ease;
}

.ticket-floating-btn button:hover {
    transform: translateY(-3px); 
    box-shadow: 0 6px 20px rgba(33,150,243,0.4);
}

.ticket-modal {
    display: none; 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100%; 
    height: 100%; 
    background: rgba(0,0,0,0.5); 
    z-index: 1001; 
    align-items: center; 
    justify-content: center;
}

.ticket-modal-content {
    background: white; 
    border-radius: 10px; 
    width: 90%; 
    max-width: 500px; 
    max-height: 90vh; 
    overflow-y: auto; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(-50px); } 
    to { opacity: 1; transform: translateY(0); }
}

.ticket-modal-header {
    padding: 20px; 
    background: linear-gradient(135deg, #2196F3, #1976D2); 
    color: white; 
    border-radius: 10px 10px 0 0; 
    display: flex; 
    justify-content: space-between; 
    align-items: center;
}

.ticket-modal-body {
    padding: 20px;
}

.ticket-step {
    display: none;
    color: black;
}

.ticket-step.active {
    display: block; 
    background-color: #ecfafb !important;
    color: black;
}

.ticket-step h4 {
    color: #333; 
    margin-bottom: 20px; 
    padding-bottom: 10px; 
    border-bottom: 2px solid #f0f0f0;
}

.ticket-step-nav {
    display: flex; 
    justify-content: space-between; 
    margin-top: 30px; 
    padding-top: 20px; 
    border-top: 1px solid #eee;
}

.ticket-btn-primary {
    padding: 12px 25px; 
    border: none; 
    border-radius: 6px; 
    font-weight: 600; 
    cursor: pointer; 
    background: linear-gradient(135deg, #2196F3, #1976D2); 
    color: white;
}

.ticket-btn-primary:hover {
    background: linear-gradient(135deg, #1976D2, #1565C0); 
    transform: translateY(-2px);
}

.ticket-btn-secondary {
    padding: 12px 25px; 
    border: none; 
    border-radius: 6px; 
    font-weight: 600; 
    cursor: pointer; 
    background: #f5f5f5; 
    color: #666;
}

.ticket-btn-secondary:hover {
    background: #e0e0e0;
}

.ticket-success {
    text-align: center; 
    padding: 30px 0;
}

.success-icon {
    font-size: 60px; 
    color: #4CAF50; 
    margin-bottom: 20px;
}

.current-url-info {
    background: #f8f9fa; 
    color: black; 
    padding: 15px; 
    border-radius: 6px; 
    margin: 15px 0; 
    border-left: 4px solid #2196F3;
}

.current-url-info p {
    margin: 0 0 5px 0;
}

#ticket-description {
    width: 100%;
    box-sizing: border-box;
}

/* Chatbot Styles */
.chat-question-list {
    max-height: 200px;
    overflow-y: auto;
    margin: 10px 0;
    border: 1px solid #eee;
    border-radius: 5px;
    padding: 5px;
}

.chat-answer-area {
    background: #e9f7ef;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #c3e6cb;
    margin: 10px 0;
    color: #155724;
}

/* Responsive */
@media(max-width: 768px){
    .ticket-modal-content {
        width: 95%; 
        margin: 10px;
    }
    
    .ticket-floating-btn {
        bottom: 20px; 
        left: 20px;
    }
    
    .ticket-floating-btn button {
        padding: 12px 20px; 
        font-size: 14px;
    }
    
    #chat-popup {
        width: 300px;
        right: 10px;
        bottom: 70px;
    }
}
</style>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined"/>
</head>
<body>

<?php
$uri = strtok($_SERVER['REQUEST_URI'], '?');
$current_page = trim(basename($uri), '/');
function isActive($pages,$current){return is_array($pages)?(in_array($current,$pages)?'active':''):($current===$pages?'active':'');}
?>

<!-- NAVIGATION -->
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
                        <a href="<?php echo URL; ?>faq/analytics"
                        class="dropdown-item <?php echo isActive('staffreturneditems', $current_page); ?>">FAQ Analytics</a>
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
                    <a class="nav-link dropdown-toggle <?php echo isActive(['getPositions','getFaqs', 'getDepartments','getOffices','getLocations'], $current_page); ?>" href="#"
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
                        <a href="<?php echo URL; ?>faq/getFaqs"
                           class="dropdown-item <?php echo isActive('getFaqs', $current_page); ?>">FAQs</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
<div class="d-flex align-items-center gap-3">
<?php if(isset($_SESSION['user_email'])):
    $user_email=$_SESSION['user_email'];
    $user_name=ucwords(str_replace(['.','_','-'],' ',explode('@',$user_email)[0])); ?>
    <div style="position:relative;display:inline-block;">
        <span id="profileTrigger" onclick="toggleProfileCard()" style="cursor:pointer;"><i class="fas fa-user-circle" style="font-size:22px;color:#e600a0;"></i><b style="margin-left:6px;font-size:16px;"><?php echo htmlspecialchars($user_name); ?></b></span>
        <div id="profileCard" style="display:none;position:absolute;top:40px;right:0;background:#fff;border:1px solid #ddd;border-radius:16px;padding:25px;width:380px;box-shadow:0px 8px 20px rgba(0,0,0,0.15);font-size:15px;line-height:1.7;z-index:999;">
            <p><i class="fas fa-smile text-warning"></i> <strong>Name:</strong> <span id="cardName"></span></p>
            <p><i class="fas fa-envelope text-danger"></i> <strong>Email:</strong> <span id="cardEmail"></span></p>
            <p><i class="fas fa-building text-info"></i> <strong>Department:</strong> <span id="cardDepartment"></span></p>
            <p><i class="fas fa-briefcase text-success"></i> <strong>Position:</strong> <span id="cardPosition"></span></p>
            <p><i class="fas fa-map-marker-alt text-muted"></i> <strong>Duty Station:</strong> <span id="cardDuty"></span></p>
            <div style="text-align:center;margin-top:20px;"><a href="<?php echo URL; ?>login/logout" class="bt-logout">LOGOUT</a></div>
        </div>
    </div>
<?php endif; ?>
</div>
</div>
</div>

<!-- CHAT BUTTONS -->
<div id="chat-button" style="position:fixed;bottom:20px;right:20px;background:#007bff;color:white;padding:12px 18px;border-radius:30px;cursor:pointer;box-shadow:0 4px 8px rgba(0,0,0,0.2);z-index:9999;">💬 How can I help you?</div>
<div id="chat-popup" style="display:none;position:fixed;bottom:80px;right:20px;width:320px;background:white;border-radius:10px;border:1px solid #ccc;box-shadow:0 4px 8px rgba(0,0,0,0.2);z-index:9999;">
    <div style="padding:10px;border-bottom:1px solid #ddd;font-weight:bold;">Support Bot<span style="float:right;cursor:pointer;" onclick="toggleChat()">✖</span></div>
    <div id="chat-box" style="height:240px;overflow-y:auto;padding:10px;"></div>
    <div id="chat-input-section" style="padding:10px;border-top:1px solid #ddd;display:flex;gap:5px;">
        <input type="text" id="chat-input" placeholder="Ask a question..." style="flex:1;padding:6px;border-radius:5px;border:1px solid #ccc;">
        <button onclick="askQuestion()" style="background:#007bff;color:white;border:none;padding:6px 10px;border-radius:5px;">Send</button>
    </div>
</div>

<!-- TICKET WIZARD  -->
<!-- <div id="ticket-floating-btn" class="ticket-floating-btn">
    <button onclick="openTicketWizard()"><i class="fas fa-life-ring"></i> <span>Raise a Ticket</span></button>
</div> -->
<div id="ticket-wizard-modal" class="ticket-modal">
    <div class="ticket-modal-content">
        <div class="ticket-modal-header">
            <h3>Raise a Support Ticket</h3>
            <button class="ticket-close-btn" onclick="closeTicketWizard()">&times;</button>
        </div>
        <div class="ticket-modal-body">
            <!-- Step 1 -->
            <div id="ticket-step-1" class="ticket-step active">
                <h4>Step 1: What do you need help with?</h4>
                <div class="form-group" style="color: black;"><label>Subject *</label><input type="text" id="ticket-subject" maxlength="255" placeholder="Brief description"></div>
                <div class="form-group" style="color: black;"><label>Category *</label>
                    <select id="ticket-category" onchange="loadSubcategories()">
                        <option value="">Select Category</option>
                        <option value="hardware">Hardware</option>
                        <option value="training">Training</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group" id="subcategory-container" style="display:none;">
                    <label style="color: black;">Subcategory</label>
                    <select id="ticket-subcategory"><option value="">Select Subcategory</option></select>
                </div>
                <div class="form-group" style="color: black;"><label>Priority</label>
                    <select id="ticket-priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="ticket-step-nav"><button class="ticket-btn-secondary" onclick="closeTicketWizard()">Cancel</button><button class="ticket-btn-primary" onclick="goToStep(2)">Next</button></div>
            </div>

            <!-- Step 2 -->
            <div id="ticket-step-2" class="ticket-step">
                <h4>Step 2: Describe the issue</h4>
                <div class="form-group" style="color: black;"><label>Description *</label><textarea id="ticket-description" rows="6" placeholder="Describe your issue"></textarea></div>
                <div class="form-group" style="color: black;"><label>Attach Screenshot (Optional)</label><input type="file" id="ticket-attachment" accept="image/*,.pdf,.doc,.docx"><small class="text-muted">Max 5MB</small></div>
                <div class="current-url-info" style="display:none;"><p><strong>Issue Page:</strong> <span id="current-url-display"></span></p></div>
                <div class="ticket-step-nav"><button class="ticket-btn-secondary" onclick="goToStep(1)">Back</button><button class="ticket-btn-primary" onclick="submitTicket()">Submit Ticket</button></div>
            </div>

            <!-- Step 3 -->
            <div id="ticket-step-3" class="ticket-step">
                <div class="ticket-success">
                    <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                    <h4>Ticket Created Successfully!</h4>
                    <p style="color: black;" id="ticket-success-number"></p>
                    <p style="color: black;" id="ticket-success-message"></p>
                    <div class="ticket-success-actions">
                        <button class="ticket-btn-primary" onclick="viewTicket()">View Ticket</button>
                        <button class="ticket-btn-secondary" onclick="closeTicketWizard()">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
/* ---------- PROFILE CARD ---------- */
function toggleProfileCard(){const card=document.getElementById('profileCard');card.style.display=(card.style.display==='none'?'block':'none');if(card.style.display==='block') fetch('<?php echo URL; ?>users/getUserProfile').then(r=>r.json()).then(d=>{document.getElementById('cardName').innerText=d.name;document.getElementById('cardEmail').innerText=d.email;document.getElementById('cardDepartment').innerText=d.department;document.getElementById('cardPosition').innerText=d.position;document.getElementById('cardDuty').innerText=d.dutystation;});}
document.addEventListener('click', e=>{const card=document.getElementById('profileCard');const trigger=document.getElementById('profileTrigger');if(!card.contains(e.target)&&!trigger.contains(e.target)){card.style.display='none';}});

/* ---------- IMPROVED CHATBOT (KEEPS QUESTIONS WITH GREEN TICKS) ---------- */
let currentSearchResults = [];

function toggleChat() {
    const popup = document.getElementById('chat-popup');
    const isHidden = popup.style.display === 'none' || popup.style.display === '';
    
    if (isHidden) {
        popup.style.display = 'block';
        document.getElementById('chat-box').innerHTML = '';
        document.getElementById('chat-input-section').style.display = 'flex';
        document.getElementById('chat-input').focus();
    } else {
        popup.style.display = 'none';
    }
}

function askQuestion() {
    const input = document.getElementById('chat-input');
    const question = input.value.trim();
    
    if (!question) return;
    
    const chatBox = document.getElementById('chat-box');
    chatBox.innerHTML += `<p><b>You:</b> ${question}</p>`;
    chatBox.scrollTop = chatBox.scrollHeight;
    
    // Add loading indicator
    const loadingId = 'loading-' + Date.now();
    chatBox.innerHTML += `<p id="${loadingId}"><b>Bot:</b> Searching...</p>`;
    chatBox.scrollTop = chatBox.scrollHeight;
    
    fetch("<?php echo URL; ?>faq/search", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "question=" + encodeURIComponent(question)
    })
    .then(r => r.json())
    .then(data => {
        // Remove loading indicator
        const loadingElement = document.getElementById(loadingId);
        if (loadingElement) loadingElement.remove();
        
        if (data.answers && data.answers.length > 0) {
            // Store search results
            currentSearchResults = data.answers;
            
            // Show message about found questions
            chatBox.innerHTML += `<p><b>Bot:</b> ${data.message}</p>`;
            
            // Create and display question list with green ticks
            displayQuestionList();
            
        } else if (data.answer) {
            // Direct answer
            chatBox.innerHTML += `<p><b>Bot:</b> ${data.answer}</p>`;
        } else {
            // No results
            chatBox.innerHTML += `<p><b>Bot:</b> ${data.message || "I couldn't find an answer."}</p>`;
        }
        
        chatBox.scrollTop = chatBox.scrollHeight;
        input.value = '';
    })
    .catch(err => {
        const loadingElement = document.getElementById(loadingId);
        if (loadingElement) loadingElement.remove();
        
        chatBox.innerHTML += `<p><b>Bot:</b> Sorry, something went wrong.</p>`;
        console.error(err);
        chatBox.scrollTop = chatBox.scrollHeight;
    });
}

function displayQuestionList() {
    const chatBox = document.getElementById('chat-box');
    
    // Remove existing question list if any
    const existingList = document.getElementById('question-list');
    if (existingList) {
        existingList.remove();
    }
    
    // Remove existing answer if any
    const existingAnswer = document.getElementById('current-answer');
    if (existingAnswer) {
        existingAnswer.remove();
    }
    
    // Create question list container
    const questionList = document.createElement('div');
    questionList.className = 'chat-question-list';
    questionList.id = 'question-list';
    
    // Add each question with green tick
    currentSearchResults.forEach((faq, i) => {
        const qDiv = document.createElement('div');
        qDiv.className = 'faq-option';
        qDiv.style = 'margin: 6px 0; padding: 8px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;';
        
        const qText = document.createElement('div');
        qText.innerHTML = `<b>Q${i+1}:</b> ${faq.question}`;
        qDiv.appendChild(qText);
        
        const btn = document.createElement('button');
        btn.innerHTML = '✔️';
        btn.title = 'View answer';
        btn.style = 'background: #dbdfe2ff; color: #333; border: none; padding: 5px 8px; border-radius: 5px; cursor: pointer; font-size: 12px;';
        
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            showAnswer(faq, i);
        });
        
        qDiv.appendChild(btn);
        questionList.appendChild(qDiv);
    });
    
    chatBox.appendChild(questionList);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function showAnswer(faq, index) {
    const chatBox = document.getElementById('chat-box');
    
    // Remove existing answer if any
    const existingAnswer = document.getElementById('current-answer');
    if (existingAnswer) {
        existingAnswer.remove();
    }
    
    // Create answer container
    const answerDiv = document.createElement('div');
    answerDiv.className = 'chat-answer-area';
    answerDiv.id = 'current-answer';
    
    answerDiv.innerHTML = `
        <p style="margin:0 0 8px 0;font-weight:bold;">${faq.question}</p>
        <p style="margin:0;">${faq.answer || "No answer available."}</p>
    `;
    
    // Insert answer after question list
    const questionList = document.getElementById('question-list');
    if (questionList) {
        questionList.insertAdjacentElement('afterend', answerDiv);
    } else {
        chatBox.appendChild(answerDiv);
    }
    
    // Scroll to show the answer
    chatBox.scrollTop = chatBox.scrollHeight;
    
    // Log the click
    fetch("<?php echo URL; ?>faq/logClick", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "faq_id=" + encodeURIComponent(faq.id)
    })
    .then(r => r.json())
    .then(d => console.log("Logged:", d))
    .catch(e => console.error(e));
}

// Add Enter key support for chat input
document.getElementById('chat-input')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        askQuestion();
    }
});

// Initialize chat
document.getElementById('chat-button').onclick = toggleChat;

// Close chat when clicking outside
document.addEventListener('click', function(e) {
    const chatPopup = document.getElementById('chat-popup');
    const chatButton = document.getElementById('chat-button');
    
    if (chatPopup.style.display === 'block' && 
        !chatPopup.contains(e.target) && 
        !chatButton.contains(e.target)) {
        chatPopup.style.display = 'none';
    }
});

/* ---------- TICKET WIZARD ---------- */
let currentTicketId = null;
let ticketCurrentStep = 1;
const maxSteps = 3;
let currentPageUrl = window.location.href;

// Open/close ticket wizard
function openTicketWizard() {
    const modal = document.getElementById('ticket-wizard-modal');
    if (!modal) return;
    modal.style.display = 'flex';
    currentPageUrl = window.location.href;

    const urlInfo = document.querySelector('.current-url-info');
    const urlDisplay = document.getElementById('current-url-display');
    if (urlInfo && urlDisplay && !currentPageUrl.includes('/ticket/')) {
        urlInfo.style.display = 'block';
        urlDisplay.textContent = currentPageUrl;
    }

    goToStep(1);
}

function closeTicketWizard() {
    const modal = document.getElementById('ticket-wizard-modal');
    if (!modal) return;
    modal.style.display = 'none';
    currentTicketId = null;

    const inputs = document.querySelectorAll('#ticket-wizard-modal input, #ticket-wizard-modal select, #ticket-wizard-modal textarea');
    inputs.forEach(el => el.value = '');
}

// Navigate steps
function goToStep(step) {
    if (step === 2 && !validateStep1()) return;
    const steps = document.querySelectorAll('.ticket-step');
    steps.forEach(el => el.classList.remove('active'));
    const target = document.getElementById(`ticket-step-${step}`);
    if (target) target.classList.add('active');
    ticketCurrentStep = step;
}

// Validate step 1
function validateStep1() {
    const subject = document.getElementById('ticket-subject')?.value.trim();
    const category = document.getElementById('ticket-category')?.value;
    if (!subject) { alert('Enter subject'); return false; }
    if (!category) { alert('Select category'); return false; }
    return true;
}

// Load subcategories
function loadSubcategories() {
    const category = document.getElementById('ticket-category').value;
    const container = document.getElementById('subcategory-container');
    const select = document.getElementById('ticket-subcategory');

    // Clear all existing options safely
    while (select.firstChild) {
        select.removeChild(select.firstChild);
    }

    // Add default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Select Subcategory';
    select.appendChild(defaultOption);

    container.style.display = 'none';
    if (!category) return;

    fetch(`/mle_inventory_tool/ticket/getSubcategoriesAjax?category=${encodeURIComponent(category)}`, {
        method: 'GET',
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.subcategories.length) {
            data.subcategories.forEach(sub => {
                if (![...select.options].some(o => o.value === sub)) {
                    const opt = document.createElement('option');
                    opt.value = sub;
                    opt.textContent = sub;
                    select.appendChild(opt);
                }
            });
            container.style.display = 'block';
        }
    })
    .catch(err => console.error(err));
}

// Submit ticket
function submitTicket() {
    if (!validateStep1()) { goToStep(1); return; }

    const description = document.getElementById('ticket-description')?.value.trim();
    if (!description) { alert('Describe the issue'); return; }

    const formData = new FormData();
    formData.append('subject', document.getElementById('ticket-subject')?.value.trim());
    formData.append('category', document.getElementById('ticket-category')?.value);
    formData.append('subcategory', document.getElementById('ticket-subcategory')?.value);
    formData.append('priority', document.getElementById('ticket-priority')?.value || 'medium');
    formData.append('description', description);
    formData.append('current_url', currentPageUrl);
    
    // Debug logging
    console.log("Submitting ticket with data:");
    for (let [key, value] of formData.entries()) {
        console.log(key + ": " + value);
    }

    fetch('/mle_inventory_tool/ticket/createAjax', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin' 
    })
    .then(res => {
        console.log("Response status:", res.status);
        return res.json();
    })
    .then(data => {
        console.log("Response data:", data);
        if (data.success) {
            currentTicketId = data.ticket_id;
            document.getElementById('ticket-success-number').textContent = `Ticket Number: ${data.ticket_number}`;
            document.getElementById('ticket-success-message').textContent = data.message;
            goToStep(3);
        } else {
            alert("Error: " + data.message);
            if (data.debug) {
                console.error("Debug info:", data.debug);
            }
        }
    })
    .catch(err => {
        console.error("Fetch error:", err);
        alert("Network error. Please try again.");
    });
}

// Keyboard shortcuts
document.addEventListener('keydown', e => {
    if (e.ctrlKey && e.shiftKey && e.key === 'T') openTicketWizard();
    if (e.key === 'Escape') closeTicketWizard();
});

// View ticket function
function viewTicket() {
    if (currentTicketId) {
        window.location.href = '/mle_inventory_tool/ticket/view/' + currentTicketId;
    } else {
        alert('Ticket ID not found');
    }
}
</script>
</body>
</html>