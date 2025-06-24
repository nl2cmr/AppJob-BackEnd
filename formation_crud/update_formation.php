<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    $required = ['idformation', 'intitule', 'date_debut', 'institution', 'niveau'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
            exit;
        }
    }

    // Vérification des droits
    $check = $conn->prepare("SELECT idformation FROM formation WHERE idformation = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $data['idformation'], $data['user_id']);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE formation SET 
        intitule = ?,
        date_debut = ?,
        date_fin = ?,
        description = ?,
        institution = ?,
        niveau = ?
        WHERE idformation = ?");
    
    $stmt->bind_param("ssssssi",
        $data['intitule'],
        $data['date_debut'],
        $data['date_fin'],
        $data['description'],
        $data['institution'],
        $data['niveau'],
        $data['idformation']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Formation mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour']);
    }
    
    $stmt->close();
}

$conn->close();
?>