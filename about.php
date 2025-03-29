<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f8f8f8; 
            margin: 0; 
            padding: 0; 
            line-height: 1.6;
        }
        .container { 
            max-width: 1200px; 
            margin: auto; 
            padding: 20px; 
        }
        .header { 
            text-align: center; 
            padding: 20px; 
            background: #4B5945; 
            color: white; 
            font-size: 24px; 
        }
        .nav { 
            text-align: center; 
            padding: 10px; 
            background: #66785F; 
            display: flex; 
            justify-content: center; 
            gap: 15px; 
        }
        .nav a { 
            color: white; 
            text-decoration: none; 
            padding: 10px 15px; 
            font-size: 18px; 
        }
        .nav a:hover { 
            background: #B2C9AD; 
            border-radius: 5px; 
        }
        .account-right {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: flex-end;
            padding: 10px 20px;
            background: #66785F;
        }
        .account-right img {
            width: 20px;
        }
        .account-right a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }
        
        /* Hero Image Styles */
        .hero-container {
            position: relative;
            width: 100%;
            height: 60vh;
            margin-bottom: 40px;
        }
        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-text {
            text-align: center;
            color: white;
            padding: 20px;
            max-width: 800px;
        }
        .hero-text h1 {
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .hero-text p {
            font-size: 24px;
            margin: 0 auto;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }
        
        /* About Us Styles */
        .section {
            background: white;
            padding: 40px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .section h2 {
            color: #4B5945;
            font-size: 32px;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
        }
        .section p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .team-member {
            text-align: center;
        }
        .team-member img {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 5px solid #B2C9AD;
        }
        .team-member h3 {
            font-size: 22px;
            margin-bottom: 5px;
            color: #4B5945;
        }
        .team-member .position {
            font-weight: bold;
            color: #66785F;
            margin-bottom: 15px;
        }
        .mission-statement {
            background: #f0f7ee;
            padding: 30px;
            border-radius: 5px;
            margin: 30px 0;
            text-align: center;
            font-style: italic;
            font-size: 18px;
        }
        .values-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .value-item {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            border-left: 4px solid #4B5945;
        }
        .value-item h3 {
            color: #4B5945;
            margin-top: 0;
        }
        
        /* Footer Styles */
        .footer {
            background-color: #f5f5f5;
            padding: 40px 0;
            font-family: Arial, sans-serif;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            padding: 0 20px;
        }
        .footer-copyright {
            width: 40%;
            padding-right: 30px;
        }
        .footer-copyright p {
            color: #666;
            font-size: 14px;
            margin: 0;
            line-height: 1.6;
        }
        .footer-column {
            width: 30%;
        }
        .footer h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer li {
            margin-bottom: 8px;
        }
        .footer a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        .footer a:hover {
            color: #4B5945;
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .hero-text h1 {
                font-size: 36px;
            }
            .hero-text p {
                font-size: 18px;
            }
            .team-grid {
                grid-template-columns: 1fr;
            }
            .footer-container {
                flex-direction: column;
            }
            .footer-copyright,
            .footer-column {
                width: 100%;
                margin-bottom: 25px;
                padding-right: 0;
            }
            .account-right {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<header class="header">
        <i class="fas fa-headset"></i> About Us
    </header>
    
    <nav class="nav">
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
        <a href="living_room.php"><i class="fas fa-couch"></i> Products</a>
        <a href="account.php"><i class="fas fa-user"></i> Account</a>
    </nav>

    <!-- Hero Image Section -->
    <div class="hero-container">
        <img src="img/about.avif" alt="Furnish Haven Team" class="hero-image">
        <div class="hero-overlay">
            <div class="hero-text">
                <h1>Our Story</h1>
                <p>Bringing quality home furnishings to Filipino families since 2010</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2>Our Beginning</h2>
            <p>Furnish Haven was founded in 2010 with a simple mission: to make beautiful, high-quality furniture accessible to every Filipino home. What started as a small workshop in Quezon City has grown into one of the country's most trusted home furnishing brands.</p>
            
            <p>Our founder, Maria Santos, began by crafting custom furniture pieces for friends and family. Word quickly spread about the exceptional quality and craftsmanship, and soon Furnish Haven was serving customers across Luzon. Today, we deliver to all regions of the Philippines, with showrooms in Manila, Cebu, and Davao.</p>
            
            <div class="mission-statement">
                "We believe every Filipino deserves a home that's both beautiful and functional - a space that reflects their personality and meets their family's needs."
            </div>
        </div>
        
        <div class="section">
            <h2>Our Mission</h2>
            <p>At Furnish Haven, we're committed to:</p>
            
            <div class="values-list">
                <div class="value-item">
                    <h3>Quality Craftsmanship</h3>
                    <p>Each piece is built to last using premium materials and time-tested techniques.</p>
                </div>
                <div class="value-item">
                    <h3>Affordable Design</h3>
                    <p>Beautiful furniture shouldn't break the bank. We keep prices fair without compromising quality.</p>
                </div>
                <div class="value-item">
                    <h3>Sustainable Practices</h3>
                    <p>We source materials responsibly and minimize waste in our production process.</p>
                </div>
                <div class="value-item">
                    <h3>Filipino Pride</h3>
                    <p>We support local artisans and celebrate Filipino design traditions.</p>
                </div>
            </div>
        </div>
        <div class="section">
    <h2>Our Promise</h2>
    <p>When you shop with Furnish Haven, you're not just buying furniture - you're investing in:</p>
    <ul style="list-style-type: none; padding: 0; max-width: 800px; margin: 0 auto;">
        <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; align-items: center;">
            <span style="color: #4B5945; font-weight: bold; margin-right: 10px;">✓</span>
            <span>100% satisfaction guarantee on all products</span>
        </li>
        <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; align-items: center;">
            <span style="color: #4B5945; font-weight: bold; margin-right: 10px;">✓</span>
            <span>Affordable ₱500 flat-rate shipping nationwide</span>
        </li>
        <li style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; align-items: center;">
            <span style="color: #4B5945; font-weight: bold; margin-right: 10px;">✓</span>
            <span>5-year warranty on all furniture frames</span>
        </li>
        <li style="padding: 10px 0; display: flex; align-items: center;">
            <span style="color: #4B5945; font-weight: bold; margin-right: 10px;">✓</span>
            <span>Dedicated customer support team available 7 days a week</span>
        </li>
    </ul>
</div>
    </div>

    <!-- Footer Section with Working Links -->
    <footer class="footer">
    <div class="footer-container">
        <div class="footer-copyright">
        <p>
                © Created by Cassandra Arcilla<br>
                Ryna David<br>
                Robyn Gonzales<br>
                Adrian Sarmiento<br>
                All photos used in this website<br>
                are intended for placeholders. Copyright<br>
                reserved to their respective owners.
            </p>
        </div>
        
        <div class="footer-column">
            <h3>Products</h3>
            <ul>
                <li><a href="living_room.php">Living Room</a></li>
                <li><a href="bedroom.php">Bedroom</a></li>
                <li><a href="dining_room.php">Dining Room</a></li>
            </ul>
        </div>
        
        <div class="footer-column">
            <h3>Contact Us</h3>
            <ul>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><a href="mailto:support@Furnish Haven.com">support@furnish_haven.com</a></li>
                <li><a href="mailto:business@Furnish Haven.com">business@furnish_haven.com</a></li>
                <li><a href="tel:04599999999">(045) 9999-9999</a></li>
            </ul>
        </div>
    </div>
</footer>
</body>
</html>