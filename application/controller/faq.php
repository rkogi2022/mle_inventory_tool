<?php

class faq extends Controller
{
    // ====== DISPLAY ALL FAQs ======
    public function getFaqs()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        $faqs = $this->model->getAllFaqs();

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/configurations/faq.php';
    }

    // ====== ADD FAQ ======
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $question = trim($_POST['question']);
            $answer   = trim($_POST['answer']);

            if ($question && $answer) {
                $this->model->addFaq($question, $answer);
                echo json_encode(['status' => 'success', 'message' => 'FAQ added successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields.']);
            }
            exit();
        }
    }
    // ====== UPDATE FAQ ======
    public function update()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id       = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $question = trim($_POST['question'] ?? '');
            $answer   = trim($_POST['answer'] ?? '');

            if ($id && $question && $answer) {
                $updated = $this->model->updateFaq($id, $question, $answer);

                if ($updated) {
                    echo json_encode(['status' => 'success', 'message' => 'FAQ updated successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update FAQ.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            }
            exit;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit;
        }
    }

    // ====== DELETE FAQ ======
    public function delete($id)
    {
        if ($id) {
            $this->model->deleteFaq($id);
            header("Location: " . URL . "faq/getFaqs?success=FAQ Deleted Successfully!");
            exit();
        }
    }

    // ====== SEARCH FAQ ======
    public function search()
    {
        session_start();

        if (!isset($_SESSION['user_email'])) {
            echo json_encode(['answers' => [], 'message' => 'Please log in first.']);
            exit();
        }

        $user_email = $_SESSION['user_email'];
        $user_name  = $_SESSION['user'] ?? 'Guest';  

        header('Content-Type: application/json');

        $question = trim($_POST['question'] ?? '');

        if ($question === '') {
            echo json_encode(['answers' => [], 'message' => 'Please type a question.']);
            exit();
        }

        // Log search with name - GET AND STORE THE ID
        $search_log_id = $this->model->logFaqSearch($user_email, $user_name, $question);
        
        // CRITICAL: Store in session so logClick() can use it
        $_SESSION['last_search_id'] = $search_log_id;
        $_SESSION['last_search_text'] = $question;
        
        error_log("Search logged with ID: $search_log_id, stored in session");

        $results = $this->model->searchFAQs($question);

        echo json_encode([
            'answers' => $results,
            'message' => count($results)
                ? "Here are some related answers:"
                : "No match found for '$question'."
        ]);
        exit();
    }
        //saving the clicked faq

    public function logClick()
    {
        session_start();
        
        error_log("=== FAQ LOG CLICK CONTROLLER CALLED ===");
        error_log("Session ID: " . session_id());
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("POST data: " . print_r($_POST, true));

        // Get user
        $email = $_SESSION['user_email'] ?? null;
        
        if (isset($_SESSION['user'])) {
            $name = $_SESSION['user'];
        } elseif (isset($_SESSION['user_name'])) {
            $name = $_SESSION['user_name'];
        } else {
            $name = 'Guest';
        }
        
        error_log("Email: " . ($email ?: 'NULL'));
        error_log("Name: " . $name);

        if (!$email) {
            error_log("ERROR: No user email in session");
            echo json_encode(['status'=>'error','message'=>'User not logged in']);
            exit;
        }

        // Check POST
        if (!isset($_POST['faq_id'])) {
            error_log("ERROR: faq_id not set in POST");
            echo json_encode(['status'=>'error','message'=>'Missing faq_id']);
            exit;
        }
        
        $faq_id = $_POST['faq_id'];
        error_log("Raw faq_id from POST: " . $faq_id);

        if (!is_numeric($faq_id)) {
            error_log("ERROR: faq_id is not numeric: " . $faq_id);
            echo json_encode(['status'=>'error','message'=>'Invalid faq_id (must be a number)']);
            exit;
        }

        $faq_id = (int) $faq_id;
        error_log("Converted faq_id: " . $faq_id);

        if ($faq_id <= 0) {
            error_log("ERROR: faq_id must be > 0, got: " . $faq_id);
            echo json_encode(['status'=>'error','message'=>'faq_id must be > 0']);
            exit;
        }

        // IMPORTANT: Get the search_log_id from session (set by search method)
        $search_log_id = $_SESSION['last_search_id'] ?? null;
        error_log("Search Log ID from session: " . ($search_log_id ?: 'NULL'));
        
        if ($search_log_id) {
            // UPDATE the existing search record with the faq_id
            error_log("Updating search record ID $search_log_id with FAQ ID $faq_id");
            $result = $this->model->updateFaqClick($search_log_id, $faq_id);
            
            // Clear the session variable after updating
            unset($_SESSION['last_search_id']);
            unset($_SESSION['last_search_text']);
        } else {
            // Fallback: create new record if no search_log_id found
            error_log("No search_log_id found in session, creating new record");
            $result = $this->model->logFaqClick($email, $name, $faq_id);
        }
        
        if ($result) {
            error_log("✅ Successfully logged FAQ click");
            echo json_encode(['status'=>'logged','faq_id'=>$faq_id]);
        } else {
            error_log("❌ Failed to log FAQ click");
            echo json_encode(['status'=>'error','message'=>'Failed to log click']);
        }
    }
    //viewing faq analytics ie most searched, most viewed
    public function analytics()
    {
        session_start();
        if (!isset($_SESSION['user_email'])) {
            header("Location: " . URL . "login");
            exit();
        }

        $topSearches  = $this->model->getTopSearches();
        $topViewedFaqs = $this->model->getTopViewedFaqs();
        $topUsers      = $this->model->getTopUsers();

        require APP . 'view/_templates/sessions.php';
        require APP . 'view/_templates/header.php';
        require APP . 'view/configurations/faq_analytics.php';
    }




}
