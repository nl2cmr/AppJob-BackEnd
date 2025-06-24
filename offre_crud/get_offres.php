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
    $stmt = $conn->prepare("SELECT idoffre, reference, titre, description, date_publication, date_expiration, salaire, type_contrat FROM offre WHERE recruteur_id = ?");
    $stmt->bind_param("i", $iduser);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $offres = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($offres);
    $stmt->close();
}

$conn->close();
?>