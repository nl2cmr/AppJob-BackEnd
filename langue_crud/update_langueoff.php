<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Validation des données
        $requiredFields = ['idlangue', 'iduser', 'intitule', 'niveau'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'message' => "Le champ $field est requis"
                ]);
                exit;
            }
        }

        // Vérification des droits
        $check = $conn->prepare("
            SELECT 1 FROM langue l
            LEFT JOIN offre o ON l.offre_id = o.idoffre
            WHERE l.idlangue = ? AND (l.utilisateur_id = ? OR o.recruteur_id = ?)
        ");
        $check->bind_param("iii", $data['idlangue'], $data['iduser'], $data['iduser']);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            http_response_code(403);
            echo json_encode([
                'success' => false, 
                'message' => 'Non autorisé: vous ne pouvez pas modifier cette langue'
            ]);
            exit;
        }

        // Mise à jour de la langue (avec gestion optionnelle de l'offre)
        $stmt = $conn->prepare("
            UPDATE langue 
            SET 
                intitule = ?, 
                niveau = ?,
                offre_id = ?
            WHERE idlangue = ?
        ");
        
        // Gestion du champ optionnel offre_id
        $offreId = !empty($data['idoffre']) ? $data['idoffre'] : NULL;
        $stmt->bind_param("ssii", 
            $data['intitule'],
            $data['niveau'],
            $offreId,
            $data['idlangue']
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Langue mise à jour avec succès'
            ]);
        } else {
            throw new Exception('Erreur lors de la mise à jour de la langue');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($check)) $check->close();
    }
}

$conn->close();
?>