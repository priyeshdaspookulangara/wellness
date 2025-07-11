// Custom JavaScript for Wellness Wonders

$(document).ready(function() {
    // Smooth scrolling (remains as is)
    $('a[href*="#"]')
      .not('[href="#"]')
      .not('[href="#0"]')
      .click(function(event) {
        if ( location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname ) {
          var target = $(this.hash);
          target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
          if (target.length) {
            event.preventDefault();
            $('html, body').animate({ scrollTop: target.offset().top }, 1000, function() {
              var $target = $(target);
              $target.focus();
              if ($target.is(":focus")) { return false; }
              else { $target.attr('tabindex','-1'); $target.focus(); };
            });
          }
        }
      });

    console.log("Wellness Wonders JS Loaded");

    // AJAX Add to Cart
    $('.add-to-cart-btn').on('click', function(e) {
        e.preventDefault();
        var $thisButton = $(this);
        var productId = $thisButton.data('product-id');
        // For product page, quantity might be selected by user
        var quantityInput = $('#quantity'); // Assuming product page has an input with id="quantity"
        var quantity = 1; // Default quantity for listing pages

        if (quantityInput.length && $thisButton.closest('.col-md-6').find(quantityInput).length) { // Check if quantity input is part of the same product block
             quantity = parseInt(quantityInput.val());
             if (isNaN(quantity) || quantity < 1) {
                 quantity = 1;
             }
        }


        $thisButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

        $.ajax({
            url: SITE_URL + '/cart_actions.php', // SITE_URL needs to be available globally in JS
            type: 'POST',
            data: {
                action: 'add',
                product_id: productId,
                quantity: quantity,
                is_ajax: 1
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $thisButton.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> Added!');
                    // Update cart count in header (assuming a span with id="cart-count-badge")
                    $('#cart-count-badge').text(response.cart_count);

                    // Optional: Show a more prominent success message (e.g., a toast notification)
                    // For now, just changing button text is fine.
                    setTimeout(function() {
                        $thisButton.removeClass('btn-success').addClass('btn-primary').html('Add to Cart').prop('disabled', false);
                    }, 2000);

                } else {
                    // Handle error - e.g., show message from response.message
                    alert('Error: ' + response.message); // Simple alert for now
                    $thisButton.removeClass('btn-danger').addClass('btn-primary').html('Add to Cart').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error, xhr.responseText);
                alert('An error occurred while adding to cart. Please try again.');
                $thisButton.removeClass('btn-danger').addClass('btn-primary').html('Add to Cart').prop('disabled', false);
            },
            complete: function() {
                // Re-enable button if not handled by success/error specific logic for some reason
                // setTimeout is used above, so this might be redundant or could conflict.
                // For now, primary re-enable is in success/error.
            }
        });
    });

    // Make SITE_URL available to JS (this should be done in header.php ideally)
    // For now, if you haven't, add this in your templates/header.php:
    // <script>var SITE_URL = "<?php echo SITE_URL; ?>";</script>
    // This is crucial for the AJAX URL to work correctly.
});
