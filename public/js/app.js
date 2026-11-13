
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


    // Helper to normalize any digits (Arabic, Persian, Western)
    function normalizeDigits(str) {
        if (!str) return '';
        var ar = '٠١٢٣٤٥٦٧٨٩۰۱۲۳۴۵۶۷۸۹';
        var en = '01234567890123456789';
        var res = '';
        for (var i = 0; i < str.length; i++) {
            var idx = ar.indexOf(str[i]);
            res += (idx !== -1) ? en[idx] : str[i];
        }
        return res.replace(/[^0-9]/g, '');
    }

    // ===========================
    // OTP Input Auto-focus & Interaction
    // ===========================
    var otpContainers = document.querySelectorAll('.otp-inputs');
    otpContainers.forEach(function (container) {
        var digits = container.querySelectorAll('.otp-digit');
        if (!digits || digits.length === 0) return;

        function updateFilledClasses() {
            digits.forEach(function (d) {
                if (d.value) {
                    d.classList.add('filled');
                } else {
                    d.classList.remove('filled');
                }
            });
        }

        digits.forEach(function (digit, index) {
            // Select content on focus/click so typing overwrites the character directly
            digit.addEventListener('focus', function () {
                this.select();
            });

            digit.addEventListener('click', function () {
                this.select();
            });

            // Handle Backspace, Delete, Arrows
            digit.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace') {
                    if (this.value) {
                        this.value = '';
                        updateFilledClasses();
                        collectOTP(container, digits);
                        e.preventDefault();
                    } else if (index > 0) {
                        digits[index - 1].focus();
                        digits[index - 1].value = '';
                        updateFilledClasses();
                        collectOTP(container, digits);
                        e.preventDefault();
                    }
                } else if (e.key === 'Delete') {
                    this.value = '';
                    updateFilledClasses();
                    collectOTP(container, digits);
                    e.preventDefault();
                } else if (e.key === 'ArrowLeft') {
                    if (index > 0) {
                        digits[index - 1].focus();
                        digits[index - 1].select();
                        e.preventDefault();
                    }
                } else if (e.key === 'ArrowRight') {
                    if (index < digits.length - 1) {
                        digits[index + 1].focus();
                        digits[index + 1].select();
                        e.preventDefault();
                    }
                }
            });

            // Handle digit input
            digit.addEventListener('input', function () {
                var clean = normalizeDigits(this.value);

                // If user typed/pasted more than 1 char directly into cell
                if (clean.length > 1) {
                    for (var k = 0; k < clean.length && (index + k) < digits.length; k++) {
                        digits[index + k].value = clean[k];
                    }
                    var targetIdx = Math.min(index + clean.length, digits.length - 1);
                    digits[targetIdx].focus();
                    digits[targetIdx].select();
                } else {
                    this.value = clean;
                    if (this.value && index < digits.length - 1) {
                        digits[index + 1].focus();
                        digits[index + 1].select();
                    }
                }

                updateFilledClasses();
                collectOTP(container, digits);
            });

            // Handle paste
            digit.addEventListener('paste', function (e) {
                e.preventDefault();
                var clipboard = (e.clipboardData || window.clipboardData);
                var raw = clipboard ? clipboard.getData('text') : '';
                var pasted = normalizeDigits(raw);

                if (!pasted) return;

                var startIdx = (pasted.length >= digits.length) ? 0 : index;
                for (var i = 0; i < digits.length; i++) {
                    if (i < startIdx) continue;
                    var charIndex = i - startIdx;
                    if (charIndex < pasted.length) {
                        digits[i].value = pasted[charIndex];
                    }
                }

                var nextIdx = Math.min(startIdx + pasted.length, digits.length - 1);
                digits[nextIdx].focus();
                digits[nextIdx].select();

                updateFilledClasses();
                collectOTP(container, digits);
            });
        });

        // Initialize state
        updateFilledClasses();
        collectOTP(container, digits);
    });

    function collectOTP(container, digits) {
        var full = '';
        digits.forEach(function (d) { 
            full += normalizeDigits(d.value); 
        });
        var hiddenField = container.parentElement ? container.parentElement.querySelector('input[type="hidden"]') : null;
        if (!hiddenField) {
            hiddenField = document.getElementById('otpFull') || document.getElementById('adminOTPFull');
        }
        if (hiddenField) {
            hiddenField.value = full;
        }
        return full;
    }


    // ===========================
    // Resend OTP Cooldown Timer
    // ===========================
    function initResendTimer(btnId, noticeId, timerId, storageKey) {
        var resendBtn = document.getElementById(btnId);
        var noticeEl = document.getElementById(noticeId);
        var timerEl = document.getElementById(timerId);

        if (!resendBtn) return;

        var key = storageKey || ('otp_resend_target_' + btnId);
        var serverSeconds = parseInt(resendBtn.getAttribute('data-resend-seconds'));
        if (isNaN(serverSeconds)) serverSeconds = 0;

        var now = Date.now();
        var targetTime = 0;

        // Check if there is already an active cooldown in sessionStorage
        var storedTarget = parseInt(sessionStorage.getItem(key));
        if (storedTarget && storedTarget > now) {
            targetTime = storedTarget;
        } else if (serverSeconds > 0) {
            targetTime = now + (serverSeconds * 1000);
            sessionStorage.setItem(key, targetTime);
        } else {
            sessionStorage.removeItem(key);
        }

        var updateTimer = function () {
            var diff = Math.ceil((targetTime - Date.now()) / 1000);
            if (diff > 0) {
                var mins = Math.floor(diff / 60);
                var secs = diff % 60;
                var formatted = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
                if (timerEl) timerEl.textContent = formatted;
                if (noticeEl) noticeEl.style.display = 'inline-block';
                resendBtn.style.display = 'none';
                resendBtn.disabled = true;
                return true;
            } else {
                sessionStorage.removeItem(key);
                if (noticeEl) noticeEl.style.display = 'none';
                resendBtn.style.display = 'inline-block';
                resendBtn.disabled = false;
                resendBtn.textContent = '🔄 ابعت تاني';
                return false;
            }
        };

        if (resendBtn._resendInterval) {
            clearInterval(resendBtn._resendInterval);
        }

        if (targetTime > Date.now()) {
            updateTimer();
            resendBtn._resendInterval = setInterval(function () {
                if (!updateTimer()) {
                    clearInterval(resendBtn._resendInterval);
                }
            }, 1000);
        } else {
            updateTimer();
        }

        // Attach click handler once
        if (!resendBtn._hasResendHandler) {
            resendBtn._hasResendHandler = true;
            resendBtn.addEventListener('click', function () {
                resendBtn.disabled = true;
                resendBtn.textContent = 'جاري الإرسال...';
                fetch('/otp/resend', { method: 'POST' })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        alert(data.message);
                        if (data.success) {
                            var cooldown = data.seconds_left || 60;
                            var newTarget = Date.now() + (cooldown * 1000);
                            sessionStorage.setItem(key, newTarget);
                            resendBtn.setAttribute('data-resend-seconds', cooldown);
                            initResendTimer(btnId, noticeId, timerId, storageKey);
                        } else {
                            resendBtn.disabled = false;
                            resendBtn.textContent = '🔄 ابعت تاني';
                        }
                    })
                    .catch(function () {
                        alert('حدث خطأ في الاتصال بالسيرفر');
                        resendBtn.disabled = false;
                        resendBtn.textContent = '🔄 ابعت تاني';
                    });
            });
        }
    }

    // Initialize resend timers for OTP pages
    initResendTimer('resendOTP', 'resendCountdownNotice', 'resendTimerCountdown', 'otp_resend_target');
    initResendTimer('adminResendOTP', 'adminResendCountdownNotice', 'adminResendTimerCountdown', 'admin_otp_resend_target');


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
        var ext = file.name ? file.name.split('.').pop().toLowerCase() : '';
        var allowedExts = ['pdf', 'doc', 'docx'];
        var allowedMimes = ['application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/x-pdf', 'application/octet-stream'];

        if (allowedExts.indexOf(ext) === -1 && allowedMimes.indexOf(file.type) === -1) {
            alert('الملف لازم يكون PDF أو Word (.pdf, .doc, .docx)');
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
        if (submitApplicationBtn) {
            var coverInput = document.getElementById('coverLetter');
            var hasCover = coverInput ? coverInput.value.trim().length > 0 : true;
            submitApplicationBtn.disabled = !(hasFile && hasCover);
        }
        if (requestEnhBtn) requestEnhBtn.disabled = !hasFile;
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    var jobTitle = document.getElementById('jobTitle');
    if (jobTitle) jobTitle.addEventListener('change', updateButtons);

    var coverLetterInput = document.getElementById('coverLetter');
    if (coverLetterInput) {
        coverLetterInput.addEventListener('input', function () {
            var errBox = document.getElementById('applyError');
            if (errBox) errBox.style.display = 'none';
            updateButtons();
        });
    }


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
        var phoneInput = document.getElementById('phone');
        if (phoneInput) {
            // Strip any non-digit characters and enforce max 11 digits in real-time
            phoneInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 11) {
                    this.value = this.value.slice(0, 11);
                }
            });
        }

        registerForm.addEventListener('submit', function (e) {
            var phone = phoneInput ? phoneInput.value.trim() : '';
            var phoneRegex = /^01[0125][0-9]{8}$/;

            if (!phone) {
                e.preventDefault();
                alert('برجاء إدخال رقم الموبايل!');
                if (phoneInput) phoneInput.focus();
                return false;
            }

            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('رقم الموبايل غير صحيح! يجب أن يتكون من 11 رقماً ويبدأ بـ 010 أو 011 أو 012 أو 015');
                if (phoneInput) phoneInput.focus();
                return false;
            }

            var passInput = document.getElementById('password');
            var password = passInput ? passInput.value : '';
            var confirmInput = document.getElementById('password_confirm') || document.querySelector('input[name="confirm_password"]');
            var confirm = confirmInput ? confirmInput.value : '';

            if (password.length < 6 || password.length > 30) {
                e.preventDefault();
                alert('كلمة المرور يجب أن تكون بين 6 و 30 حرفاً!');
                if (passInput) passInput.focus();
                return false;
            }

            if (password !== confirm) {
                e.preventDefault();
                alert('كلمة المرور وتأكيد كلمة المرور غير متطابقين!');
                if (confirmInput) confirmInput.focus();
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
            var otpVal = '';
            if (container) {
                var digits = container.querySelectorAll('.otp-digit');
                otpVal = collectOTP(container, digits);
            }
            if (!otpVal) {
                var hidden = document.getElementById('otpFull');
                otpVal = hidden ? hidden.value.trim() : '';
            }

            if (otpVal.length < 6) {
                e.preventDefault();
                var err = document.getElementById('otpError');
                if (err) {
                    err.style.display = 'flex';
                    var errText = document.getElementById('otpErrorText');
                    if (errText) errText.textContent = 'ادخل الكود كامل (6 أرقام)';
                }
                if (container) {
                    var digits = container.querySelectorAll('.otp-digit');
                    for (var i = 0; i < digits.length; i++) {
                        if (!digits[i].value) {
                            digits[i].focus();
                            break;
                        }
                    }
                }
                return false;
            }

            var err = document.getElementById('otpError');
            if (err) err.style.display = 'none';

            var btn = document.getElementById('verifyBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner" style="display:inline-block;width:14px;height:14px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span> جاري التحقق...';
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

            var coverInput = document.getElementById('coverLetter');
            var coverVal = coverInput ? coverInput.value.trim() : '';
            if (!coverVal) {
                var errBox = document.getElementById('applyError');
                var errText = document.getElementById('applyErrorText');
                if (errBox && errText) {
                    errText.textContent = 'لازم توضح ليه الوظيفة دي مناسبة ليك';
                    errBox.style.display = 'flex';
                } else {
                    alert('لازم توضح ليه الوظيفة دي مناسبة ليك');
                }
                if (coverInput) coverInput.focus();
                return;
            }

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
                .then(function (res) {
                    return res.text().then(function (text) {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Server response:', text);
                            throw new Error('فشل معالجة رد السيرفر');
                        }
                    });
                })
                .then(function (data) {
                    if (data.success) {
                        var modal = document.getElementById('successModal');
                        if (modal) modal.style.display = 'flex';
                    } else {
                        var errBox = document.getElementById('applyError');
                        var errText = document.getElementById('applyErrorText');
                        if (errBox && errText) {
                            errText.textContent = data.message || 'حدث خطأ أثناء التقديم';
                            errBox.style.display = 'flex';
                        } else {
                            alert(data.message || 'حدث خطأ');
                        }
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = 'قدّم الطلب';
                        }
                    }
                })
                .catch(function (err) {
                    console.error('Apply error:', err);
                    var errBox = document.getElementById('applyError');
                    var errText = document.getElementById('applyErrorText');
                    if (errBox && errText) {
                        errText.textContent = 'حدث خطأ في الاتصال بالسيرفر، برجاء المحاولة لاحقاً';
                        errBox.style.display = 'flex';
                    } else {
                        alert('حدث خطأ في الاتصال بالسيرفر');
                    }
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = 'قدّم الطلب';
                    }
                });
        });
    }


    // ===========================
    // Post Job Form
    // ===========================
    var postJobForm = document.getElementById('postJobForm');
    if (postJobForm) {
        var expiryInput = document.getElementById('expiryDate');
        if (expiryInput) {
            var nowStr = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            expiryInput.min = nowStr;
        }

        postJobForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var expiryEl = document.getElementById('expiryDate');
            if (expiryEl && expiryEl.value) {
                var expDate = new Date(expiryEl.value);
                if (expDate <= new Date()) {
                    var errBox = document.getElementById('postJobError');
                    var errText = document.getElementById('postJobErrorText');
                    if (errBox && errText) {
                        errText.textContent = 'تاريخ وساعة انتهاء التقديم لازم يكون في وقت مستقبلي وغير منتهي!';
                        errBox.style.display = 'flex';
                    } else {
                        alert('تاريخ وساعة انتهاء التقديم لازم يكون في وقت مستقبلي وغير منتهي!');
                    }
                    expiryEl.focus();
                    return;
                }
            }

            var salMinEl = document.getElementById('salaryMin');
            var salMaxEl = document.getElementById('salaryMax');
            var salMin = (salMinEl && salMinEl.value !== '') ? parseFloat(salMinEl.value) : null;
            var salMax = (salMaxEl && salMaxEl.value !== '') ? parseFloat(salMaxEl.value) : null;

            if (salMin !== null && salMin < 0) {
                alert('الحد الأدنى للمرتب لا يمكن أن يكون بالسالب');
                salMinEl.focus();
                return;
            }

            if (salMax !== null && salMax < 0) {
                alert('الحد الأقصى للمرتب لا يمكن أن يكون بالسالب');
                salMaxEl.focus();
                return;
            }

            if (salMin !== null && salMax !== null && salMin > salMax) {
                alert('الحد الأدنى للمرتب لا يمكن أن يكون أكبر من الحد الأقصى');
                salMinEl.focus();
                return;
            }

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
        function syncLocationFields(val) {
            var locationGroup = document.getElementById('locationGroup');
            var meetingLinkGroup = document.getElementById('meetingLinkGroup');
            var locInput = document.getElementById('location');
            var linkInput = document.getElementById('meetingLink');
            var linkLabel = document.getElementById('meetingLinkLabel');
            var locLabel = document.getElementById('locationLabel');

            if (val === 'online') {
                if (locationGroup) locationGroup.style.display = 'none';
                if (meetingLinkGroup) meetingLinkGroup.style.display = 'block';
                if (locInput) {
                    locInput.removeAttribute('required');
                    locInput.value = '';
                }
                if (linkInput) linkInput.setAttribute('required', 'required');
                if (linkLabel) linkLabel.innerHTML = 'رابط المقابلة / الاجتماع أونلاين *';
                if (locLabel) locLabel.innerHTML = 'العنوان / مقر المقابلة';
            } else if (val === 'hybrid') {
                if (locationGroup) locationGroup.style.display = 'block';
                if (meetingLinkGroup) meetingLinkGroup.style.display = 'block';
                if (locInput) locInput.setAttribute('required', 'required');
                if (linkInput) linkInput.setAttribute('required', 'required');
                if (linkLabel) linkLabel.innerHTML = 'رابط المقابلة / الاجتماع أونلاين *';
                if (locLabel) locLabel.innerHTML = 'العنوان / مقر المقابلة *';
            } else { // onsite
                if (locationGroup) locationGroup.style.display = 'block';
                if (meetingLinkGroup) meetingLinkGroup.style.display = 'none';
                if (locInput) locInput.setAttribute('required', 'required');
                if (linkInput) {
                    linkInput.removeAttribute('required');
                    linkInput.value = '';
                }
                if (linkLabel) linkLabel.innerHTML = 'رابط المقابلة / الاجتماع أونلاين';
                if (locLabel) locLabel.innerHTML = 'العنوان / مقر المقابلة *';
            }
        }

        if (locationType) {
            locationType.addEventListener('change', function () {
                syncLocationFields(this.value);
            });
            // Initial sync on page load
            if (locationType.value) {
                syncLocationFields(locationType.value);
            }
        }

        var dtInput = document.getElementById('interviewDateTime');
        if (dtInput) {
            var nowStr = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            dtInput.min = nowStr;
        }

        interviewForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var dtEl = document.getElementById('interviewDateTime');
            var dtVal = dtEl ? dtEl.value : '';
            if (!dtVal) {
                var dEl = document.getElementById('interviewDate');
                var tEl = document.getElementById('interviewTime');
                if (dEl && tEl && dEl.value && tEl.value) {
                    dtVal = dEl.value + 'T' + tEl.value;
                }
            }

            if (dtVal) {
                var ivDate = new Date(dtVal);
                if (ivDate <= new Date()) {
                    alert('موعد المقابلة يجب أن يكون في تاريخ وساعة مستقبلية ولا يمكن اختيار موعد قد مضى!');
                    if (dtEl) dtEl.focus();
                    return;
                }
            }

            var locTypeVal = locationType ? locationType.value : 'onsite';
            var linkEl = document.getElementById('meetingLink');
            var linkVal = linkEl ? linkEl.value.trim() : '';
            var locEl = document.getElementById('location');
            var locVal = locEl ? locEl.value.trim() : '';

            if ((locTypeVal === 'online' || locTypeVal === 'hybrid') && !linkVal) {
                alert('رابط المقابلة أونلاين مطلوب (يرجى إدخال رابط الاجتماع مثل Google Meet أو Zoom) ولا يمكن تركه فارغاً!');
                if (linkEl) linkEl.focus();
                return;
            }

            if ((locTypeVal === 'onsite' || locTypeVal === 'hybrid') && !locVal) {
                alert('مكان وعنوان المقابلة مطلوب ولا يمكن تركه فارغاً!');
                if (locEl) locEl.focus();
                return;
            }

            var btn = document.getElementById('scheduleBtn');
            var originalBtnText = btn ? (btn.getAttribute('data-original-text') || btn.innerHTML) : 'حفظ';
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
                        if (modal) {
                            var titleEl = document.getElementById('successModalTitle');
                            var descEl = document.getElementById('successModalDesc');
                            if (titleEl) {
                                titleEl.textContent = data.is_edit ? 'تم تحديث المقابلة بنجاح!' : 'تم تحديد المقابلة!';
                            }
                            if (descEl) {
                                descEl.textContent = data.is_edit
                                    ? 'تم حفظ التعديلات وإرسال بريد إلكتروني فوري للمتقدم بالتفاصيل الجديدة.'
                                    : 'تم حفظ الموعد وإرسال تفاصيل المقابلة للمتقدم عبر البريد الإلكتروني.';
                            }
                            modal.style.display = 'flex';
                        }
                    } else {
                        alert(data.message || 'حدث خطأ أثناء تحديد المقابلة');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = originalBtnText;
                        }
                    }
                })
                .catch(function () {
                    alert('حدث خطأ في الاتصال بالسيرفر');
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalBtnText;
                    }
                });
        });
    }


    // ===========================
    // Job Offer Live Countdown Timers
    // ===========================
    // Job Countdown Timers & Dynamic Status Timing
    // ===========================
    function formatTimeLeft(diffMs) {
        if (diffMs <= 0) return null;
        var totalSeconds = Math.floor(diffMs / 1000);
        var days = Math.floor(totalSeconds / 86400);
        var hours = Math.floor((totalSeconds % 86400) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;

        var parts = [];
        if (days > 0) parts.push(days + ' يوم');
        if (hours > 0) parts.push(hours + ' ساعة');
        if (minutes > 0) parts.push(minutes + ' دقيقة');
        if (days === 0 && hours === 0) {
            parts.push(seconds + ' ثانية');
        }

        if (parts.length === 0) {
            return seconds + ' ثانية';
        }
        return parts.join(' و ');
    }

    function initJobTimers() {
        var timerEls = document.querySelectorAll('.job-countdown');
        if (!timerEls || timerEls.length === 0) return;

        function updateAllTimers() {
            var now = new Date().getTime();
            document.querySelectorAll('.job-countdown').forEach(function (el) {
                var targetStr = el.getAttribute('data-expires');
                if (!targetStr) return;
                var target = new Date(targetStr).getTime();
                var diff = target - now;

                var textEl = el.querySelector('.countdown-text');
                if (diff <= 0) {
                    el.classList.remove('active');
                    el.classList.add('expired');
                    if (textEl) {
                        textEl.textContent = 'انتهى التقديم';
                    } else {
                        el.textContent = '⛔ انتهى وقت التقديم';
                    }

                    // 1. If on Apply page: disable submit button
                    var applyBtn = document.getElementById('submitApplicationBtn');
                    if (applyBtn) {
                        applyBtn.disabled = true;
                        applyBtn.textContent = '⛔ انتهى وقت التقديم';
                    }

                    // 2. If on Public Jobs Listing (/jobs): smoothly fade out and hide the expired card
                    var publicCard = el.closest('.job-card');
                    if (publicCard && window.location.pathname.indexOf('/jobs') !== -1) {
                        publicCard.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                        publicCard.style.opacity = '0';
                        publicCard.style.transform = 'scale(0.96)';
                        setTimeout(function () {
                            if (publicCard) publicCard.style.display = 'none';
                        }, 600);
                    }

                    // 3. If on Employer Dashboard: dynamically switch status pill & button to "إعادة فتح"
                    var employerJob = el.closest('.employer-job-item');
                    if (employerJob) {
                        var statusPill = employerJob.querySelector('.job-status-pill');
                        if (statusPill && statusPill.textContent !== '⛔ منتهية الصلاحية') {
                            statusPill.style.background = 'rgba(239, 68, 68, 0.15)';
                            statusPill.style.color = '#ef4444';
                            statusPill.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                            statusPill.textContent = '⛔ منتهية الصلاحية';
                        }

                        var toggleBtn = employerJob.querySelector('.toggle-job-btn');
                        if (toggleBtn && toggleBtn.getAttribute('data-expired') !== '1') {
                            var jobIdMatch = employerJob.id.match(/\d+/);
                            var jId = jobIdMatch ? jobIdMatch[0] : null;
                            var jTitle = employerJob.querySelector('h4') ? employerJob.querySelector('h4').textContent.trim() : '';

                            toggleBtn.setAttribute('data-active', '0');
                            toggleBtn.setAttribute('data-expired', '1');
                            toggleBtn.className = 'btn btn-primary btn-sm toggle-job-btn';
                            toggleBtn.style.color = '';
                            toggleBtn.style.borderColor = '';
                            toggleBtn.innerHTML = '🔄 إعادة فتح التقديم';
                            toggleBtn.title = 'تحديد موعد جديد وإعادة فتح التقديم';
                            toggleBtn.onclick = function () {
                                if (typeof openReopenModal === 'function') {
                                    openReopenModal(jId, jTitle, '', this);
                                }
                            };
                        }
                    }
                } else {
                    el.classList.remove('expired');
                    el.classList.add('active');
                    var formatted = formatTimeLeft(diff);
                    if (textEl) {
                        textEl.textContent = formatted;
                    } else {
                        el.textContent = '⏳ متبقي: ' + formatted;
                    }
                }
            });
        }

        updateAllTimers();
        if (!window.__jobTimerInterval) {
            window.__jobTimerInterval = setInterval(updateAllTimers, 1000);
        }
    }

    window.formatTimeLeft = formatTimeLeft;
    window.initJobTimers = initJobTimers;
    initJobTimers();


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

    // Profile Form Bio Character Counter
    var profileBio = document.getElementById('profileBio');
    var bioCharCount = document.getElementById('bioCharCount');
    if (profileBio && bioCharCount) {
        var updateBioCharCount = function () {
            var len = profileBio.value.length;
            bioCharCount.textContent = len + ' / 500 حرف';
            if (len > 500) {
                bioCharCount.style.color = '#ef4444';
            } else {
                bioCharCount.style.color = 'var(--text-muted)';
            }
        };
        profileBio.addEventListener('input', updateBioCharCount);
        updateBioCharCount();
    }

    // Profile Form
    var profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Client-side validations for Job Seeker settings
            var isUser = window.location.pathname.indexOf('/settings') !== -1 && window.location.pathname.indexOf('/employer') === -1 && window.location.pathname.indexOf('/admin') === -1;
            if (isUser) {
                var expInput = document.getElementById('profileExp');
                if (expInput && parseInt(expInput.value, 10) < 0) {
                    alert('سنوات الخبرة لا يمكن أن تكون بالسالب');
                    expInput.focus();
                    return;
                }

                if (profileBio && profileBio.value.length > 500) {
                    alert('نبذة عنك يجب ألا تتجاوز 500 حرف');
                    profileBio.focus();
                    return;
                }

                var linkedinInput = document.getElementById('profileLinkedin');
                if (linkedinInput && !linkedinInput.value.trim()) {
                    alert('رابط حساب LinkedIn مطلوب وإجباري');
                    linkedinInput.focus();
                    return;
                }

                var githubInput = document.getElementById('profileGithub');
                if (githubInput && !githubInput.value.trim()) {
                    alert('رابط حساب GitHub مطلوب وإجباري');
                    githubInput.focus();
                    return;
                }
            }

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
                    if (data.success && (data.pending || data.name_pending)) {
                        setTimeout(function () {
                            location.reload();
                        }, 1200);
                    }
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
                btn.innerHTML = '<span class="spinner"></span> جاري الإرسال...';
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
                        if (document.getElementById('newEmail')) document.getElementById('newEmail').value = '';
                        if (document.getElementById('emailPassword')) document.getElementById('emailPassword').value = '';
                        if (data.email_pending) {
                            setTimeout(function () {
                                location.reload();
                            }, 1200);
                        }
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

    // ===========================
    // Avatar / Logo Upload
    // ===========================
    var avatarPreview = document.getElementById('avatarPreview');
    var avatarFileInput = document.getElementById('avatarFileInput');
    var deleteAvatarBtn = document.getElementById('deleteAvatarBtn');
    var avatarUploadStatus = document.getElementById('avatarUploadStatus');

    if (avatarPreview && avatarFileInput) {
        var isEmployerPage = window.location.pathname.indexOf('/employer') !== -1;
        var uploadUrl = isEmployerPage ? '/employer/settings/logo' : '/settings/avatar';
        var deleteUrl = isEmployerPage ? '/employer/settings/logo/delete' : '/settings/avatar/delete';

        avatarPreview.addEventListener('click', function () {
            avatarFileInput.click();
        });

        avatarFileInput.addEventListener('change', function () {
            if (!avatarFileInput.files || avatarFileInput.files.length === 0) return;
            var file = avatarFileInput.files[0];

            // Validate type
            var allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (allowedTypes.indexOf(file.type) === -1) {
                alert('نوع الملف مش مدعوم! ارفع صورة JPG أو PNG أو WebP أو GIF');
                avatarFileInput.value = '';
                return;
            }

            // Validate size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('الصورة كبيرة أوي! أقصى حجم 5MB');
                avatarFileInput.value = '';
                return;
            }

            // Show live preview
            var reader = new FileReader();
            reader.onload = function (e) {
                var existingImg = avatarPreview.querySelector('img');
                var existingInitial = avatarPreview.querySelector('.avatar-initial');
                if (existingInitial) existingInitial.style.display = 'none';
                if (existingImg) {
                    existingImg.src = e.target.result;
                } else {
                    var img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = '';
                    img.id = 'avatarImg';
                    avatarPreview.insertBefore(img, avatarPreview.querySelector('.avatar-overlay'));
                }
            };
            reader.readAsDataURL(file);

            // Upload
            showAvatarStatus('جاري رفع الصورة... <span class="avatar-upload-spinner"></span>', 'info');

            var formData = new FormData();
            formData.append('avatar', file);

            fetch(uploadUrl, {
                method: 'POST',
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        showAvatarStatus('✅ ' + (data.message || 'تم رفع الصورة بنجاح!'), 'success');
                        // Update all navbar avatars on page
                        var allNavAvatars = document.querySelectorAll('.user-avatar');
                        allNavAvatars.forEach(function (av) {
                            if (av.id === 'avatarPreview') return; // skip the upload widget
                            var existingImg = av.querySelector('img');
                            if (existingImg) {
                                existingImg.src = data.path + '?t=' + Date.now();
                            } else {
                                av.textContent = '';
                                var img = document.createElement('img');
                                img.src = data.path + '?t=' + Date.now();
                                img.alt = '';
                                av.appendChild(img);
                            }
                        });
                        // Show delete button if not present
                        if (!deleteAvatarBtn) {
                            var infoDiv = document.querySelector('.avatar-upload-info');
                            if (infoDiv) {
                                var btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'btn btn-danger btn-sm mt-1';
                                btn.id = 'deleteAvatarBtn';
                                btn.style.fontSize = '0.8rem';
                                btn.textContent = isEmployerPage ? '🗑️ حذف الشعار' : '🗑️ حذف الصورة';
                                btn.addEventListener('click', handleDeleteAvatar);
                                infoDiv.appendChild(btn);
                                deleteAvatarBtn = btn;
                            }
                        }
                        setTimeout(function () { hideAvatarStatus(); }, 3000);
                    } else {
                        showAvatarStatus('❌ ' + (data.message || 'حدث خطأ أثناء رفع الصورة'), 'error');
                    }
                    avatarFileInput.value = '';
                })
                .catch(function () {
                    showAvatarStatus('❌ حدث خطأ في الاتصال بالسيرفر', 'error');
                    avatarFileInput.value = '';
                });
        });

        function handleDeleteAvatar() {
            var confirmMsg = isEmployerPage ? 'متأكد عاوز تحذف شعار الشركة؟' : 'متأكد عاوز تحذف صورتك الشخصية؟';
            if (!confirm(confirmMsg)) return;

            showAvatarStatus('جاري الحذف... <span class="avatar-upload-spinner"></span>', 'info');

            fetch(deleteUrl, { method: 'POST' })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        showAvatarStatus('✅ ' + (data.message || 'تم حذف الصورة بنجاح'), 'success');
                        // Reset preview to initial letter
                        var img = avatarPreview.querySelector('img');
                        if (img) img.remove();
                        var initial = avatarPreview.querySelector('.avatar-initial');
                        if (initial) {
                            initial.style.display = '';
                        } else {
                            var sp = document.createElement('span');
                            sp.className = 'avatar-initial';
                            sp.id = 'avatarInitial';
                            var nameEl = document.getElementById(isEmployerPage ? 'companyName' : 'profileName');
                            sp.textContent = nameEl && nameEl.value ? nameEl.value.charAt(0) : 'ع';
                            avatarPreview.insertBefore(sp, avatarPreview.querySelector('.avatar-overlay'));
                        }
                        // Reset navbar avatars
                        var allNavAvatars = document.querySelectorAll('.user-avatar');
                        allNavAvatars.forEach(function (av) {
                            if (av.id === 'avatarPreview') return;
                            var existingImg = av.querySelector('img');
                            if (existingImg) existingImg.remove();
                            if (!av.textContent.trim()) {
                                var nameEl = document.getElementById(isEmployerPage ? 'companyName' : 'profileName');
                                av.textContent = nameEl && nameEl.value ? nameEl.value.charAt(0) : 'ع';
                            }
                        });
                        // Remove delete button
                        if (deleteAvatarBtn) {
                            deleteAvatarBtn.remove();
                            deleteAvatarBtn = null;
                        }
                        setTimeout(function () { hideAvatarStatus(); }, 3000);
                    } else {
                        showAvatarStatus('❌ ' + (data.message || 'حدث خطأ أثناء الحذف'), 'error');
                    }
                })
                .catch(function () {
                    showAvatarStatus('❌ حدث خطأ في الاتصال بالسيرفر', 'error');
                });
        }

        if (deleteAvatarBtn) {
            deleteAvatarBtn.addEventListener('click', handleDeleteAvatar);
        }

        function showAvatarStatus(html, type) {
            if (!avatarUploadStatus) return;
            var bgColors = { info: 'var(--info-light)', success: 'var(--success-light)', error: 'var(--danger-light)' };
            var textColors = { info: 'var(--info)', success: 'var(--success)', error: 'var(--danger)' };
            avatarUploadStatus.innerHTML = html;
            avatarUploadStatus.style.display = 'block';
            avatarUploadStatus.style.background = bgColors[type] || bgColors.info;
            avatarUploadStatus.style.color = textColors[type] || textColors.info;
            avatarUploadStatus.style.padding = '8px 14px';
            avatarUploadStatus.style.borderRadius = '8px';
            avatarUploadStatus.style.fontSize = '0.85rem';
            avatarUploadStatus.style.fontWeight = '600';
        }

        function hideAvatarStatus() {
            if (avatarUploadStatus) avatarUploadStatus.style.display = 'none';
        }
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
            if (newPass.length < 6 || newPass.length > 30) { alert('كلمة المرور لازم تكون بين 6 و 30 حرفاً'); return; }
            if (newPass !== confirmPass) { alert('كلمة المرور الجديدة مش متطابقة!'); return; }
            if (newPass === currentPass) { alert('كلمة المرور الجديدة يجب أن تكون مختلفة عن كلمة المرور الحالية'); return; }

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
        var reasonInput = document.getElementById('banReason');
        if (reasonInput) reasonInput.value = '';

        var confirmBtn = document.getElementById('confirmBanBtn');
        if (confirmBtn) {
            confirmBtn.onclick = function () {
                var reason = reasonInput ? reasonInput.value : '';
                var origText = confirmBtn.innerHTML;
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner"></span> جاري الحظر...';

                var formData = new FormData();
                formData.append('user_id', userId);
                formData.append('reason', reason);

                fetch('/admin/users/ban', {
                    method: 'POST',
                    body: formData
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = origText;
                        closeModal('banModal');

                        if (data.success) {
                            var row = btn ? btn.closest('tr') : null;
                            if (row) {
                                row.classList.add('banned-row');
                                row.setAttribute('data-status', 'banned');

                                var badge = row.querySelector('.status-badge');
                                if (badge) {
                                    badge.className = 'status-badge status-badge-banned';
                                    badge.textContent = '🚫 محظور';
                                }
                                var dot = row.querySelector('.status-dot');
                                if (dot) {
                                    dot.className = 'status-dot banned';
                                    if (dot.nextSibling) dot.nextSibling.textContent = ' محظور';
                                }
                            }
                            if (btn) {
                                btn.className = 'btn btn-success btn-sm';
                                btn.textContent = '✅ إلغاء الحظر';
                                btn.setAttribute('onclick', 'unbanUser(' + userId + ', this)');
                            }
                        } else {
                            alert(data.message || 'حدث خطأ أثناء حظر المستخدم');
                        }
                    })
                    .catch(function (err) {
                        confirmBtn.disabled = false;
                        confirmBtn.innerHTML = origText;
                        console.error('Ban error:', err);
                        alert('تعذر الاتصال بالسيرفر');
                    });
            };
        }
    }
}

