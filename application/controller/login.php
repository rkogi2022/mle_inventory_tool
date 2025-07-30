<?php
use Google\Client as Google_Client;
use Google\Service\Oauth2;

class Login extends Controller
{
   
    public function index(){
        if (isset($_GET['error'])) {
            $invalid_credentials = '<div class="error-msg"> Invalid email or password </div>';
        }
        require APP . 'view/login/index.php';
    }

    public function loginUser() {
        session_start();
        echo "loginUser method reached!<br>";
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        
        $staff = $this->model->getStaff($_POST["inputEmailAddress"]);
        if (!$staff) {
            die("No user found or query failed for email: " . htmlspecialchars($_POST["inputEmailAddress"]));
        }
        
        if (empty($staff->password)) {
            die("Password is empty or not set in the database for email: " . htmlspecialchars($staff->email));
        }
        
        if ($staff != null) {
            $saved_password = $staff->password;
            $is_correct_user = password_verify($_POST["inputPassword"], $saved_password);
  
            var_dump($is_correct_user); // true or false
    
            if ($is_correct_user) {
                $_SESSION['user_email'] = $staff->email;
                $_SESSION['role'] = $staff->role;
                $_SESSION['department'] = json_decode($staff->department, true);
                $_SESSION['position'] = $staff->position;
    
                $user_email = $staff->email;  // Use the email from DB, not user input
                $user_name = explode('@', $user_email)[0];
                $_SESSION['user'] = ucfirst($user_name);
    
                header('Location:' . URL . 'home/index/');
                exit();
            } else {
                echo "Password verification failed!";
                header('Location:' . URL . 'login/index?error=invalid_credentials');
                exit();
            }
        } else {
            echo "No user found with that email!";
            header('Location:' . URL . 'login/index?error=invalid_credentials');
            exit(); 
        }
    }
    
    
    public function password_reset() {
        session_start();

        if (isset($_GET['error'])) {
            $invalid_credentials = '<div class="error-msg"> An error occured, please try again.</div>';
        } elseif (isset($_GET['success'])) {
            $successful_login = '<div class="success-msg"> Password reset successfully. Taking you back to login page...</div>';
            // Redirect after displaying success message.
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "' . URL . 'login/index/";
                    }, 1700); 
                  </script>';

        } elseif (isset($_GET['null'])) {
            $null_result = '<div class="error-msg"> No user found with the given email address.</div>';
        }
        require APP . 'view/login/forgot_password.php';
    }

 
    public function submit_password_reset() {
        if ($this->model === null) {
            echo "Model not loaded properly!";
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['inputEmailAddress'];
            $newPassword1 = $_POST['newPassword1'];

            $hashed_password = password_hash($newPassword1, PASSWORD_DEFAULT); 
            // Call model function to reset password
            $update_query = $this->model->reset_password($email, $hashed_password);

            if ($update_query) {
                header('Location:' . URL . 'login/password_reset?success');
            } else {
                header('Location:' . URL . 'login/password_reset?null');
            }

        } else {
            // redirect the user back to the sign in page upon failed validation to try again
            header('Location:' . URL . 'login/password_reset?error=invalid_credentials');
        }

    }

    public function logout(){
        session_start();
        session_unset();
        session_destroy();
        header('Location:' . URL . 'login/index/');
    }

    // public function loginWithGoogle() {
    //     session_start();
    
    //     $client = new Google_Client();
    //     $client->setClientId('1008330128121-22b9m96a6252g66ssbgnk8slthdpr64j.apps.googleusercontent.com');
    //     $client->setClientSecret('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');
    //     $client->setRedirectUri('http://localhost/mle_inventory_tool/login/googleCallback');
    //     $client->addScope('email');
    //     $client->addScope('profile');
    //     $client->setPrompt('select_account');
    
    //     $auth_url = $client->createAuthUrl();
    //     header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    //     exit();
    // }
    
    // public function googleCallback() {
    //     session_start();

    //     require_once __DIR__ . '/../../vendor/autoload.php';
    //     require_once __DIR__ . '/../config/config.php';

    //     if ($this->model === null) {
    //         echo "Model not loaded properly!";
    //         exit();
    //     }

    //     try {
    //         // Setup Google Client
    //         $client = new Google_Client();
    //         $client->setClientId('1008330128121-22b9m96a6252g66ssbgnk8slthdpr64j.apps.googleusercontent.com');
    //         $client->setClientSecret('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');
    //         $client->setRedirectUri('http://localhost/mle_inventory_tool/login/googleCallback');
    //         $client->addScope('email');
    //         $client->addScope('profile');

    //         if (!isset($_GET['code'])) {
    //             throw new Exception("Authorization code not found in callback.");
    //         }

    //         // Exchange auth code for access token
    //         $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    //         if (!is_array($accessToken) || isset($accessToken['error'])) {
    //             $errorMsg = isset($accessToken['error_description']) ? $accessToken['error_description'] : json_encode($accessToken);
    //             throw new Exception("Access token error: " . $errorMsg);
    //         }

    //         $client->setAccessToken($accessToken['access_token']);

    //         // Retrieve user info
    //         $oauth2 = new \Google\Service\Oauth2($client);
    //         $googleUser = $oauth2->userinfo->get();

    //         if (!isset($googleUser->email)) {
    //             throw new Exception("Unable to retrieve Google account email.");
    //         }

    //         $email = $googleUser->email;
    //         $staff = $this->model->getStaff($email);

    //         if ($staff) {
    //             $_SESSION['user_email'] = $staff->email;
    //             $_SESSION['role'] = $staff->role;
    //             $_SESSION['department'] = json_decode($staff->department, true);
    //             $_SESSION['position'] = $staff->position;
    //             $_SESSION['user'] = ucfirst(explode('@', $staff->email)[0]);

    //             header('Location: ' . URL . 'home/index/');
    //             exit();
    //         } else {
    //             header('Location: ' . URL . 'login/index?error=user_not_found');
    //             exit();
    //         }
    //     } catch (Exception $e) {
    //         error_log("Google Login Error: " . $e->getMessage());
    //         header('Location: ' . URL . 'login/index?error=' . urlencode($e->getMessage()));
    //         exit();
    //     }


