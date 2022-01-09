<?php


require 'common.php';


// parametri per connettersi al db
$servername = get_servername();
$username = get_username();
$password = get_password();
$dbname = get_dbname();



// controllo se posso accedere all'area Admin oppure no.
// Ammetto che non è ultra sicuro questo metodo, ma ho poco tempo per farlo
// Se qualcuno vuole farlo meglio ben venga. Grazie
if ( ! isset($_COOKIE['adminPassword']) || ! isset($_COOKIE['adminUtente']) ){
    header('Location: loginAdmin.php');
    exit();
}
// ora controllo se quello che ha scritto nel cookie corrisponde alla password salvata nel db
// ripeto che so che non è prassi... bisognerebbe fare con hash ecc... ma questo è
$utente_on_cookie = $_COOKIE["adminUtente"];
$password_on_cookie = $_COOKIE["adminPassword"];
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt=$conn->prepare("SELECT password FROM coda_user WHERE nome=?");
$stmt->bind_param('s', $utente_on_cookie);
$stmt->execute();
$result = $stmt->get_result();

// se non trovo righe, non esiste quell'utente
$row_cnt = $result->num_rows;
if($row_cnt == 0){
    // elimino i cookie
    setcookie("adminUtente", "", -1);
    setcookie("adminPassword", "", -1);
    header('Location: loginAdmin.php');
    exit();
}

$row = $result->fetch_assoc();
$password_sul_db = $row['password'];
if($password_sul_db != $password_on_cookie){
    // elimino i cookie
    setcookie("adminUtente", "", -1);
    setcookie("adminPassword", "", -1);
    header('Location: loginAdmin.php');
    exit();
}







// controllo se ho azioni da intraprendere
if (isset ($_POST['azione'])){
    if ($_POST['azione'] == 'modifica_sede'){


        // prendo le info dal db
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $stmt=$conn->prepare("UPDATE coda_main SET ulss = ?, luogo = ?, tipo = ? WHERE id = ?");
        $stmt->bind_param('sssi', $_POST['ulss'], $_POST['luogo'], $_POST['tipo'], $_POST['id_sede_modifica']  );
        $stmt->execute();

        header('Location: backoffice.php');
        exit();

    }

    if ($_POST['azione'] == 'elimina_sede'){

        // prendo le info dal db
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $stmt=$conn->prepare("DELETE FROM coda_main WHERE id = ?");
        $stmt->bind_param('i', $_POST['id_sede']  );
        $stmt->execute();

        header('Location: backoffice.php');
        exit();

    }

    if ($_POST['azione'] == 'crea_sede'){


        // prendo le info dal db
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $codice_rnd = strtoupper(getRandomString(12));
        $token_scorrimento_rnd = strtoupper(getRandomString(50));

        $stmt=$conn->prepare("INSERT INTO coda_main (id, codice, ulss, luogo, tipo, tabellone, ultimo_numero, token_scorrimento) VALUES (NULL, ?, ?, ?, ?, 'A000', 'Z999',?)");
        $stmt->bind_param('sssss', $codice_rnd, $_POST['ulss'], $_POST['luogo'], $_POST['tipo'], $token_scorrimento_rnd );
        $stmt->execute();

        // ora pesco l'id che ho appena creato e lo aggancio in suffisso al codice_rnd e a $token_scorrimento_rnd
        // così da crearli univoci
        $stmt=$conn->prepare("SELECT id FROM coda_main WHERE codice = ? ");
        $stmt->bind_param('s', $codice_rnd );
        $stmt->execute();
        $result = $stmt->get_result();
        $row_id = $result->fetch_assoc();
        $id = $row_id['id'];


        // ora aggiorno il codice con il suffisso dell'id
        $codice_rnd .= $id;
        $token_scorrimento_rnd .= $id;
        $stmt=$conn->prepare("UPDATE coda_main SET codice = ?, token_scorrimento = ? WHERE id = ?");
        $stmt->bind_param('sss', $codice_rnd, $token_scorrimento_rnd, $id );
        $stmt->execute();

        header('Location: backoffice.php');
        exit();

    }

}


















