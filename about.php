<?php
session_start();

$conn = new mysqli("localhost", "root", "", "online_merchandise");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$customer_id = $_SESSION['customer_id'] ?? null;
$session_id = session_id();


if ($customer_id) {
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM Cart WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
} else {
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM Cart WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
}
$stmt->execute();
$cart_count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us - Urban Aura</title>
  <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #fff;
      color: #000;
      font-family: 'Roboto Slab', serif;
      padding-top: 70px;
    }
    #mainNav {
      background-color: #000 !important;
      height: 70px;
      font-family: 'Montserrat', sans-serif;
    }
    #mainNav .navbar-brand,
    #mainNav .nav-link {
      color: white !important;
      font-weight: 700;
    }
    #mainNav .nav-link:hover {
      color: #ffd83d !important;
    }
    #mainNav .navbar-toggler {
      border-color: #f0c420 !important;
    }
    #mainNav .navbar-toggler-icon {
      filter: invert(99%) sepia(72%) saturate(598%) hue-rotate(357deg) brightness(99%) contrast(103%);
    }
    #mainNav .navbar-brand img {
      max-height: 55px;
      margin-top: -1px;
    }
    #mainNav .navbar-nav .nav-link {
      padding-top: 0.75rem;
      padding-bottom: 0.75rem;
    }
    .navbar-nav .nav-link.active {
      color: #f0c420 !important;
      border-bottom: 2px solid #f0c420;
    }
    .section-title {
      font-family: 'Montserrat', sans-serif;
      font-size: 2.5rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 2rem;
      color: #003366;
      border-bottom: 3px solid #f0c420;
      display: inline-block;
      padding-bottom: 0.3rem;
    }
    .form-control:focus {
      border-color: #f0c420;
      box-shadow: 0 0 5px #f0c420;
    }
    .btn-send {
      background-color: #f0c420;
      color: #000;
      font-weight: 700;
      border-radius: 2rem;
      padding: 0.6rem 1.5rem;
      border: none;
      box-shadow: 0 0 10px #f0c420;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
    .btn-send:hover {
      background-color: #ffd83d;
      box-shadow: 0 0 20px #ffd83d;
      color: #000;
    }
      .profile-pic-container {
    width: 250px;
    height: 250px;
    border-radius: 50%;
    overflow: hidden;
    border: 5px solid #f0c420;
    box-shadow: 0 4px 12px rgba(240, 196, 32, 0.5);
  }
  .profile-pic-container img.profile-pic {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="product.php">
      <img src="assets/img/logo.png" alt="Urban Aura Logo" style="height: 55px;" />
      <span class="ms-3 fw-bold" style="color: #f0c420; font-size: 1.4rem;">Urban Aura</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
      Menu <i class="fas fa-bars ms-1"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="welcome.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="product.php?sort=newest">Product</a></li>
        <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart (<?= htmlspecialchars($cart_count) ?>)</a></li>
        <?php if ($customer_id): ?>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="regform.php">Register/Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-5">
  <h2 class="section-title">About Us</h2>
  <div class="row g-5 mt-4 align-items-center">
    <div class="col-md-6 d-flex flex-column align-items-center">
      
      <div class="profile-pic-container mb-4">
        <img src="assets/me.jpg" alt="Founder/Team Member Photo" class="profile-pic" />
      </div>
      <h4 class="fw-bold text-uppercase" style="color: #003366;">Company Information</h4>
      <p style="color: #333; text-align: center; max-width: 400px;">
        Urban Aura is a streetwear brand dedicated to bold style, quality materials, and comfort. We deliver fashion-forward pieces that reflect urban culture, while staying rooted in everyday versatility.
      </p>
      <p><strong>Email:</strong> <a href="mailto:support@urbanaura.com">support@urbanaura.com</a></p>
      <p><strong>Phone:</strong> +63 912 345 6789</p>
    </div>
    <div class="col-md-6">
      <h4 class="fw-bold text-uppercase" style="color: #003366;">Contact Us</h4>
      <form>
        <div class="mb-3">
          <label for="contactName" class="form-label">Name</label>
          <input type="text" class="form-control" id="contactName" placeholder="Your name" required>
        </div>
        <div class="mb-3">
          <label for="contactEmail" class="form-label">Email</label>
          <input type="email" class="form-control" id="contactEmail" placeholder="name@example.com" required>
        </div>
        <div class="mb-3">
          <label for="contactMessage" class="form-label">Message</label>
          <textarea class="form-control" id="contactMessage" rows="4" placeholder="Your message..." required></textarea>
        </div>
        <button type="submit" class="btn btn-send">Send Message</button>
      </form>
    </div>
  </div>
</div>

<footer class="footer mt-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-4 text-lg-start">© Urban Aura 2025</div>
      <div class="col-lg-4 my-3 my-lg-0 text-center">
        <a class="btn btn-social mx-2" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        <a class="btn btn-social mx-2" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a class="btn btn-social mx-2" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
      </div>
      <div class="col-lg-4 text-lg-end">
        <a class="link-light text-decoration-none me-3" href="#!">Privacy Policy</a>
        <a class="link-light text-decoration-none" href="#!">Terms of Use</a>
      </div>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
