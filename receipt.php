<?php
session_start();

$conn = new mysqli("localhost", "root", "", "online_merchandise");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if ($order_id <= 0) {
    die("Invalid order ID.");
}

$stmt = $conn->prepare("SELECT order_date, total_amount, total_items, status, shipping_address, products FROM `Order` WHERE order_id = ? AND customer_id = ?");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order_res = $stmt->get_result();
$order = $order_res->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found or access denied.");
}

$stmt = $conn->prepare("SELECT payment_method, payment_status, payment_date FROM Payment WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$payment_res = $stmt->get_result();
$payment = $payment_res->fetch_assoc();
$stmt->close();

if (!$payment) {
    $payment = [
        'payment_method' => 'N/A',
        'payment_status' => 'N/A',
        'payment_date' => 'N/A'
    ];
}

$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, address FROM Customer WHERE customer_id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$cust_res = $stmt->get_result();
$customer = $cust_res->fetch_assoc();
$stmt->close();

if (!$customer) {
    $customer = [
        'first_name' => 'N/A',
        'last_name' => '',
        'email' => 'N/A',
        'phone' => 'N/A',
        'address' => 'N/A'
    ];
}

// Decode products JSON
$cart_items = json_decode($order['products'], true) ?: [];

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$vat_rate = 0.12;
$vat_amount = round($subtotal * $vat_rate, 2);
$grand_total = round($subtotal + $vat_amount, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Receipt - Urban Aura</title>
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
    .receipt-container {
      max-width: 700px;
      margin: auto;
      background: #f8f9fa;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(240, 196, 32, 0.3);
    }
    .receipt-header {
      border-bottom: 3px solid #f0c420;
      padding-bottom: 0.5rem;
      margin-bottom: 1.5rem;
      font-weight: 700;
      color: #003366;
      text-align: center;
    }
    table {
      width: 100%;
      margin-bottom: 1.5rem;
    }
    th, td {
      padding: 0.5rem;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #f0c420;
      color: #000;
      font-weight: 700;
    }
    .total-row td {
      font-weight: 800;
      font-size: 1.2rem;
      color: #003366;
    }
    .btn-shopagain {
      background-color: #f0c420;
      color: #000;
      font-weight: 700;
      border-radius: 2rem;
      padding: 0.75rem 2rem;
      border: none;
      display: block;
      width: 100%;
      max-width: 300px;
      margin: 1.5rem auto 0;
      box-shadow: 0 0 10px #f0c420;
      text-align: center;
      text-decoration: none;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }
    .btn-shopagain:hover {
      background-color: #ffd83d;
      box-shadow: 0 0 20px #ffd83d;
      color: #000;
      text-decoration: none;
    }
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
        <li class="nav-item"><a class="nav-link" href="product.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="whatweoffer.php">What We Offer</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container receipt-container mt-5">
  <h2 class="receipt-header">Order Receipt</h2>
  
  <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
  <p><strong>Order Date:</strong> <?= htmlspecialchars($order['order_date']) ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
  
  <h5>Customer Information</h5>
  <p><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></p>
  <p>Email: <?= htmlspecialchars($customer['email']) ?></p>
  <p>Phone: <?= htmlspecialchars($customer['phone']) ?></p>
  <p>Address: <?= nl2br(htmlspecialchars($order['shipping_address'] ?: $customer['address'])) ?></p>
  
  <h5>Payment Details</h5>
  <p>Method: <?= htmlspecialchars($payment['payment_method']) ?></p>
  <p>Status: <?= htmlspecialchars($payment['payment_status'] ?? 'N/A') ?></p>
  <p>Date: <?= htmlspecialchars($payment['payment_date']) ?></p>
  
  <h5>Items</h5>
  <?php if (count($cart_items) === 0): ?>
    <p>No order items found.</p>
    <p><strong>Total paid:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
  <?php else: ?>
  <table>
    <thead>
      <tr>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Subtotal</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cart_items as $item): 
          $subtotal_item = $item['price'] * $item['quantity'];
      ?>
      <tr>
        <td><?= htmlspecialchars($item['product_name']) ?></td>
        <td><?= (int)$item['quantity'] ?></td>
        <td>₱<?= number_format($item['price'], 2) ?></td>
        <td>₱<?= number_format($subtotal_item, 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="3" style="text-align:right;">Subtotal:</td>
        <td>₱<?= number_format($subtotal, 2) ?></td>
      </tr>
      <tr>
        <td colspan="3" style="text-align:right;">VAT (12%):</td>
        <td>₱<?= number_format($vat_amount, 2) ?></td>
      </tr>
      <tr class="total-row">
        <td colspan="3" style="text-align:right;">Grand Total:</td>
        <td>₱<?= number_format($grand_total, 2) ?></td>
      </tr>
    </tfoot>
  </table>
  <?php endif; ?>

  <a href="product.php" class="btn-shopagain">Shop Again</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
