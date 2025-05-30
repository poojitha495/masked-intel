<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if user is admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    redirect('admin.php');
}

function findMatchesInDatabase($age_range, $gender) {
    $matches = [];
    $csv_file = fopen('Personal Information.csv', 'r');
    
    // Skip header
    fgetcsv($csv_file, 0, ';');
    
    // Extract numeric age from range (e.g., "25-30 years" -> 27)
    $age_parts = explode('-', str_replace(' years', '', $age_range));
    $target_age = ($age_parts[0] + $age_parts[1]) / 2;
    
    // Convert gender to single letter
    $gender_letter = strtoupper(substr($gender, 0, 1));
    
    while (($row = fgetcsv($csv_file, 0, ';')) !== FALSE) {
        $db_age = intval($row[1]);
        $db_gender = $row[2];
        
        // Match if age is within range (±5 years) and gender matches
        if (abs($db_age - $target_age) <= 5 && $db_gender === $gender_letter) {
            $matches[] = [
                'name' => $row[0],
                'age' => $db_age,
                'gender' => $db_gender,
                'year' => $row[3]
            ];
        }
    }
    
    fclose($csv_file);
    return $matches;
}

// Clear previous analysis results on page load if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['analysis_result']);
    $analysis_result = null;
} else {
    // Handle file upload
    $analysis_result = null;
    if (isset($_FILES['face_image'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = $target_dir . basename($_FILES["face_image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Check if image file is actual image
        $check = getimagesize($_FILES["face_image"]["tmp_name"]);
        if($check !== false) {
            $uploadOk = 1;
        } else {
            $error = "File is not an image.";
            $uploadOk = 0;
        }
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
            $error = "Sorry, only JPG, JPEG & PNG files are allowed.";
            $uploadOk = 0;
        }
        
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["face_image"]["tmp_name"], $target_file)) {
                // Simulate face analysis result
                $analysis_result = [
                    'timestamp' => date('F d, Y \a\t h:i:s A'),
                    'image' => $target_file,
                    'attributes' => [
                        'Authenticity' => 'Real',
                        'Age Range' => '25-30 years',
                        'Gender' => 'Male',
                        'Emotion' => 'Neutral',
                        'Facial Hair' => 'No Beard',
                        'Eyewear' => 'No Glasses',
                        'Head Position' => 'Frontal',
                        'Eye Direction' => 'Center'
                    ]
                ];
                $_SESSION['analysis_result'] = $analysis_result;
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }
}

// Clear analysis results if requested
if (isset($_POST['clear_analysis'])) {
    unset($_SESSION['analysis_result']);
    $analysis_result = null;
}

// Get analysis result from session if exists
if (!$analysis_result && isset($_SESSION['analysis_result'])) {
    $analysis_result = $_SESSION['analysis_result'];
}

// Get user data
try {
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$db) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }
    
    $email = $_SESSION['email'];
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    mysqli_close($db);
} catch (Exception $e) {
    error_log($e->getMessage());
    $error = "An error occurred while fetching user data.";
}

