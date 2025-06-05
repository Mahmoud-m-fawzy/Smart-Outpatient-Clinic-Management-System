<?php
class Database {

    private $server = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "out patient clinic";
    public function connectToDB() {
        try {
            $link = mysqli_connect($this->server, $this->username, $this->password, $this->dbname);
            if ($link) {
                // Set charset to ensure proper encoding
                mysqli_set_charset($link, 'utf8mb4');
                return $link;
            } else {
                error_log("Database connection failed: " . mysqli_connect_error());
                throw new Exception("Could not connect to database");
            }
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            throw $e;
        }
    }
}
