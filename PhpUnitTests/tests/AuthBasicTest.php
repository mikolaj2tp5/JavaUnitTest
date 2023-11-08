<?php
require_once("app/AuthBasic.php");
require_once("app/libs/Sensor.php");

use PHPUnit\Framework\TestCase;
// nazwanie i rozszerzenie własnej klasy klasą `TestCase` zawierającą Asercje do testów 
class AuthBasicTest extends TestCase
{
    private $instance;
    private $db;
    
    // tutaj umieść kod testów (metody)
    public function setUp(): void
    {
        $this->instance = new AuthBasic();

        $this->db = new DataBaseConn('localhost', 'root', '', 'authtida');
        $this->db->connect();
    }
    public function tearDown(): void
    {
        unset($this->instance);

        $this->db->disconnect();
    }




    public function testCreateCode()
    {
        $out = $this->instance->createCode();
        // jezeli potrzeba wyświetlić cokolwiek w widoku testu, należy użyć:
        fwrite(STDERR, print_r($out, true));
        $len = strlen($out);
        $this->assertIsNumeric($out, 'Wylosowano: ' . $out);
        $this->assertEquals(6, $len, 'Długość: ' . $len);

        $out = $this->instance->createCode(4);
        $len = strlen($out);
        $this->assertIsNumeric($out, 'Wylosowano: ' . $out);
        $this->assertEquals(4, $len, 'Długość: ' . $len);
        // symulowanie wylosowania liczby o mniejszej niż oczekiwana długość, którą należy uzupełnić zerami
        // nie można liczyć, że podczas testu zawsze wygenerujemy taką liczbą, stąd skopiowanie implementacji metody
        $out = str_pad(1111, 6, '0', STR_PAD_LEFT);
        $len = strlen($out);
        $this->assertIsNumeric($out, 'Wylosowano: ' . $out);
        $this->assertEquals(6, $len, 'Długość: ' . $len);
    }





    public function testCreateAuthToken()
    {
        $sensor = new Sensor();

        
        $exp = array(
            'addrIp' => $sensor->addrIp(), 'datetime' => date("Y-m-d H:i:s"),
            'email' => "janh@testingmail.com", 'authCode' => "131313",
            'opSystem' => $sensor->system(), 'browser' => $sensor->browser()
        );

        // wywołanie testowanej metody z przykładowymi danymi użytkownika: email i jego IDentyfikator
        $out = $this->instance->createAuthToken('mikolajb@testingmail.com', 69);
        // ponieważ generowany Token jest wartością losową - musimy go napisać wartością stałą - inaczej nie ma możliwości wykonania pomyślnie testu
        $out['authCode'] = '131313';
        // wywołanie testu właściwego - Asercji (założenia)
        $this->assertEqualsCanonicalizing($exp, $out, 'Tablice są różne');
    }








    public function testVerifyQuickRegCodeValid()
    {     
        // Wstaw testowy wpis do bazy danych
        $columns = array('session_id', 'usrId', 'addrIp', 'fingerprint', 'datetime', 'email', 'authCode', 'opSystem', 'browser');
        $values = array('1234567890', 1, '127.0.0.1', 'testHash', date("Y-m-d H:i:s"), 'test@example.com', '123456', 'Linux', 'FF');

        $this->db->put('cmswebsiteauth', $columns, $values);

        // Sprawdź, czy verifyQuickRegCode zwraca true dla poprawnego kodu autoryzacyjnego
        $this->assertTrue($this->instance->verifyQuickRegCode('123456'));
    }

    public function testVerifyQuickRegCodeExpired()
    {
        // Wstaw przeterminowany testowy wpis do bazy danych
        $columns = array('session_id', 'usrId', 'addrIp', 'fingerprint', 'datetime', 'email', 'authCode', 'opSystem', 'browser');
        $values = array('1234567891', 1, '127.0.0.1', 'testHash', '2023-01-01 12:00:00', 'test@example.com', '654321', 'Linux', 'FF');

        $this->db->put('cmswebsiteauth', $columns, $values);

        // Sprawdź, czy verifyQuickRegCode zwraca false dla przeterminowanego kodu autoryzacyjnego
        $this->assertFalse($this->instance->verifyQuickRegCode('654321'));
    }

    public function testVerifyQuickRegCodeNonExistent()
    {
        // Sprawdź, czy verifyQuickRegCode zwraca false dla nieistniejącego kodu autoryzacyjnego
        $this->assertFalse($this->instance->verifyQuickRegCode('999999'));
    }
}
