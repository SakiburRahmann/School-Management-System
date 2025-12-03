<?php

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../core/models/User.php';

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->render('auth/login', ['error' => '']);
    }

    public function login(): void
    {
        session_start();

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $userModel = new User();
            $user = $userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['related_id'] = $user['related_id'];

                header('Location: /dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }

        $this->render('auth/login', ['error' => $error]);
    }
    public function showSignup(): void
    {
        $this->render('auth/signup', ['error' => '', 'message' => '']);
    }

    public function signup(): void
    {
        session_start();
        if (isset($_SESSION['user_id'])) {
            header("Location: /dashboard.php");
            exit;
        }

        $error = '';
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $role = trim($_POST['role'] ?? 'Student');

            $userModel = new User();
            if ($userModel->findByUsername($username)) {
                $error = "Username already exists!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                if ($userModel->create($username, $hashed_password, $role)) {
                    $message = "User registered successfully! <a href='/login.php'>Login here</a>";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }

        $this->render('auth/signup', ['error' => $error, 'message' => $message]);
    }
}


