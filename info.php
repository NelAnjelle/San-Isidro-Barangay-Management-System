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

// Sample public information
$barangay_info = [
    'name' => 'Barangay San Isidro',
    'address' => '123 Barangay Hall, San Isidro, Philippines',
    'contact' => '(123) 456-7890',
    'email' => 'info@sanisidro.gov.ph',
    'population' => '5,000 residents',
    'zones' => '7 zones',
];

// Sample officials data with unique image paths
$officials = [
    ['name' => 'John Doe', 'position' => 'Barangay Captain', 'contact' => '(123) 456-7890', 'photo' => 'assets/uploads/captain.jpg'],
    ['name' => 'Jane Smith', 'position' => 'Secretary', 'contact' => '(123) 456-7891', 'photo' => 'assets/uploads/secretary.jpg'],
    ['name' => 'Michael Cruz', 'position' => 'Kagawad', 'contact' => '(123) 456-7892', 'photo' => 'assets/uploads/kagawad1.jpg'],
    ['name' => 'Anna Reyes', 'position' => 'Kagawad', 'contact' => '(123) 456-7893', 'photo' => 'assets/uploads/kagawad2.jpg'],
    ['name' => 'Pedro Santos', 'position' => 'Kagawad', 'contact' => '(123) 456-7894', 'photo' => 'assets/uploads/kagawad3.jpg'],
    ['name' => 'Maria Garcia', 'position' => 'Kagawad', 'contact' => '(123) 456-7895', 'photo' => 'assets/uploads/kagawad4.jpg'],
    ['name' => 'Luis Tan', 'position' => 'Kagawad', 'contact' => '(123) 456-7896', 'photo' => 'assets/uploads/kagawad5.jpg'],
    ['name' => 'Clara Lim', 'position' => 'Kagawad', 'contact' => '(123) 456-7897', 'photo' => 'assets/uploads/kagawad6.jpg'],
    ['name' => 'Jose Morales', 'position' => 'Kagawad', 'contact' => '(123) 456-7898', 'photo' => 'assets/uploads/kagawad7.jpg'],
    ['name' => 'Teresa Aquino', 'position' => 'Kagawad', 'contact' => '(123) 456-7899', 'photo' => 'assets/uploads/kagawad8.jpg'],
    ['name' => 'Ramon Diaz', 'position' => 'Kagawad', 'contact' => '(123) 456-7900', 'photo' => 'assets/uploads/kagawad9.jpg'],
    ['name' => 'Luz Villanueva', 'position' => 'Kagawad', 'contact' => '(123) 456-7901', 'photo' => 'assets/uploads/kagawad10.jpg'],
    ['name' => 'Carlos Mendoza', 'position' => 'Kagawad', 'contact' => '(123) 456-7902', 'photo' => 'assets/uploads/kagawad11.jpg'],
    ['name' => 'Elena Navarro', 'position' => 'Kagawad', 'contact' => '(123) 456-7903', 'photo' => 'assets/uploads/kagawad12.jpg'],
];

