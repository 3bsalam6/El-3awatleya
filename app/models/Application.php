<?php
/**
 * العواطلية - Application Model
 */

class Application
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Apply for a job
     */
    public function apply($userId, $jobId, $cvId, $coverLetter = null)
    {
        // Check if already applied
        $stmt = $this->db->prepare("SELECT ApplicationID FROM JobApplications WHERE UserID = ? AND JobID = ?");
        $stmt->execute([$userId, $jobId]);
        if ($stmt->fetch()) {
            return false; // Already applied
        }

        $stmt = $this->db->prepare("
            INSERT INTO JobApplications (JobID, UserID, CVID, CoverLetter, Status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$jobId, $userId, $cvId, $coverLetter]);
        return $this->db->lastInsertId();
    }

    /**
     * Get applications by user
     */
    public function getByUser($userId)
    {
        $stmt = $this->db->prepare("
            SELECT ja.*, 
                   j.Title AS JobTitle, j.City, j.City AS JobCity, j.JobType, j.ExperienceLevel,
                   ep.CompanyName, ep.CompanyWebsite, ep.Address AS CompanyAddress,
                   uEmp.FullName AS EmployerName, uEmp.Email AS EmployerEmail,
                   iv.InterviewID, iv.InterviewDate, iv.Location AS InterviewLocation,
                   iv.LocationType AS InterviewLocationType, iv.MeetingLink,
                   iv.Notes AS InterviewNotes, iv.Status AS InterviewStatus
            FROM JobApplications ja
            JOIN Jobs j ON ja.JobID = j.JobID
            LEFT JOIN Users uEmp ON j.EmployerID = uEmp.UserID
            LEFT JOIN EmployerProfiles ep ON j.EmployerID = ep.UserID
            OUTER APPLY (
                SELECT TOP 1 *
                FROM Interviews i
                WHERE i.ApplicationID = ja.ApplicationID
                ORDER BY i.InterviewDate DESC, i.InterviewID DESC
            ) iv
            WHERE ja.UserID = ?
            ORDER BY ja.AppliedAt DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get applications for a job (employer view)
     */
    public function getByJob($jobId)
    {
        $stmt = $this->db->prepare("
            SELECT ja.*, u.FullName, u.Email, u.ProfilePicture, up.Phone, up.City,
                   up.YearsOfExp, c.FileName AS CVFile,
                   iv.InterviewID, iv.InterviewDate, iv.Location AS InterviewLocation,
                   iv.LocationType AS InterviewLocationType, iv.MeetingLink,
                   iv.Notes AS InterviewNotes, iv.Status AS InterviewStatus
            FROM JobApplications ja
            JOIN Users u ON ja.UserID = u.UserID
            LEFT JOIN UserProfiles up ON ja.UserID = up.UserID
            LEFT JOIN CVs c ON ja.CVID = c.CVID
            OUTER APPLY (
                SELECT TOP 1 *
                FROM Interviews i
                WHERE i.ApplicationID = ja.ApplicationID
                ORDER BY i.InterviewDate DESC, i.InterviewID DESC
            ) iv
            WHERE ja.JobID = ?
            ORDER BY ja.AppliedAt DESC
        ");
        $stmt->execute([$jobId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all applications for an employer's jobs
     */
    public function getByEmployer($employerId)
    {
        $stmt = $this->db->prepare("
            SELECT ja.*, j.Title AS JobTitle, u.FullName, u.Email, u.ProfilePicture,
                   up.Phone, up.City, up.YearsOfExp, c.FileName AS CVFile,
                   iv.InterviewID, iv.InterviewDate, iv.Location AS InterviewLocation,
                   iv.LocationType AS InterviewLocationType, iv.MeetingLink,
                   iv.Notes AS InterviewNotes, iv.Status AS InterviewStatus
            FROM JobApplications ja
            JOIN Jobs j ON ja.JobID = j.JobID
            JOIN Users u ON ja.UserID = u.UserID
            LEFT JOIN UserProfiles up ON ja.UserID = up.UserID
            LEFT JOIN CVs c ON ja.CVID = c.CVID
            OUTER APPLY (
                SELECT TOP 1 *
                FROM Interviews i
                WHERE i.ApplicationID = ja.ApplicationID
                ORDER BY i.InterviewDate DESC, i.InterviewID DESC
            ) iv
            WHERE j.EmployerID = ?
            ORDER BY ja.AppliedAt DESC
        ");
        $stmt->execute([$employerId]);
        return $stmt->fetchAll();
    }

    /**
     * Update application status
     */
    public function updateStatus($applicationId, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE JobApplications SET Status = ?, UpdatedAt = GETDATE() WHERE ApplicationID = ?
        ");
        return $stmt->execute([$status, $applicationId]);
    }

    /**
     * Get application by ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT ja.*, j.Title AS JobTitle, j.EmployerID, u.FullName, u.Email, u.ProfilePicture,
                   ep.CompanyName,
                   iv.InterviewID, iv.InterviewDate, iv.Location AS InterviewLocation,
                   iv.LocationType AS InterviewLocationType, iv.MeetingLink,
                   iv.Notes AS InterviewNotes, iv.Status AS InterviewStatus
            FROM JobApplications ja
            JOIN Jobs j ON ja.JobID = j.JobID
            JOIN Users u ON ja.UserID = u.UserID
            LEFT JOIN EmployerProfiles ep ON j.EmployerID = ep.UserID
            OUTER APPLY (
                SELECT TOP 1 *
                FROM Interviews i
                WHERE i.ApplicationID = ja.ApplicationID
                ORDER BY i.InterviewDate DESC, i.InterviewID DESC
            ) iv
            WHERE ja.ApplicationID = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Check if user already applied
     */
    public function hasApplied($userId, $jobId)
    {
        $stmt = $this->db->prepare("SELECT 1 FROM JobApplications WHERE UserID = ? AND JobID = ?");
        $stmt->execute([$userId, $jobId]);
        return (bool) $stmt->fetch();
    }

    /**
     * Stats
     */
    public function getStats()
    {
        $stmt = $this->db->query("
            SELECT
                (SELECT COUNT(*) FROM JobApplications) AS TotalApplications,
                (SELECT COUNT(*) FROM JobApplications WHERE Status = 'pending') AS PendingApps,
                (SELECT COUNT(*) FROM JobApplications WHERE Status = 'accepted') AS AcceptedApps
        ");
        return $stmt->fetch();
    }
}