function unbanUser(userId, btn) {
    if (confirm('متأكد عاوز تلغي الحظر؟')) {
        var origText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> جاري الإلغاء...';
        }

        var formData = new FormData();
        formData.append('user_id', userId);

        fetch('/admin/users/unban', {
            method: 'POST',
            body: formData
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }

                if (data.success) {
                    var row = btn ? btn.closest('tr') : null;
                    if (row) {
                        row.classList.remove('banned-row');
                        row.setAttribute('data-status', 'approved');

                        var badge = row.querySelector('.status-badge');
                        if (badge) {
                            badge.className = 'status-badge status-badge-approved';
                            badge.textContent = '✅ معتمد ونشط';
                        }
                        var dot = row.querySelector('.status-dot');
                        if (dot) {
                            dot.className = 'status-dot active';
                            if (dot.nextSibling) dot.nextSibling.textContent = ' نشط';
                        }
                    }
                    if (btn) {
                        btn.className = 'btn btn-danger btn-sm';
                        btn.textContent = '🚫 حظر';
                        btn.setAttribute('onclick', 'banUser(' + userId + ', this)');
                    }
                } else {
                    alert(data.message || 'حدث خطأ أثناء إلغاء الحظر');
                }
            })
            .catch(function (err) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origText;
                }
                console.error('Unban error:', err);
                alert('تعذر الاتصال بالسيرفر');
            });
    }
}

