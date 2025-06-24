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
    $required = ['iduser', 'nom', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => "$field is required"]);
            exit;
        }
    }

    $stmt = $conn->prepare("UPDATE utilisateur SET 
        nom = ?, 
        prenom = ?, 
        email = ?, 
        telephone = ?, 
        adresse = ?, 
        poste = ?, 
        description = ?
        WHERE iduser = ?"
    );

    $stmt->bind_param("sssssssi", 
        $data['nom'],
        $data['prenom'],
        $data['email'],
        $data['telephone'],
        $data['adresse'],
        $data['poste'],
        $data['description'],
        $data['iduser']
    );


    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
}

$conn->close();
?>