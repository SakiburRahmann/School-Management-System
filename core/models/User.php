<?php

require_once __DIR__ . '/../../core/Model.php';

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';

    public function findByUsername(string $username): ?array
    {
        $result = $this->query("SELECT * FROM {$this->table} WHERE username = ?", [$username]);
        $user = $result->fetch_assoc();
        return $user ?: null;
    }

    public function create(string $username, string $passwordHash, string $role, ?int $relatedId = null): bool
    {
        $data = [
            'username' => $username,
            'password' => $passwordHash,
            'role' => $role
        ];
        if ($relatedId !== null) {
            $data['related_id'] = $relatedId;
        }
        
        return (bool) $this->insert($data);
    }
}


