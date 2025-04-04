<?php
require '../connect.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

$sql = "SELECT * FROM products WHERE 1";

if ($category_id > 0) {
    $sql .= " AND category_id = $category_id";
}

if (!empty($search)) {
    $sql .= " AND (product_name LIKE '%$search%' OR description LIKE '%$search%')";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LSTUFF Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        h4 {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 30px;
            margin-bottom: 10px;
        }

        .logo {
            text-decoration: none;
            color: inherit;
            font-weight: bold;
            cursor: pointer;
            font-size: 24px;
        }

        .cart-button,
        .logout-button,
        .search-button {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            position: relative;
            transition: transform 0.2s ease;
        }

        #cart-count {
            display: none;
        }

        nav {
            padding: 10px;
        }

        nav ul {
            list-style: none;
            display: flex;
        }

        nav ul li {
            margin: 0 15px;
            position: relative;
        }

        nav ul li a {
            text-decoration: none;
            color: black;
        }

        nav ul li a:hover {
            color: #646464;
            transform: scale(1.05);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #ffffff;
            list-style: none;
            margin: 0;
            border-radius: 5px;
            z-index: 100;
        }

        .dropdown-menu li {
            padding: 1px 1px;
            font-size: 14px;
            white-space: nowrap;
        }

        .dropdown-menu li:last-child {
            border-bottom: none;
        }

        .dropdown-menu li a {
            color: black;
            text-decoration: none;
            display: block;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.5;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dropdown-menu li a:hover {
            background-color: #f0f0f0;
        }

        .dropdown-menu li.disabled a {
            color: rgba(255, 255, 255, 0.5);
            pointer-events: none;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        input {
            padding: 10px;
            width: 600px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        body {
            background: #f8f8f8;
            padding-top: 80px;
        }

        #product-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 100px;
            padding: 20px 10%;
        }

        .product-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: none;
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            width: 300px;
            height: auto;
            text-align: center;
            font-size: 14px;
        }

        .product-card h3 {
            margin-top: 10px;
            flex-grow: 1;
            text-align: center;
            font-weight: 500;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .product-card p {
            flex-grow: 1;
        }

        .price {
            text-align: start;
            font-weight: bolder;
            font-size: 20px;
            margin-top: 30px;
            margin-bottom: 10px;
        }

        .product-card .add-to-cart {
            margin-top: auto;
        }

        .add-to-cart {
            background-color: #ff9900;
            color: white;
            border: none;
            padding: 9px;
            cursor: pointer;
            width: 50%;
            font-size: 14px;
            border-radius: 20px;
        }

        .add-to-cart:hover {
            background-color: #e68a00;
        }

        .footer {
            background-color: #F4F6F8;
            padding: 40px 80px;
            border-top: 1px solid #ddd;
            margin-top: 5%;
        }

        .footer-top {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 1px solid #ddd;
            margin-left: 22%;
            margin-right: 22%;
        }

        .footer-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 3%;
        }

        .footer-item img {
            width: 50px;
            height: auto;
            margin-bottom: 10px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            padding: 40px 10%;
            flex-wrap: wrap;
        }

        .footer-column {
            margin-left: 5%;
            margin-right: 6%;
            line-height: 30px;
        }

        .footer-column h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .footer-column ul {
            list-style: none;
        }

        .footer-column ul li {
            margin-bottom: 8px;
        }

        .footer-column ul li a {
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        .footer-bottom {
            display: flex;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 1px;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: center;
            }

            input {
                width: 90%;
                margin-top: 10px;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                padding: 20px;
            }

            .footer-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const cartButton = document.querySelector(".cart-button");
            const cartCount = document.getElementById("cart-count");

            function updateCartCount() {
                let cart = JSON.parse(localStorage.getItem("cart")) || [];
                let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                if (cartCount) cartCount.textContent = totalItems;
            }

            if (cartButton) {
                cartButton.addEventListener("click", () => {
                    window.location.href = "../cart/cart.html";
                });
            }

            updateCartCount();
            document.querySelectorAll(".add-to-cart").forEach(button => {
                button.addEventListener("click", function (event) {

                    event.preventDefault();

                    let product = this.closest('.product-card');

                    if (!product) return;

                    let productName = product.querySelector("h3").textContent.trim();
                    let productPrice = parseFloat(product.querySelector(".price").textContent.replace(/[^0-9.]/g, ''));
                    let productImg = product.querySelector("img") ? product.querySelector("img").src : "";

                    let cart = JSON.parse(localStorage.getItem("cart")) || [];
                    let existingProduct = cart.find(item => item.name === productName);

                    if (existingProduct) {
                        existingProduct.quantity++;
                    } else {
                        cart.push({ name: productName, price: productPrice, image: productImg, quantity: 1 });
                    }

                    localStorage.setItem("cart", JSON.stringify(cart));
                    updateCartCount();
                    alert(`${productName} Added to cart!`);
                    console.log("Cart Updated:", cart);
                });
            });

        });
    </script>
