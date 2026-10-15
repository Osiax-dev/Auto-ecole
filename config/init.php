<?php
// ========================================
// CONFIGURATION CENTRALE
// ========================================

// 1. Buffer de sortie
if (ob_get_level() === 0) {
    ob_start();
}

// 2. Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Chemin de base
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// 4. URL de base
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $base_path_normalized = str_replace('\\', '/', BASE_PATH);
    $subfolder = str_replace($doc_root, '', $base_path_normalized);
    define('BASE_URL', $protocol . '://' . $host . $subfolder);
}

// 5. Base de données
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'auto_ecole');  // ← Nouvelle base unifiée
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

// 6. Constantes
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024);
}
if (!defined('ALLOWED_EXTENSIONS')) {
    define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);
}
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', BASE_PATH . '/uploads/');
}

// 7. Fonction url()
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// 8. Fonction redirect()
function redirect($path = '') {
    $url = url($path);
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Location: ' . $url);
    exit;
}

// 9. Fonction db() pour obtenir une connexion PDO
function db() {
    static $db = null;
    if ($db === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('Erreur DB : ' . $e->getMessage());
        }
    }
    return $db;
}

// 10. Affichage des erreurs (à désactiver en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);