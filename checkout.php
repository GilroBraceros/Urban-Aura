<?php 
session_start();
$conn = new mysqli("localhost","root","","online_merchandise");
if($conn->connect_error) die("Connection failed: ".$conn->connect_error);

$customer_id = $_SESSION['customer_id'] ?? null;
$session_id = session_id();
if(!$customer_id) header("Location: login.php");

// Handle submission
if($_SERVER['REQUEST_METHOD']==='POST'){
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $shipping = trim($_POST['address']);
    $payment_method = $_POST['payment_method'];

    $stmt = $conn->prepare("
      SELECT c.product_id, p.product_name, p.price, c.quantity
      FROM Cart c JOIN Product p USING(product_id)
      WHERE c.customer_id = ? OR (c.customer_id IS NULL AND c.session_id = ?)
    ");
    $stmt->bind_param("is",$customer_id,$session_id);
    $stmt->execute();
    $cart = $stmt->get_result();

    $cart_items = [];
    $subtotal = 0;
    $total_items = 0;

    while($i=$cart->fetch_assoc()){
      $cart_items[] = [
        'product_id' => $i['product_id'],
        'product_name' => $i['product_name'],
        'price' => $i['price'],
        'quantity' => $i['quantity']
      ];
      $subtotal += $i['price']*$i['quantity'];
      $total_items += $i['quantity'];
    }
    $stmt->close();

    if(count($cart_items)===0) die("Cart empty.");

    // Calculate VAT and total
    $vat = round($subtotal * 0.12, 2);
    $total = round($subtotal + $vat, 2);

    // Encode products as JSON string
    $products_json = json_encode($cart_items);

    // Insert into Order table
    $stmt = $conn->prepare("
      INSERT INTO `Order`
        (customer_id, session_id, shipping_address, products, total_items, order_date, total_amount, status)
      VALUES (?, ?, ?, ?, ?, NOW(), ?, 'Pending')
    ");
    $stmt->bind_param("isssid", $customer_id, $session_id, $shipping, $products_json, $total_items, $total);

    if(!$stmt->execute()) die("Order fail: ".$stmt->error);
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert Payment record
    $stmt = $conn->prepare("
      INSERT INTO Payment(order_id, payment_date, payment_method, amount, payment_status)
      VALUES (?, NOW(), ?, ?, 'Paid')
    ");
    $stmt->bind_param("isd", $order_id, $payment_method, $total);
    $stmt->execute();
    $stmt->close();

    // Clear Cart
    $stmt = $conn->prepare("
      DELETE FROM Cart WHERE customer_id = ? OR (customer_id IS NULL AND session_id = ?)
    ");
    $stmt->bind_param("is", $customer_id, $session_id);
    $stmt->execute();
    $stmt->close();

    header("Location: receipt.php?order_id=$order_id");
    exit;
}

$customer = [
    'fullname' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'first_name' => '',
    'last_name' => '',
];
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, address FROM Customer WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $row = $result->fetch_assoc()) {
    $customer['first_name'] = $row['first_name'];
    $customer['last_name'] = $row['last_name'];
    $customer['fullname'] = trim($row['first_name'] . ' ' . $row['last_name']);
    $customer['email'] = $row['email'];
    $customer['phone'] = $row['phone'];
    $customer['address'] = $row['address'];
}
$stmt->close();

$stmt = $conn->prepare("
SELECT p.product_name, p.price, c.quantity
FROM Cart c
JOIN Product p ON c.product_id = p.product_id
WHERE (c.customer_id = ? OR c.session_id = ?)
");
$stmt->bind_param("is", $customer_id, $session_id);
$stmt->execute();
$cart_result = $stmt->get_result();

$cart_items = [];
$total = 0;
while ($item = $cart_result->fetch_assoc()) {
    $cart_items[] = $item;
    $total += $item['price'] * $item['quantity'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Checkout - Urban Aura</title>
  <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    /* Your existing styles here (same as your original checkout.php) */
    body { background-color: #fff; color: #000; font-family: 'Roboto Slab', serif; padding-top: 70px; }
    #mainNav { background-color: #000 !important; height: 70px; font-family: 'Montserrat', sans-serif; }
    #mainNav .navbar-brand, #mainNav .nav-link { color: white !important; font-weight: 700; }
    #mainNav .nav-link:hover { color: #ffd83d !important; }
    #mainNav .navbar-toggler { border-color: #f0c420 !important; }
    #mainNav .navbar-toggler-icon { filter: invert(99%) sepia(72%) saturate(598%) hue-rotate(357deg) brightness(99%) contrast(103%); }
    #mainNav .navbar-brand img { max-height: 55px; margin-top: -1px; }
    #mainNav .navbar-nav .nav-link { padding-top: 0.75rem; padding-bottom: 0.75rem; }
    .navbar-nav .nav-link.active { color: #f0c420 !important; border-bottom: 2px solid #f0c420; }
    .section-title { font-family: 'Montserrat', sans-serif; font-size: 2.5rem; font-weight: 700; text-align: center; margin-bottom: 2rem; color: #003366; border-bottom: 3px solid #f0c420; display: inline-block; padding-bottom: 0.3rem; }
    .form-control:focus { border-color: #f0c420; box-shadow: 0 0 5px #f0c420; }
    .btn-checkout { background-color: #f0c420; color: #000; font-weight: 700; border-radius: 2rem; padding: 0.75rem 2rem; border: none; box-shadow: 0 0 10px #f0c420; transition: background-color 0.3s ease, box-shadow 0.3s ease; }
    .btn-checkout:hover { background-color: #ffd83d; box-shadow: 0 0 20px #ffd83d; color: #000; }
    .order-summary { background: #f8f9fa; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(240, 196, 32, 0.2); }
    .order-summary h5 { border-bottom: 2px solid #f0c420; padding-bottom: 0.5rem; margin-bottom: 1rem; font-weight: 700; }
    .cart-item { display: flex; justify-content: space-between; margin-bottom: 0.6rem; font-weight: 600; color: #003366; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="product.php">
      <img src="assets/img/logo.png" alt="Urban Aura Logo" style="height: 55px; width: auto;" />
      <span class="ms-3 fw-bold" style="color: #f0c420; font-size: 1.4rem;">Urban Aura</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
      Menu <i class="fas fa-bars ms-1"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="welcome.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="product.php">Product</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link active" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container my-5">
  <h2 class="section-title">Checkout</h2>
  <form method="POST" action="">
    <div class="row g-4">
      <div class="col-md-6">
        <h5 class="mb-3">Customer Information</h5>
        <div class="mb-3">
          <label for="fullname" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="fullname" name="fullname" value="<?= htmlspecialchars($customer['fullname']) ?>" required />
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($customer['email']) ?>" required />
        </div>
        <div class="mb-3">
          <label for="phone" class="form-label">Phone Number</label>
          <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone']) ?>" required />
        </div>
      </div>
      <div class="col-md-6">
        <h5 class="mb-3">Shipping Address</h5>
        <div class="mb-3">
          <label for="address" class="form-label">Street Address</label>
          <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($customer['address']) ?>" required />
        </div>
      </div>

      <div class="col-md-6">
        <h5 class="mb-3">Payment Method</h5>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="payment_method" id="creditcard" value="Credit Card" required />
          <label class="form-check-label" for="creditcard">Credit Card</label>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="PayPal" />
          <label class="form-check-label" for="paypal">PayPal</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="radio" name="payment_method" id="cod" value="Cash on Delivery" />
          <label class="form-check-label" for="cod">Cash on Delivery</label>
        </div>
      </div>

      <div class="col-md-6">
        <div class="order-summary">
          <h5>Order Summary</h5>
          <?php if (count($cart_items) === 0): ?>
            <p>Your cart is empty.</p>
          <?php else: ?>
            <?php foreach ($cart_items as $item): ?>
              <div class="cart-item">
                <span><?= htmlspecialchars($item['product_name']) ?> x <?= $item['quantity'] ?></span>
                <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
              </div>
            <?php endforeach; ?>
            <hr />
            <div class="cart-item" style="font-weight: 800; font-size: 1.1rem;">
              <span>Total</span>
              <span>₱<?= number_format($total, 2) ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="text-center mt-5">
      <button type="submit" class="btn btn-checkout btn-xl" <?= count($cart_items) === 0 ? 'disabled' : '' ?>>Place Order</button>
    </div>
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
        <a class="link-dark text-decoration-none me-3" href="#!">Privacy Policy</a>
        <a class="link-dark text-decoration-none" href="#!">Terms of Use</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
