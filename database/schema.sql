-- =============================================
-- العواطلية - El-3awatleya
-- SQL Server Database Schema
-- =============================================

CREATE DATABASE El3awatleya;
GO

USE El3awatleya;
GO

-- =============================================
-- 1. Users
-- =============================================
CREATE TABLE Users (
    UserID          INT IDENTITY(1,1) PRIMARY KEY,
    FullName        NVARCHAR(100)   NOT NULL,
    Email           NVARCHAR(255)   NOT NULL UNIQUE,
    PasswordHash    NVARCHAR(255)   NOT NULL,
    Role            NVARCHAR(20)    NOT NULL CHECK (Role IN ('user', 'employer', 'admin')),
    IsOTPEnabled    BIT             NOT NULL DEFAULT 0,
    IsBanned        BIT             NOT NULL DEFAULT 0,
    BanReason       NVARCHAR(500)   NULL,
    ProfilePicture  NVARCHAR(500)   NULL,
    CreatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),
    UpdatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),
    LastLoginAt     DATETIME2       NULL,

    CONSTRAINT CK_Users_AdminOTP CHECK (Role <> 'admin' OR IsOTPEnabled = 1)
);
GO

-- =============================================
-- 2. UserProfiles (Normal Users)
-- =============================================
CREATE TABLE UserProfiles (
    ProfileID       INT IDENTITY(1,1) PRIMARY KEY,
    UserID          INT             NOT NULL UNIQUE,
    Phone           NVARCHAR(20)    NULL,
    DateOfBirth     DATE            NULL,
    City            NVARCHAR(100)   NULL,
    Bio             NVARCHAR(1000)  NULL,
    LinkedInURL     NVARCHAR(500)   NULL,
    GitHubURL       NVARCHAR(500)   NULL,
    PortfolioURL    NVARCHAR(500)   NULL,
    YearsOfExp      INT             NULL DEFAULT 0,
    DesiredJobTitle NVARCHAR(200)   NULL,
    CreatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),
    UpdatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_UserProfiles_Users
        FOREIGN KEY (UserID) REFERENCES Users(UserID)
        ON DELETE CASCADE
);
GO

-- =============================================
-- 3. EmployerProfiles
-- =============================================
CREATE TABLE EmployerProfiles (
    ProfileID       INT IDENTITY(1,1) PRIMARY KEY,
    UserID          INT             NOT NULL UNIQUE,
    CompanyName     NVARCHAR(200)   NOT NULL,
    Industry        NVARCHAR(100)   NULL,
    CompanySize     NVARCHAR(50)    NULL CHECK (CompanySize IN ('1-10', '11-50', '51-200', '201-500', '500+')),
    CompanyWebsite  NVARCHAR(500)   NULL,
    CompanyLogo     NVARCHAR(500)   NULL,
    City            NVARCHAR(100)   NULL,
    Address         NVARCHAR(500)   NULL,
    Description     NVARCHAR(2000)  NULL,
    CreatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),
    UpdatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_EmployerProfiles_Users
        FOREIGN KEY (UserID) REFERENCES Users(UserID)
        ON DELETE CASCADE
);
GO

-- =============================================
-- 4. CVs
-- =============================================
CREATE TABLE CVs (
    CVID            INT IDENTITY(1,1) PRIMARY KEY,
    UserID          INT             NOT NULL,
    FileName        NVARCHAR(255)   NOT NULL,
    FilePath        NVARCHAR(500)   NOT NULL,
    FileSize        INT             NOT NULL,
    FileType        NVARCHAR(10)    NOT NULL CHECK (FileType IN ('pdf', 'doc', 'docx')),
    IsActive        BIT             NOT NULL DEFAULT 1,
    UploadedAt      DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_CVs_Users
        FOREIGN KEY (UserID) REFERENCES Users(UserID)
        ON DELETE CASCADE
);
GO