/* Uncomment to fetch from officials table
$officials = [];
$has_officials_table = $conn->query("SHOW TABLES LIKE 'officials'")->num_rows > 0;
if ($has_officials_table) {
    $result = $conn->query("SELECT name, position, contact, photo FROM officials ORDER BY 
        CASE 
            WHEN position = 'Barangay Captain' THEN 1 
            WHEN position = 'Secretary' THEN 2 
            ELSE 3 
        END, name");
    while ($row = $result->fetch_assoc()) {
        $officials[] = $row;
    }
}
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Information - Barangay San Isidro Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .official-card {
            animation: fadeIn 0.5s ease-out;
        }
        .official-img {
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }
    </style>
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
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Public Information</h2>
            <p class="text-gray-600 mb-6 text-center">Learn more about Barangay San Isidro and its services.</p>

            <!-- Barangay Details -->
            <div class="space-y-4 mb-8">
                <div class="bg-gray-50 p-4 rounded-md shadow-sm">
                    <h3 class="text-xl font-semibold text-gray-800">Barangay Details</h3>
                    <p class="text-gray-600 mt-2"><strong>Name:</strong> <?php echo htmlspecialchars($barangay_info['name']); ?></p>
                    <p class="text-gray-600"><strong>Address:</strong> <?php echo htmlspecialchars($barangay_info['address']); ?></p>
                    <p class="text-gray-600"><strong>Contact:</strong> <?php echo htmlspecialchars($barangay_info['contact']); ?></p>
                    <p class="text-gray-600"><strong>Email:</strong> <?php echo htmlspecialchars($barangay_info['email']); ?></p>
                    <p class="text-gray-600"><strong>Population:</strong> <?php echo htmlspecialchars($barangay_info['population']); ?></p>
                    <p class="text-gray-600"><strong>Zones:</strong> <?php echo htmlspecialchars($barangay_info['zones']); ?></p>
                </div>
            </div>

            <!-- Barangay Officials -->
            <div class="space-y-6">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Barangay Officials</h3>
                <?php
                $captain = array_filter($officials, fn($official) => $official['position'] === 'Barangay Captain');
                $secretary = array_filter($officials, fn($official) => $official['position'] === 'Secretary');
                $kagawads = array_filter($officials, fn($official) => $official['position'] === 'Kagawad');
                ?>

                <!-- Barangay Captain -->
                <?php if (!empty($captain)): ?>
                    <div class="bg-gray-50 p-6 rounded-md shadow-sm official-card">
                        <h4 class="text-xl font-semibold text-gray-800 text-center">Barangay Captain</h4>
                        <?php foreach ($captain as $official): ?>
                            <div class="mt-4 flex flex-col items-center">
                                <img src="<?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $official['photo']) ? htmlspecialchars($official['photo']) : 'assets/uploads/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($official['name']); ?>" class="w-32 h-32 rounded-full mb-3 official-img">
                                <p class="text-gray-600 text-lg font-semibold"><?php echo htmlspecialchars($official['name']); ?></p>
                                <p class="text-gray-500"><?php echo htmlspecialchars($official['contact'] ?? 'N/A'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Secretary -->
                <?php if (!empty($secretary)): ?>
                    <div class="bg-gray-50 p-6 rounded-md shadow-sm official-card">
                        <h4 class="text-xl font-semibold text-gray-800 text-center">Secretary</h4>
                        <?php foreach ($secretary as $official): ?>
                            <div class="mt-4 flex flex-col items-center">
                                <img src="<?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $official['photo']) ? htmlspecialchars($official['photo']) : 'assets/uploads/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($official['name']); ?>" class="w-28 h-28 rounded-full mb-3 official-img">
                                <p class="text-gray-600 text-lg font-semibold"><?php echo htmlspecialchars($official['name']); ?></p>
                                <p class="text-gray-500"><?php echo htmlspecialchars($official['contact'] ?? 'N/A'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Kagawads -->
                <?php if (!empty($kagawads)): ?>
                    <div class="bg-gray-50 p-6 rounded-md shadow-sm official-card">
                        <h4 class="text-xl font-semibold text-gray-800 mb-4 text-center">Kagawads</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            <?php foreach ($kagawads as $official): ?>
                                <div class="flex flex-col items-center">
                                    <img src="<?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $official['photo']) ? htmlspecialchars($official['photo']) : 'assets/uploads/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($official['name']); ?>" class="w-24 h-24 rounded-full mb-2 official-img">
                                    <p class="text-gray-600 font-medium"><?php echo htmlspecialchars($official['name']); ?></p>
                                    <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($official['contact'] ?? 'N/A'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center p-4 mt-auto">
        <p>&copy; <?php echo date("Y"); ?> Barangay San Isidro Management System. All rights reserved.</p>
    </footer>
</body>
</html>