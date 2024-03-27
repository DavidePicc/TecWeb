<?php 
	require_once("newPage.php");
	require_once "functions.php"; 
	use functions\functions;

	$functions = new functions();
    $functions->openDBConnection();

    if (isset($_GET['sogno'])) {
        $sogno = urldecode($_GET['sogno']);
        // Esegue la query utilizzando uno statement preparato
        $stmt = $functions->getConnection()->prepare("SELECT * FROM sogni WHERE titolo=?");
        $stmt->bind_param("s", $sogno);
        $stmt->execute();
        $risultato = $stmt->get_result();

        $pagina = new newPage("../html/acquistaSogno.html", 
								"Acquisto".$sogno, 
								$sogno, 
								"Pagina di acquisto per ".$sogno);

        if(mysqli_num_rows($risultato) > 0){
            foreach( $risultato as $row){
                $pagina->modificaHTML("{titolo}", $row['titolo']);
                $pagina->modificaHTML("{descrizione}", $row['descrizione']);
                $pagina->modificaHTML("{prezzo}", $row['prezzo']);
                $pagina->modificaHTML("{pathImg}",  "\"../assets/sogni/".$row['titolo'].".".$row['estensioneFile']."\"");

                if(isset($_SESSION['user_name']) && $_SESSION['user_name'] != "admin"){ //Se sono loggato e sono un utente 
                    $bottone = "<a href=\"confermaAcquisto.php?sogno={$row['titolo']}\" role=\"button\">Conferma acquisto</a>";
                    $pagina->modificaHTML("{bottoneCompra}",  $bottone);
                }
                
            }
        }else{
            $pagina = new newPage("../html/sognoNonTrovato.html",
                                    "Sogno non disponibile",
                                    "", "Pagina di errore per il sogno non disponibile");
        }
    } else {
        echo "Errore passaggio parametri, riprovare"; // Da migliorare
    }
	
    $functions->closeConnection();
    $pagina->modificaHTML("{breadcrumb}", "Acquisto sogno \"".$sogno."\"");
    $pagina->printPage();