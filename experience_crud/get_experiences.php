<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Récupérer toutes les expériences d'un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $iduser = $_GET['iduser'] ?? 0;
    
    $stmt = $conn->prepare("SELECT * FROM experience WHERE utilisateur_id = ?");
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $experiences = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($experiences);
    $stmt->close();
}

$conn->close();
?>