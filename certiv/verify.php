<?php
session_start();
require_once 'db-config.php';

$verification_result = null;
$certificate_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['certificate_id'])) {
    $certificate_id = trim($_POST['certificate_id']);
    
    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT c.*, r.name as recipient_name, r.email as recipient_email, 
                           i.name as issuer_name, i.organization as issuer_organization 
                           FROM certificates c 
                           JOIN recipients r ON c.recipient_id = r.id 
                           JOIN issuers i ON c.issuer_id = i.id 
                           WHERE c.certificate_id = ?");
    $stmt->bind_param("s", $certificate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $certificate_data = $result->fetch_assoc();
        
        // Verify the certificate hash
        $original_hash = $certificate_data['certificate_hash'];
        $verification_data = $certificate_data['title'] . $certificate_data['description'] . 
                            $certificate_data['issue_date'] . $certificate_data['recipient_id'] . 
                            $certificate_data['issuer_id'];
        $verification_hash = hash('sha256', $verification_data);
        
        if ($original_hash === $verification_hash) {
            $verification_result = 'valid';
        } else {
            $verification_result = 'invalid';
        }
    } else {
        $verification_result = 'not_found';
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Certificate - CertifyChain</title>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="verify.php" class="active">Verify Certificate</a></li>
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

    <section class="verify-section">
        <div class="container">
            <div class="form-container">
                <h2>Verify Certificate</h2>
                <p class="text-center mb-4">Enter the certificate ID to verify its authenticity.</p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="certificate_id">Certificate ID</label>
                        <input type="text" id="certificate_id" name="certificate_id" class="form-control" required placeholder="Enter certificate ID">
                    </div>
                    <button type="submit" class="btn btn-primary form-btn">Verify Certificate</button>
                </form>
                
                <?php if($verification_result === 'valid'): ?>
                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle"></i> This certificate is valid and authentic.
                    </div>
                    
                    <div class="certificate-card mt-4">
                        <div class="certificate-header">
                            <h3 class="certificate-title"><?php echo htmlspecialchars($certificate_data['title']); ?></h3>
                            <span class="certificate-date">Issued on: <?php echo date('M d, Y', strtotime($certificate_data['issue_date'])); ?></span>
                        </div>
                        <div class="certificate-body">
                            <div class="certificate-info">
                                <div class="certificate-info-item">
                                    <div class="certificate-info-label">Recipient</div>
                                    <div class="certificate-info-value"><?php echo htmlspecialchars($certificate_data['recipient_name']); ?></div>
                                </div>
                                <div class="certificate-info-item">
                                    <div class="certificate-info-label">Issuer</div>
                                    <div class="certificate-info-value"><?php echo htmlspecialchars($certificate_data['issuer_name']); ?></div>
                                </div>
                                <div class="certificate-info-item">
                                    <div class="certificate-info-label">Organization</div>
                                    <div class="certificate-info-value"><?php echo htmlspecialchars($certificate_data['issuer_organization']); ?></div>
                                </div>
                                <div class="certificate-info-item">
                                    <div class="certificate-info-label">Certificate ID</div>
                                    <div class="certificate-info-value"><?php echo htmlspecialchars($certificate_data['certificate_id']); ?></div>
                                </div>
                            </div>
                            <div class="certificate-description mt-3">
                                <div class="certificate-info-label">Description</div>
                                <div class="certificate-info-value"><?php echo htmlspecialchars($certificate_data['description']); ?></div>
                            </div>
                        </div>
                        <div class="certificate-footer">
                            <span class="certificate-status status-valid">Valid</span>
                            <div class="certificate-actions">
                                <a href="download.php?id=<?php echo $certificate_data['id']; ?>" class="btn-icon" title="Download Certificate">
                                    <i class="fas fa-download"></i>
                                </a>
                                <a href="share.php?id=<?php echo $certificate_data['id']; ?>" class="btn-icon" title="Share Certificate">
                                    <i class="fas fa-share-alt"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php elseif($verification_result === 'invalid'): ?>
                    <div class="alert alert-error mt-4">
                        <i class="fas fa-times-circle"></i> This certificate has been tampered with or is invalid.
                    </div>
                <?php elseif($verification_result === 'not_found'): ?>
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle"></i> No certificate found with this ID.
                    </div>
                <?php endif; ?>
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

