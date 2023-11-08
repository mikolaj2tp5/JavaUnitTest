<?php

require_once("libs/DataBaseConn.php");
require_once("libs/Sensor.php");

/**
 * Klasa do autoryzacji jednorazowego dostępu do fragmentu serwisu
 * @author Jan Horodecki
 * @since 1.0
 */

class AuthBasic
{
    /**
     * @desc Generuje kod wymagany do podania podczas autoryzacji dostępu, wg. podanych parametrów
     * @param int $length Długość kodu - liczba znaków
     * @param int $min Minimalna wartość dla generowanego numeru
     * @param int $max Maksymalna wartość dla generowanego numeru
     * @return int Zwraca wygenerowaną na podstawie parametrów liczbę, która musi zostać uzupełniana zerami, jeżeli trzeba spełnić długość
     */
    public function createCode($length = 6, $min = 1, $max = 999999)
    {
        $max = substr($max, 0, $length);
        return str_pad(mt_rand($min, $max), $length, '0', STR_PAD_LEFT); // losowanie 1-999999
    }


    public function compAuthCode($emlAuth, $idzAuth, $authCode)
    {
    }



    /**
     * @desc Weryfikuje kod autoryzacyjny dla autoryzacji jednorazowego dostępu.
     * @param string $codeNo Kod autoryzacyjny do weryfikacji.
     * @return bool True, jeśli kod autoryzacyjny jest poprawny i ważny; False, jeśli kod jest niepoprawny lub wygasł.
     */
    public function verifyQuickRegCode($codeNo)
    {
        $tbl = 'cmswebsiteauth';
        $cols = array('email', 'authCode', 'datetime');
        $options = array('where' => "authCode = '$codeNo'");
    
        $db = new DataBaseConn('localhost', 'root', '', 'authtida');
        $db->connect();
    
        $data = $db->get($tbl, $cols, $options);
    
        $db->disconnect();
    
        if (count($data) === 1) {
            $authData = $data[0];
            $authDate = strtotime($authData['datetime']);
            $currentTime = strtotime(date("Y-m-d H:i:s"));
    
            // Sprawdź, czy kod autoryzacyjny nie wygasł (np. datą ważności)
            if ($authDate >= $currentTime) {
                // Jeżeli kod autoryzacyjny jest nadal ważny, zwróć prawdę
                return true;
            }
        }
    
        // Jeżeli kod autoryzacyjny jest niepoprawny lub wygasł, zwróć fałsz
        return false;
    }

    /**
     * @desc Tworzy wpis w BD z numerem pozwalającym na uwierzytelnienie Requesta
     * Tworzony Token do uwierzytelnienia zapisując adres Email oraz ID użytkownika
     * Token musi zostać wysłany na pocztę użytkownika, stąd zwracany jest Obiekt informacyjny
     * @param string $email Adres email użytkownika do uwierzytelnienia
     * @param int $userId Numer ID użytkownika do uwierzytelnienia
     * @return array|false	Wygenerowany Token LUB Fałsz
     */
    public function createAuthToken($email, $userId)
    {
        $sensor = new Sensor();

        $authCode = $this->createCode();
        $authDate = date("Y-m-d H:i:s");

        $addrIp = $sensor->addrIp();
        $opSys = $sensor->system();
        $browser = $sensor->browser();
        $fingerprint = $sensor->genFingerprint("sha512");
        $session_id = "1234567891";

        $content = array(
            'addrIp' => $addrIp, 'datetime' => $authDate,
            'email' => $email, 'authCode' => $authCode,
            'opSystem' => $opSys, 'browser' => $browser
        );


        $tbl = 'cmswebsiteauth';
        $cols = array(
            'session_id', 'usrId', 'addrIp', 'fingerprint', 'datetime', 'email', 'authCode', 'opSystem', 'browser'
        );
       
        $vals = array(
            $session_id, $userId, $addrIp, $fingerprint, $authDate, $email, $authCode, $opSys, $browser
        );

        # START >> db->put()
        $db = new DataBaseConn('localhost', 'root', '', 'authtida');
        $db->connect();

        $db->put($tbl,$cols,$vals);

        $data = $db->get($tbl, array('addrIp','datetime','email', 'authCode', 'opSystem', 'browser'), array('where' => "session_id = $session_id"));
        
        $db->disconnect();

        // # STOP >> db->put()
        // return $content;
        if(count($data) === 1 && $data[0] == $content){
            return $content;
        }else{
            return false;
        }
    }
}
