<?php


require 'common.php';

$testo_errore = "";


// controllo se ho login in ingresso
if ($_POST['azione'] == "login"){

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

    // qui so che bisognerebbe farlo con hash ma al momento non ho tempo
    $stmt=$conn->prepare("SELECT 1 FROM coda_user WHERE nome=? AND password=?");
    $stmt->bind_param('ss', $_POST['user'], $_POST['password'] );
    $stmt->execute();
    $result = $stmt->get_result();
    $row_cnt = $result->num_rows;
    if($row_cnt > 0){
        // inserisco i cookie per tenere l'accesso
        setcookie("adminUtente", $_POST['user'], time()+54000);
        setcookie("adminPassword", $_POST['password'], time()+54000);
        header('Location: backoffice.php');
        exit();
    }
    else{
        $testo_errore = "Utente o password errati";
    }

}


// elimino comunque i cookie
setcookie("adminUtente", "", -1);
setcookie("adminPassword", "", -1);




?>

<!doctype html>
<html lang="it" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Luca Arietti">
    <meta name="generator" content="Hugo 0.84.0">
    <title>Login Area Gestione Sedi</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sticky-footer/">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="./assets/dist/js/bootstrap.bundle.min.js"></script>



    <!-- Bootstrap core CSS -->
    <link href="./assets/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>


    <link href="./assets/dist/css/sticky-footer.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">

<!-- Begin page content -->
<main class="flex-shrink-0">
    <div class="container">
        <br><br>
        <h3>Login Area Gestione Sedi</h3>
        <h4 style="color: red;">
            <?php
                echo $testo_errore;
            ?>
        </h4>
        <br>
        <form action="loginAdmin.php" method="post">
            <div class="col-6">
                <label for="user">Utente</label><br>
                <input type="text" id="user" name="user" placeholder="Utente">
            </div>
            <br>
            <div class="col-6">
                <label for="password">Password</label><br>
                <input type="password" id="password" name="password" placeholder="Password">
            </div>
            <br>
            <input type="hidden" name="azione" value="login">
            <input type="submit" class="btn btn-lg btn-primary" value="Accedi">
        </form>





    </div>
</main>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="text-muted" style="text-align:center; font-size: 70%;">Realizzato da Luca Arietti e donato alle ULSS, nella speranza di aiutare il prossimo</div>
        <div class="text-muted" style="text-align:center; font-size: 70%;"><a href="https://github.com/LucaArietti/eliminacode_online_ulss" target="_blank"><img src="./assets/github-logo.png" style="height: 1em;"></a> <a href="https://github.com/LucaArietti/eliminacode_online_ulss" target="_blank">Guarda il progetto su GitHub</a></div>
    </div>
</footer>



</body>
</html>

