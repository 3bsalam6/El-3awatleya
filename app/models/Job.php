<?php
/**
 * العواطلية - Job Model
 */

class Job
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new job
     */
    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO Jobs (EmployerID, Title, Description, JobType, ExperienceLevel, City, SalaryMin, SalaryMax, Requirements, Skills, ExpiresAt, IsActive)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $data['employer_id'],
            $data['title'],
            $data['description'],
            $data['job_type'] ?? 'full-time',
            $data['experience_level'] ?? 'entry',
            $data['city'] ?? null,
            $data['salary_min'] ?? null,
            $data['salary_max'] ?? null,
            $data['requirements'] ?? null,
            $data['skills'] ?? null,
            $data['expires_at'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Get all active jobs (for job seekers)
     */
    public function getActive($search = null, $category = null, $jobType = null)
    {
        $sql = "
            SELECT j.*, u.FullName AS EmployerName, ep.CompanyName
            FROM Jobs j
            JOIN Users u ON j.EmployerID = u.UserID
            LEFT JOIN EmployerProfiles ep ON j.EmployerID = ep.UserID
            WHERE j.IsActive = 1
              AND (j.ExpiresAt IS NULL OR j.ExpiresAt > GETDATE())
              AND j.IsRemovedByAdmin = 0
        ";
        $params = [];

        if ($search) {
            $sql .= " AND (j.Title LIKE ? OR j.Description LIKE ? OR j.City LIKE ? OR j.Skills LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($category) {
            $sql .= " AND (j.ExperienceLevel = ? OR j.Skills LIKE ?)";
            $params[] = $category;
            $params[] = "%{$category}%";
        }
        if ($jobType) {
            $sql .= " AND j.JobType = ?";
            $params[] = $jobType;
        }

        $sql .= " ORDER BY j.PostedAt DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get job by ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT j.*, u.FullName AS EmployerName, ep.CompanyName, ep.Industry, ep.City AS CompanyCity
            FROM Jobs j
            JOIN Users u ON j.EmployerID = u.UserID
            LEFT JOIN EmployerProfiles ep ON j.EmployerID = ep.UserID
            WHERE j.JobID = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get jobs by employer
     */
    public function getByEmployer($employerId)
    {
        $stmt = $this->db->prepare("
            SELECT j.*, 
                   (SELECT COUNT(*) FROM JobApplications WHERE JobID = j.JobID) AS ApplicationsCount
            FROM Jobs j
            WHERE j.EmployerID = ?
            ORDER BY j.PostedAt DESC
        ");
        $stmt->execute([$employerId]);
        return $stmt->fetchAll();
    }

    /**
     * Update job
     */
    public function update($jobId, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE Jobs SET
                Title = ?, Description = ?, JobType = ?, ExperienceLevel = ?,
                City = ?, SalaryMin = ?, SalaryMax = ?,
                Requirements = ?, Skills = ?, UpdatedAt = GETDATE()
            WHERE JobID = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['job_type'] ?? 'full-time',
            $data['experience_level'] ?? 'entry',
            $data['city'] ?? $data['location'] ?? null,
            $data['salary_min'] ?? null,
            $data['salary_max'] ?? null,
            $data['requirements'] ?? null,
            $data['skills'] ?? null,
            $jobId
        ]);
    }

    /**
     * Admin deactivate / remove job with reason
     */
    /**
     * Admin deactivate / remove job with reason
     */
    public function adminDeactivate($jobId, $reason = null)
    {
        $stmt = $this->db->prepare("
            UPDATE Jobs 
            SET IsActive = 0, 
                IsRemovedByAdmin = 1, 
                AdminRemoveReason = ?, 
                AdminReviewStatus = 'rejected',
                UpdatedAt = GETDATE() 
            WHERE JobID = ?
        ");
        return $stmt->execute([$reason ?: null, $jobId]);
    }

    /**
     * Admin activate / restore job with custom duration / expiry
     */
    public function adminActivate($jobId, $expiresAt = null)
    {
        if (!empty($expiresAt)) {
            $stmt = $this->db->prepare("
                UPDATE Jobs 
                SET IsActive = 1, 
                    IsRemovedByAdmin = 0, 
                    AdminRemoveReason = NULL, 
                    AdminReviewStatus = 'approved',
                    ExpiresAt = ?,
                    UpdatedAt = GETDATE() 
                WHERE JobID = ?
            ");
            return $stmt->execute([$expiresAt, $jobId]);
        } else {
            $stmt = $this->db->prepare("
                UPDATE Jobs 
                SET IsActive = 1, 
                    IsRemovedByAdmin = 0, 
                    AdminRemoveReason = NULL, 
                    AdminReviewStatus = 'approved',
                    ExpiresAt = CASE 
                        WHEN ExpiresAt IS NOT NULL AND ExpiresAt <= GETDATE() THEN DATEADD(DAY, 7, GETDATE()) 
                        ELSE ExpiresAt 
                    END,
                    UpdatedAt = GETDATE() 
                WHERE JobID = ?
            ");
            return $stmt->execute([$jobId]);
        }
    }

    /**
     * Resubmit job for admin review after edit by employer
     */
    public function resubmitForReview($jobId, $employerId, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE Jobs SET
                Title = ?, Description = ?, Requirements = ?, JobType = ?,
                ExperienceLevel = ?, City = ?, SalaryMin = ?, SalaryMax = ?,
                Skills = ?, ExpiresAt = ?, EmployerReviewNotes = ?,
                AdminReviewStatus = 'pending_review',
                IsActive = 0,
                IsRemovedByAdmin = 1,
                ResubmittedAt = GETDATE(),
                UpdatedAt = GETDATE()
            WHERE JobID = ? AND EmployerID = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['requirements'] ?? null,
            $data['job_type'] ?? 'full-time',
            $data['experience_level'] ?? 'entry',
            $data['city'] ?? null,
            $data['salary_min'] ?: null,
            $data['salary_max'] ?: null,
            $data['skills'] ?? null,
            $data['expires_at'] ?? null,
            $data['employer_notes'] ?? null,
            $jobId,
            $employerId
        ]);
    }

    /**
     * Stop applications and zero out the timer (ExpiresAt = GETDATE())
     */
    public function stopApplications($jobId)
    {
        $stmt = $this->db->prepare("UPDATE Jobs SET IsActive = 0, ExpiresAt = GETDATE(), UpdatedAt = GETDATE() WHERE JobID = ?");
        return $stmt->execute([$jobId]);
    }

    /**
     * Reopen job with optional new expiry
     */
    public function reopen($jobId, $newExpiresAt = null)
    {
        $stmt = $this->db->prepare("UPDATE Jobs SET IsActive = 1, IsRemovedByAdmin = 0, AdminRemoveReason = NULL, AdminReviewStatus = 'approved', ExpiresAt = ?, UpdatedAt = GETDATE() WHERE JobID = ?");
        return $stmt->execute([$newExpiresAt, $jobId]);
    }

    /**
     * Get all jobs (admin) with search and status filtering
     */
    public function getAll($search = null, $status = null)
    {
        $sql = "
            SELECT j.*, u.FullName AS EmployerName, ep.CompanyName,
                   (SELECT COUNT(*) FROM JobApplications WHERE JobID = j.JobID) AS ApplicationsCount
            FROM Jobs j
            JOIN Users u ON j.EmployerID = u.UserID
            LEFT JOIN EmployerProfiles ep ON j.EmployerID = ep.UserID
            WHERE 1=1
        ";
        $params = [];

        if ($search) {
            $sql .= " AND (j.Title LIKE ? OR ep.CompanyName LIKE ? OR u.FullName LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status === 'active') {
            $sql .= " AND j.IsActive = 1 AND j.IsRemovedByAdmin = 0 AND (j.ExpiresAt IS NULL OR j.ExpiresAt > GETDATE())";
        } elseif ($status === 'pending_review') {
            $sql .= " AND j.AdminReviewStatus = 'pending_review'";
        } elseif ($status === 'removed' || $status === 'removed_admin') {
            $sql .= " AND j.IsRemovedByAdmin = 1 AND (j.AdminReviewStatus IS NULL OR j.AdminReviewStatus != 'pending_review')";
        } elseif ($status === 'paused') {
            $sql .= " AND j.IsActive = 0 AND j.IsRemovedByAdmin = 0";
        } elseif ($status === 'expired') {
            $sql .= " AND j.ExpiresAt IS NOT NULL AND j.ExpiresAt <= GETDATE() AND j.IsRemovedByAdmin = 0";
        }

        $sql .= " ORDER BY CASE WHEN j.AdminReviewStatus = 'pending_review' THEN 0 ELSE 1 END, j.PostedAt DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get job stats
     */
    public function getStats()
    {
        $stmt = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM Jobs WHERE IsActive = 1 AND IsRemovedByAdmin = 0 AND (ExpiresAt IS NULL OR ExpiresAt > GETDATE())) AS ActiveJobs,
                (SELECT COUNT(*) FROM Jobs) AS TotalJobs,
                (SELECT COUNT(*) FROM Jobs WHERE IsRemovedByAdmin = 1 AND (AdminReviewStatus IS NULL OR AdminReviewStatus != 'pending_review')) AS RemovedJobs,
                (SELECT COUNT(*) FROM Jobs WHERE AdminReviewStatus = 'pending_review') AS PendingReviewJobs,
                (SELECT COUNT(*) FROM Jobs WHERE PostedAt >= DATEADD(DAY, -7, GETDATE())) AS NewJobsThisWeek
        ");
        return $stmt->fetch();
    }
}
