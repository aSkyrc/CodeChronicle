<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Code Chronicle/connection/session-config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['blogId'])) {
    echo json_encode(['success' => false, 'message' => 'Blog ID not provided.']);
    exit();
}

$blogId = $data['blogId'];

try {
    // Get the user profile collection
    $userProfileCollection = $db->selectCollection('user-profile');

    // Update the user's saved blogs array
    $updateResult = $userProfileCollection->updateOne(
        ['user_id' => $userId],
        ['$addToSet' => ['savedBlogs' => $blogId]] // Use $addToSet to avoid duplicates
    );

    if ($updateResult->getModifiedCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Blog already saved']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
