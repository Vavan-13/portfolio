<?php
$hostname = 'localhost';
$username = 'puev4583_dev';
$password = 'PUTev1968';
$db = 'puev4583_portfolio_evan_2025';
$dbport = 3306;

// Data Source Name
$dsn = "mysql:host=$hostname;dbname=$db;port=$dbport;charset=utf8mb4";

try {
    // Connexion à la base
    $bdd = new PDO($dsn, $username, $password);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Connexion réussie !</p>";

    // Requête : récupérer tous les utilisateurs
    $sql = "SELECT * FROM Utilisateurs";
    $stmt = $bdd->query($sql);

    // Vérification s'il y a des résultats
    if ($stmt->rowCount() > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Validé</th>
                <th>Date inscription</th>
              </tr>";

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                    <td>{$row['id_utilisateur']}</td>
                    <td>{$row['nom']}</td>
                    <td>{$row['prenom']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['role']}</td>
                    <td>" . ($row['statut_validation'] ? 'Oui' : 'Non') . "</td>
                    <td>{$row['date_inscription']}</td>
                  </tr>";
        }

        echo "</table>";
    } else {
        echo "<p>Aucun utilisateur trouvé dans la base de données.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
