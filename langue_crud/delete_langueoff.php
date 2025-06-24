<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idlangue = $data['idlangue'] ?? 0;
    $iduser = $data['iduser'] ?? 0;
    
    try {
        // Vérifie que l'utilisateur a le droit de supprimer cette langue
        $check = $conn->prepare("
            SELECT 1 FROM langue l
            LEFT JOIN offre o ON l.offre_id = o.idoffre
            WHERE l.idlangue = ? AND (l.utilisateur_id = ? OR o.recruteur_id = ?)
        ");
        $check->bind_param("iii", $idlangue, $iduser, $iduser);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM langue WHERE idlangue = ?");
        $stmt->bind_param("i", $idlangue);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Langue supprimée avec succès']);
        } else {
            throw new Exception('Erreur lors de la suppression de la langue');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($check)) $check->close();
    }
}

$conn->close();
?>