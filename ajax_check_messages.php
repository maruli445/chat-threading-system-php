<?php
session_start();
require_once 'config.php';
require_once 'models/Message.php';

header('Content-Type: application/json');

$caseId = isset($_GET['case_id']) ? intval($_GET['case_id']) : 0;
$currentCount = isset($_GET['count']) ? intval($_GET['count']) : 0;

if ($caseId <= 0) {
    echo json_encode(['hasNew' => false, 'error' => 'Invalid case ID']);
    exit;
}

try {
    $messageModel = new MessageModel();
    $messages = $messageModel->getThreadedMessages($caseId);
    $newCount = count($messages);
    
    echo json_encode([
        'hasNew' => $newCount > $currentCount,
        'count' => $newCount
    ]);
} catch (Exception $e) {
    echo json_encode(['hasNew' => false, 'error' => $e->getMessage()]);
}
?>
