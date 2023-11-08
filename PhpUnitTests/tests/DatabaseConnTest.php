<?php

use PHPUnit\Framework\TestCase;

class DataBaseConnTest extends TestCase {

    // Test dla metody get i put
    public function testGet() {
        $dbConn = new DataBaseConn('localhost', 'root', '', 'authtida');
        $dbConn->connect();

        // Wstaw dane do bazy w celu przetestowania ich pobierania
        $table = 'test_table';
        $columns = ['name', 'age'];
        $values = ['Ala', 25];
        $dbConn->put($table, $columns, $values);

        // Pobierz dane
        $data = $dbConn->get($table, null, ['where' => "age = 25"]);

        $this->assertNotEmpty($data);
        $this->assertEquals('Ala', $data[0]['name']);
        $this->assertEquals(25, $data[0]['age']);

        $dbConn->disconnect();
    }
}