function approveUser(userId, btn) {
    if (confirm('هل أنت متأكد من الموافقة على تفعيل هذا الحساب؟ سيتم إرسال بريد إلكتروني للمستخدم فوراً.')) {
        var origText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> جاري التفعيل والإرسال...';
        btn.disabled = true;

        var formData = new FormData();
        formData.append('user_id', userId);

        fetch('/admin/users/approve', {
            method: 'POST',
            body: formData
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.innerHTML = origText;
                btn.disabled = false;
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message || 'حدث خطأ في الموافقة على الحساب');
                }
            })
            .catch(function () {
                btn.innerHTML = origText;
                btn.disabled = false;
                alert('تعذر الاتصال بالسيرفر');
            });
    }
}

function openDisapproveModal(userId, userName, userEmail) {
    var modal = document.getElementById('disapproveModal');
    if (modal) {
        document.getElementById('disapproveUserId').value = userId;
        var desc = document.getElementById('disapproveUserDesc');
        if (desc) {
            desc.textContent = 'سيتم رفض حساب "' + userName + '" (' + (userEmail || '') + ') وإرسال بريد إلكتروني بالقرار.';
        }
        var reason = document.getElementById('disapproveReason');
        if (reason) reason.value = '';
        modal.style.display = 'flex';
    }
}

