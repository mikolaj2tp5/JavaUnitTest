<?php

/**
 * Klasa do obsługi połączeń z bazą danych
 * @author Jan Horodecki
 * @since 0.2
 */
class DataBaseConn
{
    private $host;
    private $user;
    private $pass;
    private $database;
    private $conn;


    /**
     * @param string $host Adres host bazy danych
     * @param string $user Nazwa użytkownika
     * @param string $pass Hasło użytkownika
     * @param string $database Nazwa bazy danych
     */
    public function __construct($host, $user, $pass, $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;
    }

    /**
     * @desc Rozpoczęcie połączenia z bazą danych
     */
    public function connect()
    {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->database);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    /**
     * @desc Zakończenie połączenia z bazą danych
     */
    public function disconnect()
    {
        $this->conn->close();
    }

    /**
     * @desc Insercja danych do bazy danych
     * @param string $table Tabela docelowa
     * @param array $columns Kolumny tabeli
     * @param array $values Wartości do wstawienia
     * @return string ID wstawionego rekordu
     */
    public function put($table, $columns = null, $values = null)
    {
        if ($columns === null || $values === null) {
            die("Columns and values must be provided.");
        }

        $columnString = implode(", ", $columns);
        $valueString = "'" . implode("', '", $values) . "'";

        $sql = "INSERT INTO $table ($columnString) VALUES ($valueString)";
        $result = $this->conn->query($sql);

        if ($result === false) {
            die("Query failed: " . $this->conn->error);
        }

        return $this->conn->insert_id;
    }

    /**
     * @desc Pobranie danych z bazy
     * @param string $table Tabela docelowa
     * @param array|null $columns Wybrane kolumny tabeli
     * @param array|null $options Opcje filtrowania
     * @return array Dane wyciągniete z bazy
     */
    public function get($table, $columns = null, $options = array())
    {
        $columnString = $columns ? implode(", ", $columns) : '*';
        $whereClause = isset($options['where']) ? 'WHERE ' . $options['where'] : '';

        $sql = "SELECT $columnString FROM $table $whereClause";
        $result = $this->conn->query($sql);

        if ($result === false) {
            die("Query failed: " . $this->conn->error);
        }

        $data = array();
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }
}
