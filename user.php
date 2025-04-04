<?php
require '../connect.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "GET" || $action === "get") {
    $sql = "SELECT user_id AS id_user, user_name AS username, phone, address FROM users";
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        echo json_encode(["status" => "error", 
        "message" => "Query failed: " . mysqli_error($conn)]);
        exit();
    }
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
    echo json_encode($users);
    exit();
}
elseif ($action === "add") {
    $username = trim($data['username'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');
    $password_raw = trim($data['password'] ?? '');
    if (empty($username) || empty($password_raw)) {
        echo json_encode(["status" => "error", 
        "message" => "Username and password are required."]);
        exit();
    }
    $password = password_hash($password_raw, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (user_name, phone, address, password) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $username, $phone, $address, $password);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", 
        "message" => "User added successfully."]);
    } else {
        echo json_encode(["status" => "error", 
        "message" => "Cannot add user."]);
    }
    mysqli_stmt_close($stmt);
    exit();
}
elseif ($action === "update") {
    $id_user = $data['id_user'] ?? 0;
    $phone = $data['phone'] ?? '';
    $address = $data['address'] ?? '';
    $sql = "UPDATE users SET phone = ?, address = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $phone, $address, $id_user);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", 
        "message" => "User updated successfully."]);
    } else {
        echo json_encode(["status" => "error", 
        "message" => "Could not update user."]);
    }
    mysqli_stmt_close($stmt);
    exit();
}
elseif ($action === "delete") {
    $id_user = $data['id_user'] ?? 0;
    if ($id_user == 0) {
        echo json_encode(["status" => "error", 
        "message" => "Invalid user ID"]);
        exit();
    }
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", 
        "message" => "User deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", 
        "message" => "Could not delete user."]);
    }
    mysqli_stmt_close($stmt);
    exit();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            color: #ffffff;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #1e1e1e;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #333;
        }

        th {
            background-color: #333;
            color: #ffffff;
            font-size: 16px;
        }

        td {
            color: #ddd;
        }

        button {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
            transition: 0.3s;
        }

        button:hover {
            opacity: 0.8;
        }

        button:first-child {
            background-color: #4CAF50; 
            color: white;
        }

        button:last-child {
            background-color: #e74c3c;
            color: white;
        }

        .submit {
            background-color: #28a745;
            color: white;
            font-size: 16px;
            padding: 7px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            display: inline-block;
            margin: 15px auto;
            box-shadow: 0px 4px 8px rgba(40, 167, 69, 0.2);
        }

        .submit:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        .submit:active {
            transform: scale(0.95);
        }

        a {
            text-decoration: none; 
            color: inherit;
        }

        .back-btn {
            background-color: #ff4d4d;
            color: white;
            font-size: 16px;
            padding: 8px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            display: inline-block;
            margin: 15px auto;
            box-shadow: 0px 4px 8px rgba(255, 77, 77, 0.2);
        }

        .back-btn:hover {
            background-color: #cc0000;
            transform: scale(1.05);
        }

        .back-btn:active {
            transform: scale(0.95);
        }

        #editForm {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #222;
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(255, 255, 255, 0.2);
        }

        #editForm h3 {
            margin-bottom: 15px;
            color: #ffffff;
        }

        #editForm label {
            display: block;
            text-align: left;
            margin-top: 10px;
            color: #bbbbbb;
            padding-right: 10px; 
        }

        #editForm input {
            width: 100%;
            padding: 8px; 
            margin-top: 5px;
            border: 1px solid #444;
            background-color: #333;
            color: white;
            border-radius: 4px;
            width: 250px;
        }

        #editForm button {
            margin-top: 15px;
            width: 100px;
        }

        #userForm input {
            padding: 8px;
            background-color: #ffffff;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            loadUsers();

            document.getElementById("userForm").addEventListener("submit", function (event) {
                event.preventDefault();
                addUser();
            });

            document.getElementById("saveEditBtn").addEventListener("click", saveEdit);
        });

        function loadUsers() {
            fetch("user.php", { method: "GET", headers: { "Content-Type": "application/json" } })
                .then(response => response.json())
                .then(data => {
                    let userTable = document.getElementById("userTable");
                    userTable.innerHTML = ""; 

                    data.forEach(user => {
                        userTable.innerHTML += `
                            <tr>
                                <td>${user.id_user}</td>
                                <td>${user.username}</td>
                                <td>${user.phone}</td>
                                <td>${user.address}</td>
                                <td>
                                    <button onclick="openEdit(${user.id_user}, '${user.phone}', '${user.address}')">Edit</button>
                                    <button onclick="deleteUser(${user.id_user})">Delete</button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => console.error("Error loading users:", error));
        }

        function addUser() {
            let username = document.getElementById("username").value.trim();
            let phone = document.getElementById("phone").value.trim();
            let address = document.getElementById("address").value.trim();
            let password = document.getElementById("password").value.trim();

            if (!username || !password) {
                alert("Username and password are required");
                return;
            }

            fetch("user.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ 
                    action: "add", 
                    username, 
                    phone, 
                    address, 
                    password 
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    document.getElementById("userForm").reset();
                    loadUsers();
                }
            })
            .catch(error => console.error("Error adding user:", error));
        }

        function openEdit(id, phone, address) {
            document.getElementById("editUserId").value = id;
            document.getElementById("editPhone").value = phone;
            document.getElementById("editAddress").value = address;
            document.getElementById("editForm").style.display = "block";
        }

        function closeEdit() {
            document.getElementById("editForm").style.display = "none";
        }

        function saveEdit() {
            let id_user = document.getElementById("editUserId").value;
            let phone = document.getElementById("editPhone").value;
            let address = document.getElementById("editAddress").value;

            fetch("user.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ action: "update", id_user, phone, address })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                closeEdit();
                loadUsers();
            })
            .catch(error => console.error("Error updating user:", error));
        }

        function deleteUser(id_user) {
            if (confirm("Are you sure?")) {
                fetch("user.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ action: "delete", id_user })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    loadUsers();
                })
                .catch(error => console.error("Error deleting user:", error));
            }
        }
    </script>
</head>
<body>
    <h1>User Management</h1>

    <form id="userForm">
        <input type="text" id="username" placeholder="Username" required>
        <input type="text" id="phone" placeholder="Phone" required>
        <input type="text" id="address" placeholder="Address" required>
        <input type="password" id="password" placeholder="Password" required>
        <button class="submit" type="submit">Add User</button>
        <a class="back-btn" href="http://localhost:5000/login">Back to Admin Homepage</a>
    </form>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="userTable"></tbody>
    </table>
    <div id="editForm">
        <h3>Edit User</h3>
        <input type="hidden" id="editUserId">
        <label>Phone:</label>
        <input type="text" id="editPhone"><br>
        <label>Address:</label>
        <input type="text" id="editAddress"><br><br>
        <button id="saveEditBtn">Save Changes</button>
        <button onclick="closeEdit()">Cancel</button>
    </div>
</body>
</html>
