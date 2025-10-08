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
    header('Content-Type: application/json');

    $question = isset($_POST['question']) ? trim($_POST['question']) : '';

    if ($question === '') {
        echo json_encode(['answers' => [], 'message' => 'Please type a question.']);
        exit();
    }

    error_log("🔍 SEARCH CALLED for keyword: " . $question);

    $results = $this->model->searchFAQs($question);
    error_log("🔎 RESULTS COUNT: " . count($results));

    if (!empty($results)) {
        // Return all related FAQs for user selection
        echo json_encode([
            'answers' => $results,
            'message' => "I found multiple related answers. Please select one below."
        ]);
    } else {
        echo json_encode([
            'answers' => [],
            'message' => "Sorry, I couldn't find anything related to '$question'."
        ]);
    }
    exit();
}




}
