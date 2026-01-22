<?php
// app/controllers/AuthController.php

class AuthController
{
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    // Login action: handles request + chooses view
    public function login(): void
    {
        session_start();

        // Already logged in → redirect
        if (isset($_SESSION['userid'])) {
            $this->redirectByRole($_SESSION['userrole']);
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
                $error = "Invalid email or password format.";
            } else {
                $user = $this->userModel->findByEmail($email);

                if ($this->userModel->verifyPassword($user, $password)) {
                    session_regenerate_id(true);

                    $_SESSION['userid']   = $user['id'];
                    $emailParts           = explode('@', $user['email']);
                    $_SESSION['username'] = ucfirst($emailParts[0]);
                    $_SESSION['useremail'] = $user['email'];
                    $_SESSION['userrole']  = $user['role'];

                    $this->redirectByRole($user['role']);
                } else {
                    $error = "Invalid email or password.";
                }
            }
        }

        // Only choose view and pass data
        $this->renderLoginView($error);
    }

    private function redirectByRole(string $role): void
    {
        if ($role === 'admin') {
            header("Location: ../admin/php/admin-dashboard.php");
        } elseif ($role === 'moderator') {
            header("Location: ../moderator/moderator-dashboardphp.php");
        } else {
            header("Location: ../passanger/php/user-dashboard.php");
        }
        exit();
    }

    private function renderLoginView(string $error): void
    {
        // No logic, just call the view file
        require __DIR__ . '../../views/auth/login.php';
    }
}
