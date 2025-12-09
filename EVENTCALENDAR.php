<?php
session_start();
include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';


if (!isset($_SESSION['login_uni_id'])) {
    header('Location: login.php');
    exit;
}


$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';


if ($isAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['description'], $_POST['edate'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $edate = $_POST['edate'];
    $posted = date('Y-m-d H:i:s'); 
    $admin_id = $_SESSION['login_uni_id'];


    try {
        $stmt = $connect->prepare("INSERT INTO event_calendar (title, description, edate, posted, admin_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindParam(1, $title);
        $stmt->bindParam(2, $description);
        $stmt->bindParam(3, $edate);
        $stmt->bindParam(4, $posted);
        $stmt->bindParam(5, $admin_id);
        $stmt->execute();
        $_SESSION['success_message'] = 'Event posted successfully!';
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error posting event: ' . $e->getMessage();
    }


    header('Location: EVENTCALENDAR.php');
    exit;

} elseif (isset($_POST['delete_id'])) {

    $delete_id = $_POST['delete_id'];

    try {
        $stmt = $connect->prepare("DELETE FROM event_calendar WHERE id = ?"); 
        $stmt->bindParam(1, $delete_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Event deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Error deleting event: ' . implode(", ", $stmt->errorInfo());
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error deleting event: ' . $e->getMessage();
    }
}


$events = [];
try {
    $stmt = $connect->prepare("SELECT id, title, description, edate, posted FROM event_calendar ORDER BY edate ASC");
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error fetching events: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Calendar</title>
    <link rel="stylesheet" href="maincontent.css">
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
                <h3>EVENT CALENDAR</h3>

                <?php if ($isAdmin): ?>
                    <form method="post" action="EVENTCALENDAR.php">
                        <input class="inputbox" type="text" name="title" placeholder="Enter event title here..." required>
                        <input class="inputbox" type="text" name="description" placeholder="Enter event description here..." required>
                        <input class="edatebox" type="date" name="edate" required>
                        <button class="postbutton" type="submit">Post Event</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="announcement">
                <h3>Upcoming Events</h3>

             
                <?php if (isset($_SESSION['success_message'])): ?>
                    <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
                <?php endif; ?>

                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <div class="announcementContent">
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                            <p>Date: <?php echo date('F j, Y', strtotime($event['edate'])); ?></p>
                            <br>
                            <small>Posted on: <?php echo date('F j, Y, g:i a', strtotime($event['posted'])); ?></small>
                            <?php if ($isAdmin): ?>
                                <form method="post" action="EVENTCALENDAR.php">
                                    <input type="hidden" name="delete_id" value="<?php echo $event['id']; ?>">
                                    <button type="submit" class="deleteButton" onclick="return confirm('Are you sure you want to delete this event?');">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming events yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
