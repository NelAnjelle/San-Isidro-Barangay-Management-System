<?php
session_start();
include 'db_connect.php';

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Handle registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $suffix = trim($_POST['suffix']);
    $phone_number = trim($_POST['phone_number']);
    $errors = [];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || 
        empty($first_name) || empty($last_name) || empty($phone_number)) {
        $errors[] = "All required fields must be filled.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!preg_match("/^[a-zA-Z ]*$/", $first_name) || !preg_match("/^[a-zA-Z ]*$/", $last_name)) {
        $errors[] = "Name fields should only contain letters and spaces.";
    }
    if (!empty($middle_name) && !preg_match("/^[a-zA-Z ]*$/", $middle_name)) {
        $errors[] = "Middle name should only contain letters and spaces.";
    }
    if (!preg_match("/^[0-9]{10,11}$/", $phone_number)) {
        $errors[] = "Invalid phone number format. Use 10 or 11 digits.";
    }
    if (!in_array($suffix, ['', 'Jr.', 'Sr.', 'II', 'III', 'IV', 'V'])) {
        $errors[] = "Invalid suffix selected.";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username or email already taken.";
    }
    $stmt->close();

    // If no errors, proceed with registration
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, middle_name, last_name, suffix, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $username, $email, $hashed_password, $first_name, $middle_name, $last_name, $suffix, $phone_number);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please log in.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Barangay Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Barangay San Isidro Management System</h1>
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name*</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
                </div>
                <div>
                    <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name*</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
                </div>
                <div>
                    <label for="suffix" class="block text-sm font-medium text-gray-700">Suffix</label>
                    <select id="suffix" name="suffix" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
                        <option value="" <?php echo (isset($_POST['suffix']) && $_POST['suffix'] == '') ? 'selected' : ''; ?>>None</option>
                        <option value="Jr." <?php echo (isset($_POST['suffix']) && $_POST['suffix'] == 'Jr.') ? 'selected' : ''; ?>>Jr.</option>
                        <option value="Sr." <?php echo (isset($_POST['suffix']) && $_POST['suffix'] == 'Sr.') ? 'selected' : ''; ?>>Sr.</option>
                        <option value="II" <?php echo (isset($_POST['suffix']) && $_POST['suffix'] == 'II') ? 'selected' : ''; ?>>II</option>
                        <option value="III" <?php echo (isset($_POST['suffix']) && $_POST['suffix'] == 'III') ? 'selected' : ''; ?>>III</option>
                        <option value="IV" <?php echo (isset($_POST['suffix']) && $_POST['suffix'] == 'IV') ? 'selected' : ''; ?>>IV</option>
                        <option value="V" <?php echo (isset($_POST['suffix']) && $_POST['suffix'] == 'V') ? 'selected' : ''; ?>>V</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number*</label>
                <input type="text" id="phone_number" name="phone_number" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
            </div>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username*</label>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email*</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password*</label>
                <input type="password" id="password" name="password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
            </div>
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password*</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
            </div>
            <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">Register</button>
            <p class="text-center text-sm text-gray-600 mt-4">Already have an account? <a href="login.php" class="text-indigo-600 hover:underline">Login here</a>.</p>
        </form>
    </div>
</body>
</html>