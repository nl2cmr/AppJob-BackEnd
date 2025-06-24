<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

$iduser = $_GET['iduser'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare("SELECT idreference, intitule, contact, relation FROM reference WHERE utilisateur_id = ?");
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $references = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($references);
    $stmt->close();
}

$conn->close();
?>