<?php
session_start();
include 'db_connect.php';

// Check if user is logged in to fetch user details
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
    <title>Barangay San Isidro Management System</title>
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
        <div class="bg-white p-8 rounded-lg shadow-lg text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Welcome to Barangay San Isidro Management System</h2>
            <p class="text-gray-600 mb-6">Your one-stop platform for managing barangay services and information.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p class="text-lg text-gray-700 mb-4">Hello, <?php echo htmlspecialchars($user['first_name']); ?>! Access your dashboard to manage your account and explore our services.</p>
                <a href="dashboard.php" class="inline-block bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">Go to Dashboard</a>
            <?php else: ?>
                <p class="text-lg text-gray-700 mb-4">Please log in or register to access our services.</p>
                <div class="space-x-4">
                    <a href="login.php" class="inline-block bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">Login</a>
                    <a href="register.php" class="inline-block bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">Register</a>
                </div>
            <?php endif; ?>

            <!-- Resident Features Section -->
            <div class="mt-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Features for Residents</h3>
                <p class="text-gray-600 mb-6">Explore the services available to you as a resident of Barangay San Isidro.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h4 class="text-lg font-medium text-gray-700">Manage Your Account</h4>
                        <p class="text-gray-600">Update your personal details and view your account information securely.</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="profile.php" class="text-indigo-600 hover:underline">Go to Profile</a>
                        <?php else: ?>
                            <a href="login.php" class="text-indigo-600 hover:underline">Login to Access</a>
                        <?php endif; ?>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h4 class="text-lg font-medium text-gray-700">Request Certificates</h4>
                        <p class="text-gray-600">Apply for barangay clearances or residence certificates and track their status.</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="certificate.php" class="text-indigo-600 hover:underline">Request Now</a>
                        <?php else: ?>
                            <a href="login.php" class="text-indigo-600 hover:underline">Login to Request</a>
                        <?php endif; ?>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h4 class="text-lg font-medium text-gray-700">View Announcements</h4>
                        <p class="text-gray-600">Stay updated with barangay news, events, and emergency alerts.</p>
                        <a href="announcements.php" class="text-indigo-600 hover:underline">View Announcements</a>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h4 class="text-lg font-medium text-gray-700">Report Incidents</h4>
                        <p class="text-gray-600">Submit complaints or incident reports (e.g., disputes) and track their progress.</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="blotter.php" class="text-indigo-600 hover:underline">Report Now</a>
                        <?php else: ?>
                            <a href="login.php" class="text-indigo-600 hover:underline">Login to Report</a>
                        <?php endif; ?>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h4 class="text-lg font-medium text-gray-700">Barangay Activities</h4>
                        <p class="text-gray-600">Access details of upcoming and past community events and programs.</p>
                        <a href="activities.php" class="text-indigo-600 hover:underline">View Activities</a>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h4 class="text-lg font-medium text-gray-700">Service Requests</h4>
                        <p class="text-gray-600">Request barangay services and monitor their status online.</p>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="services.php" class="text-indigo-600 hover:underline">Request Services</a>
                        <?php else: ?>
                            <a href="login.php" class="text-indigo-600 hover:underline">Login to Request</a>
                        <?php endif; ?>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h4 class="text-lg font-medium text-gray-700">Mobile Access</h4>
                        <p class="text-gray-600">Use our mobile-friendly platform to access services anytime, anywhere.</p>
                        <a href="mobile.php" class="text-indigo-600 hover:underline">Learn More</a>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                        <h4 class="text-lg font-medium text-gray-700">Public Information</h4>
                        <p class="text-gray-600">View barangay contacts, policies, and community statistics.</p>
                        <a href="info.php" class="text-indigo-600 hover:underline">Explore Now</a>
                    </div>
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