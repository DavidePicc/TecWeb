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

        $pagina = new newPage("../html/confermaAcquisto.html", 
								"Conferma acquisto".$sogno, 
								$sogno, 
								"Pagina di conferma acquisto per ".$sogno);
        
        $pagina->modificaHTML("{breadcrumb}", "Conferma acquisto sogno \"".$sogno."\"");
        $pagina->modificaHTML("{titolo}", $sogno);
        $pagina->modificaHTML("{username}", $_SESSION['user_name']);


        if(mysqli_num_rows($risultato) > 0){
            foreach( $risultato as $row){
                // Registrazione acquisto utilizzando uno statement preparato
                $stmt = $functions->getConnection()->prepare("INSERT INTO acquisti (user_name, articolo) VALUES (?, ?)");
                $stmt->bind_param("ss", $_SESSION['user_name'], $sogno);
                $stmt->execute();
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
    $pagina->printPage();