</head>

<body>
    <header>
        <a href="homepage.html" class="logo">LSTUFF</a>
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            <button class="search-button">🔍</button>
        </form>
        <nav>
            <ul>
                <li><a href="headphones.php">Headphones</a></li>
                <li><a href="chargers.php">Chargers</a></li>
                <li><a href="powerbanks.php">Power Banks</a></li>
                <li><a href="phone-cases.php">Phone Cases</a></li>
                <li class="dropdown">
                    <a>More ▼</a>
                    <ul class="dropdown-menu">
                        <li><a href="bluetooth-speaker.php">Bluetooth Speakers</a></li>
                        <li><a href="screen-protecters.php">Screen Protecter</a></li>
                        <li><a href="more.php">More Accessories</a></li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="icons">
            <button class="cart-button">🛒 <span id="cart-count"></span></button>
            <button onclick="window.location.href='http://localhost:5000/login'" class="logout-button">🔑</button>
        </div>
    </header>

    <div id="product-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='product-card'>";
                $base_url = "http://localhost/New_folder/product/";
                echo "<img src='" . $base_url . $row["image_url"] . "' alt='" . htmlspecialchars($row["product_name"]) . "'>";

                $max_length = 100;
                $product_name = strlen($row["product_name"]) > $max_length ? substr($row["product_name"], 0, $max_length) . "..." : $row["product_name"];
                $description = strlen($row["description"]) > $max_length ? substr($row["description"], 0, $max_length) . "..." : $row["description"];

                echo "<h3>" . htmlspecialchars($product_name) . " " . htmlspecialchars($description) . "</h3>";
                echo "<span class='price'>$" . $row["price"] . "</span>";
                echo "<button class='add-to-cart'>Add to Cart</button>";
                echo "</div>";
            }
        } else {
            echo "<p>No products found.</p>";
        }
        ?>
    </div>

    <footer class="footer">
        <div class="footer-top">
            <div class="footer-item">
                <img src="../img/truck.png" alt="Free Shipping">
                <h3>Free Shipping</h3>
                <p>On Order Over $50</p>
            </div>
            <div class="footer-item">
                <img src="../img/service.png" alt="Help Center">
                <h3>Help Center</h3>
                <p>24/7 Support System</p>
            </div>
            <div class="footer-item">
                <img src="../img/trade.png" alt="TrustPay">
                <h3>TrustPay</h3>
                <p>Easy Return Policy</p>
            </div>
            <div class="footer-item">
                <img src="../img/payment.png" alt="Secure Payments">
                <h3>Secure Payments</h3>
                <p>Trustable Payment</p>
            </div>
        </div>

        <div class="footer-content">
            <div class="footer-column">
                <h3>Store</h3>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Jobs</a></li>
                    <li><a href="#">Account</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Our Services</h3>
                <ul>
                    <li><a href="#">Manage Deliveries</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Payments</a></li>
                    <li><a href="#">In Press</a></li>
                    <li><a href="#">Returns</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Terms of Use</h3>
                <ul>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Return Policy</a></li>
                    <li><a href="#">Refund Policy</a></li>
                    <li><a href="#">Order Tracking</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h3>Products</h3>
                <ul>
                    <li><a href="#">Consumer</a></li>
                    <li><a href="#">Enterprise</a></li>
                    <li><a href="#">Carrier</a></li>
                    <li><a href="#">Cloud</a></li>
                    <li><a href="#">Executives</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            Copyright © 2025 | Built like Gizmo by Long
        </div>
    </footer>
</body>

</html>

<?php
$conn->close();
?>
