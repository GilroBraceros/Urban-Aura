<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

$conn = new mysqli("localhost", "root", "", "online_merchandise");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user info
$stmt = $conn->prepare("SELECT customer_id, first_name, last_name, gender, email, username, phone, address FROM Customer WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    session_destroy();
    header("Location: login.php");
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Cart count
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

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Welcome - Urban Aura</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
      font-family: 'Roboto Slab', serif;
      background: linear-gradient(to bottom right, #fdfdfd, #b0c4de);
      color: #000;
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

    .hero-section {
      padding: 100px 20px;
      background: #111;
      text-align: center;
      border-top: 6px solid gold;
      border-radius: 12px;
      box-shadow: 0 15px 25px rgba(0, 0, 0, 0.4);
      margin: 30px auto;
      max-width: 900px;
      color: gold;
      font-family: 'Montserrat', sans-serif;
    }
    .hero-section h1 {
      font-size: 3rem;
      font-weight: 700;
      margin-bottom: 20px;
    }
    .hero-section p {
      font-size: 1.1rem;
      color: #ddd;
      max-width: 600px;
      margin: 0 auto 30px;
    }
    .btn-explore {
      background-color: gold;
      color: black;
      padding: 10px 30px;
      font-weight: 600;
      border: none;
      border-radius: 5px;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }
    .btn-explore:hover {
      transform: scale(1.03);
    }
    .info-card {
      background: #1c1c1c;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.4);
      color: #ccc;
      max-width: 800px;
      margin: auto;
    }
    .info-card h4 {
      color: gold;
      font-weight: 600;
      margin-bottom: 20px;
      text-align: center;
    }
    .info-card .row > div {
      background: #222;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
    }
    .info-card strong {
      color: gold;
    }
    footer {
      background-color: #000;
      font-size: 0.9rem;
      color: white;
      padding: 1rem 0;
      margin-top: auto;
    }
    .btn-social i {
      font-size: 1.2rem;
      color: white;
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
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
      Menu <i class="fas fa-bars ms-1"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="welcome.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="product.php?sort=newest">Product</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
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

<section class="hero-section">
  <h1>Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h1>
  <p>Thank you for logging in to Urban Aura. Explore our latest products and personalized offers!</p>
  <a href="product.php" class="btn btn-explore">Shop Now</a>
</section>

<div class="info-card mt-5">
  <h4>Your Information</h4>
  <div class="row row-cols-1 row-cols-md-2 g-3">
    <?php
    $fields = [
      'Customer ID' => 'customer_id',
      'First Name' => 'first_name',
      'Last Name' => 'last_name',
      'Gender' => 'gender',
      'Email' => 'email',
      'Username' => 'username',
      'Phone' => 'phone',
      'Address' => 'address',
    ];
    foreach ($fields as $label => $key): ?>
      <div>
        <strong><?= htmlspecialchars($label) ?>:</strong><br>
        <span><?= htmlspecialchars($user[$key]) ?></span>
      </div>
    <?php endforeach; ?>
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
