<?php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'received_post' => $_POST,
    'post_count' => count($_POST),
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>
