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

// Fetch announcements (sample data, replace with actual database query)
$announcements = [
    ['title' => 'Community Clean-Up Drive', 'content' => 'Join us on August 10, 2025, for a barangay-wide clean-up drive.', 'date' => '2025-08-01'],
    ['title' => 'Emergency Preparedness Seminar', 'content' => 'Learn about disaster preparedness on August 15, 2025.', 'date' => '2025-08-02'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements - Barangay San Isidro Management System</title>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Announcements</h2>
            <p class="text-gray-600 mb-6 text-center">Stay updated with the latest news and events in Barangay San Isidro.</p>
            <?php if (empty($announcements)): ?>
                <p class="text-gray-600 text-center">No announcements available at this time.</p>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                            <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                            <p class="text-gray-600 mt-2"><?php echo htmlspecialchars($announcement['content']); ?></p>
                            <p class="text-sm text-gray-500 mt-2">Posted on: <?php echo htmlspecialchars($announcement['date']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Barangay San Isidro Management System. All rights reserved.</p>
    </footer>
</body>
</html>