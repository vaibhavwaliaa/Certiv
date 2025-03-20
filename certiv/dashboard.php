<?php
session_start();
require_once 'db-config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Get user statistics
$total_certificates = 0;
$pending_certificates = 0;
$verified_certificates = 0;

if ($user_role === 'issuer') {
    // Get issuer ID
    $stmt = $conn->prepare("SELECT id FROM issuers WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $issuer = $result->fetch_assoc();
        $issuer_id = $issuer['id'];
        
        // Get certificate statistics
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM certificates WHERE issuer_id = ?");
        $stmt->bind_param("i", $issuer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_certificates = $result->fetch_assoc()['total'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as pending FROM certificates WHERE issuer_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $issuer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pending_certificates = $result->fetch_assoc()['pending'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as verified FROM certificates WHERE issuer_id = ? AND status = 'verified'");
        $stmt->bind_param("i", $issuer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $verified_certificates = $result->fetch_assoc()['verified'];
        
        // Get recent certificates
        $stmt = $conn->prepare("SELECT c.*, r.name as recipient_name 
                               FROM certificates c 
                               JOIN recipients r ON c.recipient_id = r.id 
                               WHERE c.issuer_id = ? 
                               ORDER BY c.issue_date DESC LIMIT 5");
        $stmt->bind_param("i", $issuer_id);
        $stmt->execute();
        $recent_certificates = $stmt->get_result();
    }
} elseif ($user_role === 'recipient') {
    // Get recipient ID
    $stmt = $conn->prepare("SELECT id FROM recipients WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $recipient = $result->fetch_assoc();
        $recipient_id = $recipient['id'];
        
        // Get certificate statistics
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM certificates WHERE recipient_id = ?");
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_certificates = $result->fetch_assoc()['total'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as pending FROM certificates WHERE recipient_id = ? AND status = 'pending'");
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pending_certificates = $result->fetch_assoc()['pending'];
        
        $stmt = $conn->prepare("SELECT COUNT(*) as verified FROM certificates WHERE recipient_id = ? AND status = 'verified'");
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $verified_certificates = $result->fetch_assoc()['verified'];
        
        // Get recent certificates
        $stmt = $conn->prepare("SELECT c.*, i.name as issuer_name, i.organization as issuer_organization 
                               FROM certificates c 
                               JOIN issuers i ON c.issuer_id = i.id 
                               WHERE c.recipient_id = ? 
                               ORDER BY c.issue_date DESC LIMIT 5");
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $recent_certificates = $stmt->get_result();
    }
} elseif ($user_role === 'verifier') {
    // Get verification statistics
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM verifications WHERE verifier_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_certificates = $result->fetch_assoc()['total'];
    
    // Get recent verifications
    $stmt = $conn->prepare("SELECT v.*, c.title, c.certificate_id, r.name as recipient_name, i.name as issuer_name 
                           FROM verifications v 
                           JOIN certificates c ON v.certificate_id = c.id 
                           JOIN recipients r ON c.recipient_id = r.id 
                           JOIN issuers i ON c.issuer_id = i.id 
                           WHERE v.verifier_id = ? 
                           ORDER BY v.verification_date DESC LIMIT 5");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $recent_verifications = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CertifyChain</title>
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
                    <li><a href="verify.php">Verify Certificate</a></li>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="dashboard">
        <div class="container">
            <div class="dashboard-header">
                <h2 class="dashboard-title">Welcome, <?php echo htmlspecialchars($user_name); ?></h2>
                <p class="dashboard-subtitle">
                    <?php 
                    if ($user_role === 'issuer') {
                        echo 'Issuer Dashboard';
                    } elseif ($user_role === 'recipient') {
                        echo 'Recipient Dashboard';
                    } else {
                        echo 'Verifier Dashboard';
                    }
                    ?>
                </p>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <div class="stat-value"><?php echo $total_certificates; ?></div>
                    <div class="stat-label">Total Certificates</div>
                </div>
                
                <?php if ($user_role !== 'verifier'): ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-value"><?php echo $pending_certificates; ?></div>
                    <div class="stat-label">Pending Certificates</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value"><?php echo $verified_certificates; ?></div>
                    <div class="stat-label">Verified Certificates</div>
                </div>
                <?php endif; ?>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-value"><?php echo date('d'); ?></div>
                    <div class="stat-label"><?php echo date('M Y'); ?></div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="dashboard-main">
                    <?php if ($user_role === 'issuer'): ?>
                        <h3 class="section-title"><i class="fas fa-list"></i> Recent Certificates Issued</h3>
                        
                        <?php if (isset($recent_certificates) && $recent_certificates->num_rows > 0): ?>
                            <?php while ($certificate = $recent_certificates->fetch_assoc()): ?>
                                <div class="certificate-card">
                                    <div class="certificate-header">
                                        <h3 class="certificate-title"><?php echo htmlspecialchars($certificate['title']); ?></h3>
                                        <span class="certificate-date">Issued on: <?php echo date('M d, Y', strtotime($certificate['issue_date'])); ?></span>
                                    </div>
                                    <div class="certificate-body">
                                        <div class="certificate-info">
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Recipient</div>
                                                <div class="certificate-info-value"><?php echo htmlspecialchars($certificate['recipient_name']); ?></div>
                                            </div>
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Certificate ID</div>
                                                <div class="certificate-info-value"><?php echo htmlspecialchars($certificate['certificate_id']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="certificate-footer">
                                        <span class="certificate-status <?php echo $certificate['status'] === 'verified' ? 'status-valid' : 'status-pending'; ?>">
                                            <?php echo ucfirst($certificate['status']); ?>
                                        </span>
                                        <div class="certificate-actions">
                                            <a href="view-certificate.php?id=<?php echo $certificate['id']; ?>" class="btn-icon" title="View Certificate">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit-certificate.php?id=<?php echo $certificate['id']; ?>" class="btn-icon" title="Edit Certificate">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <div class="text-center mt-4">
                                <a href="all-certificates.php" class="btn btn-secondary">View All Certificates</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> You haven't issued any certificates yet.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="issue.php" class="btn btn-primary">Issue New Certificate</a>
                        </div>
                    <?php elseif ($user_role === 'recipient'): ?>
                        <h3 class="section-title"><i class="fas fa-list"></i> Your Certificates</h3>
                        
                        <?php if (isset($recent_certificates) && $recent_certificates->num_rows > 0): ?>
                            <?php while ($certificate = $recent_certificates->fetch_assoc()): ?>
                                <div class="certificate-card">
                                    <div class="certificate-header">
                                        <h3 class="certificate-title"><?php echo htmlspecialchars($certificate['title']); ?></h3>
                                        <span class="certificate-date">Issued on: <?php echo date('M d, Y', strtotime($certificate['issue_date'])); ?></span>
                                    </div>
                                    <div class="certificate-body">
                                        <div class="certificate-info">
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Issuer</div>
                                                <div class="certificate-info-value"><?php echo htmlspecialchars($certificate['issuer_name']); ?></div>
                                            </div>
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Organization</div>
                                                <div class="certificate-info-value"><?php echo htmlspecialchars($certificate['issuer_organization']); ?></div>
                                            </div>
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Certificate ID</div>
                                                <div class="certificate-info-value"><?php echo htmlspecialchars($certificate['certificate_id']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="certificate-footer">
                                        <span class="certificate-status <?php echo $certificate['status'] === 'verified' ? 'status-valid' : 'status-pending'; ?>">
                                            <?php echo ucfirst($certificate['status']); ?>
                                        </span>
                                        <div class="certificate-actions">
                                            <a href="view-certificate.php?id=<?php echo $certificate['id']; ?>" class="btn-icon" title="View Certificate">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="download.php?id=<?php echo $certificate['id']; ?>" class="btn-icon" title="Download Certificate">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <a href="share.php?id=<?php echo $certificate['id']; ?>" class="btn-icon" title="Share Certificate">
                                                <i class="fas fa-share-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <div class="text-center mt-4">
                                <a href="all-certificates.php" class="btn btn-secondary">View All Certificates</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> You don't have any certificates yet.
                            </div>
                        <?php endif; ?>
                    <?php elseif ($user_role === 'verifier'): ?>
                        <h3 class="section-title"><i class="fas fa-list"></i> Recent Verifications</h3>
                        
                        <?php if (isset($recent_verifications) && $recent_verifications->num_rows > 0): ?>
                            <?php while ($verification = $recent_verifications->fetch_assoc()): ?>
                                <div class="certificate-card">
                                    <div class="certificate-header">
                                        <h3 class="certificate-title"><?php echo htmlspecialchars($verification['title']); ?></h3>
                                        <span class="certificate-date">Verified on: <?php echo date('M d, Y', strtotime($verification['verification_date'])); ?></span>
                                    </div>
                                    <div class="certificate-body">
                                        <div class="certificate-info">
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Recipient</div>
                                                <div class="certificate-info-value"><?php echo htmlspecialchars($verification['recipient_name']); ?></div>
                                            </div>
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Issuer</div>
                                                <div class="certificate-info-value"><?php echo htmlspecialchars($verification['issuer_name']); ?></div>
                                            </div>
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Certificate ID</div>
                                                <div class="certificate-info-value"><?php echo htmlspecialchars($verification['certificate_id']); ?></div>
                                            </div>
                                            <div class="certificate-info-item">
                                                <div class="certificate-info-label">Result</div>
                                                <div class="certificate-info-value"><?php echo $verification['result'] === 'valid' ? 'Valid' : 'Invalid'; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="certificate-footer">
                                        <span class="certificate-status <?php echo $verification['result'] === 'valid' ? 'status-valid' : 'status-pending'; ?>">
                                            <?php echo ucfirst($verification['result']); ?>
                                        </span>
                                        <div class="certificate-actions">
                                            <a href="view-verification.php?id=<?php echo $verification['id']; ?>" class="btn-icon" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <div class="text-center mt-4">
                                <a href="all-verifications.php" class="btn btn-secondary">View All Verifications</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> You haven't verified any certificates yet.
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <a href="verify.php" class="btn btn-primary">Verify Certificate</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-sidebar">
                    <h3 class="section-title"><i class="fas fa-user"></i> Profile</h3>
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="profile-name"><?php echo htmlspecialchars($user_name); ?></div>
                        <div class="profile-role"><?php echo ucfirst($user_role); ?></div>
                        <div class="profile-actions mt-3">
                            <a href="profile.php" class="btn btn-secondary btn-sm">Edit Profile</a>
                        </div>
                    </div>
                    
                    <h3 class="section-title mt-4"><i class="fas fa-bell"></i> Notifications</h3>
                    <div class="notifications">
                        <div class="notification-item">
                            <div class="notification-icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-text">Welcome to CertifyChain!</div>
                                <div class="notification-time">Just now</div>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="section-title mt-4"><i class="fas fa-link"></i> Quick Links</h3>
                    <div class="quick-links">
                        <a href="index.php" class="quick-link">
                            <i class="fas fa-home"></i> Home
                        </a>
                        <a href="verify.php" class="quick-link">
                            <i class="fas fa-search"></i> Verify Certificate
                        </a>
                        <?php if ($user_role === 'issuer'): ?>
                            <a href="issue.php" class="quick-link">
                                <i class="fas fa-plus-circle"></i> Issue Certificate
                            </a>
                        <?php endif; ?>
                        <a href="help.php" class="quick-link">
                            <i class="fas fa-question-circle"></i> Help & Support
                        </a>
                    </div>
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

