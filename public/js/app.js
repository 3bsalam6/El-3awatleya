/**
 * العواطلية - Main JavaScript
 * Handles all UI interactions across the platform
 */

document.addEventListener('DOMContentLoaded', function () {

    // ===========================
    // Role Tab Switching (Register page only)
    // ===========================
    var roleTabs = document.querySelectorAll('.role-tab');
    if (roleTabs.length > 0) {
        roleTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                roleTabs.forEach(function (t) { t.classList.remove('active'); });
                tab.classList.add('active');

                var role = tab.getAttribute('data-role');

                // Update hidden role input
                var registerRole = document.getElementById('registerRole');
                if (registerRole) registerRole.value = role;

                // Toggle fields in register
                var employerFields = document.getElementById('employerFields');
                var userFields = document.getElementById('userFields');
                if (employerFields && userFields) {
                    if (role === 'employer') {
                        employerFields.style.display = 'block';
                        userFields.style.display = 'none';
                    } else {
                        employerFields.style.display = 'none';
                        userFields.style.display = 'block';
                    }
                }
            });
        });
    }


    // ===========================
    // OTP Input Auto-focus
    // ===========================
    var otpContainers = document.querySelectorAll('.otp-inputs');
    otpContainers.forEach(function (container) {
        var digits = container.querySelectorAll('.otp-digit');
        digits.forEach(function (digit, index) {
            digit.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value && index < digits.length - 1) {
                    digits[index + 1].focus();
                }
                collectOTP(container, digits);
            });

            digit.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    digits[index - 1].focus();
                }
            });

            digit.addEventListener('paste', function (e) {
                e.preventDefault();
                var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                for (var i = 0; i < Math.min(pasted.length, digits.length); i++) {
                    digits[i].value = pasted[i];
                }
                digits[Math.min(pasted.length, digits.length) - 1].focus();
                collectOTP(container, digits);
            });
        });
    });

    function collectOTP(container, digits) {
        var full = '';
        digits.forEach(function (d) { full += d.value; });
        var hiddenField = container.parentElement.querySelector('input[type="hidden"]');
        if (hiddenField) hiddenField.value = full;
    }


    // ===========================
    // OTP Timer
    // ===========================
    function startOTPTimer(timerEl, resendBtn, seconds) {
        if (!timerEl) return;
        var remaining = seconds || 120;
        if (resendBtn) resendBtn.disabled = true;

        var interval = setInterval(function () {
            remaining--;
            var mins = Math.floor(remaining / 60);
            var secs = remaining % 60;
            timerEl.textContent = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;

            if (remaining <= 0) {
                clearInterval(interval);
                timerEl.textContent = '00:00';
                if (resendBtn) resendBtn.disabled = false;
            }
        }, 1000);
    }

    // Auto-start timers if visible
    var otpTimer = document.getElementById('otpTimer');
    var resendOTP = document.getElementById('resendOTP');
    if (otpTimer) startOTPTimer(otpTimer, resendOTP, 120);

    if (resendOTP) {
        resendOTP.addEventListener('click', function () {
            resendOTP.disabled = true;
            resendOTP.textContent = 'جاري الإرسال...';
            fetch('/otp/resend', { method: 'POST' })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    alert(data.message);
                    if (data.success) {
                        resendOTP.textContent = 'ابعت تاني';
                        startOTPTimer(otpTimer, resendOTP, 120);
                    } else {
                        resendOTP.disabled = false;
                        resendOTP.textContent = 'ابعت تاني';
                    }
                })
                .catch(function () {
                    alert('حدث خطأ في الاتصال بالسيرفر');
                    resendOTP.disabled = false;
                    resendOTP.textContent = 'ابعت تاني';
                });
        });
    }

    var adminOTPTimer = document.getElementById('adminOTPTimer');
    var adminResendOTP = document.getElementById('adminResendOTP');
    if (adminOTPTimer) startOTPTimer(adminOTPTimer, adminResendOTP, 120);


    // ===========================
    // File Upload Handling
    // ===========================
    var uploadZone = document.getElementById('uploadZone');
    var cvFile = document.getElementById('cvFile');
    var fileInfo = document.getElementById('fileInfo');
    var fileName = document.getElementById('fileName');
    var fileSize = document.getElementById('fileSize');
    var removeFile = document.getElementById('removeFile');
    var analyzeBtn = document.getElementById('analyzeBtn');
    var submitApplicationBtn = document.getElementById('submitApplicationBtn');
    var requestEnhBtn = document.getElementById('requestEnhBtn');
    var cvForm = document.getElementById('cvForm');

    if (uploadZone && cvFile) {
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

        cvFile.addEventListener('change', function () {
            if (cvFile.files.length > 0) handleFileSelect(cvFile.files[0]);
        });

        if (removeFile) {
            removeFile.addEventListener('click', function () { clearFile(); });
        }
    }

    function handleFileSelect(file) {
        var allowed = ['application/pdf', 'application/msword',
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

        if (uploadZone) uploadZone.classList.add('has-file');
        if (fileInfo) fileInfo.classList.add('visible');
        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatSize(file.size);
        updateButtons();
    }

    function clearFile() {
        if (cvFile) cvFile.value = '';
        if (uploadZone) uploadZone.classList.remove('has-file');
        if (fileInfo) fileInfo.classList.remove('visible');
        updateButtons();
    }

    function updateButtons() {
        var hasFile = cvFile && cvFile.files.length > 0;
        if (analyzeBtn) {
            var jobTitle = document.getElementById('jobTitle');
            analyzeBtn.disabled = !(hasFile && jobTitle && jobTitle.value !== '');
        }
        if (submitApplicationBtn) submitApplicationBtn.disabled = !hasFile;
        if (requestEnhBtn) requestEnhBtn.disabled = !hasFile;
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    var jobTitle = document.getElementById('jobTitle');
    if (jobTitle) jobTitle.addEventListener('change', updateButtons);


    // ===========================
    // CV Form submit (Dashboard)
    // ===========================
    if (cvForm) {
        cvForm.addEventListener('submit', function () {
            var loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) loadingOverlay.classList.add('active');
        });
    }


    // ===========================
    // Login Form Submit UX
    // ===========================
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function () {
            var btn = document.getElementById('loginBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري الدخول...';
            }
        });
    }


    // ===========================
    // Register Form Submit UX & Validation
    // ===========================
    var registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            var password = document.getElementById('password') ? document.getElementById('password').value : '';
            var confirmInput = document.getElementById('password_confirm') || document.querySelector('input[name="confirm_password"]');
            var confirm = confirmInput ? confirmInput.value : '';

            if (password !== confirm) {
                e.preventDefault();
                alert('كلمة المرور وتأكيد كلمة المرور غير متطابقين!');
                return false;
            }

            var btn = document.getElementById('registerBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري التسجيل...';
            }
        });
    }


    // ===========================
    // OTP Login Verification (/otp page)
    // ===========================
    var otpLoginForm = document.getElementById('otpLoginForm');
    if (otpLoginForm) {
        otpLoginForm.addEventListener('submit', function (e) {
            var container = otpLoginForm.querySelector('.otp-inputs');
            if (container) {
                var digits = container.querySelectorAll('.otp-digit');
                collectOTP(container, digits);
            }
            var otpVal = document.getElementById('otpFull') ? document.getElementById('otpFull').value : '';
            if (otpVal.length < 6) {
                e.preventDefault();
                var err = document.getElementById('otpError');
                if (err) {
                    err.style.display = 'flex';
                    document.getElementById('otpErrorText').textContent = 'ادخل الكود كامل (6 أرقام)';
                }
                return false;
            }
            var btn = document.getElementById('verifyBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري التحقق...';
            }
        });
    }


    // ===========================
    // Forgot Password Form Submit UX
    // ===========================
    var forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', function () {
            var btn = document.getElementById('forgotBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري الإرسال...';
            }
        });
    }

    // ===========================
    // Reset Password Form Submit UX
    // ===========================
    var resetForm = document.getElementById('resetPasswordForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function (e) {
            var p1 = document.getElementById('password') ? document.getElementById('password').value : '';
            var p2 = document.getElementById('confirm_password') ? document.getElementById('confirm_password').value : '';
            var errBox = document.getElementById('resetError');
            var errText = document.getElementById('resetErrorText');

            if (p1.length < 6) {
                e.preventDefault();
                if (errBox && errText) {
                    errText.textContent = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
                    errBox.style.display = 'flex';
                }
                return;
            }

            if (p1 !== p2) {
                e.preventDefault();
                if (errBox && errText) {
                    errText.textContent = 'كلمتا المرور غير متطابقتين';
                    errBox.style.display = 'flex';
                }
                return;
            }

            if (errBox) {
                errBox.style.display = 'none';
            }

            var btn = document.getElementById('resetBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري الحفظ...';
            }
        });
    }


    // ===========================
    // ===========================
    // Job Apply Form
    // ===========================
    var applyForm = document.getElementById('applyForm');
    if (applyForm) {
        applyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('submitApplicationBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري الإرسال...';
                btn.disabled = true;
            }

            var formData = new FormData(applyForm);
            var urlParams = new URLSearchParams(window.location.search);
            var jobId = urlParams.get('id');
            if (jobId && !formData.get('job_id')) {
                formData.append('job_id', jobId);
            }

            fetch('/jobs/apply', {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modal = document.getElementById('successModal');
                    if (modal) modal.style.display = 'flex';
                } else {
                    alert(data.message || 'حدث خطأ');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'إرسال الطلب';
                    }
                }
            })
            .catch(function () {
                alert('حدث خطأ في الاتصال بالسيرفر');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'إرسال الطلب';
                }
            });
        });
    }


    // ===========================
    // Post Job Form
    // ===========================
    var postJobForm = document.getElementById('postJobForm');
    if (postJobForm) {
        postJobForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('publishJobBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري النشر...';
                btn.disabled = true;
            }

            var formData = new FormData(postJobForm);
            fetch('/employer/post-job', {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modal = document.getElementById('successModal');
                    if (modal) modal.style.display = 'flex';
                } else {
                    alert(data.message || 'حدث خطأ أثناء النشر');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'نشر الوظيفة';
                    }
                }
            })
            .catch(function () {
                alert('حدث خطأ في الاتصال بالسيرفر');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'نشر الوظيفة';
                }
            });
        });
    }


    // ===========================
    // Enhancement Form
    // ===========================
    var enhancementForm = document.getElementById('enhancementForm');
    if (enhancementForm) {
        enhancementForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('requestEnhBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري التحليل...';
                btn.disabled = true;
            }
            var formData = new FormData(enhancementForm);
            fetch('/enhancements', {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    alert(data.message || 'تم إرسال طلب التحسينات وتوليد الاقتراحات بنجاح!');
                    window.location.reload();
                } else {
                    alert(data.message || 'حدث خطأ أثناء معالجة الطلب');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'اطلب تحسينات';
                    }
                }
            })
            .catch(function () {
                alert('حدث خطأ في الاتصال بالسيرفر');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'اطلب تحسينات';
                }
            });
        });
    }


    // ===========================
    // Interview Form
    // ===========================
    var interviewForm = document.getElementById('interviewForm');
    if (interviewForm) {
        var locationType = document.getElementById('locationType');
        if (locationType) {
            locationType.addEventListener('change', function () {
                var locationGroup = document.getElementById('locationGroup');
                var meetingLinkGroup = document.getElementById('meetingLinkGroup');
                if (this.value === 'online') {
                    if (locationGroup) locationGroup.style.display = 'none';
                    if (meetingLinkGroup) meetingLinkGroup.style.display = 'block';
                } else if (this.value === 'hybrid') {
                    if (locationGroup) locationGroup.style.display = 'block';
                    if (meetingLinkGroup) meetingLinkGroup.style.display = 'block';
                } else {
                    if (locationGroup) locationGroup.style.display = 'block';
                    if (meetingLinkGroup) meetingLinkGroup.style.display = 'none';
                }
            });
        }

        interviewForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('scheduleBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري الحفظ...';
                btn.disabled = true;
            }
            var formData = new FormData(interviewForm);
            fetch('/employer/interview', {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modal = document.getElementById('successModal');
                    if (modal) modal.style.display = 'flex';
                } else {
                    alert(data.message || 'حدث خطأ أثناء تحديد المقابلة');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '📅 حدد المقابلة';
                    }
                }
            })
            .catch(function () {
                alert('حدث خطأ في الاتصال بالسيرفر');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '📅 حدد المقابلة';
                }
            });
        });
    }


    // ===========================
    // Jobs Search & Filter
    // ===========================
    var searchJobs = document.getElementById('searchJobs');
    var filterType = document.getElementById('filterType');
    var filterLevel = document.getElementById('filterLevel');

    function filterJobCards() {
        var cards = document.querySelectorAll('.job-card');
        var search = searchJobs ? searchJobs.value.toLowerCase() : '';
        var type = filterType ? filterType.value : '';
        var level = filterLevel ? filterLevel.value : '';

        cards.forEach(function (card) {
            var matchSearch = !search || card.textContent.toLowerCase().includes(search);
            var matchType = !type || card.getAttribute('data-type') === type;
            var matchLevel = !level || card.getAttribute('data-level') === level;
            card.style.display = (matchSearch && matchType && matchLevel) ? '' : 'none';
        });
    }

    if (searchJobs) searchJobs.addEventListener('input', filterJobCards);
    if (filterType) filterType.addEventListener('change', filterJobCards);
    if (filterLevel) filterLevel.addEventListener('change', filterJobCards);


    // ===========================
    // Settings Page
    // ===========================

    // OTP Toggle
    var otpToggle = document.getElementById('otpToggle');
    var otpStatus = document.getElementById('otpStatus');
    var otpSetupInfo = document.getElementById('otpSetupInfo');

    if (otpToggle) {
        otpToggle.addEventListener('change', function () {
            var enabled = this.checked ? 1 : 0;
            var formData = new FormData();
            formData.append('enabled', enabled);

            var isEmployer = window.location.pathname.indexOf('/employer') !== -1;
            var otpUrl = isEmployer ? '/employer/settings/otp' : '/settings/otp';

            fetch(otpUrl, {
                method: 'POST',
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (otpStatus) otpStatus.textContent = enabled ? 'مفعّل' : 'معطّل';
                if (otpSetupInfo) otpSetupInfo.style.display = enabled ? 'block' : 'none';
            })
            .catch(function () {
                alert('حدث خطأ في تحديث إعدادات OTP');
            });
        });
    }

    // Profile Form
    var profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('saveProfileBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري الحفظ...';
                btn.disabled = true;
            }

            var isEmployer = window.location.pathname.indexOf('/employer') !== -1;
            var isAdmin = window.location.pathname.indexOf('/admin') !== -1;
            var profileUrl = '/settings/profile';
            if (isEmployer) profileUrl = '/employer/settings/profile';
            if (isAdmin) profileUrl = '/admin/settings/profile';

            var formData = new FormData(profileForm);
            fetch(profileUrl, {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (btn) {
                    btn.innerHTML = 'حفظ التغييرات';
                    btn.disabled = false;
                }
                alert(data.message || (data.success ? 'تم حفظ التغييرات بنجاح!' : 'حدث خطأ'));
            })
            .catch(function () {
                if (btn) {
                    btn.innerHTML = 'حفظ التغييرات';
                    btn.disabled = false;
                }
                alert('حدث خطأ في الاتصال بالسيرفر');
            });
        });
    }

    // Change Email Form
    var changeEmailForm = document.getElementById('changeEmailForm');
    if (changeEmailForm) {
        changeEmailForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var newEmail = document.getElementById('newEmail') ? document.getElementById('newEmail').value.trim() : '';
            var emailPass = document.getElementById('emailPassword') ? document.getElementById('emailPassword').value : '';
            if (!newEmail || !emailPass) { alert('املا كل الخانات'); return; }

            var btn = document.getElementById('changeEmailBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري التغيير...';
                btn.disabled = true;
            }

            var isEmployer = window.location.pathname.indexOf('/employer') !== -1;
            var isAdmin = window.location.pathname.indexOf('/admin') !== -1;
            var emailUrl = '/settings/email';
            if (isEmployer) emailUrl = '/employer/settings/email';
            if (isAdmin) emailUrl = '/admin/settings/email';

            var formData = new FormData(changeEmailForm);
            fetch(emailUrl, {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (btn) {
                    btn.innerHTML = 'تغيير الإيميل';
                    btn.disabled = false;
                }
                alert(data.message);
                if (data.success) {
                    var currentEmail = document.getElementById('currentEmail');
                    if (currentEmail) currentEmail.value = newEmail;
                    if (document.getElementById('newEmail')) document.getElementById('newEmail').value = '';
                    if (document.getElementById('emailPassword')) document.getElementById('emailPassword').value = '';
                }
            })
            .catch(function () {
                if (btn) {
                    btn.innerHTML = 'تغيير الإيميل';
                    btn.disabled = false;
                }
                alert('حدث خطأ في الاتصال بالسيرفر');
            });
        });
    }

    // Change Password Form
    var changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var currentPass = document.getElementById('currentPassword') ? document.getElementById('currentPassword').value : '';
            var newPass = document.getElementById('settingsNewPassword') ? document.getElementById('settingsNewPassword').value : '';
            var confirmPass = document.getElementById('settingsConfirmPassword') ? document.getElementById('settingsConfirmPassword').value : '';

            if (!currentPass || !newPass || !confirmPass) { alert('املا كل الخانات'); return; }
            if (newPass.length < 6) { alert('كلمة المرور لازم تكون 6 حروف على الأقل'); return; }
            if (newPass !== confirmPass) { alert('كلمة المرور الجديدة مش متطابقة!'); return; }

            var btn = document.getElementById('changePasswordBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner"></span> جاري التغيير...';
                btn.disabled = true;
            }

            var isEmployer = window.location.pathname.indexOf('/employer') !== -1;
            var isAdmin = window.location.pathname.indexOf('/admin') !== -1;
            var passUrl = '/settings/password';
            if (isEmployer) passUrl = '/employer/settings/password';
            if (isAdmin) passUrl = '/admin/settings/password';

            var formData = new FormData();
            formData.append('current_password', currentPass);
            formData.append('new_password', newPass);
            formData.append('confirm_password', confirmPass);

            fetch(passUrl, {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (btn) {
                    btn.innerHTML = 'تغيير كلمة المرور';
                    btn.disabled = false;
                }
                alert(data.message);
                if (data.success) {
                    if (document.getElementById('currentPassword')) document.getElementById('currentPassword').value = '';
                    if (document.getElementById('settingsNewPassword')) document.getElementById('settingsNewPassword').value = '';
                    if (document.getElementById('settingsConfirmPassword')) document.getElementById('settingsConfirmPassword').value = '';
                }
            })
            .catch(function () {
                if (btn) {
                    btn.innerHTML = 'تغيير كلمة المرور';
                    btn.disabled = false;
                }
                alert('حدث خطأ في الاتصال بالسيرفر');
            });
        });
    }

});


