<?php
/**
 * العواطلية - User Model
 */

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Find user by ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE UserID = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new user
     */
    public function create($data)
    {
        $isOTP = ($data['role'] === 'admin') ? 1 : 0;
        $stmt = $this->db->prepare("
            INSERT INTO Users (FullName, Email, PasswordHash, Role, IsOTPEnabled)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'],
            $isOTP
        ]);

        $userId = $this->db->lastInsertId();

        // Create profile based on role
        if ($data['role'] === 'user') {
            $stmt = $this->db->prepare("
                INSERT INTO UserProfiles (UserID, Phone, City)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $data['phone'] ?? null,
                $data['city'] ?? null
            ]);
        } elseif ($data['role'] === 'employer') {
            $stmt = $this->db->prepare("
                INSERT INTO EmployerProfiles (UserID, CompanyName, Industry, City)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $data['company_name'] ?? '',
                $data['industry'] ?? null,
                $data['city'] ?? null
            ]);
        }

        return $userId;
    }

    /**
     * Update password
     */
    public function updatePassword($userId, $newPassword)
    {
        $stmt = $this->db->prepare("UPDATE Users SET PasswordHash = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
        return $stmt->execute([password_hash($newPassword, PASSWORD_BCRYPT), $userId]);
    }

    /**
     * Update email
     */
    public function updateEmail($userId, $newEmail)
    {
        $stmt = $this->db->prepare("UPDATE Users SET Email = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
        return $stmt->execute([$newEmail, $userId]);
    }

    /**
     * Update OTP setting
     */
    public function updateOTPSetting($userId, $enabled)
    {
        $stmt = $this->db->prepare("UPDATE Users SET IsOTPEnabled = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
        return $stmt->execute([$enabled ? 1 : 0, $userId]);
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        $stmt = $this->db->prepare("UPDATE Users SET FullName = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
        $stmt->execute([$data['name'], $userId]);

        // Update role-specific profile
        $user = $this->findById($userId);
        if ($user['Role'] === 'user') {
            $stmt = $this->db->prepare("
                UPDATE UserProfiles SET 
                    Phone = ?, City = ?, Bio = ?, YearsOfExp = ?,
                    LinkedInURL = ?, GitHubURL = ?, PortfolioURL = ?,
                    UpdatedAt = GETDATE()
                WHERE UserID = ?
            ");
            $stmt->execute([
                $data['phone'] ?? null,
                $data['city'] ?? null,
                $data['bio'] ?? null,
                $data['years_exp'] ?? 0,
                $data['linkedin'] ?? null,
                $data['github'] ?? null,
                $data['portfolio'] ?? null,
                $userId
            ]);
        } elseif ($user['Role'] === 'employer') {
            $stmt = $this->db->prepare("
                UPDATE EmployerProfiles SET
                    CompanyName = ?, Industry = ?, CompanySize = ?,
                    CompanyWebsite = ?, City = ?, Address = ?,
                    Description = ?, UpdatedAt = GETDATE()
                WHERE UserID = ?
            ");
            $stmt->execute([
                $data['company_name'] ?? null,
                $data['industry'] ?? null,
                $data['company_size'] ?? null,
                $data['website'] ?? null,
                $data['city'] ?? null,
                $data['address'] ?? null,
                $data['description'] ?? null,
                $userId
            ]);
        }
    }

    /**
     * Get user profile (with role-specific data)
     */
    public function getProfile($userId)
    {
        $user = $this->findById($userId);
        if (!$user) return null;

        if ($user['Role'] === 'user') {
            $stmt = $this->db->prepare("SELECT * FROM UserProfiles WHERE UserID = ?");
            $stmt->execute([$userId]);
            $user['profile'] = $stmt->fetch() ?: [];
        } elseif ($user['Role'] === 'employer') {
            $stmt = $this->db->prepare("SELECT * FROM EmployerProfiles WHERE UserID = ?");
            $stmt->execute([$userId]);
            $user['profile'] = $stmt->fetch() ?: [];
        }

        return $user;
    }

    /**
     * Update last login
     */
    public function updateLastLogin($userId)
    {
        $stmt = $this->db->prepare("UPDATE Users SET LastLoginAt = GETDATE() WHERE UserID = ?");
        $stmt->execute([$userId]);
    }

    /**
     * Ban user
     */
    public function ban($userId, $reason = null)
    {
        $stmt = $this->db->prepare("UPDATE Users SET IsBanned = 1, BanReason = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
        return $stmt->execute([$reason, $userId]);
    }

    /**
     * Unban user
     */
    public function unban($userId)
    {
        $stmt = $this->db->prepare("UPDATE Users SET IsBanned = 0, BanReason = NULL, UpdatedAt = GETDATE() WHERE UserID = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Get all users (admin)
     */
    public function getAll($role = null, $search = null)
    {
        $sql = "SELECT * FROM Users WHERE Role != 'admin'";
        $params = [];

        if ($role) {
            $sql .= " AND Role = ?";
            $params[] = $role;
        }
        if ($search) {
            $sql .= " AND (FullName LIKE ? OR Email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY CreatedAt DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get user stats for admin dashboard
     */
    public function getStats()
    {
        $stmt = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM Users WHERE Role = 'user') AS TotalUsers,
                (SELECT COUNT(*) FROM Users WHERE Role = 'employer') AS TotalEmployers,
                (SELECT COUNT(*) FROM Users WHERE IsBanned = 1) AS BannedUsers,
                (SELECT COUNT(*) FROM Users WHERE CreatedAt >= DATEADD(DAY, -7, GETDATE())) AS NewUsersThisWeek
        ");
        return $stmt->fetch();
    }

    /**
     * Check if a normal user exists (for forgot password)
     */
    public function checkUserExistsForReset($email)
    {
        $stmt = $this->db->prepare("SELECT UserID, FullName, Email FROM Users WHERE Email = ? AND Role = 'user' AND IsBanned = 0");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