public function loginWithGoogle() 
{
    session_start();

    require_once __DIR__ . '/../../vendor/autoload.php';

    $client = new Google_Client();
    $client->setClientId('1031515607275-aousibbi9p6e4cvbq8q585cohe8j4gv2.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('https://mleinventory.evidenceaction.org/login/googleCallback');
    $client->addScope('email');
    $client->addScope('profile');
    $client->setPrompt('select_account');

    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit();
}

public function googleCallback() 
{
    session_start();

    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../config/config.php';

    if ($this->model === null) {
        echo "Model not loaded properly!";
        exit();
    }

    try {
        $client = new Google_Client();
        $client->setClientId('1031515607275-aousibbi9p6e4cvbq8q585cohe8j4gv2.apps.googleusercontent.com');
        $client->setClientSecret('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');
        $client->setRedirectUri('https://mleinventory.evidenceaction.org/login/googleCallback');

        if (!isset($_GET['code'])) {
            throw new Exception("Authorization code not found in callback.");
        }

        // Exchange code for access token
        $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (!is_array($accessToken) || isset($accessToken['error'])) {
            error_log("Access token fetch error: " . json_encode($accessToken));
            $errorMsg = $accessToken['error_description'] ?? $accessToken['error'] ?? 'Unknown error';
            throw new Exception("Access token error: " . $errorMsg);
        }

        $client->setAccessToken($accessToken['access_token']);

        $oauth2 = new \Google\Service\Oauth2($client);
        $googleUser = $oauth2->userinfo->get();

        if (!isset($googleUser->email)) {
            throw new Exception("Unable to retrieve Google account email.");
        }

        $email = $googleUser->email;
        $staff = $this->model->getStaff($email);

        if ($staff) {
            $_SESSION['user_email'] = $staff->email;
            $_SESSION['role'] = $staff->role;
            $_SESSION['department'] = json_decode($staff->department, true);
            $_SESSION['position'] = $staff->position;
            $_SESSION['user'] = ucfirst(explode('@', $staff->email)[0]);

            header('Location: ' . URL . 'home/index/');
            exit();
        } else {
            header('Location: ' . URL . 'login/index?error=user_not_found');
            exit();
        }

    } catch (Exception $e) {
        error_log("Google Login Error: " . $e->getMessage());
        header('Location: ' . URL . 'login/index?error=' . urlencode($e->getMessage()));
        exit();
    }
}


    // }
   
}





