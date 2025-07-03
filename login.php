<?php
session_start();

$conn = new mysqli("localhost", "root", "", "online_merchandise");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT customer_id, first_name, last_name, gender, email, password, username, phone, address FROM Customer WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['customer_id'] = $row['customer_id'];
                $_SESSION['user_data'] = [
                    $row['first_name'],
                    $row['last_name'],
                    $row['gender'],
                    '', '', $row['email'], '', '', '', '', '', $row['username']
                ];
                header("Location: welcome.php");
                exit;
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "No matching account found.";
        }
        $stmt->close();
    } else {
        $message = "Please enter both username and password.";
    }
}

// Determine cart count only if logged in
$customer_id = $_SESSION['customer_id'] ?? null;
$cart_count = 0;
if ($customer_id) {
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM Cart WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $cart_count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login - Urban Aura</title>
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
  min-height: 100vh;
}

#main-content {
  flex: 1 0 auto; 
}

footer {
  flex-shrink: 0; 
  background-color: #000;
  font-size: 0.9rem;
  color: white;
  padding: 1rem 0;
  margin-top: 0; 
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

    .login-form {
      max-width: 450px;
      margin: 40px auto;
      padding: 40px;
      background: #111;
      border-radius: 16px;
      box-shadow: 0 15px 25px rgba(0,0,0,0.5);
      border-top: 6px solid gold;
      color: #fff;
      font-family: 'Montserrat', sans-serif;
    }
    .form-label { color: #f8f9fa; font-weight: 600; }
    .form-control {
      background: #222;
      color: white;
      border: 1px solid #555;
    }
    .form-control:focus {
      border-color: gold;
      box-shadow: none;
      color: #000;
    }
    .btn-primary {
      background: gold;
      color: black;
      font-weight: 700;
      border: none;
      transition: 0.3s;
      transform: 0.2s;
    }
    .btn-primary:hover {
      background: #ffcc00;
      transform: scale(1.03);
    }
    footer {
      background-color: #000;
      font-size: 0.9rem;
      color: white;
      padding: 1rem 0;
      margin-top: auto;
      font-family: 'Montserrat', sans-serif;
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
        <li class="nav-item"><a class="nav-link" href="welcome.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="product.php?sort=newest">Product</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart (<?= htmlspecialchars($cart_count) ?>)</a></li>
        <?php if ($customer_id): ?>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link active" href="regform.php">Register/Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="login-form">
  <h2 class="text-center mb-4" style="color: gold;">User Login</h2>
  <?php if ($message): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <form method="post" novalidate>
    <div class="mb-3">
      <label class="form-label" for="username">Username</label>
      <input id="username" name="username" class="form-control" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label class="form-label" for="password">Password</label>
      <input id="password" type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Log In</button>
  </form>
  <p class="text-center mt-3 text-light">
    Don't have an account? <a href="regform.php" class="text-info">Register here</a>.
  </p>
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