function confirmDisapprove() {
    var userId = document.getElementById('disapproveUserId').value;
    var reason = document.getElementById('disapproveReason') ? document.getElementById('disapproveReason').value : '';
    var btn = document.getElementById('confirmDisapproveBtn');

    if (!userId) return;

    var origText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> جاري الرفض وإرسال الإيميل...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('user_id', userId);
    formData.append('reason', reason);

    fetch('/admin/users/disapprove', {
        method: 'POST',
        body: formData
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.innerHTML = origText;
            btn.disabled = false;
            closeModal('disapproveModal');
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message || 'حدث خطأ أثناء رفض الحساب');
            }
        })
        .catch(function () {
            btn.innerHTML = origText;
            btn.disabled = false;
            alert('تعذر الاتصال بالسيرفر');
        });
}

function approveProfileRequest(requestId, btn) {
    if (!confirm('هل أنت متأكد من قبول تعديل البيانات وتحديث الحساب فوراً؟ سيتم إرسال بريد إلكتروني للمستخدم.')) {
        return;
    }
    var origText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> جاري القبول...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('request_id', requestId);

    fetch('/admin/profile-requests/approve', {
        method: 'POST',
        body: formData
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.innerHTML = origText;
            btn.disabled = false;
            if (data.success) {
                alert(data.message);
                var row = document.getElementById('profile-req-row-' + requestId);
                if (row) row.remove();
                window.location.reload();
            } else {
                alert(data.message || 'حدث خطأ أثناء معالجة الطلب');
            }
        })
        .catch(function () {
            btn.innerHTML = origText;
            btn.disabled = false;
            alert('تعذر الاتصال بالسيرفر');
        });
}

function rejectProfileRequest(requestId, btn) {
    var reason = prompt('هل أنت متأكد من رفض طلب التعديل؟ يمكنك كتابة سبب الرفض (اختياري) لإرساله للمستخدم:');
    if (reason === null) return;

    var origText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner"></span> جاري الرفض...';
    btn.disabled = true;

    var formData = new FormData();
    formData.append('request_id', requestId);
    formData.append('reason', reason);

    fetch('/admin/profile-requests/reject', {
        method: 'POST',
        body: formData
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.innerHTML = origText;
            btn.disabled = false;
            if (data.success) {
                alert(data.message);
                var row = document.getElementById('profile-req-row-' + requestId);
                if (row) row.remove();
                window.location.reload();
            } else {
                alert(data.message || 'حدث خطأ أثناء رفض الطلب');
            }
        })
        .catch(function () {
            btn.innerHTML = origText;
            btn.disabled = false;
            alert('تعذر الاتصال بالسيرفر');
        });
}


function openAdminActivateModal(jobId, jobTitle, requestedExpiry, btn) {
    var modal = document.getElementById('adminActivateJobModal');
    if (!modal) {
        if (confirm('تأكيد تفعيل وظيفة "' + (jobTitle || '#' + jobId) + '"؟')) {
            submitAdminDirectActivate(jobId, requestedExpiry, btn);
        }
        return;
    }
    var idInput = document.getElementById('adminActivateTargetId');
    if (idInput) idInput.value = jobId;
    var titleEl = document.getElementById('adminActivateTargetTitle');
    if (titleEl) titleEl.textContent = 'الوظيفة: ' + (jobTitle || '#' + jobId);

    var expiryInput = document.getElementById('adminActivateExpiryInput');
    if (expiryInput) {
        if (requestedExpiry) {
            expiryInput.value = requestedExpiry;
        } else {
            // Default 7 days from now
            var d = new Date();
            d.setDate(d.getDate() + 7);
            var yr = d.getFullYear();
            var mo = String(d.getMonth() + 1).padStart(2, '0');
            var day = String(d.getDate()).padStart(2, '0');
            var hr = String(d.getHours()).padStart(2, '0');
            var mn = String(d.getMinutes()).padStart(2, '0');
            expiryInput.value = yr + '-' + mo + '-' + day + 'T' + hr + ':' + mn;
        }
    }
    modal.style.display = 'flex';
}

function closeAdminActivateModal() {
    var modal = document.getElementById('adminActivateJobModal');
    if (modal) modal.style.display = 'none';
}

function setAdminActivateQuickDuration(amount, unit) {
    var now = new Date();
    if (unit === 'minutes') {
        now.setMinutes(now.getMinutes() + amount);
    } else if (unit === 'hours') {
        now.setHours(now.getHours() + amount);
    } else if (unit === 'days') {
        now.setDate(now.getDate() + amount);
    }
    var year = now.getFullYear();
    var month = String(now.getMonth() + 1).padStart(2, '0');
    var day = String(now.getDate()).padStart(2, '0');
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');
    var formatted = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    var input = document.getElementById('adminActivateExpiryInput');
    if (input) {
        input.value = formatted;
        input.classList.add('highlight-pulse');
        setTimeout(function () { input.classList.remove('highlight-pulse'); }, 800);
    }
}

