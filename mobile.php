<?php
session_start();
include 'db_connect.php';

// Fetch user details if logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT first_name, last_name, suffix FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Access - Barangay San Isidro Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="bg-green-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Barangay San Isidro Management System</h1>
            <div class="space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-sm">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ($user['suffix'] ? ' ' . $user['suffix'] : '')); ?>!</span>
                    <a href="home.php" class="hover:underline">Home</a>
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <a href="logout.php" class="hover:underline">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="hover:underline">Login</a>
                    <a href="register.php" class="hover:underline">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto mt-8 px-4 flex-grow">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Mobile Access</h2>
            <p class="text-gray-600 mb-6 text-center">Access Barangay San Isidro Management System on the go!</p>
            <div class="space-y-4">
                <p class="text-gray-700">Our platform is fully responsive, allowing you to access all resident features from your smartphone or tablet. Whether you're requesting a certificate, reporting an incident, or checking announcements, you can do it anytime, anywhere.</p>
                <p class="text-gray-700">To get started, simply visit our website on your mobile browser. No app download is required, as our site is optimized for mobile devices.</p>
                <div class="text-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="inline-block bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="inline-block bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Login to Access</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Barangay San Isidro Management System. All rights reserved.</p>
    </footer>
</body>
</html>