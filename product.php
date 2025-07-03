<?php
session_start();

$conn = new mysqli("localhost", "root", "", "online_merchandise");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$customer_id = $_SESSION['customer_id'] ?? null;
$session_id = session_id();

function next_order($current_order) {
    return $current_order === 'asc' ? 'desc' : 'asc';
}

// Handle Add to Cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["product_id"])) {
    $product_id = (int)$_POST["product_id"];
    $qty = max(1, (int)$_POST["qty"]);

    if ($customer_id) {
        $check = $conn->prepare("SELECT cart_id FROM Cart WHERE customer_id = ? AND product_id = ?");
        $check->bind_param("ii", $customer_id, $product_id);
    } else {
        $check = $conn->prepare("SELECT cart_id FROM Cart WHERE session_id = ? AND product_id = ?");
        $check->bind_param("si", $session_id, $product_id);
    }

    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        if ($customer_id) {
            $update = $conn->prepare("UPDATE Cart SET quantity = quantity + ? WHERE customer_id = ? AND product_id = ?");
            $update->bind_param("iii", $qty, $customer_id, $product_id);
        } else {
            $update = $conn->prepare("UPDATE Cart SET quantity = quantity + ? WHERE session_id = ? AND product_id = ?");
            $update->bind_param("isi", $qty, $session_id, $product_id);
        }
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO Cart (customer_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
        $insert->bind_param("issi", $customer_id, $session_id, $product_id, $qty);
        $insert->execute();
    }
}

// Get cart count
if ($customer_id) {
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM Cart WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
} else {
    $stmt = $conn->prepare("SELECT SUM(quantity) AS total FROM Cart WHERE session_id = ?");
    $stmt->bind_param("s", $session_id);
}
$stmt->execute();
$cart_count = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Parse sort and order from URL
$sort = $_GET['sort'] ?? 'newest';
$order_dir = $_GET['order'] ?? null;

switch ($sort) {
    case 'cheapest':
        if (!in_array($order_dir, ['asc', 'desc'])) $order_dir = 'asc';
        $order = "ORDER BY price " . strtoupper($order_dir);
        break;
    case 'recommended':
        if (!in_array($order_dir, ['asc', 'desc'])) $order_dir = 'desc';
        $order = "ORDER BY product_rating " . strtoupper($order_dir);
        break;
    case 'newest':
    default:
        if (!in_array($order_dir, ['asc', 'desc'])) $order_dir = 'desc';
        $order = "ORDER BY date_added " . strtoupper($order_dir);
        $sort = 'newest';
        break;
}

$products = $conn->query("SELECT * FROM Product $order LIMIT 20");
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Urban Aura - Shop</title>
<link rel="icon" href="assets/favicon.ico" />
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

.category-title {
    font-family: 'Montserrat', sans-serif;
    font-weight: 700;
    font-size: 2.8rem;
    margin-top: 3rem;
    margin-bottom: 2rem;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    text-align: center;
    color: #003366;;
    position: relative;
    display: inline-block;
    padding-bottom: 0.5rem;
}

/* Sort buttons container */
.sort-container {
    display: flex;
    justify-content: flex-end;
    max-width: 1200px;
    margin: 0 auto 1rem;
    padding: 0 1rem;
    gap: 1rem;
}


.sort-buttons button {
    background: transparent;
    border: none;
    color: #003366;;
    font-weight: 700;
    padding: 0.3rem 0.8rem;
    cursor: pointer;
    border-radius: 30px;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    user-select: none;
    transition: color 0.3s;
}

.sort-buttons button:hover,
.sort-buttons button.active {
    color: #003366;;
}

.sort-buttons button i {
    font-size: 0.8rem;
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


form > input[type=number] {
    width: 60px;
    display: inline-block;
    margin-right: 0.5rem;
    border-radius: 5px;
    border: 1px solid #ccc;
    padding: 0.25rem 0.4rem;
}

.btn-add-cart {
    background:  #f0c420;
    color:  #003366;;
    font-weight: 700;
    border-radius: 30px;
    padding: 0.3rem 1rem;
    cursor: pointer;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    transition: all 0.3s ease;
}

.btn-add-cart:hover {
    background-color: #f0c420;
    color: #000;
    box-shadow: 0 0 10px #f0c420cc;
}

.btn-add-cart i {
    font-size: 0.9rem;
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
        <li class="nav-item"><a class="nav-link <?= $sort=='newest' ? 'active' : '' ?>" href="product.php?sort=newest">Product</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart (<?= $cart_count ?>)</a></li>
        <?php if ($customer_id): ?>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="regform.php">Register/Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<header class="masthead d-flex align-items-center justify-content-center">
  <div class="container text-center">
    <div class="masthead-subheading">Welcome To Urban Aura!</div>
    <div class="masthead-heading text-uppercase">Shop the Vibe</div>
  </div>
</header>

<main class="container mt-5">

  <h2 class="category-title mb-0 text-center">Collections</h2>

  <div class="sort-container">
    <div class="sort-buttons">
      <button
        class="<?= $sort === 'newest' ? 'active' : '' ?>"
        data-sort="newest"
        data-order="<?= next_order($sort === 'newest' ? $order_dir : 'desc') ?>"
        type="button"
      >
        Newest <i class="fas fa-arrow-<?= ($sort === 'newest' && $order_dir === 'asc') ? 'up' : 'down' ?>"></i>
      </button>

      <button
        class="<?= $sort === 'recommended' ? 'active' : '' ?>"
        data-sort="recommended"
        data-order="<?= next_order($sort === 'recommended' ? $order_dir : 'desc') ?>"
        type="button"
      >
        Recommended <i class="fas fa-arrow-<?= ($sort === 'recommended' && $order_dir === 'asc') ? 'up' : 'down' ?>"></i>
      </button>

      <button
        class="<?= $sort === 'cheapest' ? 'active' : '' ?>"
        data-sort="cheapest"
        data-order="<?= next_order($sort === 'cheapest' ? $order_dir : 'asc') ?>"
        type="button"
      >
        Cheapest <i class="fas fa-arrow-<?= ($sort === 'cheapest' && $order_dir === 'asc') ? 'up' : 'down' ?>"></i>
      </button>
    </div>
  </div>

  <div class="products-grid">
    <?php while($row = $products->fetch_assoc()): ?>
      <div class="product-card">
        <img src="<?= htmlspecialchars('assets/'.$row['product_id'].'.jpg') ?>"
             alt="<?= htmlspecialchars($row['product_name']) ?>" />
        <div class="product-name"><?= htmlspecialchars($row['product_name']) ?></div>
        <div class="product-desc"><?= htmlspecialchars($row['description']) ?></div>
        <div class="product-price">₱<?= number_format($row['price'], 2) ?></div>
        <div class="product-rating">Rating: <?= htmlspecialchars($row['product_rating']) ?></div>
        <div class="product-date">Added on: <?= date('Y-m-d', strtotime($row['date_added'])) ?></div>
        <form method="post" class="d-inline-block">
          <input type="number" name="qty" value="1" min="1" style="width: 60px;" />
          <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>" />
          <button class="btn-add-cart" type="submit">Add to Cart</button>
        </form>
      </div>
    <?php endwhile; ?>
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


</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
 
  document.querySelectorAll('.sort-buttons button').forEach(button => {
    button.addEventListener('click', () => {
      const sort = button.getAttribute('data-sort');
      const order = button.getAttribute('data-order');

      const url = new URL(window.location);
      url.searchParams.set('sort', sort);
      url.searchParams.set('order', order);
      window.location.href = url.toString();
    });
  });
</script>

</body>
</html>
