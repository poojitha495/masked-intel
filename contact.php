<?php
require_once 'config.php';

// Initialize variables
$success = $error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'organization' => 'Organization Name',
            'inquiry_type' => 'Type of Inquiry',
            'message' => 'Message'
        ];

        $errors = [];
        foreach ($required_fields as $field => $label) {
            if (empty($_POST[$field])) {
                $errors[] = "$label is required";
            }
        }

        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }

        // Connect to database
        $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$db) {
            throw new Exception("Database connection failed: " . mysqli_connect_error());
        }

        // Create messages table if it doesn't exist
        $table_check = mysqli_query($db, "SHOW TABLES LIKE 'messages'");
        if (mysqli_num_rows($table_check) === 0) {
            $create_table_query = "CREATE TABLE messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50),
                organization VARCHAR(255),
                inquiry_type VARCHAR(100) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (email),
                INDEX (created_at)
            )";
            
            if (!mysqli_query($db, $create_table_query)) {
                throw new Exception("Failed to create messages table: " . mysqli_error($db));
            }
        }

        // Prepare and insert the message
        $name = $_POST['first_name'] . ' ' . $_POST['last_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $organization = $_POST['organization'];
        $inquiry_type = $_POST['inquiry_type'];
        $message = $_POST['message'];

        $query = "INSERT INTO messages (name, email, phone, organization, inquiry_type, message) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $db->error);
        }

        $stmt->bind_param("ssssss", $name, $email, $phone, $organization, $inquiry_type, $message);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to send message: " . $stmt->error);
        }

        $success = "Your message has been sent successfully!";
        mysqli_close($db);

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Masked Intel - Contact Us</title>
  <link rel="stylesheet" href="styles.css" />
  <script src="script.js" defer></script> <!-- Add this line -->
  <style>
    .contact-container {
      max-width: 1000px;
      margin: 3rem auto;
      padding: 3rem;
      background: linear-gradient(145deg, rgba(255, 255, 255, 0.05) 0%, rgba(255, 255, 255, 0.1) 100%);
      backdrop-filter: blur(15px);
      border-radius: 20px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      border: 3px solid rgba(255, 255, 255, 0.2);
      text-align: center;
    }

    .page-title {
      text-align: center;
      color: #fff;
      margin-bottom: 3rem;
      font-size: 2.5rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 2px;
      position: relative;
    }

    .page-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 3px;
      background: linear-gradient(90deg, #4a90e2, #357abd);
      border-radius: 2px;
    }

    .contact-form {
      display: grid;
      gap: 2rem;
      margin-top: 2rem;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 0.8rem;
      align-items: center;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 2rem;
      width: 100%;
    }

    label {
      color: #fff;
      font-weight: 600;
      font-size: 1.1rem;
      letter-spacing: 0.5px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
      background: linear-gradient(90deg, #ffffff, #e6e6e6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 0.5rem;
      text-align: center;
    }

    input, select, textarea {
      padding: 1rem 1.2rem;
      border: 3px solid rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      background: rgba(20, 30, 50, 0.8); /* Add a bluish tint */
      color: #fff;
      font-size: 1rem;
      transition: all 0.3s ease;
      width: 100%;
      text-align: center;
    }

    /* Placeholder styling for different browsers */
    input::placeholder,
    textarea::placeholder,
    select::placeholder {
      color: rgba(255, 255, 255, 0.7);
      font-size: 1rem;
      opacity: 1;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* For Firefox */
    input::-moz-placeholder,
    textarea::-moz-placeholder {
      color: rgba(255, 255, 255, 0.7);
      opacity: 1;
    }

    /* For Edge */
    input:-ms-input-placeholder,
    textarea:-ms-input-placeholder {
      color: rgba(255, 255, 255, 0.7);
      opacity: 1;
    }

    /* For Internet Explorer */
    input::-ms-input-placeholder,
    textarea::-ms-input-placeholder {
      color: rgba(255, 255, 255, 0.7);
      opacity: 1;
    }

    select option {
      background: rgba(20, 30, 50, 1); /* Match the bluish background */
      color: #fff;
    }

    input:hover, select:hover, textarea:hover {
      border-color: rgba(74, 144, 226, 0.5); /* Add a subtle blue border on hover */
      background: rgba(20, 30, 50, 0.9); /* Slightly darken the background on hover */
    }

    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: #4a90e2; /* Bright blue border on focus */
      border-width: 3px;
      box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.2); /* Add a blue glow */
      background: rgba(20, 30, 50, 1); /* Darker bluish background on focus */
    }

    textarea {
      min-height: 180px;
      resize: vertical;
      line-height: 1.6;
      text-align: center;
    }

    .submit-btn {
      background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
      color: white;
      padding: 1.2rem 2.5rem;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 1.1rem;
      font-weight: 600;
      letter-spacing: 1px;
      text-transform: uppercase;
      transition: all 0.3s ease;
      width: 100%;
      max-width: 300px;
      margin: 1rem auto;
      display: block;
      text-align: center;
    }

    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
    }

    .submit-btn:active {
      transform: translateY(0);
    }

    .contact-info {
      margin: 3rem auto; /* Center horizontally */
      padding: 2rem;
      border-top: 3px solid rgba(255, 255, 255, 0.2);
      text-align: center;
      color: #fff;
      background: rgba(0, 0, 0, 0.2);
      border-radius: 15px;
      border: 3px solid rgba(255, 255, 255, 0.2);
      max-width: 600px; /* Limit the width for better centering */
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); /* Add a subtle shadow */
    }

    .contact-info h3 {
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      color: #4a90e2;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .contact-info p {
      font-size: 1.1rem;
      margin: 1rem 0;
      color: rgba(255, 255, 255, 0.9);
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .required {
      color: #ff6b6b;
      margin-left: 4px;
    }

    @media (max-width: 768px) {
      .contact-container {
        margin: 2rem;
        padding: 2rem;
      }

      .form-row {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }

      input, select, textarea {
        width: 100%;
      }
    }

    /* Add animation for form elements */
    .form-group {
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInUp 0.5s forwards;
    }

    @keyframes fadeInUp {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .form-group:nth-child(1) { animation-delay: 0.1s; }
    .form-group:nth-child(2) { animation-delay: 0.2s; }
    .form-group:nth-child(3) { animation-delay: 0.3s; }
    .form-group:nth-child(4) { animation-delay: 0.4s; }
    .form-group:nth-child(5) { animation-delay: 0.5s; }
    .form-group:nth-child(6) { animation-delay: 0.6s; }
  </style>
</head>
<body>
  <div class="geometric-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
  </div>

  <header class="navbar">
    <div class="logo">
      <div class="logo-wrapper">
        <img src="logo.png" alt="Masked Intel Logo Icon" class="logo-icon" />
      </div>
      <span>MASKED INTEL</span>
    </div>

    <nav>
      <a href="about.php">About</a>
      <a href="features.php">Features</a>
      <a href="contact.php">Contact</a>
      <button class="nav-btn" onclick="redirectToLogin()">Admin Login</button>
    </nav>
  </header>

  <main>
    <div class="contact-container">
      <h1 class="page-title">Get in Touch</h1>
      
      <?php if ($success): ?>
        <div class="alert alert-success">
          <?php echo $success; ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form class="contact-form" method="POST" action="contact.php">
        <div class="form-row">
          <div class="form-group">
            <label for="first_name">First Name <span class="required">*</span></label>
            <input type="text" id="first_name" name="first_name" required placeholder="Your First Name">
          </div>
          <div class="form-group">
            <label for="last_name">Last Name <span class="required">*</span></label>
            <input type="text" id="last_name" name="last_name" required placeholder="Your Last Name">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" id="email" name="email" required placeholder="Your Email Address">
          </div>
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="Your Phone Number">
          </div>
        </div>

        <div class="form-group">
          <label for="organization">Organization Name</label>
          <input type="text" id="organization" name="organization" placeholder="Your Organization Name">
        </div>

        <div class="form-group">
          <label for="inquiry_type">Type of Inquiry <span class="required">*</span></label>
          <select id="inquiry_type" name="inquiry_type" required>
            <option value="">Select an option</option>
            <option value="facial_recognition">Facial Recognition Implementation</option>
            <option value="crowd_analytics">Crowd Analytics Solutions</option>
            <option value="demo">Request Demo</option>
            <option value="support">Technical Support</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="form-group">
          <label for="message">Message <span class="required">*</span></label>
          <textarea id="message" name="message" placeholder="Please describe your requirements or questions in detail... We're here to help!" required></textarea>
        </div>

        <button type="submit" class="submit-btn">Send Message</button>
      </form>

      <div class="contact-info">
        <h3>Other Ways to Reach Us</h3>
        <p>📧 Email: n210495@rguktn.ac.in</p>
        <p>📧 Email: n210494@rguktn.ac.in</p>
        <p>📞 Phone: 9347871250</p>
        <p>📍 Location: RGUKT NUZVID, Nuzvid, Eluru, Andhra Pradesh</p>
      </div>
    </div>
  </main>

  <section class="brand-feature">
    <div class="brand-logo">
      <img src="logo.png" alt="Masked Intel Badge Logo" />
      <p>Trusted AI Security</p>
    </div>
  </section>

  <script>
    function redirectToLogin() {
      window.location.href = "login.php";
    }
  </script>
</body>
</html>