// ===========================
// Global Functions
// ===========================

function closeModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}

function banUser(userId, btn) {
    var modal = document.getElementById('banModal');
    if (modal) {
        modal.style.display = 'flex';
        document.getElementById('confirmBanBtn').onclick = function () {
            var reason = document.getElementById('banReason') ? document.getElementById('banReason').value : '';
            var formData = new FormData();
            formData.append('user_id', userId);
            formData.append('reason', reason);

            fetch('/admin/users/ban', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var row = btn.closest('tr');
                    row.classList.add('banned-row');
                    row.querySelector('.status-dot').className = 'status-dot banned';
                    row.querySelector('.status-dot').nextSibling.textContent = ' محظور';
                    btn.className = 'btn btn-success btn-sm';
                    btn.textContent = '✅ إلغاء الحظر';
                    btn.setAttribute('onclick', 'unbanUser(' + userId + ', this)');
                    closeModal('banModal');
                } else {
                    alert(data.message || 'حدث خطأ');
                }
            })
            .catch(function() { alert('تعذر الاتصال بالسيرفر'); });
        };
    }
}

function unbanUser(userId, btn) {
    if (confirm('متأكد عاوز تلغي الحظر؟')) {
        var formData = new FormData();
        formData.append('user_id', userId);

        fetch('/admin/users/unban', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var row = btn.closest('tr');
                row.classList.remove('banned-row');
                row.querySelector('.status-dot').className = 'status-dot active';
                row.querySelector('.status-dot').nextSibling.textContent = ' نشط';
                btn.className = 'btn btn-danger btn-sm';
                btn.textContent = '🚫 حظر';
                btn.setAttribute('onclick', 'banUser(' + userId + ', this)');
            } else {
                alert(data.message || 'حدث خطأ');
            }
        })
        .catch(function() { alert('تعذر الاتصال بالسيرفر'); });
    }
}

