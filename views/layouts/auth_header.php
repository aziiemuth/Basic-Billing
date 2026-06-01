<?php /** @var array $data */ ?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['title']) ? $data['title'] : SITENAME; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/style.css?v=<?php echo time(); ?>">

    <!-- PWA Settings -->
    <link rel="manifest" href="<?php echo URLROOT; ?>/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ISP Portal">
    <link rel="apple-touch-icon" href="<?php echo URLROOT; ?>/assets/icon-192.png">

    <!-- Anti-FOUC: terapkan tema sebelum render -->
    <script>
        (function(){
            var t = localStorage.getItem('billingapp_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.setAttribute('data-bs-theme', t === 'light' ? 'light' : 'dark');
        })();
    </script>
</head>
<body class="auth-page">

    <!-- Theme Toggle (pojok kanan atas) -->
    <div class="auth-theme-toggle">
        <button id="themeToggleBtn" title="Ganti Tema" aria-label="Toggle Dark/Light Mode">
            <i class="bi bi-sun-fill icon-dark fs-5"></i>
            <i class="bi bi-moon-stars-fill icon-light fs-5"></i>
        </button>
    </div>

