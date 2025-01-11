<?php

class RentCarDB {
    private static $instance = null;
    private $connection;

    private $serverName = "127.0.0.1"; // Servidor (localhost o 127.0.0.1)
    private $database = "RentCarDB"; // Nombre de tu base de datos
    private $username = "root"; // Usuario de MariaDB
    private $password = "1234"; // Contraseña vacía

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=$this->serverName;dbname=$this->database;charset=utf8mb4",
                $this->username,
                $this->password
            );

            // Configuración para mostrar errores de conexión
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error en la conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new RentCarDB();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>
