<?php
session_start();

include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';

if (!isset($_SESSION['login_uni_id'])) {
    header('Location: login.php'); 
    exit;
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['docs'])) {
        $posted = date('Y-m-d H:i:s');
        $admin_id = $_SESSION['login_uni_id'];

  
        $attachments = [];

    
        foreach ($_FILES['attachment']['name'] as $key => $fileName) {
            if ($_FILES['attachment']['error'][$key] == UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/'; 
                $targetFilePath = $uploadDir . basename($fileName);

               
                if (move_uploaded_file($_FILES['attachment']['tmp_name'][$key], $targetFilePath)) {
                    $attachments[] = $fileName; 
                } else {
                    $_SESSION['error_message'] = 'Error uploading file: ' . $fileName;
                }
            }
        }

     
        try {
            foreach ($attachments as $filename) {
                $stmt = $connect->prepare("INSERT INTO documents (filename, attachment, posted, admin_id) VALUES (?, ?, ?, ?)");
                $stmt->bindParam(1, $filename);
                $stmt->bindParam(2, $filename); 
                $stmt->bindParam(3, $posted);
                $stmt->bindParam(4, $admin_id);
                if (!$stmt->execute()) {
                    $_SESSION['error_message'] = 'Error posting document: ' . implode(", ", $stmt->errorInfo());
                }
            }
            $_SESSION['success_message'] = 'Documents posted successfully!';
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error posting document: ' . $e->getMessage();
        }

    } elseif (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        try {
            $stmt = $connect->prepare("DELETE FROM documents WHERE id = ?");
            $stmt->bindParam(1, $delete_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Document deleted successfully!';
            } else {
                $_SESSION['error_message'] = 'Error deleting document: ' . implode(", ", $stmt->errorInfo());
            }
        } catch (Exception $e) {
            $_SESSION['error_message'] = 'Error deleting document: ' . $e->getMessage();
        }
    }

    header('Location: DOCUMENTS.php');
    exit;
}

$documents = [];
try {
    $stmt = $connect->prepare("SELECT id, filename, attachment, posted FROM documents ORDER BY posted DESC");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error fetching documents: " . htmlspecialchars($e->getMessage());
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
            <button class="button" onclick="location.href='DOCUMENTS.php';">DOCUMENTS</button>
            <button class="button" onclick="location.href='signupPage.php?action=login';" style="margin-top: auto;">LOG OUT</button>
        </div>
        
        <div class="main-content">
            <div class="announcement">
                <h3>UPLOAD DOCUMENTS</h3>
                <form method="post" action="DOCUMENTS.php" enctype="multipart/form-data">
                    <input class="inputbox" type="text" name="filename" placeholder="Enter file name here..." required>
                    
              
                    <div id="fileNames" class="file-names-container"></div>

                    <label for="attachment" class="attachment-button">Choose Files</label>
                    <input type="file" id="attachment" name="attachment[]" multiple required onchange="showFileNames()" style="display: none;"> 
                    
                    <button class="button postbutton" type="submit" name="docs">Upload Documents</button>
                </form>
                
                
            </div>

            <div class="announcement">
                <h3>DOCUMENTS</h3>
                <?php if (isset($_SESSION['success_message'])): ?>
                    <p class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
                <?php endif; ?>
                <?php if (isset($_SESSION['error_message'])): ?>
                    <p class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
                <?php endif; ?>
                <?php if (!empty($documents)): ?>
                    <?php foreach ($documents as $document): ?>
                        <div class="announcementContent">
                            <h4><?php echo htmlspecialchars($document['filename']); ?></h4>
                            <p><a href="download.php?file=<?php echo urlencode($document['attachment']); ?>">Download</a></p>
                            <small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($document['posted'])); ?></small>
                            
                            <?php if ($isAdmin): ?>
                                <form method="post" action="DOCUMENTS.php" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $document['id']; ?>">
                                    <button class="deleteButton" type="submit" onclick="return confirm('Are you sure you want to delete this document?');">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No documents available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
