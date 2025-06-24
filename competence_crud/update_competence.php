<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true); 

    // Vérification des droits
    $check = $conn->prepare("SELECT idcompetence FROM competence WHERE idcompetence = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $data['idcompetence'], $data['user_id']);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE competence SET intutile = ?, niveau = ? WHERE idcompetence = ?");
    $stmt->bind_param("ssi", $data['intutile'], $data['niveau'], $data['idcompetence']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Compétence mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour']);
    }
    
    $stmt->close();
}

$conn->close();
?>