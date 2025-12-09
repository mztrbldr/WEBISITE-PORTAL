<?php
session_start();
include 'C:\xampp\htdocs\TRIAL\org_portal\pages\org_portalDb.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['login_uni_id'])) {
    header('Location: login.php'); 
    exit;
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $question = $_POST['question'];
    $user_id = $_SESSION['login_uni_id']; 
    $posted = date('Y-m-d H:i:s'); 

    try {
        $stmt = $connect->prepare("INSERT INTO questions (qs, user_id, posted) VALUES (?, ?, ?)");
        $stmt->bindParam(1, $question);
        $stmt->bindParam(2, $user_id);
        $stmt->bindParam(3, $posted);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Question submitted successfully!';
        } else {
            $_SESSION['error_message'] = 'Error submitting question: ' . implode(", ", $stmt->errorInfo());
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error submitting question: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    try {
        $stmt = $connect->prepare("DELETE FROM questions WHERE id = ? AND (user_id = ? OR ? = 'admin')");
        $stmt->bindParam(1, $delete_id);
        $stmt->bindParam(2, $_SESSION['login_uni_id']);
        $stmt->bindParam(3, $_SESSION['role']); 

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Question deleted successfully!';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error deleting question: ' . $e->getMessage();
    }
}

$questions = [];
try {
    $stmt = $connect->prepare("SELECT q.id, q.qs, q.user_id, q.posted FROM questions q LEFT JOIN user_profile u ON q.user_id = u.uni_id ORDER BY q.posted DESC");
    $stmt->execute();
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {
    echo "Error fetching questions: " . htmlspecialchars($e->getMessage());
}

$answers = [];
try {
    $stmt = $connect->prepare("SELECT a.id, a.q_id, a.ans, a.admin_id, a.posted FROM answers a");
    $stmt->execute();
    $answers = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch (Exception $e) {
    echo "Error fetching answers: " . htmlspecialchars($e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer_id'])) {
    $answer_id = $_POST['answer_id'];

    try {
        $stmt = $connect->prepare("DELETE FROM answers WHERE id = ? AND admin_id = ?");
        $stmt->bindParam(1, $answer_id);
        $stmt->bindParam(2, $_SESSION['login_uni_id']);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Answer deleted successfully!';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error deleting answer: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Hub</title>
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
                <h3>Ask a Question</h3>
                <form method="post" action="QUESTIONHUB.php">
                    <input class="inputbox" type="text" name="question" placeholder="Enter your question here..." required>
                    <button type="submit" class="postbutton">Submit Question</button>
                </form>
            </div>

            <div class="announcement">
                <h3>Questions</h3>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
                <?php endif; ?>

                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $question): ?>
                        <div class="announcementContent">
                            <p><strong><?php echo htmlspecialchars($question['user_id']); ?></strong></p>
                            <p><?php echo htmlspecialchars($question['qs']); ?></p>
                            <small>(Posted on: <?php echo date('F j, Y, g:i a', strtotime($question['posted'])); ?>)</small>

                            <?php if ($isAdmin || $question['user_id'] === $_SESSION['login_uni_id']): ?>
                                <form method="post" action="QUESTIONHUB.php" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $question['id']; ?>">
                                    <button type="submit" class="deleteButton" onclick="return confirm('Are you sure you want to delete this question?');">Delete</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($isAdmin): ?>
                                <form method="post" action="ANSWER.php">
                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                    <textarea class="inputbox" name="answer" placeholder="Write your answer here..." required></textarea>
                                    <button type="submit" class="postbutton">Post Answer</button>
                                </form>
                            <?php endif; ?>

                            <h4>Admin's Answers:</h4>
                            <?php foreach ($answers as $answer): ?>
                                <?php if ($answer['q_id'] == $question['id']): ?>
                                    <div >
                                        <p><strong>Admin: <?php echo htmlspecialchars($answer['admin_id']); ?></strong></p>
                                        <p><?php echo htmlspecialchars($answer['ans']); ?></p>
                                        <small>(Posted on: <?php echo date('F j, Y, g:i a', strtotime($answer['posted'])); ?>)</small>

                                        <?php if ($isAdmin): ?>
                                            <form method="post" action="QUESTIONHUB.php" style="display:inline;">
                                                <input type="hidden" name="answer_id" value="<?php echo $answer['id']; ?>">
                                                <button type="submit" class="deleteButton" onclick="return confirm('Are you sure you want to delete this answer?');">Delete Answer</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No questions available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
