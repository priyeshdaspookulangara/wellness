// Custom JavaScript for Wellness Wonders Admin Panel

$(document).ready(function() {
    console.log("Admin JS Loaded");

    // Example: Confirm delete actions
    // This can be made more generic later if needed
    $('.confirm-delete').on('click', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    // Initialize tooltips (if using Bootstrap tooltips)
    $('[data-toggle="tooltip"]').tooltip();

    // Add more admin-specific JS functions here
});
