<?php
// models/FareChangeRequest.php

class FareChangeRequest
{
    private mysqli $conn;

    public function __construct(mysqli $conn)
    {
        $this->conn = $conn;
    }

    public function create(int $routeId, float $oldFare, float $newFare, int $moderatorId): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO fare_change_requests (route_id, old_fare, new_fare, moderator_id, status, created_at)
             VALUES (?, ?, ?, ?, 'pending', NOW())"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("iddi", $routeId, $oldFare, $newFare, $moderatorId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function getPending(): array
    {
        $sql = "SELECT fcr.id, fcr.route_id, fcr.old_fare, fcr.new_fare,
                       fcr.created_at, u.email AS moderator_email, r.name AS route_name
                FROM fare_change_requests fcr
                JOIN users u ON fcr.moderator_id = u.id
                JOIN routes r ON fcr.route_id = r.id
                WHERE fcr.status = 'pending'
                ORDER BY fcr.created_at DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function approve(int $requestId, int $adminId): bool
    {
        $this->conn->begin_transaction();
        try {
            // Get request
            $stmt = $this->conn->prepare(
                "SELECT route_id, new_fare FROM fare_change_requests WHERE id = ? AND status = 'pending' FOR UPDATE"
            );
            if (!$stmt) {
                $this->conn->rollback();
                return false;
            }
            $stmt->bind_param("i", $requestId);
            $stmt->execute();
            $res = $stmt->get_result();
            $request = $res->fetch_assoc();
            $stmt->close();

            if (!$request) {
                $this->conn->rollback();
                return false;
            }

            // Update main routes table
            $stmt2 = $this->conn->prepare("UPDATE routes SET base_fare = ? WHERE id = ?");
            if (!$stmt2) {
                $this->conn->rollback();
                return false;
            }
            $stmt2->bind_param("di", $request['new_fare'], $request['route_id']);
            $stmt2->execute();
            $stmt2->close();

            // Mark request as approved
            $stmt3 = $this->conn->prepare(
                "UPDATE fare_change_requests
                 SET status = 'approved', reviewed_by = ?, reviewed_at = NOW()
                 WHERE id = ?"
            );
            if (!$stmt3) {
                $this->conn->rollback();
                return false;
            }
            $stmt3->bind_param("ii", $adminId, $requestId);
            $stmt3->execute();
            $stmt3->close();

            $this->conn->commit();
            return true;
        } catch (Throwable $e) {
            $this->conn->rollback();
            return false;
        }
    }

    public function reject(int $requestId, int $adminId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE fare_change_requests
             SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW()
             WHERE id = ? AND status = 'pending'"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $adminId, $requestId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
