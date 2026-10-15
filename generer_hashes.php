<?php
// Script pour générer les hashes de mots de passe
// À exécuter UNE FOIS pour obtenir les bons hashes

$passwords = [
    'Admin123!' => 'admin@autoecole.com',
    'Moniteur123!' => 'moniteur@autoecole.com',
    'Secret123!' => 'secretaire@autoecole.com',
    'Apprenant123!' => 'jean@email.com'
];

echo "<h1>🔐 Hashes de mots de passe</h1>";
echo "<style>body{font-family:Arial;padding:40px;} table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ddd;padding:12px;text-align:left;} th{background:#1e3a8a;color:white;}</style>";
echo "<table>";
echo "<tr><th>Email</th><th>Mot de passe</th><th>Hash (à copier dans SQL)</th></tr>";

foreach ($passwords as $mdp => $email) {
    $hash = password_hash($mdp, PASSWORD_BCRYPT);
    echo "<tr>";
    echo "<td>$email</td>";
    echo "<td><strong>$mdp</strong></td>";
    echo "<td style='font-family:monospace;font-size:11px;'>$hash</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p style='margin-top:20px;color:#666;'>💡 Copiez les hashes ci-dessus et remplacez-les dans le fichier <code>test_data.sql</code></p>";
?>