-- =============================================
-- 5. Jobs
-- =============================================
CREATE TABLE Jobs (
    JobID           INT IDENTITY(1,1) PRIMARY KEY,
    EmployerID      INT             NOT NULL,
    Title           NVARCHAR(200)   NOT NULL,
    Description     NVARCHAR(4000)  NOT NULL,
    Requirements    NVARCHAR(4000)  NULL,
    JobType         NVARCHAR(30)    NOT NULL CHECK (JobType IN ('full-time', 'part-time', 'remote', 'internship', 'freelance')),
    ExperienceLevel NVARCHAR(30)    NOT NULL CHECK (ExperienceLevel IN ('entry', 'mid', 'senior', 'lead')),
    City            NVARCHAR(100)   NULL,
    SalaryMin       DECIMAL(10,2)   NULL,
    SalaryMax       DECIMAL(10,2)   NULL,
    SalaryCurrency  NVARCHAR(5)     NULL DEFAULT 'EGP',
    Skills          NVARCHAR(1000)  NULL,
    IsActive        BIT             NOT NULL DEFAULT 1,
    IsRemovedByAdmin BIT            NOT NULL DEFAULT 0,
    AdminRemoveReason NVARCHAR(500) NULL,
    PostedAt        DATETIME2       NOT NULL DEFAULT GETDATE(),
    ExpiresAt       DATETIME2       NULL,
    UpdatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Jobs_Employers
        FOREIGN KEY (EmployerID) REFERENCES Users(UserID)
);
GO

-- =============================================
-- 6. JobApplications
-- =============================================
CREATE TABLE JobApplications (
    ApplicationID   INT IDENTITY(1,1) PRIMARY KEY,
    JobID           INT             NOT NULL,
    UserID          INT             NOT NULL,
    CVID            INT             NOT NULL,
    CoverLetter     NVARCHAR(2000)  NULL,
    Status          NVARCHAR(30)    NOT NULL DEFAULT 'pending'
                    CHECK (Status IN ('pending', 'reviewed', 'shortlisted', 'interview', 'accepted', 'rejected')),
    EmployerNotes   NVARCHAR(1000)  NULL,
    AppliedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),
    UpdatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Applications_Jobs
        FOREIGN KEY (JobID) REFERENCES Jobs(JobID),
    CONSTRAINT FK_Applications_Users
        FOREIGN KEY (UserID) REFERENCES Users(UserID),
    CONSTRAINT FK_Applications_CVs
        FOREIGN KEY (CVID) REFERENCES CVs(CVID),
    CONSTRAINT UQ_UserJob UNIQUE (JobID, UserID)
);
GO

-- =============================================
-- 7. Evaluations
-- =============================================
CREATE TABLE Evaluations (
    EvaluationID    INT IDENTITY(1,1) PRIMARY KEY,
    UserID          INT             NOT NULL,
    CVID            INT             NOT NULL,
    TargetRole      NVARCHAR(200)   NOT NULL,
    OverallScore    INT             NOT NULL CHECK (OverallScore BETWEEN 0 AND 100),
    Verdict         NVARCHAR(30)    NOT NULL CHECK (Verdict IN ('excellent', 'good', 'needs_work', 'poor')),
    Summary         NVARCHAR(2000)  NULL,
    IsLowScore      BIT             NOT NULL DEFAULT 0,
    EvaluatedAt     DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Evaluations_Users
        FOREIGN KEY (UserID) REFERENCES Users(UserID),
    CONSTRAINT FK_Evaluations_CVs
        FOREIGN KEY (CVID) REFERENCES CVs(CVID)
);
GO

-- =============================================
-- 8. EvaluationSkills
-- =============================================
CREATE TABLE EvaluationSkills (
    SkillID         INT IDENTITY(1,1) PRIMARY KEY,
    EvaluationID    INT             NOT NULL,
    SkillName       NVARCHAR(100)   NOT NULL,
    ScorePercent    INT             NOT NULL CHECK (ScorePercent BETWEEN 0 AND 100),
    Level           NVARCHAR(10)    NOT NULL CHECK (Level IN ('high', 'mid', 'low')),

    CONSTRAINT FK_EvalSkills_Evaluations
        FOREIGN KEY (EvaluationID) REFERENCES Evaluations(EvaluationID)
        ON DELETE CASCADE
);
GO

-- =============================================
-- 9. RecommendedCourses
-- =============================================
CREATE TABLE RecommendedCourses (
    CourseID        INT IDENTITY(1,1) PRIMARY KEY,
    EvaluationID    INT             NOT NULL,
    CourseName      NVARCHAR(200)   NOT NULL,
    Provider        NVARCHAR(100)   NULL,
    CourseURL       NVARCHAR(500)   NULL,
    SkillTargeted   NVARCHAR(100)   NOT NULL,
    Description     NVARCHAR(500)   NULL,
    IsFree          BIT             NOT NULL DEFAULT 0,

    CONSTRAINT FK_Courses_Evaluations
        FOREIGN KEY (EvaluationID) REFERENCES Evaluations(EvaluationID)
        ON DELETE CASCADE
);
GO

