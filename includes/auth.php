<?php
require_once __DIR__ . '/../config/database.php';

class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?)");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($user['status'] == 'pending') {
                return 'pending'; // special return value to show pending message
            }
            if (password_verify($password, $user['password']) && $user['status'] == 'active') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];
                return true;
            }
        }
        return false;
    }
    
    public function register($data) {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, full_name, phone, role, status) VALUES (?, ?, ?, ?, ?, 'member', 'pending')");
        $stmt->bind_param("sssss", $data['username'], $data['email'], $hashed_password, $data['full_name'], $data['phone']);
        if ($stmt->execute()) {
            // Notify admins
            notifyAdmins('New User Registration', $data['full_name'] . ' has registered and needs approval.');
            return $this->conn->insert_id;
        }
        return false;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['role']) && in_array($_SESSION['role'], array('owner', 'admin'));
    }
    
    public function isOwner() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'owner';
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) return null;
        
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function addAdmin($user_id) {
        if (!$this->isOwner()) return false;
        
        $stmt = $this->conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        return $stmt->execute();
    }
    
    public function getAdmins() {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE role IN ('owner', 'admin')");
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
}

$auth = new Auth($conn);
?>