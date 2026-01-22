<?php
// app/models/u ser.php

class User
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    // Only DB and business logic here
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, email, passwordhash, role FROM users WHERE email = ? LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        return $user ?: null;
    }

    public function verifyPassword(?array $user, string $password): bool
    {
        if (!$user || empty($user['passwordhash'])) {
            return false;
        }

        return password_verify($password, $user['passwordhash']);
    }
}
