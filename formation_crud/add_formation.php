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
    $required = ['iduser', 'intitule', 'date_debut', 'date_fin', 'institution'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }

    $stmt = $conn->prepare("INSERT INTO formation (utilisateur_id, intitule, date_debut, date_fin, description, institution, niveau) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss",
        $data['iduser'],
        $data['intitule'],
        $data['date_debut'],
        $data['date_fin'],
        $data['description'],
        $data['institution'],
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