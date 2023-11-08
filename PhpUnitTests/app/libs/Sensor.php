<?php

require("app/libs/whichBrowser/vendor/autoload.php");
/**
 * Klasa Sensor
 *
 * Klasa ta reprezentuje narzędzie do zbierania informacji o kliencie, takie jak przeglądarka i system operacyjny.
 * Dodatkowo umożliwia generowanie kodu szyfrowania na podstawie informacji o kliencie.
 * 
 * @author Jan Horodecki
 * @since 1.0
 */
class Sensor {
    private $result;

    public function __construct() {
        $this->result = new WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * @desc Funkcja porównuje adres IP klienta z listą znanych lokalnych adresów IP.
     *
     * @return bool True, jeśli klient jest lokalny, w przeciwnym razie false.
     */
    public function isLocal() {
        $localAddresses = ['localhost', 'local', '127.0.0.1', '::1', '192.168.'];
        $clientIp = $this->addrIp();

        foreach ($localAddresses as $address) {
            if (strpos($clientIp, $address) === 0) {
                return true;
            }
        }

        return false;
    }


    /**
     * @desc Funkcja pobiera dres IP klienta
     *
     * @return string Zwraca adres IP klienta
     */
    public function addrIp() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }


    /**
     * @desc Funkcja pobiera dane o przeglądarce internetowej
     *
     * @return string Zwraca nazwę przeglądarki internetowej
     */
    public function browser() {
        return $this->result->browser->toString();
    }


    /**
     * @desc Funkcja pobiera dane o systemie operacyjnym
     *
     * @return string Zwraca informację o systemie operacyjnym
     */
    public function system() {
        return $this->result->os->toString();
    }


    /**
     * @desc Generuje kod szyfrowania
     * @param string $algo Algorytm szyfrowania
     * @return string Kod szyfrujący
    */
    public function genFingerprint($algo = "sha512") {
        
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $remoteAddress = $_SERVER['REMOTE_ADDR'];
        $uniqueHash = uniqid();
        $isSecure = true;
    
        $dataToHash = $userAgent . $remoteAddress . $uniqueHash . $isSecure;
        return hash_hmac($algo, $dataToHash, 'YourSecretKey');
    }
}