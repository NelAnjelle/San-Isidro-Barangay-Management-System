<?php
session_start();
include 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, suffix FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle certificate request
$errors = [];
$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $certificate_type = trim($_POST['certificate_type']);
    $purpose = trim($_POST['purpose']);
    
    // Validation
    if (empty($certificate_type) || empty($purpose)) {
        $errors[] = "All fields are required.";
    }
    if (!in_array($certificate_type, ['Barangay Clearance', 'Residence Certificate'])) {
        $errors[] = "Invalid certificate type.";
    }

    // Insert request if no errors
    if (empty($errors)) {
        $status = "Pending";
        $stmt = $conn->prepare("INSERT INTO certificate_requests (user_id, certificate_type, purpose, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $_SESSION['user_id'], $certificate_type, $purpose, $status);
        if ($stmt->execute()) {
            $success = "Certificate request submitted successfully!";
        } else {
            $errors[] = "Failed to submit request. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch user's certificate requests
$requests = [];
$stmt = $conn->prepare("SELECT certificate_type, purpose, status, created_at FROM certificate_requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Requests - Barangay San Isidro Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="bg-green-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Barangay San Isidro Management System</h1>
            <div class="space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ($user['suffix'] ? ' ' . $user['suffix'] : '')); ?>!</span>
                <a href="home.php" class="hover:underline">Home</a>
                <a href="dashboard.php" class="hover:underline">Dashboard</a>
                <a href="logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto mt-8 px-4 flex-grow">
        <div class="bg-white p-8 rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Request a Certificate</h2>
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="certificate_type" class="block text-sm font-medium text-gray-700">Certificate Type*</label>
                    <select id="certificate_type" name="certificate_type" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="Barangay Clearance">Barangay Clearance</option>
                        <option value="Residence Certificate">Residence Certificate</option>
                    </select>
                </div>
                <div>
                    <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose*</label>
                    <textarea id="purpose" name="purpose" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Submit Request</button>
            </form>
            <div class="mt-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Your Certificate Requests</h3>
                <?php if (empty($requests)): ?>
                    <p class="text-gray-600">No certificate requests found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="py-2 px-4 border-b text-left">Certificate Type</th>
                                    <th class="py-2 px-4 border-b text-left">Purpose</th>
                                    <th class="py-2 px-4 border-b text-left">Status</th>
                                    <th class="py-2 px-4 border-b text-left">Date Requested</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['certificate_type']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['purpose']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['status']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($request['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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