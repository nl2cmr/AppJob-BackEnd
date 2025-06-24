<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    if (empty($data['intitule']) || !in_array($data['niveau'], ['débutant', 'intermediaire', 'avancé'])) {
        echo json_encode(['success' => false, 'message' => 'Données invalides']);
        exit;
    }

    // Vérification des droits
    $check = $conn->prepare("SELECT idlangue FROM langue WHERE idlangue = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $data['idlangue'], $data['user_id']);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE langue SET 
        intitule = ?,
        niveau = ?
        WHERE idlangue = ?");
    
    $stmt->bind_param("ssi",
        $data['intitule'],
        $data['niveau'],
        $data['idlangue']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Langue mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour']);
    }
    
    $stmt->close();
}

$conn->close();
?>