<?php
/**
 * Authentication Module
 * Handles user login, registration, and session management
 * 
 * @package BarCIE
 * @version 1.0.0
 */

class AuthModule {
    
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Handle user login
     */
    public function login($email, $password) {
        try {
            // Validate input
            if (empty($email) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Email and password are required'
                ];
            }
            
            // Prepare statement to prevent SQL injection
            $stmt = $this->conn->prepare("SELECT id, username, password, email FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            $user = $result->fetch_assoc();
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            // Set session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();
            
            logMessage("User logged in: {$user['email']}", 'INFO');
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ];
            
        } catch (Exception $e) {
            logMessage("Login error: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'message' => 'An error occurred during login'
            ];
        }
    }
    

    /**
     * Handle admin login
     */
    public function adminLogin($username, $password) {
        try {
            if (empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Username and password are required'
                ];
            }
            
            $stmt = $this->conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }
            
            $admin = $result->fetch_assoc();
            
            // Verify password
            if (!password_verify($password, $admin['password'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid username or password'
                ];
            }
            
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['last_activity'] = time();
            
            logMessage("Admin logged in: {$admin['username']}", 'INFO');
            
            return [
                'success' => true,
                'message' => 'Admin login successful',
                'data' => [
                    'admin_id' => $admin['id'],
                    'username' => $admin['username']
                ]
            ];
            
        } catch (Exception $e) {
            logMessage("Admin login error: " . $e->getMessage(), 'ERROR');
            return [
                'success' => false,
                'message' => 'An error occurred during login'
            ];
        }
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        // Destroy all session data
        $_SESSION = array();
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logged out successfully'
        ];
    }
    
    /**
     * Check if user is authenticated
     */
    public function isAuthenticated() {
        // Check if session exists
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            
            if ($elapsed > SESSION_LIFETIME) {
                $this->logout();
                return false;
            }
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Check if admin is authenticated
     */
    public function isAdminAuthenticated() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return false;
        }
        
        if (isset($_SESSION['last_activity'])) {
            $elapsed = time() - $_SESSION['last_activity'];
            
            if ($elapsed > SESSION_LIFETIME) {
                $this->logout();
                return false;
            }
        }
        
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Get current user info
     */
    public function getCurrentUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'email' => $_SESSION['email'] ?? null
        ];
    }
}
