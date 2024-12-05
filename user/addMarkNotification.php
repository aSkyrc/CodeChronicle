<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notificationId = $_POST['notification_id'];

    try {
        // Convert the notification ID from string to MongoDB ObjectId
        $notificationObjectId = new MongoDB\BSON\ObjectId($notificationId);

        // Attempt to delete the notification
        $result = $notificationsCollection->deleteOne(['_id' => $notificationObjectId]);

        // Check if a notification was deleted
        if ($result->getDeletedCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete notification.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