function submitAdminActivateJob() {
    var idInput = document.getElementById('adminActivateTargetId');
    var jobId = idInput ? idInput.value : null;
    if (!jobId) return;

    var expiryInput = document.getElementById('adminActivateExpiryInput');
    var expiresAt = expiryInput ? expiryInput.value.trim() : '';

    var btn = document.getElementById('confirmAdminActivateBtn');
    var origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner" style="display:inline-block;width:12px;height:12px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span> جاري التفعيل...';
    }

    var formData = new FormData();
    formData.append('job_id', jobId);
    formData.append('action', 'activate');
    if (expiresAt) {
        formData.append('expires_at', expiresAt);
    }

    fetch('/admin/jobs/toggle', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            if (data.success) {
                closeAdminActivateModal();
                var row = document.getElementById('admin-job-row-' + jobId);
                if (row) {
                    row.className = '';
                    row.setAttribute('data-status-cat', 'active');
                    var statusCell = row.querySelector('.job-status-cell');
                    if (statusCell) {
                        statusCell.innerHTML = '<span class="status-dot active"></span> <span style="color: var(--success); font-weight: 600;">نشطة ومتاحة</span>';
                    }
                    if (data.expires_formatted) {
                        var expCell = row.querySelector('.job-expiry-cell');
                        if (expCell) {
                            expCell.innerHTML = '<span style="font-size: 0.85rem;">' + data.expires_formatted + '</span>';
                        }
                    }
                    var actCell = row.querySelector('.job-actions-cell');
                    if (actCell) {
                        var title = row.getAttribute('data-title') || '';
                        var safeTitle = title.replace(/'/g, "\\'");
                        actCell.innerHTML = '<div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">' +
                            '<button type="button" class="btn btn-danger btn-sm action-btn" onclick="openAdminRemoveModal(' + jobId + ', \'' + safeTitle + '\', this)">🗑️ تعطيل</button>' +
                            '</div>';
                    }
                }
                if (typeof filterAdminJobsTable === 'function') filterAdminJobsTable();
                alert(data.message || 'تم تفعيل الوظيفة بنجاح');
            } else {
                alert(data.message || 'حدث خطأ أثناء تفعيل الوظيفة');
            }
        })
        .catch(function (err) {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            console.error('Job activate error:', err);
            alert('تعذر الاتصال بالسيرفر');
        });
}

function submitAdminDirectActivate(jobId, expiresAt, btn) {
    var formData = new FormData();
    formData.append('job_id', jobId);
    formData.append('action', 'activate');
    if (expiresAt) formData.append('expires_at', expiresAt);

    fetch('/admin/jobs/toggle', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var row = document.getElementById('admin-job-row-' + jobId);
                if (row) {
                    row.className = '';
                    row.setAttribute('data-status-cat', 'active');
                    var statusCell = row.querySelector('.job-status-cell');
                    if (statusCell) statusCell.innerHTML = '<span class="status-dot active"></span> <span style="color: var(--success); font-weight: 600;">نشطة ومتاحة</span>';
                    if (data.expires_formatted) {
                        var expCell = row.querySelector('.job-expiry-cell');
                        if (expCell) expCell.innerHTML = '<span style="font-size: 0.85rem;">' + data.expires_formatted + '</span>';
                    }
                    var actCell = row.querySelector('.job-actions-cell');
                    if (actCell) {
                        var title = row.getAttribute('data-title') || '';
                        var safeTitle = title.replace(/'/g, "\\'");
                        actCell.innerHTML = '<div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">' +
                            '<button type="button" class="btn btn-danger btn-sm action-btn" onclick="openAdminRemoveModal(' + jobId + ', \'' + safeTitle + '\', this)">🗑️ تعطيل</button>' +
                            '</div>';
                    }
                }
                if (typeof filterAdminJobsTable === 'function') filterAdminJobsTable();
                alert(data.message || 'تم تفعيل الوظيفة بنجاح');
            } else {
                alert(data.message || 'حدث خطأ');
            }
        })
        .catch(function () { alert('تعذر الاتصال بالسيرفر'); });
}

function openAdminRemoveModal(jobId, jobTitle, btn) {
    var modal = document.getElementById('removeJobModal');
    if (!modal) return;
    var idInput = document.getElementById('removeJobTargetId');
    if (idInput) idInput.value = jobId;
    var titleEl = document.getElementById('removeJobTargetTitle');
    if (titleEl) titleEl.textContent = 'الوظيفة: ' + (jobTitle || '#' + jobId);
    var reasonEl = document.getElementById('removeReason');
    if (reasonEl) reasonEl.value = '';
    modal.style.display = 'flex';
}

function closeAdminRemoveModal() {
    var modal = document.getElementById('removeJobModal');
    if (modal) modal.style.display = 'none';
}

function submitAdminRemoveJob() {
    var idInput = document.getElementById('removeJobTargetId');
    var jobId = idInput ? idInput.value : null;
    if (!jobId) return;
    var reasonInput = document.getElementById('removeReason');
    var reason = reasonInput ? reasonInput.value.trim() : '';
    if (!reason) {
        alert('يرجى كتابة سبب التعطيل ليتم إعلام صاحب العمل به');
        if (reasonInput) reasonInput.focus();
        return;
    }
    var btn = document.getElementById('confirmRemoveBtn');
    var origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner" style="display:inline-block;width:12px;height:12px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span> جاري التعطيل...';
    }

    var formData = new FormData();
    formData.append('job_id', jobId);
    formData.append('action', 'deactivate');
    formData.append('reason', reason);

    fetch('/admin/jobs/toggle', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            if (data.success) {
                closeAdminRemoveModal();
                var row = document.getElementById('admin-job-row-' + jobId);
                if (row) {
                    row.className = 'removed-row';
                    row.setAttribute('data-status-cat', 'removed');
                    var statusCell = row.querySelector('.job-status-cell');
                    if (statusCell) {
                        var safeReason = reason.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
                        var shortReason = safeReason.length > 45 ? safeReason.substring(0, 45) + '...' : safeReason;
                        statusCell.innerHTML = '<span class="status-dot banned"></span> <strong style="color: var(--danger);">معطلة من الإدارة</strong>' +
                            '<div class="admin-remove-reason-text text-muted" style="font-size: 0.75rem; margin-top: 3px; max-width: 200px; line-height: 1.3;" title="' + safeReason + '">السبب: ' + shortReason + '</div>';
                    }
                    var actCell = row.querySelector('.job-actions-cell');
                    if (actCell) {
                        var title = row.getAttribute('data-title') || '';
                        var safeTitle = title.replace(/'/g, "\\'");
                        actCell.innerHTML = '<div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">' +
                            '<button type="button" class="btn btn-success btn-sm action-btn" onclick="openAdminActivateModal(' + jobId + ', \'' + safeTitle + '\', \'\', this)">♻️ تفعيل</button>' +
                            '</div>';
                    }
                }
                if (typeof filterAdminJobsTable === 'function') filterAdminJobsTable();
                alert(data.message || 'تم تعطيل الوظيفة بنجاح');
            } else {
                alert(data.message || 'حدث خطأ أثناء تعطيل الوظيفة');
            }
        })
        .catch(function (err) {
            if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
            console.error('Job deactivate error:', err);
            alert('تعذر الاتصال بالسيرفر');
        });
}

function openAdminReviewModal(jobId) {
    var modal = document.getElementById('adminJobReviewModal');
    if (!modal) return;
    modal.style.display = 'flex';

    var loadingEl = document.getElementById('reviewModalLoading');
    var contentEl = document.getElementById('reviewModalContent');
    if (loadingEl) loadingEl.style.display = 'block';
    if (contentEl) contentEl.style.display = 'none';

    fetch('/admin/jobs/details?id=' + jobId)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success || !data.job) {
                alert(data.message || 'تعذر تحميل بيانات الوظيفة');
                closeAdminReviewModal();
                return;
            }
            var j = data.job;
            document.getElementById('revTitle').textContent = j.Title || '';
            document.getElementById('revCompany').textContent = j.CompanyName || j.EmployerName || 'غير محدد';
            document.getElementById('revEmployer').textContent = j.EmployerName || '';
            document.getElementById('revCity').textContent = j.City || 'عن بُعد';
            document.getElementById('revType').textContent = j.JobType || '';
            document.getElementById('revLevel').textContent = j.ExperienceLevel || '';
            document.getElementById('revExpiry').textContent = j.ExpiresAtFormatted || 'غير محدد';
            document.getElementById('revDescription').textContent = j.Description || '';
            document.getElementById('revRequirements').textContent = j.Requirements || 'لا توجد متطلبات مسجلة';

            // Skills badges
            var skillsBox = document.getElementById('revSkills');
            if (skillsBox) {
                skillsBox.innerHTML = '';
                var skills = (j.Skills || '').split(',').map(function (s) { return s.trim(); }).filter(Boolean);
                if (skills.length > 0) {
                    skills.forEach(function (s) {
                        var sp = document.createElement('span');
                        sp.className = 'tag tag-sm';
                        sp.textContent = s;
                        skillsBox.appendChild(sp);
                    });
                } else {
                    skillsBox.innerHTML = '<span class="text-muted" style="font-size:0.8rem;">لا توجد مهارات مسجلة</span>';
                }
            }

            // Employer notes
            var empBox = document.getElementById('revEmployerNotesBox');
            var empNotes = document.getElementById('revEmployerNotes');
            if (empBox && empNotes) {
                if (j.EmployerReviewNotes) {
                    empNotes.textContent = j.EmployerReviewNotes;
                    empBox.style.display = 'block';
                } else {
                    empBox.style.display = 'none';
                }
            }

            // Previous remove reason
            var prevBox = document.getElementById('revPrevReasonBox');
            var prevReason = document.getElementById('revPrevReason');
            if (prevBox && prevReason) {
                if (j.AdminRemoveReason) {
                    prevReason.textContent = j.AdminRemoveReason;
                    prevBox.style.display = 'block';
                } else {
                    prevBox.style.display = 'none';
                }
            }

            // Hook buttons
            var approveBtn = document.getElementById('revApproveBtn');
            if (approveBtn) {
                approveBtn.onclick = function () {
                    closeAdminReviewModal();
                    openAdminActivateModal(j.JobID, j.Title, j.ExpiresAtInput);
                };
            }

            var rejectBtn = document.getElementById('revRejectBtn');
            if (rejectBtn) {
                rejectBtn.onclick = function () {
                    closeAdminReviewModal();
                    openAdminRemoveModal(j.JobID, j.Title);
                };
            }

            if (loadingEl) loadingEl.style.display = 'none';
            if (contentEl) contentEl.style.display = 'block';
        })
        .catch(function (err) {
            console.error('Job details error:', err);
            alert('تعذر تحميل تفاصيل الوظيفة');
            closeAdminReviewModal();
        });
}

function closeAdminReviewModal() {
    var modal = document.getElementById('adminJobReviewModal');
    if (modal) modal.style.display = 'none';
}

