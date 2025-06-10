<?php
// Database connection class
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $conn;
    private $error;
    
    public function __construct() {
        // Set DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        );
        
        // Create PDO instance
        try {
            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            echo 'Connection Error: ' . $this->error;
        }
    }
    
    // Get connection
    public function getConnection() {
        return $this->conn;
    }
    
    // Prepare statement with query
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    // Execute query
    public function query($sql) {
        return $this->conn->query($sql);
    }
    
    // Get row count
    public function rowCount($sql) {
        return $this->query($sql)->rowCount();
    }
    
    // Get single record as object
    public function single($sql, $params = []) {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    // Get results set as array of objects
    public function resultSet($sql, $params = []) {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Get last insert ID
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    // Transactions
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollBack() {
        return $this->conn->rollBack();
    }
}
