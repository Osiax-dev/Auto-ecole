<?php
// ========================================
// CONFIGURATION GLOBALE
// ========================================

// Sécurité : empêcher l'accès direct
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// ========================================
// BASE DE DONNÉES
// ========================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'auto_ecole');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// ========================================
// URL DE BASE
// ========================================
define('BASE_URL', 'http://localhost/auto-ecole');

// ========================================
// CHEMINS
// ========================================
define('UPLOAD_DIR', APP_ROOT . '/uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// ========================================
// CONSTANTES DIVERSES
// ========================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 Mo
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// ========================================
// AFFICHAGE DES ERREURS (à désactiver en production)
// ========================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ========================================
// DÉMARRAGE DE SESSION SÉCURISÉ
// ========================================
if (session_status() === PHP_SESSION_NONE) {
    // Paramètres de session sécurisés
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Mettre à 1 en HTTPS
    session_start();
}