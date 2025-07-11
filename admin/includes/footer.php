<?php require_once __DIR__ . '/../../config.php'; ?>
    <!-- Main page content ends here -->
    </div> <!-- /.container-fluid pt-3 -->
</div> <!-- /#content -->

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- Using min.js instead of slim for potential full jQuery features if needed by admin scripts -->
    <!-- Bootstrap JS Bundle CDN (includes Popper.js) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Admin JS (optional) -->
    <script src="<?php echo SITE_URL; ?>/admin/assets/js/admin-main.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</body>
</html>
