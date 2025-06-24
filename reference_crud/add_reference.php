<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    if (empty($data['intitule']) || empty($data['contact'])) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO reference (utilisateur_id, intitule, relation, contact) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss",
        $data['iduser'],
        $data['intitule'],
        $data['relation'],
        $data['contact']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Référence ajoutée avec succès', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

$conn->close();
?>