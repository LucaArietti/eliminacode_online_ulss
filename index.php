<?php

require 'common.php';


// parametri per connettersi al db
$servername = get_servername();
$username = get_username();
$password = get_password();
$dbname = get_dbname();



// se non è specificato un codice (letto male il qr code) scrivo di riscannerizzare il qr code
if ( ! isset($_GET['codice']) ){
    echo "<h1>Errore! Non trovo la sede corretta.<br><br>Prova a reinquadrare il QR&nbsp;code</h1>";
    exit();
}



if ( isset($_GET['nuovo_numero']) ){
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
            $stmt=$conn->prepare("SELECT id, luogo, tipo, ultimo_numero FROM coda_main WHERE codice=UPPER(?)");
            $stmt->bind_param('s', $_GET['codice']);
            $stmt->execute();
            $result = $stmt->get_result();

            // se non trovo la sede - codice, do errore
            $row_cnt = $result->num_rows;
            if($row_cnt == 0){
                //header("Location: index.php?errmes=" . urlencode("Errore! Non trovo la sede corretta"));
                echo "<h1>Errore! Non trovo la sede corretta.<br><br>Prova a reinquadrare il QR&nbsp;code</h1>";
                exit();
            }


            $row = $result->fetch_assoc();
            $ultimo_numero_consegnato = $row["ultimo_numero"];
            // controllo se son arrivato all'ultimo numero ---> Z999
            if($ultimo_numero_consegnato == "Z999"){
                $nuovo_numero = "A000";     // lo metto al primo valore così da riniziare il giro
            }
            else{
                $ultimo_numero_consegnato++;
                $nuovo_numero = $ultimo_numero_consegnato;
            }
            // salvo nel DB il $nuovo_numero
            $stmt=$conn->prepare("UPDATE coda_main SET ultimo_numero = ? WHERE id = ?");
            $stmt->bind_param('si', $nuovo_numero, $row["id"]);
            $stmt->execute();

            $conn->commit();
            $flag = false;
        }
        catch (mysqli_sql_exception $exception) {
            $conn->rollback();
        }

    }






    $numero_chiaro = $nuovo_numero;
    $numero_criptato = cripta_cookie($numero_chiaro);
    setcookie("numero", $numero_criptato, time()+54000);        // 15 ore


    // ricarico questa pagina così da leggere il cookie
    header('Location: index.php?codice=' . $_GET['codice']);
}


if ( ! isset($_COOKIE['numero']) ){
    // se non ho il numero salvato, chiedo all'utente se vuole prendere un nuovo numero


    // prendo le info luogo e tipo dal db
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $stmt=$conn->prepare("SELECT luogo, tipo FROM coda_main WHERE codice=UPPER(?)");
    $stmt->bind_param('s', $_GET['codice']);
    $stmt->execute();
    $result = $stmt->get_result();

    // se non trovo la sede - codice, do errore
    $row_cnt = $result->num_rows;
    if($row_cnt == 0){
        //header("Location: index.php?errmes=" . urlencode("Errore! Non trovo la sede corretta"));
        echo "<h1>Errore! Non trovo la sede corretta.<br><br>Prova a reinquadrare il QR&nbsp;code</h1>";
        exit();
    }
    $row = $result->fetch_assoc();
    $luogo = $row['luogo'];
    $tipo = $row['tipo'];



    // mostro la pagina del pulsante
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
    </style>


    <link href="./assets/dist/css/sticky-footer.css" rel="stylesheet">
</head>
<body class="d-flex flex-column h-100">

<!-- Begin page content -->
<main class="flex-shrink-0">
    <div class="container">
        <br>
        <p style="text-align:center;">Sede di <?php echo $luogo; ?><br><?php echo $tipo; ?></p>
        <br><br><br><br><br><br>
        <form id="form_prendi_numero" action="index.php" method="get">
            <div class="row">
                <input name="nuovo_numero" value="1" hidden>
                <input name="codice" value="<?php echo $_GET['codice']; ?>" hidden>
                <button id="prendi_numero" type="submit" class="btn btn-primary btn-lg btn-block">Prendi il numero</button>
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
        $(document).ready(function () {

        $("#form_prendi_numero").submit(function (e) {

            //disable the submit button
            $("#prendi_numero").attr("disabled", true);

            $("#prendi_numero").html('Caricamento...');

            return true;

        });
    });
</script>



</body>
</html>


<?php

    // mi fermo qui
    exit();

}
else{
    // se ho già il cookie (in caso di ricarica pagina o reinquadramento qr code)


    // prendo le info luogo e tipo dal db
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $stmt=$conn->prepare("SELECT luogo, tipo FROM coda_main WHERE codice=UPPER(?)");
    $stmt->bind_param('s', $_GET['codice']);
    $stmt->execute();
    $result = $stmt->get_result();

    // se non trovo la sede - codice, do errore
    $row_cnt = $result->num_rows;
    if($row_cnt == 0){
        //header("Location: index.php?errmes=" . urlencode("Errore! Non trovo la sede corretta"));
        echo "<h1>Errore! Non trovo la sede corretta.<br><br>Prova a reinquadrare il QR&nbsp;code</h1>";
        exit();
    }
    $row = $result->fetch_assoc();
    $luogo = $row['luogo'];
    $tipo = $row['tipo'];



    $numero_chiaro = decripta_cookie($_COOKIE['numero']);

}





