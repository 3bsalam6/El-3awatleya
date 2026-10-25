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
        $approvalStatus = ($data['role'] === 'admin') ? 'approved' : 'pending';
        $stmt = $this->db->prepare("
            INSERT INTO Users (FullName, Email, PasswordHash, Role, IsOTPEnabled, ApprovalStatus)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['role'],
            $isOTP,
            $approvalStatus
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
     * Update non-sensitive user profile fields only
     */
    public function updateUserProfileOnly($userId, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE UserProfiles SET 
                Phone = ?, City = ?, Bio = ?, YearsOfExp = ?,
                LinkedInURL = ?, GitHubURL = ?, PortfolioURL = ?,
                UpdatedAt = GETDATE()
            WHERE UserID = ?
        ");
        return $stmt->execute([
            $data['phone'] ?? null,
            $data['city'] ?? null,
            $data['bio'] ?? null,
            $data['years_exp'] ?? 0,
            $data['linkedin'] ?? null,
            $data['github'] ?? null,
            $data['portfolio'] ?? null,
            $userId
        ]);
    }

    /**
     * Update non-sensitive employer profile fields only
     */
    public function updateEmployerProfileOnly($userId, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE EmployerProfiles SET
                Industry = ?, CompanySize = ?,
                CompanyWebsite = ?, City = ?, Address = ?,
                Description = ?, UpdatedAt = GETDATE()
            WHERE UserID = ?
        ");
        return $stmt->execute([
            $data['industry'] ?? null,
            $data['company_size'] ?? null,
            $data['website'] ?? null,
            $data['city'] ?? null,
            $data['address'] ?? null,
            $data['description'] ?? null,
            $userId
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data, $updateSensitive = false)
    {
        if ($updateSensitive && !empty($data['name'])) {
            $stmt = $this->db->prepare("UPDATE Users SET FullName = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
            $stmt->execute([$data['name'], $userId]);
        }

        // Update role-specific profile
        $user = $this->findById($userId);
        if ($user['Role'] === 'user') {
            $this->updateUserProfileOnly($userId, $data);
        } elseif ($user['Role'] === 'employer') {
            if ($updateSensitive && !empty($data['company_name'])) {
                $stmt = $this->db->prepare("UPDATE EmployerProfiles SET CompanyName = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
                $stmt->execute([$data['company_name'], $userId]);
            }
            $this->updateEmployerProfileOnly($userId, $data);
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
            $profile = $stmt->fetch() ?: [];
            $user['profile'] = $profile;
            foreach ($profile as $k => $v) {
                if (!isset($user[$k]) || $user[$k] === null || $user[$k] === '') {
                    $user[$k] = $v;
                }
            }
        } elseif ($user['Role'] === 'employer') {
            $stmt = $this->db->prepare("SELECT * FROM EmployerProfiles WHERE UserID = ?");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch() ?: [];
            $user['profile'] = $profile;
            foreach ($profile as $k => $v) {
                if (!isset($user[$k]) || $user[$k] === null || $user[$k] === '') {
                    $user[$k] = $v;
                }
            }
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
     * Approve user account
     */
    public function approve($userId)
    {
        $stmt = $this->db->prepare("UPDATE Users SET ApprovalStatus = 'approved', RejectionReason = NULL, UpdatedAt = GETDATE() WHERE UserID = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Disapprove / Reject user account
     */
    public function disapprove($userId, $reason = null)
    {
        $stmt = $this->db->prepare("UPDATE Users SET ApprovalStatus = 'rejected', RejectionReason = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
        return $stmt->execute([$reason, $userId]);
    }

    /**
     * Get all users (admin)
     */
    public function getAll($role = null, $search = null, $status = null)
    {
        $sql = "SELECT * FROM Users WHERE Role != 'admin'";
        $params = [];

        if ($role) {
            $sql .= " AND Role = ?";
            $params[] = $role;
        }
        if ($status) {
            $sql .= " AND ApprovalStatus = ?";
            $params[] = $status;
        }
        if ($search) {
            $sql .= " AND (FullName LIKE ? OR Email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY CASE WHEN ApprovalStatus = 'pending' THEN 0 ELSE 1 END, CreatedAt DESC";

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
                (SELECT COUNT(*) FROM Users WHERE ApprovalStatus = 'pending') AS PendingUsers,
                (SELECT COUNT(*) FROM Users WHERE CreatedAt >= DATEADD(DAY, -7, GETDATE())) AS NewUsersThisWeek
        ");
        return $stmt->fetch();
    }

    /**
     * Check if a user exists (for forgot password) - works for any user role
     */
    public function checkUserExistsForReset($email)
    {
        $stmt = $this->db->prepare("SELECT UserID, FullName, Email, Role FROM Users WHERE Email = ? AND IsBanned = 0 AND ApprovalStatus = 'approved'");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Request sensitive data change (such as FullName or CompanyName)
     */
    public function requestNameChange($userId, $field, $oldVal, $newVal)
    {
        $stmt = $this->db->prepare("SELECT RequestID FROM ProfileChangeRequests WHERE UserID = ? AND FieldChanged = ? AND Status = 'pending'");
        $stmt->execute([$userId, $field]);
        $existingId = $stmt->fetchColumn();

        if ($existingId) {
            $update = $this->db->prepare("UPDATE ProfileChangeRequests SET NewValue = ?, RequestedAt = GETDATE() WHERE RequestID = ?");
            return $update->execute([$newVal, $existingId]);
        } else {
            $insert = $this->db->prepare("INSERT INTO ProfileChangeRequests (UserID, FieldChanged, OldValue, NewValue, Status, RequestedAt) VALUES (?, ?, ?, ?, 'pending', GETDATE())");
            return $insert->execute([$userId, $field, $oldVal, $newVal]);
        }
    }

    /**
     * Get pending sensitive data change request for user
     */
    public function getPendingNameChange($userId, $field = null)
    {
        $sql = "SELECT * FROM ProfileChangeRequests WHERE UserID = ? AND Status = 'pending'";
        $params = [$userId];
        if ($field) {
            $sql .= " AND FieldChanged = ?";
            $params[] = $field;
        }
        $sql .= " ORDER BY RequestedAt DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Get all pending sensitive profile change requests (for Admin)
     */
    public function getAllPendingProfileRequests()
    {
        $stmt = $this->db->query("
            SELECT pcr.*, u.FullName, u.Email, u.Role, ep.CompanyName
            FROM ProfileChangeRequests pcr
            JOIN Users u ON pcr.UserID = u.UserID
            LEFT JOIN EmployerProfiles ep ON u.UserID = ep.UserID
            WHERE pcr.Status = 'pending'
            ORDER BY pcr.RequestedAt DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Approve sensitive profile change request
     */
    public function approveProfileRequest($requestId, $adminId)
    {
        $stmt = $this->db->prepare("
            SELECT pcr.*, u.Email, u.FullName, u.Role, ep.CompanyName
            FROM ProfileChangeRequests pcr
            JOIN Users u ON pcr.UserID = u.UserID
            LEFT JOIN EmployerProfiles ep ON u.UserID = ep.UserID
            WHERE pcr.RequestID = ? AND pcr.Status = 'pending'
        ");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch();
        if (!$req) return false;

        $userId = $req['UserID'];
        $newVal = $req['NewValue'];
        $field = $req['FieldChanged'];

        if ($field === 'FullName') {
            $upUser = $this->db->prepare("UPDATE Users SET FullName = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
            $upUser->execute([$newVal, $userId]);
        } elseif ($field === 'CompanyName') {
            $upEmp = $this->db->prepare("UPDATE EmployerProfiles SET CompanyName = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
            $upEmp->execute([$newVal, $userId]);
            $upUser = $this->db->prepare("UPDATE Users SET FullName = ?, UpdatedAt = GETDATE() WHERE UserID = ?");
            $upUser->execute([$newVal, $userId]);
        }

        $upReq = $this->db->prepare("UPDATE ProfileChangeRequests SET Status = 'approved', ReviewedAt = GETDATE(), ReviewedBy = ? WHERE RequestID = ?");
        $upReq->execute([$adminId, $requestId]);

        return $req;
    }

    /**
     * Reject sensitive profile change request
     */
    public function rejectProfileRequest($requestId, $adminId, $reason = null)
    {
        $stmt = $this->db->prepare("
            SELECT pcr.*, u.Email, u.FullName, u.Role, ep.CompanyName
            FROM ProfileChangeRequests pcr
            JOIN Users u ON pcr.UserID = u.UserID
            LEFT JOIN EmployerProfiles ep ON u.UserID = ep.UserID
            WHERE pcr.RequestID = ? AND pcr.Status = 'pending'
        ");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch();
        if (!$req) return false;

        $upReq = $this->db->prepare("UPDATE ProfileChangeRequests SET Status = 'rejected', AdminNotes = ?, ReviewedAt = GETDATE(), ReviewedBy = ? WHERE RequestID = ?");
        $upReq->execute([$reason ?: null, $adminId, $requestId]);

        return $req;
    }
}
