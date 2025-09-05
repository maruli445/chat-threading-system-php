<?php
require_once __DIR__ . '/../config.php';

class MessageModel {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function getMessagesByCase($caseId) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username, u.display_name, u.avatar_color,
                   COUNT(replies.id) as reply_count
            FROM messages m
            JOIN users u ON m.user_id = u.id
            LEFT JOIN messages replies ON m.id = replies.parent_message_id
            WHERE m.case_id = ?
            GROUP BY m.id
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }
    
    public function getThreadedMessages($caseId) {
        // Get all messages for the case
        $messages = $this->getMessagesByCase($caseId);
        
        // Organize into threads
        $threaded = [];
        $messageMap = [];
        
        foreach ($messages as $message) {
            $messageMap[$message['id']] = $message;
            $messageMap[$message['id']]['replies'] = [];
        }
        
        foreach ($messages as $message) {
            if ($message['parent_message_id'] === null) {
                // Root message
                $threaded[] = &$messageMap[$message['id']];
            } else {
                // Reply message
                if (isset($messageMap[$message['parent_message_id']])) {
                    $messageMap[$message['parent_message_id']]['replies'][] = &$messageMap[$message['id']];
                }
            }
        }
        
        return $threaded;
    }
    
    public function createMessage($caseId, $userId, $content, $imagePath = null, $parentMessageId = null) {
        $stmt = $this->db->prepare("
            INSERT INTO messages (case_id, user_id, content, image_path, parent_message_id) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$caseId, $userId, $content, $imagePath, $parentMessageId]);
        
        if ($result) {
            // Update case timestamp
            $updateCase = $this->db->prepare("UPDATE cases SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updateCase->execute([$caseId]);
            
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    public function getMessageById($id) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.username, u.display_name, u.avatar_color
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function deleteMessage($id) {
        $stmt = $this->db->prepare("DELETE FROM messages WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getLatestMessages($caseId, $since = null) {
        $sql = "
            SELECT m.*, u.username, u.display_name, u.avatar_color
            FROM messages m
            JOIN users u ON m.user_id = u.id
            WHERE m.case_id = ?
        ";
        
        $params = [$caseId];
        
        if ($since) {
            $sql .= " AND m.created_at > ?";
            $params[] = $since;
        }
        
        $sql .= " ORDER BY m.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>
