<?php
session_start();

$conn = new mysqli("localhost", "root", "", "online_merchandise");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$customer_id = $_SESSION['customer_id'] ?? null;
$session_id = session_id();

// Build cart query based on login status
if ($customer_id) {
    $stmt = $conn->prepare("SELECT c.cart_id, p.product_name, p.price, c.quantity FROM Cart c JOIN Product p ON c.product_id = p.product_id WHERE c.customer_id = ?");
    $stmt->bind_param("i", $customer_id);
} else {
    $stmt = $conn->prepare("SELECT c.cart_id, p.product_name, p.price, c.quantity FROM Cart c JOIN Product p ON c.product_id = p.product_id WHERE c.session_id = ?");
    $stmt->bind_param("s", $session_id);
}
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;
$cart_count = 0;
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $cart_count += $row['quantity'];
    $total += $row['price'] * $row['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Cart - Urban Aura</title>
<link rel="icon" href="assets/favicon.ico" />
<script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

body {
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}

body {
background-color: #fff;
color: #000;
font-family: 'Roboto Slab', serif;
padding-top: 70px;
}
#mainNav {
background-color: #000 !important;
height: 70px;
overflow: hidden;
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

.masthead {
position: relative;
background: url('assets/img/header-bg.jpg') no-repeat center center;
background-size: cover;
height: 400px;
min-height: 350px;
background-color: #000;
display: flex;
align-items: center;
justify-content: center;
text-align: center;
color: #f0c420;
font-family: 'Montserrat', sans-serif;
flex-direction: column;
padding: 0 1rem;
}
.masthead-subheading {
font-family: 'Roboto Slab', serif;
font-weight: 400;
font-size: 1.8rem;
margin-bottom: 0.3rem;
color: #f0c420;
text-shadow: 0 0 10px rgba(240,196,32,0.7);
}
.masthead-heading {
font-weight: 700;
font-size: 4rem;
text-transform: uppercase;
letter-spacing: 0.15em;
margin-bottom: 1.5rem;
color: #f0c420;
text-shadow: 0 0 20px rgba(240,196,32,0.9);
}
.btn-warning.btn-xl {
font-size: 1.5rem;
padding: 1rem 3rem;
border-radius: 3rem;
box-shadow: 0 0 15px #f0c420;
transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out;
font-weight: 700;
letter-spacing: 0.1em;
text-transform: uppercase;
}
.btn-warning.btn-xl:hover,
.btn-warning.btn-xl:focus {
box-shadow: 0 0 30px #ffd83d, 0 0 40px #ffd83d inset;
transform: scale(1.05);
color: #000;
background-color: #f0c420;
border-color: #f0c420;
}

.category-title {
font-family: 'Montserrat', sans-serif;
font-weight: 700;
font-size: 2.8rem;
margin-top: 3rem;
margin-bottom: 2rem;
text-transform: uppercase;
letter-spacing: 0.15em;
text-align: center;
color: #003366;
position: relative;
display: inline-block;
padding-bottom: 0.5rem;
}


.products-grid {
display: grid;
grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
gap: 1.8rem 1.5rem;
padding: 0 1rem;
max-width: 1200px;
margin: 0 auto 4rem;
}

.product-card {
background: #ffffff;
border-radius: 16px;
padding: 1.2rem;
text-align: center;
color: #222;
box-shadow: 0 4px 16px rgba(0,0,0,0.06);
transition: all 0.3s ease;
display: flex;
flex-direction: column;
justify-content: space-between;
height: 100%;
overflow: hidden;
}

.product-card:hover {
box-shadow: 0 8px 30px rgba(0,0,0,0.12);
transform: translateY(-5px);
}

.product-card img {
max-width: 100%;
border-radius: 12px;
margin-bottom: 0.8rem;
object-fit: cover;
height: 180px;
}

.product-name {
font-family: 'Montserrat', sans-serif;
font-weight: 700;
font-size: 1.1rem;
margin-bottom: 0.5rem;
color: #003366;
}

.product-desc {
font-size: 0.88rem;
color: #666;
margin-bottom: 0.8rem;
min-height: 42px;
flex-grow: 1;
}

.product-price {
font-weight: 700;
font-size: 1.05rem;
color: #222;
margin-bottom: 0.7rem;
}

.product-rating {
font-size: 0.95rem;
color: #003366;
margin-bottom: 0.4rem;
}

.product-rating i {
margin: 0 1px;
}

.product-date {
font-size: 0.8rem;
color: #999;
margin-bottom: 0.7rem;
}

.btn-add-cart {
background-color: #f0c420;
color: #000;
font-weight: 600;
border-radius: 30px;
padding: 0.5rem 1.5rem;
border: none;
box-shadow: 0 2px 8px rgba(240, 196, 32, 0.5);
transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s;
margin-top: auto;
}

.btn-add-cart:hover {
background-color: #ffd83d;
box-shadow: 0 4px 16px rgba(240, 196, 32, 0.7);
color: #000;
transform: translateY(-2px);
}

body {
background-color: #fff;
color: #000;
font-family: 'Roboto Slab', serif;
padding-top: 70px;
}
 #mainNav {
      background-color: #000 !important;
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
    #mainNav {
  height: 70px;
  overflow: hidden;
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


.masthead {
position: relative;
background: url('assets/img/header-bg.jpg') no-repeat center center;
background-size: cover;
height: 400px;
min-height: 350px;
background-color: #000;
display: flex;
align-items: center;
justify-content: center;
text-align: center;
color: #f0c420;
font-family: 'Montserrat', sans-serif;
flex-direction: column;
padding: 0 1rem;
}
.masthead-subheading {
font-family: 'Roboto Slab', serif;
font-weight: 400;
font-size: 1.8rem;
margin-bottom: 0.3rem;
color: #f0c420;
text-shadow: 0 0 10px rgba(240,196,32,0.7);
}
.masthead-heading {
font-weight: 700;
font-size: 4rem;
text-transform: uppercase;
letter-spacing: 0.15em;
margin-bottom: 1.5rem;
color: #f0c420;
text-shadow: 0 0 20px rgba(240,196,32,0.9);
}
.btn-warning.btn-xl {
font-size: 1.5rem;
padding: 1rem 3rem;
border-radius: 3rem;
box-shadow: 0 0 15px #f0c420;
transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out;
font-weight: 700;
letter-spacing: 0.1em;
text-transform: uppercase;
}
.btn-warning.btn-xl:hover,
.btn-warning.btn-xl:focus {
box-shadow: 0 0 30px #ffd83d, 0 0 40px #ffd83d inset;
transform: scale(1.05);
color: #000;
background-color: #f0c420;
border-color: #f0c420;
}

.category-title {
font-family: 'Montserrat', sans-serif;
font-weight: 700;
font-size: 2.8rem;
margin-top: 3rem;
margin-bottom: 2rem;
text-transform: uppercase;
letter-spacing: 0.15em;
text-align: center;
color: #003366;
position: relative;
display: inline-block;
padding-bottom: 0.5rem;
}


.products-grid {
display: grid;
grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
gap: 1.8rem 1.5rem;
padding: 0 1rem;
max-width: 1200px;
margin: 0 auto 4rem;
}

.product-card {
background: #ffffff;
border-radius: 16px;
padding: 1.2rem;
text-align: center;
color: #222;
box-shadow: 0 4px 16px rgba(0,0,0,0.06);
transition: all 0.3s ease;
display: flex;
flex-direction: column;
justify-content: space-between;
height: 100%;
overflow: hidden;
}

.product-card:hover {
box-shadow: 0 8px 30px rgba(0,0,0,0.12);
transform: translateY(-5px);
}

.product-card img {
max-width: 100%;
border-radius: 12px;
margin-bottom: 0.8rem;
object-fit: cover;
height: 180px;
}

.product-name {
font-family: 'Montserrat', sans-serif;
font-weight: 700;
font-size: 1.1rem;
margin-bottom: 0.5rem;
color: #003366;
}

.product-desc {
font-size: 0.88rem;
color: #666;
margin-bottom: 0.8rem;
min-height: 42px;
flex-grow: 1;
}

.product-price {
font-weight: 700;
font-size: 1.05rem;
color: #222;
margin-bottom: 0.7rem;
}

.product-rating {
font-size: 0.95rem;
color: #003366;
margin-bottom: 0.4rem;
}

.product-rating i {
margin: 0 1px;
}

.product-date {
font-size: 0.8rem;
color: #999;
margin-bottom: 0.7rem;
}

.btn-add-cart {
background-color: #f0c420;
color: #000;
font-weight: 600;
border-radius: 30px;
padding: 0.5rem 1.5rem;
border: none;
box-shadow: 0 2px 8px rgba(240, 196, 32, 0.5);
transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s;
margin-top: auto;
}

.btn-add-cart:hover {
background-color: #ffd83d;
box-shadow: 0 4px 16px rgba(240, 196, 32, 0.7);
color: #000;
transform: translateY(-2px);
}
.btn-warning.btn-smaller {
  font-size: 1rem;           
  padding: 0.5rem 1.5rem;  
  border-radius: 2rem;      
  box-shadow: 0 0 10px #f0c420;  
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  transition: box-shadow 0.3s ease-in-out, transform 0.3s ease-in-out;
}

.btn-warning.btn-smaller:hover,
.btn-warning.btn-smaller:focus {
  box-shadow: 0 0 20px #ffd83d, 0 0 30px #ffd83d inset;
  transform: scale(1.05);
  color: #000;
  background-color: #f0c420;
  border-color: #f0c420;
}



<?php include 'style.css'; ?>
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
        <li class="nav-item"><a class="nav-link" href="product.php">Product</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item">
          <a class="nav-link active" href="cart.php">Cart (<?= $cart_count ?>)</a>
        </li>
        <?php if ($customer_id): ?>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="regform.php">Register/Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container my-5 flex-grow-1 d-flex flex-column">
  <h2 class="category-title">Shopping Cart</h2>
  <?php if (count($cart_items) === 0): ?>
    <p>Your cart is empty.</p>
  <?php else: ?>
    <div class="table-responsive flex-grow-1">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Subtotal</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cart_items as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['product_name']) ?></td>
              <td><?= $item['quantity'] ?></td>
              <td>₱<?= number_format($item['price'], 2) ?></td>
              <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
              <td>
                <form method="POST" action="remove_cart.php" style="display:inline;">
                  <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="text-end">
      <h4>Total: ₱<?= number_format($total, 2) ?></h4>
      <a href="checkout.php" class="btn btn-warning btn-smaller mt-3">Proceed to Checkout</a>
    </div>
  <?php endif; ?>
</main>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

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

</body>
</html>
