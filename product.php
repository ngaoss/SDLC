<?php
require 'connect.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

    if (isset($data["action"]) && $data["action"] === "delete") {
    }

    if (isset($data["product_id"])) {
    } else {
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            text-align: center;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #1e1e1e;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #333;
        }

        th {
            background-color: #333;
            color: #ffffff;
        }

        td img {
            width: 50px;
            height: auto;
        }

        button {
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            transition: 0.3s;
        }

        button:hover {
            opacity: 0.8;
            background-color: #45a049;
        }
        
        .back-btn {
            background-color: #ff4d4d; 
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s, transform 0.2s;
            box-shadow: 0px 4px 8px rgba(255, 77, 77, 0.2);
            display: inline-block;
            margin: 10px;
        }

        .back-btn:hover {
            background-color: #cc0000;
            transform: scale(1.05);
        }

        .back-btn:active {
            transform: scale(0.95);
        }

        #addForm, #editForm {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #1e1e1e;
            padding: 20px;
            width: 400px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.2);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        input, select {
            padding: 10px;
            border: 1px solid #444;
            border-radius: 5px;
            background: #2c2c2c;
            color: #fff;
            font-size: 14px;
            width: 94.7%;
        }

        input:focus, select:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button[type="submit"] {
            background-color: #007BFF;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        button[type="button"] {
            background-color: #d9534f;
        }

        button[type="button"]:hover {
            background-color: #c9302c;
        }

        #editImagePreview {
            display: block;
            margin: 10px auto;
            width: 80px;
            height: auto;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Product Management</h1>
    <button class="back-btn" onclick="window.location.href = 'http://localhost:5000/login';">Back to Admin Homepage</button>

    <button onclick="openAddForm()">Add Product</button>

    <table id="productTable">
    </table>

    <div id="addForm">
        <h2>Add New Product</h2>
        <form id="productForm">
            <label for="category">Category</label>
            <select id="category" name="category" required></select>

            <label for="productName">Product Name</label>
            <input type="text" id="productName" name="product_name" required />

            <label for="description">Description</label>
            <input type="text" id="description" name="description" required />

            <label for="price">Price</label>
            <input type="number" id="price" name="price" required />

            <label for="image">Image</label>
            <input type="file" id="image" name="image" />

            <button type="submit">Add Product</button>
            <button type="button" onclick="closeAddForm()">Cancel</button>
        </form>
    </div>

    <!-- Edit Product Form -->
    <div id="editForm">
        <h2>Edit Product</h2>
        <form id="editProductForm">
            <input type="hidden" id="editProductId" name="product_id" />

            <label for="editCategory">Category</label>
            <select id="editCategory" name="category" required></select>

            <label for="editProductName">Product Name</label>
            <input type="text" id="editProductName" name="product_name" required />

            <label for="editDescription">Description</label>
            <input type="text" id="editDescription" name="description" required />

            <label for="editPrice">Price</label>
            <input type="number" id="editPrice" name="price" required />

            <label for="editImage">Image</label>
            <input type="file" id="editImage" name="image" />

            <img id="editImagePreview" src="uploads/default.png" alt="Image Preview" />

            <button type="submit">Update Product</button>
            <button type="button" onclick="deleteProduct()">Delete Product</button>
            <button type="button" onclick="closeEditForm()">Cancel</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            loadProducts();
            loadCategories();
        });

        function loadCategories() {
            fetch("product.php?categories=true")
                .then(response => response.json())
                .then(categories => {
                    let addCategorySelect = document.getElementById("category");
                    let editCategorySelect = document.getElementById("editCategory");

                    addCategorySelect.innerHTML = `<option value="">Select a category</option>`;
                    editCategorySelect.innerHTML = `<option value="">Select a category</option>`;

                    categories.forEach(cat => {
                        addCategorySelect.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
                        editCategorySelect.innerHTML += `<option value="${cat.category_id}">${cat.category_name}</option>`;
                    });
                })
                .catch(error => console.error("Error loading categories:", error));
        }

        function loadProducts() {
            fetch("product.php", { method: "GET" })
                .then(response => response.json())
                .then(data => {
                    let productTable = document.getElementById("productTable");
                    productTable.innerHTML = "";
                    data.forEach(product => {
                        let imageUrl = product.image_url && product.image_url !== "0" ? product.image_url : "uploads/default.png"; // Fallback image
                        productTable.innerHTML += `
                            <tr>
                                <td>${product.product_id}</td>
                                <td>${product.product_name}</td>
                                <td>${product.category_id}</td>
                                <td>${product.description}</td>
                                <td>${product.price}</td>
                                <td><img src="${imageUrl}" width="50" onerror="this.onerror=null; this.src='uploads/default.png';"></td>
                                <td>
                                    <button onclick="openEditForm(${product.product_id}, '${product.product_name}', '${product.category_id}', '${product.description}', '${product.price}', '${imageUrl}')">Edit</button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => console.error("Error loading products:", error));
        }

        document.getElementById("productForm").addEventListener("submit", function (e) {
            e.preventDefault();
            let formData = new FormData(this);

            fetch("product.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    document.getElementById("productForm").reset();
                    closeAddForm();
                    loadProducts();
                }
            })
            .catch(error => console.error("Error adding product:", error));
        });

        document.getElementById("editProductForm").addEventListener("submit", function (e) {
            e.preventDefault();

            let formData = new FormData(this);
            formData.append("action", "update"); 
            formData.append("product_id", document.getElementById("editProductId").value);

            fetch("product.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    closeEditForm();
                    loadProducts();
                }
            })
            .catch(error => console.error("Error updating product:", error));
        });

        function deleteProduct() {
            let productId = document.getElementById("editProductId").value;

            if (confirm("Are you sure you want to delete this product?")) {
                let formData = new FormData();
                formData.append("action", "delete");
                formData.append("product_id", productId);

                fetch("product.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === "success") {
                        closeEditForm();
                        loadProducts();
                    }
                })
                .catch(error => console.error("Error deleting product:", error));
            }
        }

        function openAddForm() {
            document.getElementById("addForm").style.display = "block";
        }

        function closeAddForm() {
            document.getElementById("addForm").style.display = "none";
        }

        function openEditForm(id, name, category, description, price, image) {
            document.getElementById("editProductId").value = id;
            document.getElementById("editProductName").value = name;
            document.getElementById("editCategory").value = category;
            document.getElementById("editDescription").value = description;
            document.getElementById("editPrice").value = price;

            let imagePreview = document.getElementById("editImagePreview");
            imagePreview.src = image ? image : "uploads/default.png";  

            document.getElementById("editForm").style.display = "block";
        }

        function closeEditForm() {
            document.getElementById("editForm").style.display = "none";
        }
    </script>
</body>
</html>
