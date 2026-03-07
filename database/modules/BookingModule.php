<?php
/**
 * Booking Module
 * Handles booking creation, updates, and management
 * 
 * @package BarCIE
 * @version 1.0.0
 */

require_once __DIR__ . '/../config.php';

class BookingModule
{

    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    /**
     * Generate receipt number
     */
    public function generateReceiptNumber($prefix = 'BARCIE')
    {
        $date = date('Ymd');
        $pattern = $prefix . '-' . $date . '-%';

        $stmt = $this->conn->prepare("SELECT receipt_no FROM bookings WHERE receipt_no LIKE ? ORDER BY receipt_no DESC LIMIT 1");
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $lastReceipt = $result->fetch_assoc()['receipt_no'];
            $lastNumber = (int) substr($lastReceipt, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $date . '-' . $newNumber;
    }

    /**
     * Check room availability
     */
    public function checkAvailability($roomId, $checkin, $checkout, $excludeBookingId = null)
    {
        $query = "SELECT id FROM bookings 
                  WHERE room_id = ? 
                  AND status IN ('confirmed', 'approved', 'pending', 'checked_in') 
                  AND checkin < ? 
                  AND checkout > ?";

        if ($excludeBookingId) {
            $query .= " AND id != ?";
        }

        $stmt = $this->conn->prepare($query);

        if ($excludeBookingId) {
            $stmt->bind_param("issi", $roomId, $checkout, $checkin, $excludeBookingId);
        } else {
            $stmt->bind_param("iss", $roomId, $checkout, $checkin);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows === 0;
    }

    /**
     * Create new booking
     */
    public function createBooking($data)
    {
        try {
            // Validate required fields
            $required = ['room_id', 'guest_name', 'guest_email', 'contact_number', 'checkin', 'checkout', 'details'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '{$field}' is required"];
                }
            }

            // Check availability
            if (!$this->checkAvailability($data['room_id'], $data['checkin'], $data['checkout'])) {
                return ['success' => false, 'message' => 'Room is not available for the selected dates'];
            }

            // Generate receipt number
            $receiptNo = $this->generateReceiptNumber();

            // Insert booking
            $stmt = $this->conn->prepare("
                INSERT INTO bookings (
                    receipt_no, room_id, guest_name, guest_email, contact_number,
                    checkin, checkout, details, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");

            $stmt->bind_param(
                "sissssss",
                $receiptNo,
                $data['room_id'],
                $data['guest_name'],
                $data['guest_email'],
                $data['contact_number'],
                $data['checkin'],
                $data['checkout'],
                $data['details']
            );

            if ($stmt->execute()) {
                $bookingId = $this->conn->insert_id;

                logMessage("Booking created: {$receiptNo}", 'INFO');

                return [
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'data' => [
                        'booking_id' => $bookingId,
                        'receipt_no' => $receiptNo
                    ]
                ];
            } else {
                throw new Exception('Failed to create booking');
            }

        } catch (Exception $e) {
            logMessage("Booking creation error: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Failed to create booking'];
        }
    }

    /**
     * Update booking status
     */
    public function updateStatus($bookingId, $status, $adminId = null)
    {
        try {
            $validStatuses = ['pending', 'approved', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'rejected'];

            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status'];
            }

            $stmt = $this->conn->prepare("UPDATE bookings SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("si", $status, $bookingId);

            if ($stmt->execute()) {
                logMessage("Booking {$bookingId} status updated to {$status}", 'INFO');

                return [
                    'success' => true,
                    'message' => 'Booking status updated successfully'
                ];
            } else {
                throw new Exception('Failed to update booking status');
            }

        } catch (Exception $e) {
            logMessage("Status update error: " . $e->getMessage(), 'ERROR');
            return ['success' => false, 'message' => 'Failed to update booking status'];
        }
    }

    /**
     * Get booking by ID
     */
    public function getBooking($bookingId)
    {
        $stmt = $this->conn->prepare("
            SELECT b.*, i.name as room_name, i.item_type, i.capacity, i.price 
            FROM bookings b
            LEFT JOIN items i ON b.room_id = i.id
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    /**
     * Get bookings with filters
     */
    public function getBookings($filters = [])
    {
        $query = "SELECT b.*, i.name as room_name, i.item_type 
                  FROM bookings b 
                  LEFT JOIN items i ON b.room_id = i.id 
                  WHERE 1=1";

        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            $query .= " AND b.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (!empty($filters['guest_email'])) {
            $query .= " AND b.guest_email = ?";
            $params[] = $filters['guest_email'];
            $types .= 's';
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND b.checkin >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND b.checkout <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        $query .= " ORDER BY b.created_at DESC";

        if (!empty($filters['limit'])) {
            $query .= " LIMIT ?";
            $params[] = $filters['limit'];
            $types .= 'i';
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }

        return $bookings;
    }
}
