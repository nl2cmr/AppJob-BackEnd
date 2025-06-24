<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Vérification des droits
    $check = $conn->prepare("SELECT idoffre FROM offre WHERE idoffre = ? AND recruteur_id = ?");
    $check->bind_param("ii", $data['idoffre'], $data['user_id']);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE offre SET titre = ?, reference = ?, description = ?, date_publication = ?, date_expiration = ?, salaire = ?, type_contrat = ? WHERE idoffre = ?");
    $stmt->bind_param("sssssssi", 
        $data['titre'], 
        $data['reference'], 
        $data['description'], 
        $data['date_publication'], 
        $data['date_expiration'], 
        $data['salaire'], 
        $data['type_contrat'], 
        $data['idoffre']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Offre mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour: ' . $conn->error]);
    }
    
    $stmt->close();
}

$conn->close();
?>