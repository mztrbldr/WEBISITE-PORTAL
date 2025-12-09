<?php
session_start();
include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['login_uni_id'])) {
    header('Location: login.php'); 
    exit;
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['announcement'])) {
        $title = $_POST['title'];
        $message = $_POST['announcement'];
        $posted = date('Y-m-d H:i:s'); 
        $admin_id = $_SESSION['login_uni_id'];

        // Declare a variable for attachments
        $attachments = []; 

        // Check for file attachments
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'][0] != UPLOAD_ERR_NO_FILE) {
            foreach ($_FILES['attachment']['name'] as $key => $fileName) {
                if ($_FILES['attachment']['error'][$key] == UPLOAD_ERR_OK) {
                    $uploadDir = 'uploads/'; 
                    $targetFilePath = $uploadDir . basename($fileName);
                    
                    // Ensure the upload directory is writable
                    if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
                        $_SESSION['error_message'] = 'Upload directory is not writable.';
                    } elseif (move_uploaded_file($_FILES['attachment']['tmp_name'][$key], $targetFilePath)) {
                        $attachments[] = $fileName; 
                    } else {
                        $_SESSION['error_message'] = 'Error moving uploaded file: ' . $fileName;
                    }
                } 
            }
        }

        try {
            // Prepare to insert the announcement
            $stmt = $connect->prepare("INSERT INTO announcements (title, message, posted, admin_id, attachment) VALUES (?, ?, ?, ?, ?)");
            $stmt->bindParam(1, $title);
            $stmt->bindParam(2, $message);
            $stmt->bindParam(3, $posted);
            $stmt->bindParam(4, $admin_id);
            // Convert attachments array to string or use an empty string if there are no attachments
            $attachmentsStr = !empty($attachments) ? implode(',', $attachments) : '';
            $stmt->bindParam(5, $attachmentsStr); 
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Announcement posted successfully!';
            } else {
                $_SESSION['error_message'] = 'Error posting announcement: ' . implode(", ", $stmt->errorInfo());
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error posting announcement: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        try {
            $stmt = $connect->prepare("DELETE FROM announcements WHERE id = ?"); 
            $stmt->bindParam(1, $delete_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Announcement deleted successfully!';
            } else {
                $_SESSION['error_message'] = 'Error deleting announcement: ' . implode(", ", $stmt->errorInfo());
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error deleting announcement: ' . $e->getMessage();
        }
    }

    header('Location: DASHBOARD.php');
    exit;
}

// Fetch announcements logic...
$announcements = [];
try {
    $stmt = $connect->prepare("SELECT id, title, message, posted, attachment FROM announcements ORDER BY posted DESC"); 
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {
    echo "Error fetching announcements: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="maincontent.css">
    <script>
        function showFileNames() {
            const fileInput = document.getElementById('attachment');
            const fileNamesContainer = document.getElementById('fileNames');
            fileNamesContainer.innerHTML = ''; 
            const files = fileInput.files;
            for (let i = 0; i < files.length; i++) {
                fileNamesContainer.innerHTML += `<div>${files[i].name}</div>`;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>
                <img src="../image/icpep.selogo.png" style="height: 50px; margin-top: 2px; margin-right: 5px;">
                ICpEP.se
            </h2>
            <h2>PHINMA UPang</h2>
            <button class="button" onclick="location.href='DASHBOARD.php';">DASHBOARD</button>
            <button class="button" onclick="location.href='EVENTCALENDAR.php';">EVENT CALENDAR</button>
            <button class="button" onclick="location.href='QUESTIONHUB.php';">QUESTION HUB</button>
            <?php if ($isAdmin): ?>
                <button class="button" onclick="location.href='DOCUMENTS.php';">DOCUMENTS</button>
            <?php endif; ?>
            <button class="button" onclick="location.href='signupPage.php?action=login';" style="margin-top: auto;">LOG OUT</button>
        </div>
        
        <div class="main-content">
            <div class="announcement">
                <?php if ($isAdmin): ?>
                    <h3>Post an Announcement</h3>
                    <form method="post" action="DASHBOARD.php" enctype="multipart/form-data"> 
                        <input class="inputbox" type="text" name="title" placeholder="Enter announcement title..." required>
                        <input class="inputbox" type="text" name="announcement" placeholder="Enter announcement here..." required>
                        
                        <div id="fileNames" class="file-names-container"></div>

                        <label for="attachment" class="attachment-button">Choose Files</label>
                        <input type="file" id="attachment" name="attachment[]" multiple onchange="showFileNames()" style="display: none;"> 
                        
                        <button class="postbutton" type="submit">Post Announcement</button>
                    </form>  
                <?php else: ?>
                    <p>DASHBOARD</p>
                <?php endif; ?>
            </div>

            <div class="announcement">
                <h3>ANNOUNCEMENTS</h3>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <p class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <p class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
                <?php endif; ?>

                <?php if (!empty($announcements)): ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcementContent">
                            <h4><?php echo htmlspecialchars($announcement['title']); ?></h4>
                            <p><?php echo htmlspecialchars($announcement['message']); ?></p>
                            <small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($announcement['posted'])); ?></small>

                            <?php if (!empty($announcement['attachment'])): ?>
                                <?php
                                $attachments = explode(',', $announcement['attachment']);
                                foreach ($attachments as $attachment):
                                    $filePath = "uploads/" . htmlspecialchars($attachment);
                                    $fileType = pathinfo($filePath, PATHINFO_EXTENSION);
                                    if (in_array(strtolower($fileType), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <img src="<?php echo $filePath; ?>" alt="Uploaded Image" style="max-width: 100%; height: auto;">
                                    <?php else: ?>
                                        <p><a href="<?php echo $filePath; ?>" target="_blank">Download Attachment</a></p>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($isAdmin): ?>
                                <form method="post" action="DASHBOARD.php">
                                    <input type="hidden" name="delete_id" value="<?php echo $announcement['id']; ?>">
                                    <button type="submit" class="deleteButton" onclick="return confirm('Are you sure you want to delete this announcement?');">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No announcements available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
