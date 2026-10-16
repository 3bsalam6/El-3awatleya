/**
 * العواطلية - Main JavaScript
 * Handles file upload, form validation, and UI interactions
 */

document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // File Upload Handling
    // ===========================
    const uploadZone = document.getElementById('uploadZone');
    const cvFile = document.getElementById('cvFile');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    const analyzeBtn = document.getElementById('analyzeBtn');
    const cvForm = document.getElementById('cvForm');

    if (uploadZone && cvFile) {
        // Drag & drop
        uploadZone.addEventListener('dragover', function (e) {
            e.preventDefault();
            uploadZone.classList.add('drag-over');
        });

        uploadZone.addEventListener('dragleave', function () {
            uploadZone.classList.remove('drag-over');
        });

        uploadZone.addEventListener('drop', function (e) {
            e.preventDefault();
            uploadZone.classList.remove('drag-over');
            if (e.dataTransfer.files.length > 0) {
                cvFile.files = e.dataTransfer.files;
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });

        // File input change
        cvFile.addEventListener('change', function () {
            if (cvFile.files.length > 0) {
                handleFileSelect(cvFile.files[0]);
            }
        });

        // Remove file
        if (removeFile) {
            removeFile.addEventListener('click', function () {
                clearFile();
            });
        }
    }

    function handleFileSelect(file) {
        var allowed = ['application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (allowed.indexOf(file.type) === -1) {
            alert('الملف لازم يكون PDF أو Word');
            clearFile();
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('الملف كبير أوي! أقصى حجم 5MB');
            clearFile();
            return;
        }

        // Show file info
        uploadZone.classList.add('has-file');
        fileInfo.classList.add('visible');
        fileName.textContent = file.name;
        fileSize.textContent = formatSize(file.size);
        updateAnalyzeBtn();
    }

    function clearFile() {
        if (cvFile) cvFile.value = '';
        if (uploadZone) uploadZone.classList.remove('has-file');
        if (fileInfo) fileInfo.classList.remove('visible');
        updateAnalyzeBtn();
    }

    function updateAnalyzeBtn() {
        if (!analyzeBtn) return;
        var jobTitle = document.getElementById('jobTitle');
        var hasFile = cvFile && cvFile.files.length > 0;
        var hasJob = jobTitle && jobTitle.value !== '';
        analyzeBtn.disabled = !(hasFile && hasJob);
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    // Job title change
    var jobTitle = document.getElementById('jobTitle');
    if (jobTitle) {
        jobTitle.addEventListener('change', updateAnalyzeBtn);
    }

    // CV Form submit
    if (cvForm) {
        cvForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('active');
            }

            // Simulate processing delay (backend will handle this later)
            setTimeout(function () {
                window.location.href = '/evaluation';
            }, 2500);
        });
    }


    // ===========================
    // Login Form Handling
    // ===========================
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var email = document.getElementById('email').value.trim();
            var password = document.getElementById('password').value;

            if (!email || !password) {
                alert('ادخل الإيميل والباسورد');
                return;
            }

            var btn = document.getElementById('loginBtn');
            btn.innerHTML = '<span class="spinner"></span> جاري الدخول...';
            btn.disabled = true;

            // Simulate login (backend later)
            setTimeout(function () {
                window.location.href = '/dashboard';
            }, 1500);
        });
    }


    // ===========================
    // Register Form Handling
    // ===========================
    var registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var name = document.getElementById('name').value.trim();
            var email = document.getElementById('email').value.trim();
            var password = document.getElementById('password').value;
            var confirm = document.getElementById('password_confirm').value;

            if (!name || !email || !password || !confirm) {
                alert('املا كل الخانات');
                return;
            }

            if (password !== confirm) {
                alert('كلمة المرور مش متطابقة!');
                return;
            }

            if (password.length < 6) {
                alert('كلمة المرور لازم تكون 6 حروف على الأقل');
                return;
            }

            var btn = document.getElementById('registerBtn');
            btn.innerHTML = '<span class="spinner"></span> جاري التسجيل...';
            btn.disabled = true;

            // Simulate register (backend later)
            setTimeout(function () {
                window.location.href = '/dashboard';
            }, 1500);
        });
    }

});