// prendo le info dal db
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt=$conn->prepare("SELECT id, codice, ulss, luogo, tipo, token_scorrimento FROM coda_main");
// $stmt->bind_param('s', $_GET['codice']);
$stmt->execute();
$result = $stmt->get_result();

// se non trovo sedi do errore
$row_cnt = $result->num_rows;
if($row_cnt == 0){
    //header("Location: index.php?errmes=" . urlencode("Errore! Non trovo la sede corretta"));
    echo "<h1>Errore! Non trovo nessuna sede</h1>";
    exit();
}








?>



<!doctype html>
<html lang="it" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Luca Arietti">
    <meta name="generator" content="Hugo 0.84.0">
    <title>ADMIN Coda online</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sticky-footer/">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="./assets/dist/js/bootstrap.bundle.min.js"></script>


    <script src="./assets/dist/js/qrcode.min.js"></script>



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

        <h3>Puoi creare una nuova sede (con tabellone dei numeri annesso) oppure modificare o eliminare le sedi già presenti</h3>

        <br><br>
        <div class="row">
            <a class="btn btn-lg btn-primary btn-block" data-bs-toggle="collapse" href="#collapseCreaNuovaSede" role="button" aria-expanded="false" aria-controls="collapseCreaNuovaSede">Crea nuova sede</a>
        </div>
        <br>
        <div class="collapse" id="collapseCreaNuovaSede">
            <div class="card card-body">

                <form action="backoffice.php" method="post">
                    <input type="hidden" name="azione" value="crea_sede">
                    <div>
                        <label for="ulss_new" class="form-label">ULSS (ad esempio <i>ULSS 9</i>)</label>
                        <input type="text" class="form-control" name="ulss" id="ulss_new" value="" maxlength="250" required>
                    </div>
                    <br><br>
                    <div>
                        <label for="luogo_new" class="form-label">Luogo (definire univocamente, ad esempio <i>Centro polifunzionale Bussolengo</i> oppure <i>Legnago EX Lidl</i>)</label>
                        <input type="text" class="form-control" name="luogo" id="luogo_new" value="" maxlength="250" required>
                    </div>
                    <br><br>
                    <div>
                        <label for="tipo_new" class="form-label">Tipo di servizio (<i>Tamponi</i> o <i>Vaccini</i>...)</label>
                        <input type="text" class="form-control" name="tipo" id="tipo_new" value="" maxlength="250" required>
                    </div>
                    <br><br>
                    <div style="text-align:center;">
                        <button class="btn btn-lg btn-success" type="submit">Crea Sede</button>
                    </div>
                </form>


            </div>
        </div>

        <br><br><br>

        <div class="accordion" id="accordionSedi">

