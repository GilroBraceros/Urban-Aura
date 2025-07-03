<?php
session_start();

$conn = new mysqli("localhost", "root", "", "online_merchandise");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$inputs = [
    'firstname'=>'', 'lastname'=>'', 'gender'=>'', 'birth'=>'',
    'phone'=>'', 'email'=>'', 'street'=>'', 'city'=>'',
    'province'=>'', 'zipcode'=>'', 'country'=>'', 'username'=>''
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($inputs as $k => $_) {
        $inputs[$k] = trim($_POST[$k] ?? '');
    }
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validation
    if (!preg_match("/^[a-zA-Z ]{2,50}$/", $inputs['firstname'])) {
        $errors['firstname'] = "First name must be letters/spaces, 2–50 chars.";
    }
    if (!preg_match("/^[a-zA-Z ]{2,50}$/", $inputs['lastname'])) {
        $errors['lastname'] = "Last name must be letters/spaces, 2–50 chars.";
    }
    if (!in_array($inputs['gender'], ['Male','Female','Other'])) {
        $errors['gender'] = "Please select a valid gender.";
    }
    $b = DateTime::createFromFormat('Y-m-d', $inputs['birth']);
    if (!$b || (new DateTime())->diff($b)->y < 18) {
        $errors['birth'] = "You must be at least 18 years old.";
    }
    if (!preg_match("/^09\d{9}$/", $inputs['phone'])) {
        $errors['phone'] = "Phone must be 11 digits starting with 09.";
    }
    if (!filter_var($inputs['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address.";
    }
    if (!preg_match("/^[a-zA-Z0-9\s.,'#\-]{5,100}$/", $inputs['street'])) {
        $errors['street'] = "Street must be 5–100 chars.";
    }
    if (!preg_match("/^[a-zA-Z ]{2,50}$/", $inputs['city'])) {
        $errors['city'] = "City must be letters/spaces, 2–50 chars.";
    }
    if (!preg_match("/^[a-zA-Z ]{2,50}$/", $inputs['province'])) {
        $errors['province'] = "Province must be letters/spaces, 2–50 chars.";
    }
    if (!preg_match("/^\d{4}$/", $inputs['zipcode'])) {
        $errors['zipcode'] = "Zip code must be exactly 4 digits.";
    }
    if (!preg_match("/^[a-zA-Z ]{2,50}$/", $inputs['country'])) {
        $errors['country'] = "Country must be letters/spaces, 2–50 chars.";
    }
    if (!preg_match("/^\w{5,20}$/", $inputs['username'])) {
        $errors['username'] = "Username must be 5–20 chars (letters/digits/_).";
    }
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        $errors['password'] = "Pwd must be 8+ chars, upper/lower/digit/special.";
    }
    if ($password !== $confirm) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT customer_id FROM Customer WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $inputs['username'], $inputs['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['username'] = "Username or email already exists.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $address = $inputs['street'] . ', ' . $inputs['city'] . ', ' . $inputs['province'] . ', ' . $inputs['zipcode'] . ', ' . $inputs['country'];

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO Customer (first_name, last_name, gender, email, username, password, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssss",
            $inputs['firstname'],
            $inputs['lastname'],
            $inputs['gender'],
            $inputs['email'],
            $inputs['username'],
            $hashed_password,
            $inputs['phone'],
            $address
        );
        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            $errors['general'] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}


$cart_count = $_SESSION['cart_count'] ?? 0;
$customer_id = $_SESSION['customer_id'] ?? null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Braceros' Merchandise Registration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
       html, body {
      height: 100%;
      margin: 0;
      display: flex;
      flex-direction: column;
      font-family: 'Montserrat', sans-serif;
      background: linear-gradient(to bottom right, #fdfdfd, #b0c4de);
      color: #000;
      padding-top: 70px;
      min-height: 100vh;
    }
    /* Navbar CSS identical to regform.php */
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

    .nav-link { color: white !important; font-weight:500; }
    .register-form {
      max-width:850px; margin:40px auto; padding:40px;
      background:#111; border-radius:16px;  
      box-shadow:0 15px 25px rgba(0,0,0,0.5);
      border-top:6px solid gold;
      color: #fff;
    }
    .form-label { color:#f8f9fa; font-weight:500; }
    .form-control { background:#222; color:#fff; border:1px solid #555; }
    .form-control:focus { border-color:gold; box-shadow:none; }
    .btn-primary {
      background:gold; color:black; font-weight:600; border:none;
      transition:0.3s; transform:0.2s;
    }
    .btn-primary:hover { background:#ffcc00; transform:scale(1.03); }
    .btn-reset {
      background:#6c757d; color:white; font-weight:600;
      transition:0.3s; transform:0.2s;
    }
    .btn-reset:hover { background:#5a6268; transform:scale(1.02); }
    footer { background:#000; color:white; text-align:center; padding:1rem 0; margin-top:60px; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="welcome.php">
      <img src="assets/img/logo.png" alt="Urban Aura Logo" style="height: 55px;" />
      <span class="ms-3 fw-bold" style="color: #f0c420; font-size: 1.4rem;">Urban Aura</span>
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
      Menu <i class="fas fa-bars ms-1"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="welcome.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="product.php?sort=newest">Product</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="cart.php">Cart (<?= htmlspecialchars($cart_count) ?>)</a>
        </li>
        <?php if ($customer_id): ?>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link active" href="regform.php">Register/Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container register-form">
  <h2 class="text-center text-warning">Registration Form</h2>
  <?php if (isset($errors['general'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
  <?php endif; ?>
  <form method="post" novalidate>
    <div class="row mb-3">
      <div class="col">
        <label class="form-label">First Name</label>
        <input name="firstname" class="form-control <?= isset($errors['firstname']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['firstname']) ?>">
        <?php if (isset($errors['firstname'])): ?>
          <div class="invalid-feedback"><?= $errors['firstname'] ?></div>
        <?php endif; ?>
      </div>
      <div class="col">
        <label class="form-label">Last Name</label>
        <input name="lastname" class="form-control <?= isset($errors['lastname']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['lastname']) ?>">
        <?php if (isset($errors['lastname'])): ?>
          <div class="invalid-feedback"><?= $errors['lastname'] ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>">
          <option value="">Select</option>
          <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
            <option value="<?= $g ?>" <?= ($inputs['gender'] === $g) ? 'selected' : '' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['gender'])): ?>
          <div class="invalid-feedback"><?= $errors['gender'] ?></div>
        <?php endif; ?>
      </div>
      <div class="col">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="birth" class="form-control <?= isset($errors['birth']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['birth']) ?>">
        <?php if (isset($errors['birth'])): ?>
          <div class="invalid-feedback"><?= $errors['birth'] ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">Phone Number</label>
        <input name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['phone']) ?>">
        <?php if (isset($errors['phone'])): ?>
          <div class="invalid-feedback"><?= $errors['phone'] ?></div>
        <?php endif; ?>
      </div>
      <div class="col">
        <label class="form-label">Email Address</label>
        <input name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['email']) ?>">
        <?php if (isset($errors['email'])): ?>
          <div class="invalid-feedback"><?= $errors['email'] ?></div>
        <?php endif; ?>
      </div>
    </div>

    <h5 class="text-light">Address Details</h5>
    <div class="mb-3">
      <label class="form-label">Street</label>
      <input name="street" class="form-control <?= isset($errors['street']) ? 'is-invalid' : '' ?>"
        value="<?= htmlspecialchars($inputs['street']) ?>">
      <?php if (isset($errors['street'])): ?>
        <div class="invalid-feedback"><?= $errors['street'] ?></div>
      <?php endif; ?>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">City</label>
        <input name="city" class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['city']) ?>">
        <?php if (isset($errors['city'])): ?>
          <div class="invalid-feedback"><?= $errors['city'] ?></div>
        <?php endif; ?>
      </div>
      <div class="col">
        <label class="form-label">Province</label>
        <input name="province" class="form-control <?= isset($errors['province']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['province']) ?>">
        <?php if (isset($errors['province'])): ?>
          <div class="invalid-feedback"><?= $errors['province'] ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">Zip Code</label>
        <input name="zipcode" maxlength="4"
          class="form-control <?= isset($errors['zipcode']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['zipcode']) ?>">
        <?php if (isset($errors['zipcode'])): ?>
          <div class="invalid-feedback"><?= $errors['zipcode'] ?></div>
        <?php endif; ?>
      </div>
      <div class="col">
        <label class="form-label">Country</label>
        <input name="country" class="form-control <?= isset($errors['country']) ? 'is-invalid' : '' ?>"
          value="<?= htmlspecialchars($inputs['country']) ?>">
        <?php if (isset($errors['country'])): ?>
          <div class="invalid-feedback"><?= $errors['country'] ?></div>
        <?php endif; ?>
      </div>
    </div>

    <h5 class="text-light">Account Information</h5>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
        value="<?= htmlspecialchars($inputs['username']) ?>">
      <?php if (isset($errors['username'])): ?>
        <div class="invalid-feedback"><?= $errors['username'] ?></div>
      <?php endif; ?>
    </div>

    <div class="row mb-3">
      <div class="col">
        <label class="form-label">Password</label>
        <input type="password" name="password"
          class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
        <?php if (isset($errors['password'])): ?>
          <div class="invalid-feedback"><?= $errors['password'] ?></div>
        <?php endif; ?>
      </div>
            <div class="col">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password"
          class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>">
        <?php if (isset($errors['confirm_password'])): ?>
          <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="d-flex justify-content-between">
      <button type="reset" class="btn btn-reset">Reset</button>
      <button type="submit" class="btn btn-primary">Register</button>
    </div>

    <p class="text-center mt-3 text-light">
      Already have an account? <a href="login.php" class="text-info">Log-in here</a>.
    </p>
  </form>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

