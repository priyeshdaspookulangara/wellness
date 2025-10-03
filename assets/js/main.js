$(document).ready(function() {
    // Like button AJAX handler
    $('.like-product-btn').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var productId = button.data('product-id');

        // Prevent multiple clicks while request is processing
        button.prop('disabled', true);

        $.ajax({
            url: 'like_action.php', // Assuming this is relative to the page
            type: 'POST',
            data: {
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update like count on the page
                    button.find('.like-count').text(response.like_count);

                    // Toggle button appearance
                    if (response.action === 'liked') {
                        button.removeClass('btn-outline-danger').addClass('btn-danger');
                        button.attr('title', 'Unlike Product');
                    } else {
                        button.removeClass('btn-danger').addClass('btn-outline-danger');
                        button.attr('title', 'Like Product');
                    }
                } else {
                    // Handle error, e.g., show an alert
                    alert(response.message || 'An error occurred.');
                }
            },
            error: function() {
                alert('A server error occurred. Please try again.');
            },
            complete: function() {
                // Re-enable the button
                button.prop('disabled', false);
            }
        });
    });

    // You might have other scripts here for cart, etc.
    // For example:
    $('.add-to-cart-btn').on('click', function() {
        var productId = $(this).data('product-id');
        // Add to cart logic would go here
        console.log('Add to cart clicked for product: ' + productId);
    });
});