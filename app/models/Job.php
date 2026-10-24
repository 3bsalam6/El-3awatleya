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
     * Toggle job active status
     */
    public function toggleActive($jobId)
    {
        $stmt = $this->db->prepare("UPDATE Jobs SET IsActive = CASE WHEN IsActive = 1 THEN 0 ELSE 1 END, UpdatedAt = GETDATE() WHERE JobID = ?");
        return $stmt->execute([$jobId]);
    }

    /**
     * Get all jobs (admin)
     */
    public function getAll($search = null)
    {
        $sql = "
            SELECT j.*, u.FullName AS EmployerName, ep.CompanyName,
                   (SELECT COUNT(*) FROM JobApplications WHERE JobID = j.JobID) AS ApplicationsCount
            FROM Jobs j
            JOIN Users u ON j.EmployerID = u.UserID
            LEFT JOIN EmployerProfiles ep ON j.EmployerID = ep.UserID
        ";
        $params = [];

        if ($search) {
            $sql .= " WHERE j.Title LIKE ? OR ep.CompanyName LIKE ?";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $sql .= " ORDER BY j.PostedAt DESC";
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
                (SELECT COUNT(*) FROM Jobs WHERE IsActive = 1) AS ActiveJobs,
                (SELECT COUNT(*) FROM Jobs) AS TotalJobs,
                (SELECT COUNT(*) FROM Jobs WHERE PostedAt >= DATEADD(DAY, -7, GETDATE())) AS NewJobsThisWeek
        ");
        return $stmt->fetch();
    }
}
