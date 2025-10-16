<?php
// This is a simple HTML email template.
// It will be populated with data from the order.
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation - <?php echo SITE_NAME; ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f4f4f4; padding: 10px; text-align: center; }
        .content { padding: 20px 0; }
        .footer { padding: 10px; text-align: center; font-size: 0.9em; color: #777; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; }
        th { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Order Confirmation</h2>
        </div>
        <div class="content">
            <p>Hello <?php echo htmlspecialchars($customer_name); ?>,</p>
            <p>Thank you for your order! Here are the details:</p>
            <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order['id']); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date("F j, Y", strtotime($order['order_date']))); ?></p>

            <h4>Order Summary</h4>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p style="text-align: right; margin-top: 20px;"><strong>Total:</strong> $<?php echo htmlspecialchars(number_format($order['total_amount'], 2)); ?></p>

            <h4>Shipping Address</h4>
            <p>
                <?php echo htmlspecialchars($shipping_address['address_line1']); ?><br>
                <?php if (!empty($shipping_address['address_line2'])) echo htmlspecialchars($shipping_address['address_line2']) . '<br>'; ?>
                <?php echo htmlspecialchars($shipping_address['city']); ?>, <?php echo htmlspecialchars($shipping_address['state']); ?> <?php echo htmlspecialchars($shipping_address['postal_code']); ?><br>
                <?php echo htmlspecialchars($shipping_address['country']); ?>
            </p>

            <p>We will notify you again once your order has shipped.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</body>
</html>