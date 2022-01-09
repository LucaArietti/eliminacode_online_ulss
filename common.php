<?php

function get_servername(){
    return "";
}

function get_username(){
    return "";
}

function get_password(){
    return "";
}

function get_dbname(){
    return "";
}

















function get_chiave_cookie(){
    // pesco dal db la chiave per decodificare e codificare il cookie

    // parametri per connettersi al db
    $servername = get_servername();
    $username = get_username();
    $password = get_password();
    $dbname = get_dbname();

    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $stmt=$conn->prepare("SELECT valore FROM coda_variabili WHERE nome='chiave_cookie'");
    //$stmt->bind_param('s', $_GET['codice']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['valore'];

}


function cripta_cookie($testo_in_chiaro){
    $key_cookie_id = get_chiave_cookie();
    $ivlen = openssl_cipher_iv_length($cipher="AES-256-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($testo_in_chiaro, $cipher, $key_cookie_id, $options=OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key_cookie_id, $as_binary=true);
    return base64_encode( $iv.$hmac.$ciphertext_raw );
}

function DEcripta_cookie($testo_cifrato){
    $key_cookie_id = get_chiave_cookie();
    $c = base64_decode($testo_cifrato);
    $ivlen = openssl_cipher_iv_length($cipher = "AES-256-CBC");
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len = 32);
    $ciphertext_raw = substr($c, $ivlen + $sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key_cookie_id, $options = OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key_cookie_id, $as_binary = true);
    if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
    {
        return $original_plaintext;
    }
    // elimino i cookie
    setcookie("numero", "", -1);
    header("Location: index.php");
    exit();
}


function getRandomString($lunghezza) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $lunghezza; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}


?>