$matches = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['match_database'])) {
    if (isset($_SESSION['analysis_result'])) {
        $age_range = $_SESSION['analysis_result']['attributes']['Age Range'];
        $gender = $_SESSION['analysis_result']['attributes']['Gender'];
        $matches = findMatchesInDatabase($age_range, $gender);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Masked Intel</title>
  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #1a237e;
      --secondary-color: #4a90e2;
      --text-color: #ffffff;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
      color: var(--text-color);
      min-height: 100vh;
    }

    .top-nav {
      background: rgba(30, 58, 138, 0.95);
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .brand {
      font-size: 1.5rem;
      font-weight: bold;
      color: var(--text-color);
      text-decoration: none;
    }

    .nav-links {
      display: flex;
      gap: 2rem;
      align-items: center;
    }

    .nav-link {
      color: var(--text-color);
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      transition: background-color 0.3s;
    }

    .nav-link:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .logout-btn {
      background: #3b82f6;
      color: white;
      padding: 0.5rem 1.5rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .logout-btn:hover {
      background: #2563eb;
    }

    .main-content {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 2rem;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 2rem;
      }

      .analysis-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 2.5rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .analysis-title {
      color: var(--text-color);
      font-size: 28px;
      margin-bottom: 2rem;
      text-align: center;
      position: relative;
      font-weight: 600;
    }

    .analysis-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 50px;
      height: 3px;
      background: #3b82f6;
      border-radius: 2px;
    }

    .analysis-report {
      width: 100%;
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      padding: 2rem;
      margin-top: 2rem;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .report-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .report-header h3 {
      color: #fff;
      font-size: 24px;
      margin-bottom: 0.5rem;
    }

    .report-timestamp {
      color: rgba(255, 255, 255, 0.7);
      font-size: 14px;
    }

    .uploaded-image {
      width: 100%;
      max-width: 300px;
      height: auto;
      border-radius: 10px;
      margin: 1.5rem auto;
      display: block;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .attributes-list {
      display: grid;
      gap: 1rem;
      margin: 2rem 0;
    }

    .attribute-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      background: rgba(30, 58, 138, 0.3);
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .attribute-label {
      color: rgba(255, 255, 255, 0.7);
      font-size: 14px;
    }

    .attribute-value {
      color: #fff;
      font-size: 14px;
      font-weight: 500;
    }

    .action-buttons {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      margin-top: 2rem;
    }

    .action-button {
      width: 100%;
      padding: 1rem;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      transition: all 0.3s ease;
      font-size: 14px;
    }

    .match-button {
      background: #ef4444;
      color: white;
    }

    .match-button:hover {
      background: #dc2626;
      transform: translateY(-2px);
    }

    .download-button {
      background: #22c55e;
      color: white;
    }

    .download-button:hover {
      background: #16a34a;
      transform: translateY(-2px);
    }

    .upload-form {
      width: 100%;
      text-align: center;
      padding: 2rem;
      border: 2px dashed rgba(255, 255, 255, 0.2);
      border-radius: 15px;
      margin-top: 1rem;
      background: rgba(30, 58, 138, 0.3);
    }

    .upload-text {
      color: rgba(255, 255, 255, 0.9);
      margin-bottom: 1.5rem;
      font-size: 1rem;
      line-height: 1.5;
    }

    .upload-button {
      background: #3b82f6;
      color: white;
      padding: 0.8rem 2rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
      font-size: 1rem;
    }

    .upload-button:hover {
      background: #2563eb;
      transform: translateY(-2px);
    }

    .upload-button i {
      font-size: 1.2rem;
    }

    #submitButton {
      display: none;
      margin-top: 1rem;
    }

    .preview-box {
      width: 100%;
      max-width: 400px;
      height: 300px;
      margin: 0 auto 2rem;
      border-radius: 15px;
      overflow: hidden;
      position: relative;
      background: rgba(30, 58, 138, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .preview-box img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .detection-box {
      position: absolute;
      border: 2px solid #22c55e;
      pointer-events: none;
    }

    /* Face detection box styling */
    #facePreview .detection-box {
      border: 2px solid #22c55e;
      box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.2);
    }

    .matches-container {
        width: 100%;
        margin-top: 2rem;
    }

    .match-results {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 2rem;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .match-results h3 {
        color: #fff;
        font-size: 20px;
        margin-bottom: 1.5rem;
      text-align: center;
    }

    .match-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .match-table th,
    .match-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .match-table th {
        color: rgba(255, 255, 255, 0.7);
        font-weight: 500;
        font-size: 14px;
    }

    .match-table td {
        color: #fff;
        font-size: 14px;
    }

    .match-table tr:hover {
        background: rgba(30, 58, 138, 0.3);
    }

    .no-matches {
      text-align: center;
        color: rgba(255, 255, 255, 0.7);
        padding: 2rem;
    }
  </style>
</head>

<body>
  <nav class="top-nav">
    <a href="index.php" class="brand">MASKED INTEL</a>
    <div class="nav-links">
      <a href="about.php" class="nav-link">About</a>
      <a href="features.php" class="nav-link">Features</a>
      <a href="contact.php" class="nav-link">Contact</a>
      <a href="logout.php" class="logout-btn">Logout</a>
  </div>
    </nav>

  <div class="main-content">
      <div class="analysis-card">
      <h2 class="analysis-title">Face Analysis</h2>
      <?php if ($analysis_result): ?>
        <div class="analysis-report">
          <div class="report-header">
            <h3>Face Analysis Report</h3>
            <span class="report-timestamp">Report Generated: <?php echo $analysis_result['timestamp']; ?></span>
    </div>

          <img src="<?php echo htmlspecialchars($analysis_result['image']); ?>" alt="Analyzed face" class="uploaded-image">

          <div class="attributes-list">
            <?php foreach ($analysis_result['attributes'] as $label => $value): ?>
              <div class="attribute-item">
                <span class="attribute-label"><?php echo htmlspecialchars($label); ?></span>
                <span class="attribute-value"><?php echo htmlspecialchars($value); ?></span>
        </div>
            <?php endforeach; ?>
            </div>

          <div class="action-buttons">
            <form method="POST" style="width: 100%;">
                <button type="submit" name="match_database" class="action-button match-button">
                    <i class="fas fa-search"></i>
                    Match with Criminal Database
            </button>
            </form>
            <a href="generate_pdf.php" class="action-button download-button" style="text-decoration: none; text-align: center;">
                <i class="fas fa-download"></i>
                Download PDF Report
            </a>
          </div>
        </div>

        <?php if (isset($_POST['match_database'])): ?>
            <div class="matches-container">
                <div class="match-results">
                    <h3>Database Match Results</h3>
                    <?php if (!empty($matches)): ?>
                        <table class="match-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Gender</th>
                                    <th>Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($matches as $match): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($match['name']); ?></td>
                                        <td><?php echo htmlspecialchars($match['age']); ?></td>
                                        <td><?php echo htmlspecialchars($match['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($match['year']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-matches">
                            No matches found in the database.
            </div>
                    <?php endif; ?>
            </div>
            </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="preview-box" id="facePreview">
          <img src="face card.png" alt="Face analysis example">
          <div class="detection-box"></div>
          </div>
        <div class="upload-form">
          <p class="upload-text">Upload a face image to detect personal attributes</p>
          <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
            <input type="file" name="face_image" id="face_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
            <button type="button" class="upload-button" onclick="document.getElementById('face_image').click()">
              <i class="fas fa-upload"></i>
              Upload Image
            </button>
            <button type="submit" id="submitButton" class="upload-button" style="display: none; margin-top: 1rem;">
              <i class="fas fa-check"></i>
              Analyze Face
            </button>
          </form>
          </div>
      <?php endif; ?>
      </div>

      <div class="analysis-card">
      <h2 class="analysis-title">Crowd Analysis</h2>
      <div class="preview-box">
        <img src="crowd card.png" alt="Crowd analysis example">
        </div>
      <div class="upload-form">
        <p class="upload-text">Upload an image of a crowd to analyze mask compliance and headcount</p>
        <button type="button" class="upload-button">
              <i class="fas fa-upload"></i>
              Upload Image
            </button>
          </div>
          </div>
        </div>

  <script>
    function previewImage(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
          var previewBox = input.closest('.analysis-card').querySelector('.preview-box img');
          previewBox.src = e.target.result;
          document.getElementById('submitButton').style.display = 'inline-flex';
        }
        reader.readAsDataURL(input.files[0]);
      }
    }

    function validateForm() {
      var fileInput = document.getElementById('face_image');
      if (!fileInput.files || fileInput.files.length === 0) {
        alert('Please select an image to upload');
        return false;
      }
      return true;
    }
  </script>
</body>

</html>