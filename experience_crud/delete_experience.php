<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idexperience = $data['idexperience'] ?? 0;
    
    $stmt = $conn->prepare("DELETE FROM experience WHERE idexperience = ?");
    $stmt->bind_param("i", $idexperience);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Experience deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed']);
    }
    
    $stmt->close();
}

$conn->close();
?>