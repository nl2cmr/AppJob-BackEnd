<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    try {
        // Validation
        $requiredFields = ['iddiplome', 'iduser', 'titre', 'date_obtention'];
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
        $check = $conn->prepare("SELECT 1 FROM diplome WHERE iddiplome = ? AND utilisateur_id = ?");
        $check->bind_param("ii", $data['iddiplome'], $data['iduser']);
        $check->execute();
        
        if ($check->get_result()->num_rows === 0) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Non autorisé'
            ]);
            exit;
        }

        // Mise à jour
        $stmt = $conn->prepare("
            UPDATE diplome 
            SET 
                titre = ?,
                date_obtention = ?,
                offre_id = ?
            WHERE iddiplome = ?
        ");
        
        // Gestion optionnelle de l'offre_id
        $offreId = !empty($data['idoffre']) ? $data['idoffre'] : NULL;
        $stmt->bind_param("ssii", 
            $data['titre'],
            $data['date_obtention'],
            $offreId,
            $data['iddiplome']
        );
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Diplôme mis à jour avec succès'
            ]);
        } else {
            throw new Exception('Erreur lors de la mise à jour');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) $stmt->close();
        if (isset($check)) $check->close();
    }
}

$conn->close();
?>