<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idreference = $data['idreference'] ?? 0;
    $user_id = $data['user_id'] ?? 0;

    // Validation des données
    if (empty($data['intitule']) || empty($data['contact'])) {
        echo json_encode(['success' => false, 'message' => 'L\'intitulé et le contact sont obligatoires']);
        exit;
    }

    // Vérification des droits
    $check = $conn->prepare("SELECT idreference FROM reference WHERE idreference = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $idreference, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Non autorisé']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE reference SET intitule = ?, contact = ?, relation = ? WHERE idreference = ?");
    $stmt->bind_param("sssi", 
        $data['intitule'],
        $data['contact'],
        $data['relation'],
        $idreference
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Référence mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour']);
    }
    
    $stmt->close();
}

$conn->close();
?>