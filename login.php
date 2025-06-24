<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Configuration de la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apprecherche";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Échec de la connexion à la base de données: ' . $conn->connect_error
    ]);
    exit;
}

// Récupération des données du corps de la requête
$data = json_decode(file_get_contents('php://input'), true);

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($data['email']) ? trim($data['email']) : '';
    $password = isset($data['password']) ? $data['password'] : '';

    // Vérification que les champs sont remplis
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'L\'email et le mot de passe sont requis.'
        ]);
        exit;
    }

    // Vérifier si l'utilisateur existe
    $stmt = $conn->prepare("SELECT iduser, mot_de_passe FROM utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['mot_de_passe'])) {
            // Requête pour toutes les données de l'utilisateur
            $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE iduser = ?");
            $stmt->bind_param("i", $user['iduser']);
            $stmt->execute();
            $fullUser = $stmt->get_result()->fetch_assoc();

            // Réponse JSON avec les infos
            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => [
                    'iduser' => $fullUser['iduser'],
                    'nom' => $fullUser['nom'],
                    'prenom' => $fullUser['prenom'],
                    'email' => $fullUser['email'],
                    'poste' => $fullUser['poste'] ?? '',
                    'telephone' => $fullUser['telephone'] ?? '',
                    'adresse' => $fullUser['adresse'] ?? '',
                    'presentation' => $fullUser['description'] ?? '',
                    'role' => $fullUser['role']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Mot de passe incorrect.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucun utilisateur trouvé avec cet email.'
        ]);
    }

    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée.'
    ]);
}

$conn->close();
?>
