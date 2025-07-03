<?php
session_start();

$conn = new mysqli("localhost", "root", "", "online_merchandise");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$customer_id = $_SESSION['customer_id'] ?? null;
$session_id = session_id();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);


    $stmt = $conn->prepare("SELECT cart_id FROM Cart WHERE cart_id = ? AND (customer_id = ? OR session_id = ?)");
    $stmt->bind_param("iis", $cart_id, $customer_id, $session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {

        $del = $conn->prepare("DELETE FROM Cart WHERE cart_id = ?");
        $del->bind_param("i", $cart_id);
        $del->execute();
        $del->close();
    }

    $stmt->close();
}


header("Location: cart.php");
exit;
?>
