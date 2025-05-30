<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Masked Intel - Home</title>
    <link rel="stylesheet" href="styles.css" />
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
        <?php if (isLoggedIn()): ?>
          <a href="dashboard.php" class="nav-btn">Dashboard</a>
          <a href="logout.php" class="nav-btn">Logout</a>
        <?php else: ?>
          <a href="login.php" class="nav-btn">Login</a>
        <?php endif; ?>
      </nav>
    </header>

    <main class="hero">
      <div class="hero-text">
        <h1>Welcome to<br /><span>Masked Intel</span></h1>
        <p>
          An advanced facial recognition and crowd analytic tool built to
          enhance public safety and security.
        </p>
      </div>

      <div class="hero-image">
        <img src="3d-face-auth.png" alt="3D face UI" />
      </div>
    </main>

    <section class="brand-feature">
      <div class="brand-logo">
        <img src="logo.png" alt="Masked Intel Badge Logo" />
        <p>Trusted AI Security</p>
      </div>
    </section>
  </body>
</html>