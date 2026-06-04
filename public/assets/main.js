document.addEventListener('DOMContentLoaded', function() {
    console.log('Billing App UI Loaded');

    // ── THEME TOGGLE ──────────────────────────────────────
    const html = document.documentElement;
    const THEME_KEY = 'billingapp_theme';

    // Terapkan tema tersimpan (atau default dark)
    const savedTheme = localStorage.getItem(THEME_KEY) || 'dark';
    html.setAttribute('data-theme', savedTheme);
    // Sync Bootstrap data-bs-theme
    html.setAttribute('data-bs-theme', savedTheme === 'light' ? 'light' : 'dark');

    const themeBtn = document.getElementById('themeToggleBtn');
    if (themeBtn) {
        themeBtn.addEventListener('click', function() {
            const current = html.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            html.setAttribute('data-bs-theme', next === 'light' ? 'light' : 'dark');
            localStorage.setItem(THEME_KEY, next);
        });
    }
    // ──────────────────────────────────────────────────────


    // SweetAlert2 Toast configuration
    if (typeof Swal !== 'undefined') {
        const isDark = (localStorage.getItem('billingapp_theme') || 'dark') === 'dark';
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f8fafc' : '#1e293b',
            iconColor: '#38bdf8',
            customClass: {
                popup: 'border border-secondary border-opacity-25 shadow-lg rounded-3'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Override native window.alert
        window.alert = function(message) {
            let iconType = 'info';
            const msgLower = (message || '').toLowerCase();
            
            if (msgLower.includes('error') || msgLower.includes('gagal') || msgLower.includes('tidak') || msgLower.includes('hapus')) {
                iconType = 'error';
            } else if (msgLower.includes('sukses') || msgLower.includes('berhasil') || msgLower.includes('lunas') || msgLower.includes('✅')) {
                iconType = 'success';
            } else if (msgLower.includes('peringatan') || msgLower.includes('yakin')) {
                iconType = 'warning';
            }

            Toast.fire({
                icon: iconType,
                title: message
            });
        };
    }

    // Handle AJAX Form Submissions if data-ajax attribute is present
    const ajaxForms = document.querySelectorAll('form[data-ajax="true"]');
    
    ajaxForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            const actionUrl = form.getAttribute('action');
            const formData = new FormData(form);
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
            
            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    // Fallback to normal submission if it's not a redirect
                    form.removeAttribute('data-ajax');
                    form.submit();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    });

    // Handle loading spinner for standard form submissions (non-AJAX)
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (e.defaultPrevented) return;
        if (form.getAttribute('data-ajax') === 'true') return;
        
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            // Check if button already has a spinner to avoid duplicate triggers
            if (submitBtn.querySelector('.spinner-border')) return;

            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
            setTimeout(() => {
                submitBtn.disabled = true;
            }, 0);
        }
    });

    // Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        // Create overlay element
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        // Toggle sidebar
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
            
            if (sidebar.classList.contains('show')) {
                overlay.classList.add('show');
            } else {
                overlay.classList.remove('show');
            }
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        });
    }

    // PWA Install Prompt Handler
    let deferredPrompt;
    const installBtn = document.getElementById('pwa-install-btn');
    const installNav = document.getElementById('pwa-install-nav');
    const installContainer = document.getElementById('pwa-install-container');

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (installNav) {
            installNav.classList.remove('d-none');
            installNav.classList.add('d-block');
        }
        if (installContainer) {
            installContainer.classList.remove('d-none');
            installContainer.classList.add('d-block');
        }
    });

    if (installBtn) {
        installBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('PWA installation accepted');
                } else {
                    console.log('PWA installation dismissed');
                }
                deferredPrompt = null;
                if (installNav) {
                    installNav.classList.add('d-none');
                    installNav.classList.remove('d-block');
                }
                if (installContainer) {
                    installContainer.classList.add('d-none');
                    installContainer.classList.remove('d-block');
                }
            });
        });
    }

    window.addEventListener('appinstalled', () => {
        console.log('PWA was installed');
        if (installNav) {
            installNav.classList.add('d-none');
            installNav.classList.remove('d-block');
        }
        if (installContainer) {
            installContainer.classList.add('d-none');
            installContainer.classList.remove('d-block');
        }
    });
});

// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const scripts = document.getElementsByTagName('script');
        let swPath = '/sw.js';
        for (let i = 0; i < scripts.length; i++) {
            const src = scripts[i].src;
            if (src && src.includes('/assets/main.js')) {
                swPath = src.replace('assets/main.js', 'sw.js');
                break;
            }
        }
        navigator.serviceWorker.register(swPath)
            .then(reg => console.log('Service Worker registered. Scope:', reg.scope))
            .catch(err => console.error('Service Worker registration failed:', err));
    });
}