-- =============================================
-- 10. EnhancementRequests
-- =============================================
CREATE TABLE EnhancementRequests (
    RequestID       INT IDENTITY(1,1) PRIMARY KEY,
    UserID          INT             NOT NULL,
    CVID            INT             NOT NULL,
    RequestMessage  NVARCHAR(1000)  NULL,
    Status          NVARCHAR(20)    NOT NULL DEFAULT 'pending'
                    CHECK (Status IN ('pending', 'processing', 'completed', 'no_result')),
    RequestedAt     DATETIME2       NOT NULL DEFAULT GETDATE(),
    CompletedAt     DATETIME2       NULL,

    CONSTRAINT FK_EnhRequests_Users
        FOREIGN KEY (UserID) REFERENCES Users(UserID),
    CONSTRAINT FK_EnhRequests_CVs
        FOREIGN KEY (CVID) REFERENCES CVs(CVID)
);
GO

-- =============================================
-- 11. EnhancementResults
-- =============================================
CREATE TABLE EnhancementResults (
    ResultID        INT IDENTITY(1,1) PRIMARY KEY,
    RequestID       INT             NOT NULL,
    Category        NVARCHAR(50)    NOT NULL,
    Suggestion      NVARCHAR(2000)  NOT NULL,
    Priority        NVARCHAR(10)    NOT NULL DEFAULT 'medium'
                    CHECK (Priority IN ('high', 'medium', 'low')),
    CreatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_EnhResults_Requests
        FOREIGN KEY (RequestID) REFERENCES EnhancementRequests(RequestID)
        ON DELETE CASCADE
);
GO



-- =============================================
-- 12. Interviews
-- =============================================
CREATE TABLE Interviews (
    InterviewID     INT IDENTITY(1,1) PRIMARY KEY,
    ApplicationID   INT             NOT NULL,
    JobID           INT             NOT NULL,
    EmployerID      INT             NOT NULL,
    CandidateID     INT             NOT NULL,
    InterviewDate   DATETIME2       NOT NULL,
    Location        NVARCHAR(500)   NOT NULL,
    LocationType    NVARCHAR(20)    NOT NULL DEFAULT 'onsite'
                    CHECK (LocationType IN ('onsite', 'online', 'hybrid')),
    MeetingLink     NVARCHAR(500)   NULL,
    Notes           NVARCHAR(1000)  NULL,
    Status          NVARCHAR(20)    NOT NULL DEFAULT 'scheduled'
                    CHECK (Status IN ('scheduled', 'confirmed', 'completed', 'cancelled')),
    CreatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),
    UpdatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Interviews_Applications
        FOREIGN KEY (ApplicationID) REFERENCES JobApplications(ApplicationID),
    CONSTRAINT FK_Interviews_Jobs
        FOREIGN KEY (JobID) REFERENCES Jobs(JobID),
    CONSTRAINT FK_Interviews_Employer
        FOREIGN KEY (EmployerID) REFERENCES Users(UserID),
    CONSTRAINT FK_Interviews_Candidate
        FOREIGN KEY (CandidateID) REFERENCES Users(UserID)
);
GO

-- =============================================
-- 13. ActivityLogs
-- =============================================
CREATE TABLE ActivityLogs (
    LogID           INT IDENTITY(1,1) PRIMARY KEY,
    UserID          INT             NULL,
    Action          NVARCHAR(50)    NOT NULL,
    EntityType      NVARCHAR(50)    NULL,
    EntityID        INT             NULL,
    Details         NVARCHAR(1000)  NULL,
    IPAddress       NVARCHAR(45)    NULL,
    CreatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_ActivityLogs_Users
        FOREIGN KEY (UserID) REFERENCES Users(UserID)
);
GO

