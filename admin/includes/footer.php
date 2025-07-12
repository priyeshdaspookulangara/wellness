</main> <!-- /.main-content -->

<footer class="mt-auto py-3 bg-light border-top" style="background-color: var(--card-bg); border-top-color: var(--card-border-color);">
    <div class="container-fluid text-center">
        <span class="text-muted" style="color: var(--text-muted-color);">&copy; <?php echo date("Y"); ?> <?php echo SITE_NAME; ?> Admin Panel.</span>
    </div>
</footer>

</div> <!-- /#content-wrapper -->
</div> <!-- /#admin-wrapper -->

    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS Bundle CDN (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Admin JS -->
    <script>
    $(document).ready(function () {
        // Sidebar Toggle
        $('#sidebarCollapseBtn').on('click', function () {
            $('#sidebar').toggleClass('collapsed');
            // Optional: On smaller screens, you might want a different behavior,
            // like making the sidebar an overlay. Bootstrap 5's offcanvas might be
            // a better fit for that, but this is a basic horizontal collapse.
             if ($(window).width() <= 992) { // Corresponds to Bootstrap's lg breakpoint
                $('#sidebar').toggleClass('active'); // 'active' can be used for overlay display on mobile
            }
        });

        // Sidebar Accordion: Ensure only one submenu is open at a time
        // This relies on Bootstrap 5's collapse component and data-bs-parent attribute on the .collapse elements.
        // If not using data-bs-parent, you might need manual JS like this:
        // $('#sidebar .components .collapse').on('show.bs.collapse', function () {
        //    $('#sidebar .components .collapse.show').not($(this)).collapse('hide');
        // });
        // For Bootstrap 5, ensure your collapsible elements have a common parent specified in data-bs-parent
        // e.g. <ul class="collapse list-unstyled" id="productSubmenu" data-bs-parent="#sidebar ul.components">
        // The current HTML in header.php uses data-bs-parent="#sidebar" which should work for top-level items.
        // If you have nested accordions, the parent might need to be more specific.
        // The provided Bootstrap 5 accordion example uses data-bs-parent on each .accordion-collapse for a single accordion.
        // For multiple independent collapsible items that should behave as an accordion,
        // you might need to explicitly set data-bs-parent on each .collapse element pointing to their shared accordion container.
        // The current HTML in header.php uses unique IDs for collapse elements and `data-bs-toggle="collapse"` which is standard.
        // To ensure only one is open, the `data-bs-parent` attribute on the `.collapse` elements is key.
        // I will add `data-bs-parent="#sidebar .components"` to the `ul.collapse` elements in `admin/includes/header.php`.

        // Generic confirm delete
        $('.confirm-delete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });

        // Initialize Bootstrap Tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
    </script>
    <?php
        // Optional: Include page-specific JS file if $page_js is set (not currently used)
        // if (isset($page_js) && !empty($page_js)) {
        //    echo '<script src="' . SITE_URL . 'admin/assets/js/' . htmlspecialchars($page_js) . '"></script>';
        // }
    ?>
</body>
</html>
