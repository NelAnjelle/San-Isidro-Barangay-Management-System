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

$errors = [];
$success = '';

// Handle announcement creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $event_start_datetime = trim($_POST['event_start_datetime']);
    $event_end_datetime = trim($_POST['event_end_datetime']);
    $event_location = trim($_POST['event_location']);
    
    if (empty($title) || empty($content)) {
        $errors[] = "Title and content are required.";
    } elseif (empty($event_start_datetime)) {
        $errors[] = "Event start date and time are required.";
    } elseif (empty($event_end_datetime)) {
        $errors[] = "Event end date and time are required.";
    } elseif (strtotime($event_end_datetime) <= strtotime($event_start_datetime)) {
        $errors[] = "Event end date and time must be after the start date and time.";
    } elseif (empty($event_location)) {
        $errors[] = "Event location is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, admin_id, event_start_datetime, event_end_datetime, event_location) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $title, $content, $_SESSION['admin_id'], $event_start_datetime, $event_end_datetime, $event_location);
        if ($stmt->execute()) {
            $success = "Announcement created successfully.";
        } else {
            $errors[] = "Failed to create announcement: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle announcement deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND admin_id = ?");
    $stmt->bind_param("ii", $delete_id, $_SESSION['admin_id']);
    if ($stmt->execute()) {
        $success = "Announcement deleted successfully.";
    } else {
        $errors[] = "Failed to delete announcement: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch announcements
$query = "SELECT id, title, content, event_start_datetime, event_end_datetime, event_location FROM announcements WHERE admin_id = " . $_SESSION['admin_id'] . " ORDER BY event_start_datetime DESC";
$announcements = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barangay Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }
        .gradient-bg-nav {
            background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
        }
        .input-focus {
            transition: all 0.3s ease;
        }
        .input-focus:focus {
            border-color: #15803d;
            box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.2);
        }
        .btn-hover {
            transition: all 0.3s ease;
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        <div class="bg-white p-8 rounded-2xl shadow-xl animate-fadeIn">
            <div class="flex justify-center mb-6">
                <img src="img/logo.png" alt="Barangay San Isidro Logo" class="h-12">
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 mb-6 text-center">Announcement Management</h2>
            <p class="text-center text-gray-600 mb-6">Create and manage barangay announcements.</p>

            <!-- Success/Error Messages -->
            <?php if (!empty($success)): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
                    <p class="text-sm"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
                    <?php foreach ($errors as $error): ?>
                        <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Create Announcement Form -->
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Create New Announcement</h3>
                <form method="POST" action="" class="space-y-5">
                    <input type="hidden" name="create" value="1">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" id="title" name="title" required
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 input-focus"
                               placeholder="Enter announcement title">
                    </div>
                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                        <textarea id="content" name="content" required rows="4"
                                  class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 input-focus"
                                  placeholder="Enter announcement content"></textarea>
                    </div>
                    <div>
                        <label for="event_start_datetime" class="block text-sm font-medium text-gray-700">Event Start Date and Time</label>
                        <input type="datetime-local" id="event_start_datetime" name="event_start_datetime" required
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 input-focus"
                               placeholder="Select event start date and time">
                    </div>
                    <div>
                        <label for="event_end_datetime" class="block text-sm font-medium text-gray-700">Event End Date and Time</label>
                        <input type="datetime-local" id="event_end_datetime" name="event_end_datetime" required
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 input-focus"
                               placeholder="Select event end date and time">
                    </div>
                    <div>
                        <label for="event_location" class="block text-sm font-medium text-gray-700">Event Location</label>
                        <input type="text" id="event_location" name="event_location" required
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-900 input-focus"
                               placeholder="Enter event location">
                    </div>
                    <button type="submit"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-lg btn-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Create Announcement
                    </button>
                </form>
            </div>

            <!-- Announcements List -->
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Existing Announcements</h3>
                <?php if ($announcements->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-gray-50 rounded-lg shadow-sm">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Title</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Content</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Event Start</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Event End</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Location</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $announcements->fetch_assoc()): ?>
                                    <tr class="border-t">
                                        <td class="px-4 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?php echo htmlspecialchars(substr($row['content'], 0, 100)) . (strlen($row['content']) > 100 ? '...' : ''); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($row['event_start_datetime']); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($row['event_end_datetime']); ?></td>
                                        <td class="px-4 py-2 text-sm text-gray-600"><?php echo htmlspecialchars($row['event_location']); ?></td>
                                        <td class="px-4 py-2">
                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600 text-center">No announcements found.</p>
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