<?php
require_once __DIR__ . '/../config/DBConnector.php';

function getAllQuestions() {
    require_once __DIR__ . '/../config/DBConnector.php';
    $pdo = Database::getConnection();

    $stmt = $pdo->query("
        SELECT q.*,
            (SELECT COUNT(*) FROM votes WHERE question_id = q.id AND vote_type = 'up') AS upvotes,
            (SELECT COUNT(*) FROM votes WHERE question_id = q.id AND vote_type = 'down') AS downvotes
        FROM questions q
        ORDER BY q.created_at DESC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
