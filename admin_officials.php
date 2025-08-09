<?php
session_start();
include 'db_connect.php';

// Redirect if not an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch admin details
$stmt = $conn->prepare("SELECT first_name, last_name, suffix FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Handle add/edit official
$success_message = $error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $contact = trim($_POST['contact']);
    $photo = null;

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/uploads/';
        $photo_name = time() . '_' . basename($_FILES['photo']['name']);
        $photo_path = $upload_dir . $photo_name;
        $allowed_types = ['image/jpeg', 'image/png'];
        if (in_array($_FILES['photo']['type'], $allowed_types) && $_FILES['photo']['size'] <= 2 * 1024 * 1024) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/' . $photo_path)) {
                $photo = $photo_path;
            } else {
                $error_message = "Failed to upload photo.";
            }
        } else {
            $error_message = "Invalid photo type or size (max 2MB, JPG/PNG).";
        }
    }

    if (!$error_message) {
        if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
            // Update existing official
            $edit_id = $_POST['edit_id'];
            if ($photo) {
                // Delete old photo if new one is uploaded
                $stmt = $conn->prepare("SELECT photo FROM officials WHERE id = ?");
                $stmt->bind_param("i", $edit_id);
                $stmt->execute();
                $old_photo = $stmt->get_result()->fetch_assoc()['photo'];
                if ($old_photo && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $old_photo)) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $old_photo);
                }
                $stmt->close();
                $stmt = $conn->prepare("UPDATE officials SET name = ?, position = ?, contact = ?, photo = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $name, $position, $contact, $photo, $edit_id);
            } else {
                $stmt = $conn->prepare("UPDATE officials SET name = ?, position = ?, contact = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $position, $contact, $edit_id);
            }
            if ($stmt->execute()) {
                $success_message = "Official updated successfully!";
            } else {
                $error_message = "Failed to update official.";
            }
            $stmt->close();
        } else {
            // Add new official
            $stmt = $conn->prepare("INSERT INTO officials (name, position, contact, photo, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $position, $contact, $photo);
            if ($stmt->execute()) {
                $success_message = "Official added successfully!";
            } else {
                $error_message = "Failed to add official.";
            }
            $stmt->close();
        }
    }
}

// Handle delete official
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("SELECT photo FROM officials WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $photo = $stmt->get_result()->fetch_assoc()['photo'];
    if ($photo && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $photo)) {
        unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $photo);
    }
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM officials WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $success_message = "Official deleted successfully!";
    } else {
        $error_message = "Failed to delete official.";
    }
    $stmt->close();
}

// Fetch officials
$officials = [];
$result = $conn->query("SELECT id, name, position, contact, photo FROM officials ORDER BY 
    CASE 
        WHEN position = 'Barangay Captain' THEN 1 
        WHEN position = 'Secretary' THEN 2 
        ELSE 3 
    END, name");
while ($row = $result->fetch_assoc()) {
    $officials[] = $row;
}

// Fetch official for editing (if edit_id is set)
$edit_official = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $conn->prepare("SELECT id, name, position, contact, photo FROM officials WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_official = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officials Management - Barangay San Isidro Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg-nav {
            background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
        }
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
    <nav class="gradient-bg-nav text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Barangay San Isidro Management System - Admin</h1>
            <div class="space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name'] . ($admin['suffix'] ? ' ' . $admin['suffix'] : '')); ?>!</span>
                <a href="admin_dashboard.php" class="hover:underline">Dashboard</a>
                <a href="admin_logout.php" class="hover:underline">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto mt-8 px-4 flex-grow">
        <div class="bg-white p-8 rounded-2xl shadow-xl">
            <h2 class="text-3xl font-extrabold text-gray-900 mb-6 text-center">Officials Management</h2>
            <p class="text-center text-gray-600 mb-6">Add, edit, or delete barangay officials' information.</p>

            <!-- Messages -->
            <?php if ($success_message): ?>
                <p class="text-green-600 mb-4 text-center"><?php echo htmlspecialchars($success_message); ?></p>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <p class="text-red-600 mb-4 text-center"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <!-- Add/Edit Official Form -->
            <div class="mb-8">
                <h3 class="text-2xl font-semibold text-gray-800 mb-4"><?php echo $edit_official ? 'Edit Official' : 'Add New Official'; ?></h3>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <?php if ($edit_official): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $edit_official['id']; ?>">
                    <?php endif; ?>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" id="name" value="<?php echo $edit_official ? htmlspecialchars($edit_official['name']) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                        <select name="position" id="position" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="Barangay Captain" <?php echo $edit_official && $edit_official['position'] === 'Barangay Captain' ? 'selected' : ''; ?>>Barangay Captain</option>
                            <option value="Secretary" <?php echo $edit_official && $edit_official['position'] === 'Secretary' ? 'selected' : ''; ?>>Secretary</option>
                            <option value="Kagawad" <?php echo $edit_official && $edit_official['position'] === 'Kagawad' ? 'selected' : ''; ?>>Kagawad</option>
                        </select>
                    </div>
                    <div>
                        <label for="contact" class="block text-sm font-medium text-gray-700">Contact</label>
                        <input type="text" name="contact" id="contact" value="<?php echo $edit_official ? htmlspecialchars($edit_official['contact']) : ''; ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700">Photo (JPG/PNG, max 2MB)</label>
                        <input type="file" name="photo" id="photo" accept="image/jpeg,image/png" class="mt-1 block w-full">
                        <?php if ($edit_official && $edit_official['photo']): ?>
                            <p class="text-sm text-gray-500 mt-1">Current photo: <img src="<?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $edit_official['photo']) ? htmlspecialchars($edit_official['photo']) : 'assets/uploads/placeholder.jpg'; ?>" alt="Current Photo" class="w-16 h-16 rounded-full official-img inline-block"></p>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"><?php echo $edit_official ? 'Update Official' : 'Add Official'; ?></button>
                </form>
            </div>

            <!-- Officials List -->
            <div>
                <h3 class="text-2xl font-semibold text-gray-800 mb-4">Current Officials</h3>
                <?php if (empty($officials)): ?>
                    <p class="text-gray-600">No officials found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="py-2 px-4 border-b text-left">Photo</th>
                                    <th class="py-2 px-4 border-b text-left">Name</th>
                                    <th class="py-2 px-4 border-b text-left">Position</th>
                                    <th class="py-2 px-4 border-b text-left">Contact</th>
                                    <th class="py-2 px-4 border-b text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($officials as $official): ?>
                                    <tr class="official-card">
                                        <td class="py-2 px-4 border-b">
                                            <img src="<?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $official['photo']) ? htmlspecialchars($official['photo']) : 'assets/uploads/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($official['name']); ?>" class="w-12 h-12 rounded-full official-img">
                                        </td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($official['name']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($official['position']); ?></td>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($official['contact'] ?? 'N/A'); ?></td>
                                        <td class="py-2 px-4 border-b">
                                            <a href="admin_officials.php?edit_id=<?php echo $official['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
                                            <a href="admin_officials.php?delete_id=<?php echo $official['id']; ?>" class="text-red-600 hover:underline ml-2" onclick="return confirm('Are you sure you want to delete this official?');">Delete</a>
                                        </td>
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