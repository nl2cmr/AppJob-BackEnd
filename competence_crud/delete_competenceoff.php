<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idcompetence = $data['idcompetence'] ?? 0;
    $iduser = $data['iduser'] ?? 0;
    
    try {
        // Vérifie que l'utilisateur a le droit de supprimer cette compétence
        $check = $conn->prepare("
            SELECT 1 FROM competence c
            LEFT JOIN offre o ON c.offre_id = o.idoffre
            WHERE c.idcompetence = ? AND (c.utilisateur_id = ? OR o.recruteur_id = ?)
        ");
        $check->bind_param("iii", $idcompetence, $iduser, $iduser);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM competence WHERE idcompetence = ?");
        $stmt->bind_param("i", $idcompetence);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Erreur lors de la suppression');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($check)) $check->close();
    }
}

$conn->close();
?>