function removeJob(jobId, btn) {
    var modal = document.getElementById('removeJobModal');
    if (modal) {
        modal.style.display = 'flex';
        document.getElementById('confirmRemoveBtn').onclick = function () {
            toggleJobAjax(jobId, btn, 'removeJobModal', false);
        };
    } else {
        if (confirm('متأكد عاوز تعطل الوظيفة دي؟')) {
            toggleJobAjax(jobId, btn, null, false);
        }
    }
}

function restoreJob(jobId, btn) {
    if (confirm('متأكد عاوز تفعل الوظيفة دي تاني؟')) {
        toggleJobAjax(jobId, btn, null, true);
    }
}

function toggleJobAjax(jobId, btn, modalId, isRestoring) {
    var formData = new FormData();
    formData.append('job_id', jobId);

    fetch('/admin/jobs/toggle', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var row = btn.closest('tr');
            if (isRestoring) {
                row.classList.remove('removed-row');
                row.querySelector('.status-dot').className = 'status-dot active';
                row.querySelector('.status-dot').nextSibling.textContent = ' نشط';
                btn.className = 'btn btn-danger btn-sm';
                btn.textContent = '🗑️ تعطيل';
                btn.setAttribute('onclick', 'removeJob(' + jobId + ', this)');
            } else {
                row.classList.add('removed-row');
                row.querySelector('.status-dot').className = 'status-dot banned';
                row.querySelector('.status-dot').nextSibling.textContent = ' معطل';
                btn.className = 'btn btn-success btn-sm';
                btn.textContent = '♻️ تفعيل';
                btn.setAttribute('onclick', 'restoreJob(' + jobId + ', this)');
            }
            if (modalId) closeModal(modalId);
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(function() { alert('تعذر الاتصال بالسيرفر'); });
}

function contactApplicant(appId) {
    var modal = document.getElementById('contactModal');
    if (modal) modal.style.display = 'flex';
}

function sendContact() {
    alert('تم إرسال الرسالة للمتقدم!');
    closeModal('contactModal');
}

function updateStatus(btn, status) {
    var card = btn.closest('.applicant-card');
    var appId = card ? card.getAttribute('data-app-id') : null;
    if (appId) {
        updateApplicationStatus(appId, status, btn);
    }
}

function updateApplicationStatus(appId, status, btn) {
    var formData = new FormData();
    formData.append('application_id', appId);
    formData.append('status', status);

    fetch('/employer/applicants/status', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var statusLabels = {
                'shortlisted': '⭐ قائمة قصيرة',
                'interview': '📞 مقابلة',
                'accepted': '🎉 مقبول',
                'rejected': '❌ مرفوض'
            };
            var statusClasses = {
                'shortlisted': 'status-interview',
                'interview': 'status-interview',
                'accepted': 'status-interview',
                'rejected': 'status-rejected'
            };
            var card = btn.closest('.applicant-card');
            var badge = card.querySelector('.status-badge');
            if (badge) {
                badge.className = 'status-badge ' + (statusClasses[status] || 'status-pending');
                badge.textContent = statusLabels[status] || status;
            }
        } else {
            alert(data.message || 'حدث خطأ');
        }
    })
    .catch(function() {
        alert('تعذر الاتصال بالسيرفر');
    });
}

function downloadBackup() {
    var btn = document.getElementById('downloadBackupBtn');
    btn.innerHTML = '<span>⏳</span> جاري التحميل...';
    setTimeout(function () {
        alert('تم تحميل النسخة الاحتياطية بنجاح!');
        btn.innerHTML = '<span>💾</span> تحميل نسخة احتياطية';
    }, 2000);
}

function confirmDeleteAccount() {
    if (confirm('⚠️ متأكد عاوز تحذف حسابك نهائياً؟ مفيش رجعة بعد كده!')) {
        if (confirm('تأكيد أخير: كل بياناتك وطلباتك هتتحذف. متأكد؟')) {
            alert('تم حذف الحساب نهائياً.');
            window.location.href = '/login';
        }
    }
}
