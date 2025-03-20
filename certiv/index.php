<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CertifyChain - Certificate Validation System</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <i class="fas fa-certificate"></i>
                <h1>CertifyChain</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="verify.php">Verify Certificate</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h2>Secure Certificate Validation</h2>
                <p>Issue and verify certificates with our advanced validation system</p>
                <div class="hero-buttons">
                    <a href="verify.php" class="btn btn-primary">Verify Certificate</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="issue.php" class="btn btn-secondary">Issue Certificate</a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-secondary">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-image">
                <img src="/placeholder.svg?height=400&width=400" alt="Certificate illustration">
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2>Key Features</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <i class="fas fa-lock"></i>
                    <h3>Immutable Certificates</h3>
                    <p>Once issued, certificates cannot be tampered with, ensuring authenticity.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-search"></i>
                    <h3>Instant Verification</h3>
                    <p>Verify certificates instantly using a unique hash or QR code.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Secure Storage</h3>
                    <p>All certificates are securely stored with advanced encryption.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h3>Multiple User Roles</h3>
                    <p>Different access levels for issuers, recipients, and verifiers.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="how-it-works">
        <div class="container">
            <h2>How It Works</h2>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Issue Certificate</h3>
                    <p>Institutions create and issue digital certificates with unique identifiers.</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Secure Storage</h3>
                    <p>Certificates are hashed and stored securely in our database.</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Share Certificate</h3>
                    <p>Recipients receive a unique link or QR code to share their certificates.</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Verify Authenticity</h3>
                    <p>Third parties can instantly verify the authenticity of any certificate.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-certificate"></i>
                    <h2>CertifyChain</h2>
                    <p>Secure Certificate Validation System</p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="verify.php">Verify Certificate</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-envelope"></i> info@certifychain.com</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2023 CertifyChain. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>

