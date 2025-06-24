<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idcompetence = $data['idcompetence'] ?? 0;
    
    // Vérifier que la compétence appartient bien à l'utilisateur
    $user = json_decode(file_get_contents('php://input'), true)['user_id'] ?? 0;
    $check = $conn->prepare("SELECT idcompetence FROM competence WHERE idcompetence = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $idcompetence, $user);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM competence WHERE idcompetence = ?");
    $stmt->bind_param("i", $idcompetence);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Compétence supprimée']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de suppression']);
    }
    
    $stmt->close();
}

$conn->close();
?>