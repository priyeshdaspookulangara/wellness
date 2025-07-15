<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1>About Us</h1>
        <p>Welcome to <?php echo SITE_NAME; ?>, your trusted source for high-quality wellness products. We are dedicated to providing you with the best products to support your journey towards a healthier and more balanced lifestyle.</p>

        <h2>Our Mission</h2>
        <p>Our mission is to empower individuals to take control of their health and well-being by offering a curated selection of products that are safe, effective, and backed by science. We believe in the power of nature and technology to enhance our lives, and we are committed to bringing you innovative solutions that make a real difference.</p>

        <h2>Our Story</h2>
        <p>Founded in [Year], <?php echo SITE_NAME; ?> started with a simple idea: to make wellness accessible to everyone. Our founders, a team of health enthusiasts and experts, were passionate about finding natural and effective ways to improve their own health. They discovered the incredible benefits of magnetic therapy, natural supplements, and other wellness products, and they wanted to share these discoveries with the world.</p>
        <p>From our humble beginnings, we have grown into a thriving online store, serving thousands of customers worldwide. We are proud of the community we have built and the positive impact we have had on people's lives.</p>

        <h2>Our Commitment to Quality</h2>
        <p>We understand that when it comes to your health, only the best will do. That's why we are committed to sourcing the highest quality products from reputable manufacturers. Every product we offer is carefully selected and rigorously tested to ensure it meets our strict standards of quality, safety, and efficacy.</p>

        <h2>Why Choose Us?</h2>
        <ul>
            <li><strong>Premium Quality:</strong> We offer only the best products that we trust and use ourselves.</li>
            <li><strong>Expert Knowledge:</strong> Our team has the expertise to help you find the right products for your needs.</li>
            <li><strong>Customer-Centric:</strong> Your satisfaction is our top priority. We are always here to help with any questions or concerns.</li>
            <li><strong>Educational Resources:</strong> We believe in empowering our customers with knowledge. Our blog and resources are here to help you learn more about wellness.</li>
        </ul>

        <h2>Meet the Team</h2>
        <p>We are a small but dedicated team of professionals who are passionate about health and wellness. We are here to support you on your wellness journey.</p>
        <!-- Optional: Add team member profiles here -->

        <h2>Contact Us</h2>
        <p>We love to hear from our customers! If you have any questions, feedback, or just want to say hello, please don't hesitate to <a href="<?php echo SITE_URL; ?>/contact">contact us</a>.</p>
    </div>
</div>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
