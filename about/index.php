<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../templates/header.php';

$pageTitle = 'About Us';
?>

<div class="row">
    <div class="col-12">
        <h1>About <?php echo SITE_NAME; ?></h1>
        <p class="lead">Learn more about our mission, our values, and the team dedicated to enhancing your well-being.</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <h2>Our Mission</h2>
        <p>At <?php echo SITE_NAME; ?>, our mission is to provide high-quality, effective wellness products that empower individuals to live healthier, more balanced lives. We believe in the power of natural and therapeutic solutions to improve physical and mental well-being. We are committed to sourcing the best materials, ensuring product excellence, and providing our customers with the education and support they need on their wellness journey.</p>

        <h2>Our Values</h2>
        <ul>
            <li><strong>Quality:</strong> We never compromise on the quality of our products. Every item is carefully selected and tested to meet our high standards.</li>
            <li><strong>Integrity:</strong> We operate with transparency and honesty in all our interactions with customers, partners, and the community.</li>
            <li><strong>Education:</strong> We strive to be a trusted source of information, helping our customers make informed decisions about their health.</li>
            <li><strong>Customer Focus:</strong> Your satisfaction and well-being are at the core of everything we do. We are here to support you.</li>
        </ul>
    </div>
    <div class="col-md-6">
        <img src="https://via.placeholder.com/500x350.png?text=Our+Team" class="img-fluid rounded" alt="A placeholder image representing the Wellness Wonders team">
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <h2>Meet the Team</h2>
        <p>We are a small, passionate team of wellness advocates, product experts, and customer service professionals. While we may not show our faces here, we are united by a shared dedication to our mission and to you, our valued customer.</p>
        <!-- In a real site, you might have team member bios here -->
    </div>
</div>


<?php
require_once __DIR__ . '/../templates/footer.php';
?>
