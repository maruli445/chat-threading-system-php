<?php
require_once __DIR__ . '/../config.php';

class CaseModel {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function getAllCases() {
        $stmt = $this->db->query("
            SELECT c.*, 
                   COUNT(m.id) as message_count,
                   MAX(m.created_at) as last_activity
            FROM cases c 
            LEFT JOIN messages m ON c.id = m.case_id 
            GROUP BY c.id 
            ORDER BY c.updated_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function getCaseById($id) {
        $stmt = $this->db->prepare("SELECT * FROM cases WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createCase($title) {
        $stmt = $this->db->prepare("INSERT INTO cases (title) VALUES (?)");
        $stmt->execute([$title]);
        return $this->db->lastInsertId();
    }
    
    public function updateCaseStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE cases SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function deleteCase($id) {
        $stmt = $this->db->prepare("DELETE FROM cases WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