function restoreAdminJob(jobId, jobTitle, btn) {
    openAdminActivateModal(jobId, jobTitle, '', btn);
}

function filterAdminJobsTable() {
    var statusSelect = document.getElementById('filterJobStatus');
    var statusFilter = statusSelect ? statusSelect.value.trim() : '';
    var searchInput = document.getElementById('searchAdminJobs');
    var searchQuery = searchInput ? searchInput.value.trim().toLowerCase() : '';
    var rows = document.querySelectorAll('#adminJobsTable tbody tr');
    var visibleCount = 0;

    rows.forEach(function (row) {
        if (row.cells.length < 5) return;
        var cat = row.getAttribute('data-status-cat') || '';
        var title = row.getAttribute('data-title') || '';
        var comp = row.getAttribute('data-company') || '';
        var emp = row.getAttribute('data-employer') || '';
        var id = row.getAttribute('data-job-id') || '';

        var matchesStatus = (!statusFilter || cat === statusFilter);
        var matchesSearch = (!searchQuery || title.includes(searchQuery) || comp.includes(searchQuery) || emp.includes(searchQuery) || id.includes(searchQuery));

        if (matchesStatus && matchesSearch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    var countEl = document.getElementById('visibleJobsCount');
    if (countEl) countEl.textContent = visibleCount;
}

// Backwards compatibility wrappers
function removeJob(jobId, btn) {
    var row = btn ? btn.closest('tr') : document.getElementById('admin-job-row-' + jobId);
    var title = row ? row.getAttribute('data-title') : '';
    openAdminRemoveModal(jobId, title, btn);
}
function restoreJob(jobId, btn) {
    var row = btn ? btn.closest('tr') : document.getElementById('admin-job-row-' + jobId);
    var title = row ? row.getAttribute('data-title') : '';
    openAdminActivateModal(jobId, title, '', btn);
}
function toggleJobAjax(jobId, btn, modalId, isRestoring) {
    if (isRestoring) {
        openAdminActivateModal(jobId, '', '', btn);
    } else {
        openAdminRemoveModal(jobId, '', btn);
    }
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
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var statusLabels = {
                    'shortlisted': '⭐ القائمة القصيرة',
                    'interview': '📞 مقابلة',
                    'accepted': '🎉 مقبول',
                    'rejected': '❌ مرفوض',
                    'pending': '⏳ قيد المراجعة'
                };
                var statusClasses = {
                    'shortlisted': 'status-interview',
                    'interview': 'status-interview',
                    'accepted': 'status-interview',
                    'rejected': 'status-rejected',
                    'pending': 'status-pending'
                };
                var card = btn ? btn.closest('.applicant-card') : document.getElementById('applicant-card-' + appId);
                if (card) {
                    card.setAttribute('data-status', status);
                    var badge = card.querySelector('.status-badge');
                    if (badge) {
                        badge.className = 'status-badge ' + (statusClasses[status] || 'status-pending');
                        badge.textContent = statusLabels[status] || status;
                    }

                    if (status === 'rejected') {
                        var actions = card.querySelector('.applicant-actions');
                        if (actions) {
                            actions.innerHTML = '<span class="action-status-label" style="color: var(--danger); font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">❌ تم رفض هذا الطلب</span>';
                        }
                        var cbLabel = card.querySelector('.applicant-checkbox-label');
                        if (cbLabel) {
                            cbLabel.remove();
                            updateSelectedApplicants();
                        }
                    } else if (status === 'interview' || status === 'accepted') {
                        var actions = card.querySelector('.applicant-actions');
                        if (actions) {
                            actions.innerHTML = '<span class="action-status-label" style="color: #4a8c5c; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">📅 تم قبول الطلب وتحديد المقابلة</span>' +
                                ' <a href="/employer/interview?application_id=' + appId + '" class="btn btn-outline btn-sm" style="font-size: 0.8rem;">✏️ تعديل المقابلة</a>';
                        }
                        var cbLabel = card.querySelector('.applicant-checkbox-label');
                        if (cbLabel) {
                            cbLabel.remove();
                            updateSelectedApplicants();
                        }
                    }
                }
                recalculateApplicantCounts();

                // Re-check current active filter visibility
                var activeBtn = document.querySelector('.status-filter-btn.btn-primary, .status-filter-btn.btn-success');
                var currentFilter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
                if (currentFilter && currentFilter !== 'all' && card) {
                    if (currentFilter === 'interview' && (status === 'interview' || status === 'accepted')) {
                        card.style.display = 'block';
                    } else if (currentFilter === status) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                }
            } else {
                alert(data.message || 'حدث خطأ');
            }
        })
        .catch(function () {
            alert('تعذر الاتصال بالسيرفر');
        });
}

function toggleShortlist(appId, btn) {
    if (!btn) return;
    var isCurrentlyShortlisted = (btn.getAttribute('data-shortlisted') === '1');
    var targetStatus = isCurrentlyShortlisted ? 'pending' : 'shortlisted';

    btn.disabled = true;
    var originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner" style="display:inline-block;width:12px;height:12px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span> جاري التحديث...';

    var formData = new FormData();
    formData.append('application_id', appId);
    formData.append('status', targetStatus);

    fetch('/employer/applicants/status', {
        method: 'POST',
        body: formData
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.disabled = false;
            if (data.success) {
                var card = btn.closest('.applicant-card') || document.getElementById('applicant-card-' + appId);
                if (targetStatus === 'shortlisted') {
                    btn.setAttribute('data-shortlisted', '1');
                    btn.className = 'btn btn-sm shortlist-toggle-btn';
                    btn.style.background = '#f59e0b';
                    btn.style.borderColor = '#f59e0b';
                    btn.style.color = '#fff';
                    btn.style.fontWeight = '700';
                    btn.innerHTML = '★ إزالة من القائمة القصيرة';

                    if (card) {
                        card.setAttribute('data-status', 'shortlisted');
                        var badge = card.querySelector('.status-badge');
                        if (badge) {
                            badge.className = 'status-badge status-interview';
                            badge.textContent = '⭐ القائمة القصيرة';
                        }
                    }
                } else {
                    btn.setAttribute('data-shortlisted', '0');
                    btn.className = 'btn btn-outline btn-sm shortlist-toggle-btn';
                    btn.style.background = 'transparent';
                    btn.style.borderColor = '#f59e0b';
                    btn.style.color = '#f59e0b';
                    btn.style.fontWeight = '700';
                    btn.innerHTML = '⭐ إضافة للقائمة القصيرة';

                    if (card) {
                        card.setAttribute('data-status', 'pending');
                        var badge = card.querySelector('.status-badge');
                        if (badge) {
                            badge.className = 'status-badge status-pending';
                            badge.textContent = '⏳ قيد المراجعة';
                        }
                    }
                }

                recalculateApplicantCounts();

                // Re-check active filter tab
                var activeBtn = document.querySelector('.status-filter-btn.btn-primary, .status-filter-btn.btn-success');
                var currentFilter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
                if (currentFilter && currentFilter !== 'all' && card) {
                    if (currentFilter === 'shortlisted') {
                        card.style.display = (targetStatus === 'shortlisted') ? 'block' : 'none';
                    } else if (currentFilter === 'pending') {
                        card.style.display = (targetStatus === 'pending') ? 'block' : 'none';
                    }
                }
            } else {
                btn.innerHTML = originalHtml;
                alert(data.message || 'حدث خطأ أثناء تعديل القائمة القصيرة');
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            alert('تعذر الاتصال بالسيرفر');
        });
}

function rejectApplicant(appId, btn) {
    if (!confirm('هل أنت متأكد من رفض هذا الطلب؟')) return;

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner" style="display:inline-block;width:12px;height:12px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span> جاري الرفض...';
    }

    var formData = new FormData();
    formData.append('application_id', appId);
    formData.append('status', 'rejected');

    fetch('/employer/applicants/status', {
        method: 'POST',
        body: formData
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var card = btn ? btn.closest('.applicant-card') : document.getElementById('applicant-card-' + appId);
                if (card) {
                    card.setAttribute('data-status', 'rejected');

                    var badge = card.querySelector('.status-badge');
                    if (badge) {
                        badge.className = 'status-badge status-rejected';
                        badge.textContent = '❌ مرفوض';
                    }

                    var actions = card.querySelector('.applicant-actions');
                    if (actions) {
                        actions.innerHTML = '<span class="action-status-label" style="color: var(--danger); font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">❌ تم رفض هذا الطلب</span>';
                    }

                    var cbLabel = card.querySelector('.applicant-checkbox-label');
                    if (cbLabel) {
                        var cb = cbLabel.querySelector('.applicant-checkbox');
                        if (cb && cb.checked) {
                            cb.checked = false;
                        }
                        cbLabel.remove();
                        updateSelectedApplicants();
                    }
                }

                recalculateApplicantCounts();

                var activeBtn = document.querySelector('.status-filter-btn.btn-primary, .status-filter-btn.btn-success');
                var currentFilter = activeBtn ? activeBtn.getAttribute('data-filter') : 'all';
                if (currentFilter && currentFilter !== 'all' && currentFilter !== 'rejected' && card) {
                    card.style.display = 'none';
                }
            } else {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '❌ رفض';
                }
                alert(data.message || 'حدث خطأ أثناء رفض الطلب');
            }
        })
        .catch(function () {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '❌ رفض';
            }
            alert('تعذر الاتصال بالسيرفر');
        });
}

function updateSelectedApplicants() {
    var checkedBoxes = document.querySelectorAll('.applicant-checkbox:checked');
    var count = checkedBoxes.length;
    var toolbar = document.getElementById('bulkActionsToolbar');
    var countEl = document.getElementById('selectedApplicantsCount');
    if (countEl) countEl.textContent = count;
    if (toolbar) {
        toolbar.style.display = (count > 0) ? 'block' : 'none';
    }

    var allCheckboxes = document.querySelectorAll('.applicant-checkbox');
    var master = document.getElementById('selectAllCheckbox');
    if (master) {
        master.checked = (allCheckboxes.length > 0 && checkedBoxes.length === allCheckboxes.length);
    }
}

function toggleSelectAllApplicants(masterCheckbox) {
    var isChecked = masterCheckbox ? masterCheckbox.checked : false;
    var checkboxes = document.querySelectorAll('.applicant-checkbox');
    checkboxes.forEach(function (cb) {
        var card = cb.closest('.applicant-card');
        if (card && card.style.display !== 'none') {
            cb.checked = isChecked;
        } else if (!isChecked) {
            cb.checked = false;
        }
    });
    updateSelectedApplicants();
}

function clearSelectedApplicants() {
    var checkboxes = document.querySelectorAll('.applicant-checkbox');
    checkboxes.forEach(function (cb) { cb.checked = false; });
    var master = document.getElementById('selectAllCheckbox');
    if (master) master.checked = false;
    updateSelectedApplicants();
}

