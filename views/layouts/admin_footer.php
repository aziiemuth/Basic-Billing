        </div> <!-- end main-content -->
    </div> <!-- end wrapper -->

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Vanilla JS -->
    <script src="<?php echo URLROOT; ?>/assets/main.js"></script>
    
    <!-- PHP Session Toast Handlers -->
    <?php if (isset($_SESSION['toast_success'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof window.alert === 'function') {
                window.alert('✅ <?php echo addslashes($_SESSION['toast_success']); ?>');
            }
        });
    </script>
    <?php unset($_SESSION['toast_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['toast_error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof window.alert === 'function') {
                window.alert('❌ Error: <?php echo addslashes($_SESSION['toast_error']); ?>');
            }
        });
    </script>
    <?php unset($_SESSION['toast_error']); ?>
    <?php endif; ?>
</body>
</html>
