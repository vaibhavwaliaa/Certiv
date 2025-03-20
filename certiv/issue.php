<?php
session_start();
require_once 'db-config.php';

// Check if user is logged in and is an issuer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'issuer') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get issuer ID
$stmt = $conn->prepare("SELECT id FROM issuers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Create issuer profile if it doesn't exist
    header('Location: create-issuer-profile.php');
    exit;
}

$issuer = $result->fetch_assoc();
$issuer_id = $issuer['id'];

// Get recipients for dropdown
$stmt = $conn->prepare("SELECT id, name, email FROM recipients");
$stmt->execute();
$recipients = $stmt->get_result();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $recipient_id = $_POST['recipient_id'];
    $issue_date = date('Y-m-d');
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    
    // Validate input
    if (empty($title) || empty($description) || empty($recipient_id)) {
        $error = 'Please fill in all required fields';
    } else {
        // Generate unique certificate ID
        $certificate_id = 'CERT-' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        // Generate certificate hash
        $verification_data = $title . $description . $issue_date . $recipient_id . $issuer_id;
        $certificate_hash = hash('sha256', $verification_data);
            
        // Insert certificate
        $stmt = $conn->prepare("INSERT INTO certificates (certificate_id, title, description, issue_date, expiry_date, recipient_id, issuer_id, certificate_hash, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'verified')");
        $stmt->bind_param("sssssiis", $certificate_id, $title, $description, $issue_date, $expiry_date, $recipient_id, $issuer_id, $certificate_hash);
        
        if ($stmt->execute()) {
            $success = 'Certificate issued successfully!';
        } else {
            $error = 'Failed to issue certificate. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Certificate - CertifyChain</title>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="issue-section">
        <div class="container">
            <div class="form-container">
                <h2>Issue New Certificate</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="title">Certificate Title</label>
                        <input type="text" id="title" name="title" class="form-control" required placeholder="e.g., Bachelor of Computer Science">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required placeholder="Describe the achievement or qualification"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="recipient_id">Recipient</label>
                        <select id="recipient_id" name="recipient_id" class="form-control" required>
                            <option value="">Select Recipient</option>
                            <?php while ($recipient = $recipients->fetch_assoc()): ?>
                                <option value="<?php echo $recipient['id']; ?>">
                                    <?php echo htmlspecialchars($recipient['name'] . ' (' . $recipient['email'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date (Optional)</label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control">
                        <div class="form-text">Leave blank if the certificate does not expire</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary form-btn">Issue Certificate</button>
                </form>
                
                <div class="form-footer">
                    <p>Can't find the recipient? <a href="add-recipient.php">Add New Recipient</a></p>
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