-- =============================================
-- 14. Backups
-- =============================================
CREATE TABLE Backups (
    BackupID        INT IDENTITY(1,1) PRIMARY KEY,
    AdminID         INT             NOT NULL,
    FileName        NVARCHAR(255)   NOT NULL,
    FilePath        NVARCHAR(500)   NOT NULL,
    FileSize        BIGINT          NULL,
    BackupType      NVARCHAR(20)    NOT NULL DEFAULT 'full'
                    CHECK (BackupType IN ('full', 'users', 'jobs', 'applications')),
    CreatedAt       DATETIME2       NOT NULL DEFAULT GETDATE(),

    CONSTRAINT FK_Backups_Admin
        FOREIGN KEY (AdminID) REFERENCES Users(UserID)
);
GO


-- =============================================
-- INDEXES
-- =============================================
CREATE INDEX IX_Users_Email          ON Users(Email);
CREATE INDEX IX_Users_Role           ON Users(Role);
CREATE INDEX IX_Jobs_EmployerID      ON Jobs(EmployerID);
CREATE INDEX IX_Jobs_IsActive        ON Jobs(IsActive);
CREATE INDEX IX_Jobs_PostedAt        ON Jobs(PostedAt DESC);
CREATE INDEX IX_Applications_JobID   ON JobApplications(JobID);
CREATE INDEX IX_Applications_UserID  ON JobApplications(UserID);
CREATE INDEX IX_Applications_Status  ON JobApplications(Status);
CREATE INDEX IX_CVs_UserID           ON CVs(UserID);
CREATE INDEX IX_Evaluations_UserID   ON Evaluations(UserID);

CREATE INDEX IX_ActivityLogs_UserID  ON ActivityLogs(UserID);
CREATE INDEX IX_ActivityLogs_Action  ON ActivityLogs(Action);
CREATE INDEX IX_Interviews_CandidateID ON Interviews(CandidateID);
GO


-- =============================================
-- STORED PROCEDURES
-- =============================================

-- Check if user exists (for Forgot Password - Normal Users only)
CREATE PROCEDURE sp_CheckUserExists
    @Email NVARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT UserID, FullName, Email, Role
    FROM Users
    WHERE Email = @Email AND Role = 'user' AND IsBanned = 0;
END
GO



-- Log Activity
CREATE PROCEDURE sp_LogActivity
    @UserID INT = NULL,
    @Action NVARCHAR(50),
    @EntityType NVARCHAR(50) = NULL,
    @EntityID INT = NULL,
    @Details NVARCHAR(1000) = NULL,
    @IPAddress NVARCHAR(45) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO ActivityLogs (UserID, Action, EntityType, EntityID, Details, IPAddress)
    VALUES (@UserID, @Action, @EntityType, @EntityID, @Details, @IPAddress);
END
GO

-- Admin Dashboard Stats
CREATE PROCEDURE sp_GetAdminStats
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        (SELECT COUNT(*) FROM Users WHERE Role = 'user')       AS TotalUsers,
        (SELECT COUNT(*) FROM Users WHERE Role = 'employer')   AS TotalEmployers,
        (SELECT COUNT(*) FROM Jobs WHERE IsActive = 1)         AS ActiveJobs,
        (SELECT COUNT(*) FROM JobApplications)                 AS TotalApplications,
        (SELECT COUNT(*) FROM Users WHERE IsBanned = 1)        AS BannedUsers,
        (SELECT COUNT(*) FROM Evaluations)                     AS TotalEvaluations,
        (SELECT COUNT(*) FROM Users WHERE CreatedAt >= DATEADD(DAY, -7, GETDATE())) AS NewUsersThisWeek,
        (SELECT COUNT(*) FROM Jobs WHERE PostedAt >= DATEADD(DAY, -7, GETDATE()))   AS NewJobsThisWeek;
END
GO


-- =============================================
-- 15. RememberTokens (افتكرني)
-- =============================================
CREATE TABLE RememberTokens (
    TokenID     INT IDENTITY(1,1) PRIMARY KEY,
    UserID      INT             NOT NULL,
    Token       NVARCHAR(255)   NOT NULL,
    ExpiresAt   DATETIME2       NOT NULL,
    CreatedAt   DATETIME2       NOT NULL DEFAULT GETDATE(),
    CONSTRAINT FK_RememberTokens_Users FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE
);
GO


-- =============================================
-- SEED DATA
-- =============================================

-- Default Admin (password: admin123)
INSERT INTO Users (FullName, Email, PasswordHash, Role, IsOTPEnabled)
VALUES (N'مدير النظام', 'admin@el3awatleya.com', '$2y$10$REPLACE_WITH_REAL_HASH', 'admin', 1);
GO
