<?php
/**
 * Datenbankverbindung für Feuerwehr App
 */

class Database {
    private $host = 'mysql';
    private $db_name = 'feuerwehr_app';
    private $username = 'feuerwehr_user';
    private $password = 'feuerwehr_password';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Datenbankverbindung fehlgeschlagen: " . $exception->getMessage());
        }

        return $this->conn;
    }
}

// Globale Datenbankverbindung
$database = new Database();
$db = $database->getConnection();

if ($db) {
    try {
        require_once __DIR__ . '/../includes/ui-theme.php';
        feuerwehr_ensure_ui_theme_setting($db);
    } catch (Throwable $e) {
        error_log('ui-theme Init: ' . $e->getMessage());
    }
}
?>
