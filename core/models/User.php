<?php
/**
 * User Model
 * Handles user authentication and management
 */

class User extends BaseModel {
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username AND is_active = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Update last login
            $this->update($user['user_id'], ['last_login' => date('Y-m-d H:i:s')]);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Create user with hashed password
     */
    public function createUser($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        return $this->create($data);
    }
    
    /**
     * Update password
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        $sql = "SELECT * FROM {$this->table} WHERE role = :role ORDER BY username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role' => $role]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get user with related info
     */
    public function getUserWithRelated($userId) {
        $user = $this->find($userId);
        
        if ($user && $user['related_id']) {
            switch ($user['role']) {
                case 'Teacher':
                    $sql = "SELECT * FROM teachers WHERE teacher_id = :id";
                    break;
                case 'Student':
                    $sql = "SELECT * FROM students WHERE student_id = :id";
                    break;
                default:
                    return $user;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $user['related_id']]);
            $user['related_info'] = $stmt->fetch();
        }
        
        return $user;
    }
    
    /**
     * Check if username exists
     */
    public function usernameExists($username, $excludeId = null) {
        return $this->exists('username', $username, $excludeId);
    }

    /**
     * Find user by username
     */
    public function findByUsername($username) {
        return $this->queryOne("SELECT * FROM {$this->table} WHERE username = :username", ['username' => $username]);
    }
    
    /**
     * Deactivate user
     */
    public function deactivate($userId) {
        return $this->update($userId, ['is_active' => 0]);
    }
    
    /**
     * Activate user
     */
    public function activate($userId) {
        return $this->update($userId, ['is_active' => 1]);
    }
}
