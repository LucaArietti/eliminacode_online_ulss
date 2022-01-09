<?php


require 'common.php';


// parametri per connettersi al db
$servername = get_servername();
$username = get_username();
$password = get_password();
$dbname = get_dbname();



// se non è specificato un codice (letto male il qr code) scrivo di riscannerizzare il qr code
if ( ! isset($_GET['codice']) ){
    echo "<h1>Errore! Non trovo la sede corretta.<br><br>È giusto il link che hai usato?</h1>";
    exit();
}



if ( isset($_GET['incrementa_tabellone']) ){
    // pesco da db il numero e lo inserisco nel cookie, avanzando il db

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $flag = true;
    while ($flag){
        $conn->begin_transaction();
        try{
            $stmt=$conn->prepare("SELECT id, tabellone FROM coda_main WHERE codice=UPPER(?) AND token_scorrimento=?");
            $stmt->bind_param('ss', $_GET['codice'], $_GET['token']);
            $stmt->execute();
            $result = $stmt->get_result();

            // se non trovo la sede - codice, do errore
            $row_cnt = $result->num_rows;
            if($row_cnt == 0){
                //header("Location: index.php?errmes=" . urlencode("Errore! Non trovo la sede corretta"));
                echo "<h1>Errore! Non trovo la sede corretta.<br><br>È giusto il link che hai usato?</h1>";
                exit();
            }


            $row = $result->fetch_assoc();
            $numero_tabellone = $row["tabellone"];
            // controllo se son arrivato all'ultimo numero ---> Z999
            if($numero_tabellone == "Z999"){
                $nuovo_numero = "A000";     // lo metto al primo valore così da riniziare il giro
            }
            else{
                $numero_tabellone++;
                $nuovo_numero = $numero_tabellone;
            }
            // salvo nel DB il $nuovo_numero
            $stmt=$conn->prepare("UPDATE coda_main SET tabellone = ? WHERE id = ?");
            $stmt->bind_param('si', $nuovo_numero, $row["id"]);
            $stmt->execute();

            $conn->commit();
            $flag = false;
        }
        catch (mysqli_sql_exception $exception) {
            $conn->rollback();
        }

    }



    // ricarico questa pagina così da avercela pulita
    header('Location: avanza_numero.php?codice=' . $_GET['codice'] . "&token=" . $_GET['token']);

}


// qui sotto, la normale pagina mostrata al personale ulss


// prendo le info luogo e tipo dal db
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt=$conn->prepare("SELECT luogo, tipo, ultimo_numero FROM coda_main WHERE codice=UPPER(?) AND token_scorrimento=?");
$stmt->bind_param('ss', $_GET['codice'], $_GET['token']);
$stmt->execute();
$result = $stmt->get_result();

// se non trovo la sede - codice, do errore
$row_cnt = $result->num_rows;
if($row_cnt == 0){
    //header("Location: index.php?errmes=" . urlencode("Errore! Non trovo la sede corretta"));
    echo "<h1>Errore! Non trovo la sede corretta.<br><br>È giusto il link che hai usato?</h1>";
    exit();
}
$row = $result->fetch_assoc();
$luogo = $row['luogo'];
$tipo = $row['tipo'];
$ultimo_numero = $row['ultimo_numero'];


?>



<!doctype html>
<html lang="it" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Luca Arietti">
    <meta name="generator" content="Hugo 0.84.0">
    <title>Coda online</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sticky-footer/">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>



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



        /* aggiungo il font consolas */
        @font-face {
            font-family: consolas;
            src: url(./assets/font/CONSOLA.TTF) format("truetype");
        }
    </style>


    <link href="./assets/dist/css/sticky-footer.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">

<!-- Begin page content -->
<main class="flex-shrink-0">
    <div class="container">
        <p style="text-align:center;">Sede di <?php echo $luogo; ?> - <?php echo $tipo; ?></p>

        <h5 class="mt-4"><i>Tocca al numero...</i></h5>
        <p id="tabellone" class="lead" style="text-align:center; border-style: solid; border-color: red; border-width: 1px; color: red; font-family:consolas; font-size:70px;">-</p>

        <div>La visualizzazione del numero del tabellone viene aggiornata in automatico ogni 10 secondi</div>

        <h5 class="mt-5"><i>Ultimo numero preso:</i>&nbsp;&nbsp;&nbsp;
            <span class="lead" style="font-family:consolas; font-size: 1.5em;"><?php echo $ultimo_numero; ?></span>
        </h5>

        <br><br><br>
        <form id="form_incrementa_tabellone" action="avanza_numero.php" method="get">
            <div class="row">
                <input name="incrementa_tabellone" value="1" hidden>
                <input name="codice" value="<?php echo $_GET['codice']; ?>" hidden>
                <input name="token" value="<?php echo $_GET['token']; ?>" hidden>
                <button id="bottone_incrementa_tabellone" type="submit" class="btn btn-success btn-lg btn-block">Incrementa numero</button>
            </div>
        </form>



        <br><br><br>
    </div>
</main>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="text-muted" style="text-align:center; font-size: 70%;">Realizzato da Luca Arietti e donato alle ULSS, nella speranza di aiutare il prossimo</div>
        <div class="text-muted" style="text-align:center; font-size: 70%;"><a href="https://github.com/LucaArietti/eliminacode_online_ulss" target="_blank"><img src="./assets/github-logo.png" style="height: 1em;"></a> <a href="https://github.com/LucaArietti/eliminacode_online_ulss" target="_blank">Guarda il progetto su GitHub</a></div>
    </div>
</footer>


<script>
    // definisco la funzione che mi chiama l'aggiornamento
    function get_numero_tabellone(){
        $.ajax({
            url:"ajax_aggiorna_numero.php",
            type: "GET",
            data: { codice: "<?php echo $_GET['codice']; ?>"},
            success:function(result){
                $("#tabellone").html(result);

            },
            error: function(richiesta,stato,errori){
                log("Chiamata fallita:"+stato+" "+errori);
            }
        });
    }


    $(document).ready(function(){
        get_numero_tabellone();
    });

    // imposto che si aggiorni ogni 10 secondi
    setInterval(get_numero_tabellone, 10000);





    // per disabilitare il bottone di incremento numero e lanciare il form
    $(document).ready(function () {

        $("#form_incrementa_tabellone").submit(function (e) {

            //disable the submit button
            $("#bottone_incrementa_tabellone").attr("disabled", true);

            $("#bottone_incrementa_tabellone").html('Caricamento...');

            return true;

        });
    });

</script>

</body>
</html>