function openBulkInterviewModal() {
    var checkedBoxes = document.querySelectorAll('.applicant-checkbox:checked');
    if (!checkedBoxes || checkedBoxes.length === 0) {
        alert('برجاء تحديد متقدم واحد على الأقل أولاً');
        return;
    }

    var names = [];
    checkedBoxes.forEach(function (cb) {
        var name = cb.getAttribute('data-name');
        if (name) names.push(name);
    });

    var summaryEl = document.getElementById('bulkSelectedListSummary');
    if (summaryEl) {
        summaryEl.innerHTML = '<strong style="color: var(--primary);">المتقدمين المحددين (' + checkedBoxes.length + '):</strong> ' +
            names.map(function (n) { return '<span class="badge" style="margin: 2px 4px; background: rgba(196,113,43,0.15); color: inherit; padding: 2px 8px; border-radius: 4px; border: 1px solid rgba(196,113,43,0.3);">' + n + '</span>'; }).join(' ');
    }

    var dtInput = document.getElementById('bulkInterviewDatetime');
    if (dtInput) {
        var nowStr = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        dtInput.min = nowStr;
        if (!dtInput.value) {
            var tomorrow = new Date(Date.now() + 24 * 60 * 60 * 1000 - new Date().getTimezoneOffset() * 60000);
            tomorrow.setHours(10, 0, 0, 0);
            dtInput.value = tomorrow.toISOString().slice(0, 16);
        }
    }

    var modal = document.getElementById('bulkInterviewModal');
    if (modal) modal.style.display = 'flex';
}

function closeBulkInterviewModal() {
    var modal = document.getElementById('bulkInterviewModal');
    if (modal) modal.style.display = 'none';
}

function toggleBulkLocationType(type) {
    var onlineField = document.getElementById('bulkOnlineField');
    var onsiteField = document.getElementById('bulkOnsiteField');
    if (type === 'online') {
        if (onlineField) onlineField.style.display = 'block';
        if (onsiteField) onsiteField.style.display = 'none';
    } else {
        if (onlineField) onlineField.style.display = 'none';
        if (onsiteField) onsiteField.style.display = 'block';
    }
}

function submitBulkInterview(e) {
    if (e) e.preventDefault();

    var checkedBoxes = document.querySelectorAll('.applicant-checkbox:checked');
    if (!checkedBoxes || checkedBoxes.length === 0) {
        alert('لم يتم تحديد أي متقدمين');
        return;
    }

    var appIds = [];
    checkedBoxes.forEach(function (cb) {
        var v = parseInt(cb.value, 10);
        if (v) appIds.push(v);
    });

    var dtInput = document.getElementById('bulkInterviewDatetime');
    var dtVal = dtInput ? dtInput.value : '';
    if (!dtVal) {
        alert('برجاء تحديد موعد وتاريخ المقابلة');
        if (dtInput) dtInput.focus();
        return;
    }

    var targetDate = new Date(dtVal);
    if (targetDate <= new Date()) {
        alert('موعد المقابلة يجب أن يكون في تاريخ وساعة مستقبلية وغير منتهي');
        if (dtInput) dtInput.focus();
        return;
    }

    var locTypeRadio = document.querySelector('input[name="bulk_loc_type"]:checked');
    var locType = locTypeRadio ? locTypeRadio.value : 'online';

    var meetingLinkInput = document.getElementById('bulkMeetingLink');
    var meetingLink = meetingLinkInput ? meetingLinkInput.value.trim() : '';

    var locationInput = document.getElementById('bulkLocation');
    var location = locationInput ? locationInput.value.trim() : '';

    if (locType === 'online') {
        if (!meetingLink) {
            alert('برجاء إدخال رابط المقابلة الأونلاين');
            if (meetingLinkInput) meetingLinkInput.focus();
            return;
        }
        if (!location) {
            location = 'أونلاين عبر الإنترنت';
        }
    } else {
        if (!location) {
            alert('برجاء إدخال عنوان ومقر المقابلة بالتفصيل');
            if (locationInput) locationInput.focus();
            return;
        }
    }

    var notesInput = document.getElementById('bulkNotes');
    var notes = notesInput ? notesInput.value.trim() : '';

    var submitBtn = document.getElementById('submitBulkInterviewBtn');
    var originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner" style="display:inline-block;width:14px;height:14px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin .8s linear infinite;"></span> جاري الإرسال وتحديد المواعيد...';
    }

    var formData = new FormData();
    formData.append('application_ids', JSON.stringify(appIds));
    formData.append('interview_datetime', dtVal);
    formData.append('location_type', locType);
    formData.append('location', location);
    formData.append('meeting_link', meetingLink);
    formData.append('notes', notes);

    fetch('/employer/applicants/bulk-interview', {
        method: 'POST',
        body: formData
    })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml || '🚀 إرسال وتحديد المقابلة للجميع';
            }

            if (data.success) {
                var updatedIds = data.application_ids || appIds;
                updatedIds.forEach(function (id) {
                    var card = document.getElementById('applicant-card-' + id) || document.querySelector('.applicant-card[data-app-id="' + id + '"]');
                    if (card) {
                        card.setAttribute('data-status', 'interview');

                        var badge = card.querySelector('.status-badge');
                        if (badge) {
                            badge.className = 'status-badge status-interview';
                            badge.textContent = '📞 مقابلة';
                        }

                        var actions = card.querySelector('.applicant-actions');
                        if (actions) {
                            actions.innerHTML = '<span class="action-status-label" style="color: #4a8c5c; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">📅 تم قبول الطلب وتحديد المقابلة</span>' +
                                ' <a href="/employer/interview?application_id=' + id + '" class="btn btn-outline btn-sm" style="font-size: 0.8rem;">✏️ تعديل المقابلة</a>';
                        }

                        var cbLabel = card.querySelector('.applicant-checkbox-label');
                        if (cbLabel) {
                            cbLabel.remove();
                        }
                    }
                });

                recalculateApplicantCounts();
                clearSelectedApplicants();
                closeBulkInterviewModal();

                alert(data.message || 'تم إرسال تفاصيل القبول وتحديد المقابلة لجميع المتقدمين بنجاح! 🎉');
            } else {
                alert(data.message || 'حدث خطأ أثناء تحديد المقابلة');
            }
        })
        .catch(function () {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml || '🚀 إرسال وتحديد المقابلة للجميع';
            }
            alert('تعذر الاتصال بالسيرفر');
        });
}

// Global exposure
window.toggleShortlist = toggleShortlist;
window.rejectApplicant = rejectApplicant;
window.updateSelectedApplicants = updateSelectedApplicants;
window.toggleSelectAllApplicants = toggleSelectAllApplicants;
window.clearSelectedApplicants = clearSelectedApplicants;
window.openBulkInterviewModal = openBulkInterviewModal;
window.closeBulkInterviewModal = closeBulkInterviewModal;
window.toggleBulkLocationType = toggleBulkLocationType;
window.submitBulkInterview = submitBulkInterview;

function recalculateApplicantCounts() {
    var cards = document.querySelectorAll('.applicant-card');
    var all = cards.length;
    var shortlisted = 0, pending = 0, interview = 0, rejected = 0;
    cards.forEach(function (c) {
        var s = c.getAttribute('data-status') || 'pending';
        if (s === 'shortlisted') shortlisted++;
        else if (s === 'pending') pending++;
        else if (s === 'interview' || s === 'accepted') interview++;
        else if (s === 'rejected') rejected++;
    });
    var elAll = document.getElementById('count-all');
    var elShort = document.getElementById('count-shortlisted');
    var elPend = document.getElementById('count-pending');
    var elInter = document.getElementById('count-interview');
    var elRej = document.getElementById('count-rejected');
    if (elAll) elAll.textContent = all;
    if (elShort) elShort.textContent = shortlisted;
    if (elPend) elPend.textContent = pending;
    if (elInter) elInter.textContent = interview;
    if (elRej) elRej.textContent = rejected;
}

function initApplicantsFilter() {
    var buttons = document.querySelectorAll('.status-filter-btn');
    if (!buttons || buttons.length === 0) return;

    function filterCards(filter) {
        var cards = document.querySelectorAll('.applicant-card');
        var visibleCount = 0;

        cards.forEach(function (card) {
            var st = card.getAttribute('data-status') || 'pending';
            var match = false;
            if (filter === 'all') {
                match = true;
            } else if (filter === 'interview') {
                match = (st === 'interview' || st === 'accepted');
            } else {
                match = (st === filter);
            }

            if (match) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        var noMatch = document.getElementById('noFilteredApplicants');
        if (noMatch) {
            if (visibleCount === 0 && cards.length > 0) {
                noMatch.style.display = 'block';
                var titleEl = document.getElementById('noFilteredTitle');
                var descEl = document.getElementById('noFilteredDesc');
                if (filter === 'shortlisted') {
                    if (titleEl) titleEl.textContent = 'لا يوجد متقدمين في القائمة القصيرة حتى الآن ⭐';
                    if (descEl) descEl.textContent = 'اضغط على زر "⭐ قائمة قصيرة" عند أي متقدم لإضافته إلى هنا.';
                } else {
                    if (titleEl) titleEl.textContent = 'لا يوجد متقدمين في هذا القسم';
                    if (descEl) descEl.textContent = 'لم يتم العثور على طلبات مطابقة لهذا التصنيف.';
                }
            } else {
                noMatch.style.display = 'none';
            }
        }

        buttons.forEach(function (b) {
            var f = b.getAttribute('data-filter');
            if (f === filter) {
                b.classList.remove('btn-outline');
                if (f === 'shortlisted') {
                    b.classList.add('btn-success');
                    b.style.background = '#f59e0b';
                    b.style.borderColor = '#f59e0b';
                    b.style.color = '#fff';
                } else {
                    b.classList.add('btn-primary');
                }
            } else {
                b.classList.remove('btn-primary', 'btn-success');
                b.classList.add('btn-outline');
                if (f === 'shortlisted') {
                    b.style.background = 'transparent';
                    b.style.borderColor = '#f59e0b';
                    b.style.color = '#f59e0b';
                } else {
                    b.style.background = '';
                    b.style.borderColor = '';
                    b.style.color = '';
                }
            }
        });
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var filter = btn.getAttribute('data-filter');
            filterCards(filter);
            var url = new URL(window.location);
            if (filter === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', filter);
            }
            window.history.replaceState({}, '', url);
        });
    });

    var urlParams = new URLSearchParams(window.location.search);
    var initialStatus = urlParams.get('status') || 'all';
    filterCards(initialStatus);
}

document.addEventListener('DOMContentLoaded', initApplicantsFilter);
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initApplicantsFilter();
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

// ===========================
// Employer Job Toggle (Pause / Resume Accepting Applications)
// ===========================
var currentReopenBtn = null;

