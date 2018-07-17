<?php
class Url {
    // DB Stuff
    private $conn;
    private $table = 'urls_url';
    private $limit = 5000000;
    private $offset;

    // Properties
    public $id;
    public $name;
    public $created_at;

    // Constructor with DB
    public function __construct($db, $offset) {
        $this->conn = $db;
        $this->offset = $offset;
    }

    // Get categories
    public function read() {
        // Create query
        $query = 'SELECT 
        *
      FROM
        ' . $this->table . '
      LIMIT '.$this->limit.'
      OFFSET '.$this->offset;

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }
}