// qui sotto la pagina home utente
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
    <p id="tabellone" class="lead" style="text-align:center; border-style: solid; border-color: red; border-width: 1px; color: red; font-family:consolas; font-size:100px;">-</p>
	<br>
    <p id="frase_successo" style="text-align:center; color: white;">-</p>

	<h5 class="mt-4"><i>Il tuo numero</i></h5>
    <p id="num_utente" class="lead" style="text-align:center; border-style: solid; border-width: 1px; font-family:consolas; font-size:100px;"><?php echo $numero_chiaro; ?></p>
    <div>Solamente quando è il tuo turno, recati a piedi all'ingresso. <b>Non andare prima</b>, altrimenti rendi inutile questo sistema! Nel frattempo attendi in auto</div>
	
	
	<br><br>
	<hr>
	<div>Il tabellone viene aggiornato in automatico ogni 10 secondi.<br>Se dovessi uscire dalla pagina, reinquadra il QR&nbsp;code con lo stesso dispositivo e non perderai il tuo numero.</div>
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


    // definisco la funzione che capisca se è il mio turno o meno
    // con mio turno si intende se il tabellone è uguale al mio biglietto o se è di poco superiore ( massimo 200 numeri più avanti )
    // se è il mio turno restituisco true, altrimenti false
    // tiene in considerazione anche quando si "cambia il rotolo di numeri"
    // nel senso che il sistema capisca che il numero preso dell'utente del tipo Z9xx
    // è stato superato da A0yy
    function tabellone_maggiore_num_utente(tabellone, turno_utente){
        if(tabellone === turno_utente){
            return true;
        }

        // ora guardo se il tabellone è > di 200 allora restituisco false, altrimenti true

        var differenza_numeri;
        var primo_carattere_tabellone = tabellone.substring(0,1);
        var primo_carattere_utente = turno_utente.substring(0,1);
        primo_carattere_tabellone = primo_carattere_tabellone.toUpperCase();
        primo_carattere_utente = primo_carattere_utente.toUpperCase();
        if (primo_carattere_tabellone === primo_carattere_utente){
            // guardo la sola differenza tra numeri
            differenza_numeri = parseInt(tabellone.substring(1)) - parseInt(turno_utente.substring(1));
            return (differenza_numeri <= 200 && differenza_numeri >= 0);
        }
        else{
            // esempio tab: B001
            //         ute: A950
            // avanzo di uno la lettera del turno utente per vedere se è uguale. Se è uguale, aggiungo 1000 al numero del tabellone e vedo la differenza
            var primo_carattere_utente_piu_1;
            if (primo_carattere_utente === 'Z'){
                primo_carattere_utente_piu_1 = 'A';
            }
            else{
                primo_carattere_utente_piu_1 = String.fromCharCode(primo_carattere_utente.charCodeAt(0) + 1);
            }
            if(primo_carattere_utente_piu_1 !== primo_carattere_tabellone){
                return false;
            }
            else{
                // i caratteri sono uguali, vediamo se i numeri sono vicini
                // guardo la sola differenza tra numeri
                differenza_numeri = ( parseInt( tabellone.substring(1) ) + 1000) - parseInt(turno_utente.substring(1));
                return (differenza_numeri <= 200 && differenza_numeri >= 0);
            }


        }


    }




    // definisco la funzione che mi chiama l'aggiornamento
    function get_numero_tabellone(){
        $.ajax({
            url:"ajax_aggiorna_numero.php",
            type: "GET",
            data: { codice: "<?php echo $_GET['codice']; ?>"},
            success:function(result){
                $("#tabellone").html(result);
                //ora attendo 10 secondi
                //setTimeout(function(){get_fb();}, 10000);

                // ora controllo se è il turno dell'utente o se comunque l'ho superato
                var turno_utente = $("#num_utente").text();
                if (tabellone_maggiore_num_utente(result, turno_utente)){
                    document.getElementById("tabellone").style.color = "green";
                    document.getElementById("tabellone").style.borderColor = "green";

                    // ora dato che è il mio turno faccio qualcosa, notifica?
                    $("#frase_successo").html("<b>È il tuo turno!</b>");
                    document.getElementById("frase_successo").style.color = "green";
                    document.getElementById("frase_successo").style.fontSize = "40px";


                    // provo la notifica, se non l'ho già mandata
                    // controllo quindi i cookie
                    var cookie_js = document.cookie;
                    var gia_avvisato = cookie_js.includes("gia_avvisato=1");

                    if( ! gia_avvisato){
                        if (typeof Notification !== "undefined"){
                            var tipoNotifica = Notification.permission;
                            if(tipoNotifica != "denied"){
                                Notification.requestPermission().then(function (permission) {
                                    if (permission == "granted"){
                                        console.log(permission);
                                        var title = "È il tuo turno!";
                                        icon = './assets/warning.png';
                                        var body = "È il tuo turno ai <?php echo $tipo; ?>";
                                        var notification = new Notification(title, { body, icon });
                                        window.navigator.vibrate([500, 200, 500, 200, 500]);
                                        notification.onclick = function(){
                                            window.parent.focus();
                                            notification.close();
                                        }
                                    }


                                });
                            }
                        }
                        // mando comunque un normale alert
                        alert("È il tuo turno!");

                        // imposto il cookie così da non avvertire più
                        var now = new Date();
                        var time = now.getTime();
                        time += 3600 * 1000;
                        now.setTime(time);
                        document.cookie =
                            'gia_avvisato=1' +
                            '; expires=' + now.toUTCString();
                    }


                }
                else{
                    document.getElementById("tabellone").style.color = "red";
                    document.getElementById("tabellone").style.borderColor = "red";

                    $("#frase_successo").html("-");
                    document.getElementById("frase_successo").style.color = "white";
                    document.getElementById("frase_successo").style.fontSize = null;
                }
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

</script>

    
  </body>
</html>