function toggleEmployerJob(jobId, btn, newExpiresAt, onComplete) {
    if (!btn) {
        btn = document.querySelector('#employer-job-' + jobId + ' .toggle-job-btn');
    }
    var isCurrentlyActive = btn ? btn.getAttribute('data-active') === '1' : false;
    var isExpired = btn ? btn.getAttribute('data-expired') === '1' : false;

    // If job is expired and no new expiry provided, open reopen modal right away
    if (isExpired && !newExpiresAt) {
        var jobItem = document.getElementById('employer-job-' + jobId);
        var jobTitle = jobItem && jobItem.querySelector('h4') ? jobItem.querySelector('h4').textContent.trim() : '';
        openReopenModal(jobId, jobTitle, '', btn);
        return;
    }

    if (!newExpiresAt) {
        var confirmMsg = isCurrentlyActive
            ? 'هل تريد إيقاف استقبال طلبات التقديم على هذه الوظيفة وتصفير العداد؟'
            : 'هل تريد إعادة فتح واستئناف استقبال طلبات التقديم على هذه الوظيفة؟';

        if (!confirm(confirmMsg)) return;
    }

    var originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> جاري التحديث...';
    }

    var formData = new FormData();
    formData.append('job_id', jobId);
    if (newExpiresAt) {
        formData.append('new_expires_at', newExpiresAt);
    }

    fetch('/employer/jobs/toggle', {
        method: 'POST',
        body: formData
    })
        .then(function (res) {
            return res.text().then(function (text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Server response was not JSON:', text);
                    throw new Error('استجابة غير صحيحة من السيرفر');
                }
            });
        })
        .then(function (data) {
            if (btn) btn.disabled = false;
            if (typeof onComplete === 'function') onComplete();

            if (data.require_new_deadline) {
                if (btn) btn.innerHTML = originalHtml;
                openReopenModal(jobId, data.job_title, data.old_expires_at, btn);
                return;
            }

            if (data.success) {
                var jobItem = document.getElementById('employer-job-' + jobId);
                var statusPill = jobItem ? jobItem.querySelector('.job-status-pill') : null;
                var timerBox = jobItem ? jobItem.querySelector('.job-timer-box') : null;

                if (data.is_active) {
                    if (btn) {
                        btn.setAttribute('data-active', '1');
                        btn.removeAttribute('data-expired');
                        btn.className = 'btn btn-outline btn-sm toggle-job-btn';
                        btn.style.color = 'var(--danger)';
                        btn.style.borderColor = 'rgba(239, 68, 68, 0.4)';
                        btn.innerHTML = '⏸️ إيقاف التقديم';
                        btn.title = 'إيقاف استقبال طلبات جديدة على هذه الوظيفة وتصفير العداد';
                        btn.onclick = function () { toggleEmployerJob(jobId, this); };
                    }

                    if (statusPill) {
                        statusPill.style.background = 'rgba(74, 140, 92, 0.15)';
                        statusPill.style.color = '#4a8c5c';
                        statusPill.style.borderColor = 'rgba(74, 140, 92, 0.3)';
                        statusPill.textContent = '🟢 نشطة';
                    }

                    if (timerBox && data.expires_iso) {
                        timerBox.innerHTML = '<span class="timer-badge active job-countdown" data-expires="' + data.expires_iso + '">⏳ متبقي: <strong class="countdown-text">جاري الحساب...</strong></span>';
                    }

                    if (typeof window.initJobTimers === 'function') {
                        window.initJobTimers();
                    }
                } else {
                    // Paused / stopped -> timer is zeroed out
                    if (btn) {
                        btn.setAttribute('data-active', '0');
                        btn.setAttribute('data-expired', '1');
                        btn.className = 'btn btn-primary btn-sm toggle-job-btn';
                        btn.style.color = '';
                        btn.style.borderColor = '';
                        btn.innerHTML = '🔄 إعادة فتح التقديم';
                        btn.title = 'تحديد موعد جديد وإعادة فتح التقديم';
                        btn.onclick = function () {
                            var jTitle = jobItem && jobItem.querySelector('h4') ? jobItem.querySelector('h4').textContent.trim() : '';
                            openReopenModal(jobId, jTitle, '', this);
                        };
                    }

                    if (statusPill) {
                        statusPill.style.background = 'rgba(239, 68, 68, 0.15)';
                        statusPill.style.color = '#ef4444';
                        statusPill.style.borderColor = 'rgba(239, 68, 68, 0.3)';
                        statusPill.textContent = '⏸️ التقديم متوقف';
                    }

                    if (timerBox) {
                        timerBox.innerHTML = '<span class="timer-badge expired">⛔ التقديم متوقف (العداد: 00:00)</span>';
                    }
                }
            } else {
                if (btn) btn.innerHTML = originalHtml;
                alert(data.message || 'حدث خطأ أثناء تعديل حالة الوظيفة');
            }
        })
        .catch(function (err) {
            console.error('Toggle job error:', err);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
            if (typeof onComplete === 'function') onComplete();
            alert('تعذر استكمال العملية: ' + (err.message || 'خطأ في الاتصال'));
        });
}

function openReopenModal(jobId, jobTitle, oldExpiresAt, btn) {
    currentReopenBtn = btn || document.querySelector('#employer-job-' + jobId + ' .toggle-job-btn');
    var modal = document.getElementById('reopenJobModal');
    var promptEl = document.getElementById('reopenJobPrompt');
    var idInput = document.getElementById('reopenJobId');

    if (idInput) idInput.value = jobId;
    if (promptEl) {
        promptEl.innerHTML = 'انتهى موعد التقديم السابق على وظيفة <strong>' + (jobTitle || '') + '</strong>' +
            (oldExpiresAt ? ' (' + oldExpiresAt + ')' : '') +
            '.<br>اختر موعداً جديداً لاستئناف استقبال طلبات التوظيف:';
    }

    // Default to +24 hours
    setQuickDeadline(24, 'hours');

    if (modal) modal.style.display = 'flex';
}

function closeReopenModal() {
    var modal = document.getElementById('reopenJobModal');
    if (modal) modal.style.display = 'none';
}

function setQuickDeadline(amount, unit) {
    var expiryInput = document.getElementById('reopenNewExpiry');
    if (!expiryInput) return;
    expiryInput.disabled = false;

    var ms = 0;
    if (unit === 'minutes') ms = amount * 60 * 1000;
    else if (unit === 'hours') ms = amount * 3600 * 1000;
    else if (unit === 'days') ms = amount * 86400 * 1000;

    var now = new Date();
    var targetDate = new Date(now.getTime() + ms);
    var year = targetDate.getFullYear();
    var month = String(targetDate.getMonth() + 1).padStart(2, '0');
    var day = String(targetDate.getDate()).padStart(2, '0');
    var hours = String(targetDate.getHours()).padStart(2, '0');
    var minutes = String(targetDate.getMinutes()).padStart(2, '0');
    var localIso = year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;

    expiryInput.value = localIso;

    var minDate = new Date(now.getTime() + 60000);
    var minIso = minDate.getFullYear() + '-' +
        String(minDate.getMonth() + 1).padStart(2, '0') + '-' +
        String(minDate.getDate()).padStart(2, '0') + 'T' +
        String(minDate.getHours()).padStart(2, '0') + ':' +
        String(minDate.getMinutes()).padStart(2, '0');
    expiryInput.min = minIso;
}

function submitReopenJob() {
    var jobId = document.getElementById('reopenJobId') ? document.getElementById('reopenJobId').value : null;
    if (!jobId) return;

    var input = document.getElementById('reopenNewExpiry');
    var newExpiryVal = input ? input.value : '';
    if (!newExpiryVal) {
        alert('برجاء تحديد الموعد الجديد لانتهاء التقديم');
        return;
    }
    var targetDate = new Date(newExpiryVal);
    if (targetDate <= new Date()) {
        alert('الموعد الجديد لازم يكون في وقت مستقبلي وغير منتهي');
        return;
    }

    var confirmBtn = document.getElementById('confirmReopenBtn');
    var originalConfirmHtml = confirmBtn ? confirmBtn.innerHTML : '';
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<span class="spinner"></span> جاري الحفظ...';
    }

    var targetBtn = currentReopenBtn || document.querySelector('#employer-job-' + jobId + ' .toggle-job-btn');

    toggleEmployerJob(jobId, targetBtn, newExpiryVal, function () {
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = originalConfirmHtml || '✅ حفظ وفتح التقديم';
        }
        closeReopenModal();
    });
}

// ===================================
// Image Lightbox / Modal Preview
// ===================================
function openImageLightbox(src, name) {
    if (!src) return;

    var modal = document.getElementById('imageLightboxModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'imageLightboxModal';
        modal.className = 'image-lightbox-overlay';
        modal.innerHTML =
            '<div class="image-lightbox-wrapper" onclick="event.stopPropagation()">' +
                '<button type="button" class="image-lightbox-close" onclick="closeImageLightbox()" aria-label="إغلاق">&times;</button>' +
                '<div class="image-lightbox-body">' +
                    '<img id="imageLightboxImg" src="" alt="" class="image-lightbox-img">' +
                '</div>' +
                '<div class="image-lightbox-footer">' +
                    '<span id="imageLightboxCaption" class="image-lightbox-caption"></span>' +
                    '<a id="imageLightboxDownload" href="" target="_blank" class="image-lightbox-link" title="فتح بالحجم الكامل">🔍 فتح الصورة بالحجم الكامل</a>' +
                '</div>' +
            '</div>';

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeImageLightbox();
            }
        });

        document.body.appendChild(modal);
    }

    var img = document.getElementById('imageLightboxImg');
    var caption = document.getElementById('imageLightboxCaption');
    var link = document.getElementById('imageLightboxDownload');

    if (img) {
        img.src = src;
        img.alt = name || 'صورة المتقدم';
    }
    if (caption) {
        caption.textContent = name ? '👤 ' + name : '👤 صورة المتقدم';
    }
    if (link) {
        link.href = src;
    }

    modal.style.display = 'flex';
    requestAnimationFrame(function () {
        modal.classList.add('active');
    });
    document.body.style.overflow = 'hidden';
}

function closeImageLightbox() {
    var modal = document.getElementById('imageLightboxModal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(function () {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 220);
    } else {
        document.body.style.overflow = '';
    }
}

// Close on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
        var modal = document.getElementById('imageLightboxModal');
        if (modal && modal.classList.contains('active')) {
            closeImageLightbox();
        }
    }
});

// Click delegation for any applicant image or avatar containing an image
document.addEventListener('click', function (e) {
    // Avoid triggering on avatar upload previews in settings
    if (e.target.closest('#avatarPreview, #avatarUploadArea')) return;

    var avatar = e.target.closest('.applicant-info .user-avatar, .cv-received-item .user-avatar, .applicant-card .user-avatar, [data-lightbox="true"], .avatar-lightbox');
    if (avatar) {
        var img = avatar.querySelector('img');
        if (img && img.src) {
            var name = img.getAttribute('alt') || avatar.getAttribute('data-name') || '';
            openImageLightbox(img.src, name);
        }
    }
});

window.openImageLightbox = openImageLightbox;
window.closeImageLightbox = closeImageLightbox;



