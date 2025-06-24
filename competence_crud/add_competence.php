<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['iduser', 'intitule', 'niveau'];
    // ... (vérification des champs)

    $stmt = $conn->prepare("INSERT INTO competence (utilisateur_id, offre_id, intutile, niveau) VALUES (?,?, ?, ?)");
    $stmt->bind_param("iiss", 
        $data['iduser'],
        $data['idoffre'],
        $data['intitule'],
        $data['niveau']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Experience added']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add experience']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
}

$conn->close();
?>