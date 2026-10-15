<?php
echo "<h1>🔍 Vérification de la configuration</h1>";
echo "<style>body{font-family:Arial;padding:40px;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// 1. Vérifier PHP version
echo "<h2>1. Version PHP</h2>";
$php_version = phpversion();
if (version_compare($php_version, '7.4', '>=')) {
    echo "<p class='ok'>✅ PHP $php_version (OK)</p>";
} else {
    echo "<p class='error'>❌ PHP $php_version (Requiert PHP 7.4+)</p>";
}

// 2. Vérifier extensions
echo "<h2>2. Extensions PHP</h2>";
$extensions = ['pdo', 'pdo_mysql', 'session', 'json', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='ok'>✅ Extension $ext chargée</p>";
    } else {
        echo "<p class='error'>❌ Extension $ext manquante</p>";
    }
}

// 3. Vérifier connexion DB
echo "<h2>3. Connexion à la base de données</h2>";
try {
    require_once 'config/config.php';
    require_once 'config/Database.php';
    $db = Database::getInstance()->getConnection();
    echo "<p class='ok'>✅ Connexion à la base de données réussie</p>";
    
    // Vérifier les tables
    $tables = ['utilisateurs', 'documents', 'paiements', 'cours'];
    echo "<h3>Tables existantes :</h3>";
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p class='ok'>✅ Table $table ($count enregistrements)</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Table $table manquante</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur : " . $e->getMessage() . "</p>";
}

// 4. Vérifier les fichiers
echo "<h2>4. Fichiers requis</h2>";
$files = [
    'index.php',
    'inscription.php',
    'connexion.php',
    'profil.php',
    'test.php',
    'admin/moniteur.php',
    'admin/secretaire.php',
    'admin/admin.php',
    'config/config.php',
    'config/Database.php',
    'includes/Crud.php',
    'includes/header.php',
    'includes/footer.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p class='ok'>✅ $file</p>";
    } else {
        echo "<p class='error'>❌ $file manquant</p>";
    }
}

// 5. Vérifier les dossiers
echo "<h2>5. Dossiers requis</h2>";
$dirs = ['uploads', 'uploads/documents', 'assets', 'assets/css', 'assets/js', 'admin', 'config', 'includes'];
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? '(accessible en écriture)' : '(lecture seule)';
        echo "<p class='ok'>✅ $dir $writable</p>";
    } else {
        echo "<p class='error'>❌ Dossier $dir manquant</p>";
    }
}

echo "<hr>";
echo "<h2>🎯 Résumé</h2>";
echo "<p>Ouvrez <a href='test.php'>test.php</a> pour commencer les tests.</p>";
?>