<?php
            for ($i=0; $i < $row_cnt; $i++){

                $row = $result->fetch_assoc();
                $id = $row['id'];
                $codice = $row['codice'];
                $ulss = $row['ulss'];
                $luogo = $row['luogo'];
                $tipo = $row['tipo'];
                $token_scorrimento = $row['token_scorrimento'];
                $link_token_scorrimento = "https://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) ."/avanza_numero.php?codice=". $codice . "&token=" . $token_scorrimento;    //  $_SERVER['REQUEST_URI'];
                $link_qrCode = "https://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) ."/index.php?codice=". $codice;    //  $_SERVER['REQUEST_URI'];

?>
                <div class="accordion-item">
                <h2 class="accordion-header" id="<?php echo $id; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $id; ?>" aria-expanded="false" aria-controls="collapse<?php echo $id; ?>">
                        <?php echo $ulss . "&nbsp;&nbsp;" . $luogo . "&nbsp;-&nbsp;" . $tipo; ?>
                    </button>
                </h2>
                <div id="collapse<?php echo $id; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $id; ?>" data-bs-parent="#accordionSedi">
                    <div class="accordion-body">


                        <form action="backoffice.php" method="post">
                            <input type="hidden" name="azione" value="modifica_sede">
                            <input type="hidden" name="id_sede_modifica" value="<?php echo $id; ?>">
                            <div>
                                <label for="ulss<?php echo $id; ?>" class="form-label">ULSS</label>
                                <input type="text" class="form-control" name="ulss" id="ulss<?php echo $id; ?>" value="<?php echo $ulss; ?>" maxlength="250" required>
                            </div>
                            <br><br>
                            <div>
                                <label for="luogo<?php echo $id; ?>" class="form-label">Luogo (definire univocamente)</label>
                                <input type="text" class="form-control" name="luogo" id="luogo<?php echo $id; ?>" value="<?php echo $luogo; ?>" maxlength="250" required>
                            </div>
                            <br><br>
                            <div>
                                <label for="tipo<?php echo $id; ?>" class="form-label">Tipo di servizio (<i>Tamponi</i> o <i>Vaccini</i>...)</label>
                                <input type="text" class="form-control" name="tipo" id="tipo<?php echo $id; ?>" value="<?php echo $tipo; ?>" maxlength="250" required>
                            </div>
                            <br><br>
                            <div style="text-align:center;">
                                <button class="btn btn-lg btn-success" type="submit">Salva</button>
                            </div>
                        </form>
                            <br><br><br>

                        Codice univoco sede:&nbsp;<?php echo $codice; ?><br><br>
                        <button id="bottoneCopiaLinkAppunti" class="btn btn-outline-primary" onclick="copiaInAppunti('<?php echo $link_token_scorrimento; ?>')" >Clicca qui per copiare il link da utilizzare per scorrere i numeri</button>
                        <br><br><br>
                        QR Code da mostrare agli utenti (fare tasto destro sopra al QR e "Salva immagine" o "Copia immagine")
                        <div id="qrcode<?php echo $id; ?>"></div>
                        <script type="text/javascript">
                            new QRCode(document.getElementById("qrcode<?php echo $id; ?>"),
                                    {
                                        text: "<?php echo $link_qrCode; ?>",
                                        width: 200,
                                        height: 200
                                    }
                                );
                            $("#qrcode<?php echo $id; ?>").attr('title', '');
                        </script>


                            <br><br><br>
                            <div>
                                <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#modalEliminaSede" onclick="modalEliminaSedeJS(<?php echo $id; ?>)">Elimina questa sede</button>
                            </div>
                            <br><br>


                    </div>
                </div>
            </div>

<?php
            }
?>


        </div>







        <div class="modal fade" id="modalEliminaSede" tabindex="-1" aria-labelledby="modalEliminaSedeLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sei sicuro?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Sei davvero sicuro di eliminare la sede di<br><span id="modalTestoDaAggiungere"></span> ?<br><br>Tutte le persone in coda perderanno il loro numero e anche il tabellone corrispondente verrà eliminato</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                        <form action="backoffice.php" method="post">
                            <input type="hidden" name="id_sede" id="id_sede" value="">
                            <input type="hidden" name="azione" value="elimina_sede">
                            <button type="submit" class="btn btn-danger">Elimina</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>











    </div>
</main>

<footer class="footer mt-auto py-3 bg-light">
    <div class="container">
        <div class="text-muted" style="text-align:center; font-size: 70%;">Realizzato da Luca Arietti e donato alle ULSS, nella speranza di aiutare il prossimo</div>
        <div class="text-muted" style="text-align:center; font-size: 70%;"><a href="https://github.com/LucaArietti/eliminacode_online_ulss" target="_blank"><img src="./assets/github-logo.png" style="height: 1em;"></a> <a href="https://github.com/LucaArietti/eliminacode_online_ulss" target="_blank">Guarda il progetto su GitHub</a></div>
    </div>
</footer>


<script>
    function modalEliminaSedeJS(id){
        var ulss = $('#ulss' + id.toString() ).val();
        var luogo = $('#luogo' + id.toString() ).val();
        var tipo = $('#tipo' + id.toString() ).val();

        $('#id_sede').val(id);
        $("#modalTestoDaAggiungere").html(ulss + ' ' + luogo + ' - ' + tipo);
    }

    function copiaInAppunti(testo){
        // var copyText = document.getElementById("myInput");
        /* Select the text field */

        // copyText.select();
        // copyText.setSelectionRange(0, 99999); /* For mobile devices */
        /* Copy the text inside the text field */

        navigator.clipboard.writeText(testo);
        alert("Link copiato!")


    }
</script>

<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>



</body>
</html>



