<?php
    require_once("newPage.php");
    require_once("functions.php");

    use functions\functions;
    $functions = new functions();
    $functions->openDBConnection();

    $pagina = new newPage("../html/dashboardUser.html", "Area personale user", "Area personale user", "Area personale user Saudade");

    if(isset($_SESSION['user_name'])){
        if($_SESSION['user_name'] != "admin") { 
            //Verifico se esiste utente (improbabile)
            $stmt = $functions->getConnection()->prepare("SELECT * FROM utenti WHERE user_name=?");
            $stmt->bind_param("s", $_SESSION['user_name']);
            $stmt->execute();
            $risultato = $stmt->get_result();
    
            if(mysqli_num_rows($risultato) > 0){ //Se utente esiste ed è registrato
                //ACQUISTI
                $stmt = $functions->getConnection()->prepare("SELECT * FROM acquisti WHERE user_name=?");
                $stmt->bind_param("s", $_SESSION['user_name']);
                $stmt->execute();
                $ris = $stmt->get_result();
    
                if(mysqli_num_rows($ris) > 0){
                    $acquisti = "";
                    while ($row = $ris->fetch_assoc()) {
                        $acquisti .= "<li>" . $row['articolo'] . " ";
                        $acquisti .= $row['data'] . " ";
                        $acquisti .= "<a href=\"aggiungiRecensione.php?sogno=".urldecode($row['articolo'])."\">Lascia una recensione</a> </li>";
                    }
                    $pagina->modificaHTML("{elencosogni}", $acquisti);
                }else{
                    $pagina->modificaHTML("{elencosogni}", "<p>Nessun acquisto ancora effettuato</p>");
                }

                //RECENSIONI
                $recenz = $functions->getConnection()->prepare("SELECT * FROM recensioni WHERE user_name=? ORDER BY data;");
                        $recenz->bind_param("s", $_SESSION['user_name']);
                        $recenz->execute();
                        $recenz = $recenz->get_result();

                if($recenz == null){
                    $rec = "Ancora nessuna recensione";
                    $pagina->modificaHTML("{recensioni}", $rec);
                }        
                else{
                    $rec = "";

                    foreach( $recenz as $row){
                        $rec .= file_get_contents("../html/recensioneTemp.html");

                        $rec = str_replace("{utente}", "<span class='titoletto'>Sogno: </span>" . $row['articolo'], $rec);
                        $rec = str_replace("{stelle}", $row['stelle'], $rec);
                        //$rec = str_replace("{sogno}", $row['articolo'], $rec);

                        $text = $row['testo'];
                        $maxLength=20;
                        if (strlen($text) > $maxLength) {
                            $text = substr($text, 0, $maxLength) . '<a href="#" class="read-more" onclick="espandi(event)">...leggi tutto</a><span class="full-description hidden">' . substr($text, $maxLength) . '</span>';
                        }
                        $rec = str_replace("{testo}", $text, $rec);
                    }

                    $pagina->modificaHTML("{recensioni}", $rec);
                }
                //DA FARE

                //RECENSIONI
                $prenotaz = $functions->getConnection()->prepare("SELECT * FROM prenotazioni WHERE user_name=? ORDER BY data;");
                $prenotaz->bind_param("s", $_SESSION['user_name']);
                $prenotaz->execute();
                $prenotaz = $prenotaz->get_result();

                if($prenotaz == null){
                    $rec = "<p>Non hai prentazioni, <a href=\"calendario.php\" >prenota ora</a></p>";
                    $pagina->modificaHTML("{prenotazione}", $rec);
                }        
                else{

                    foreach( $prenotaz as $row){
                        $text = "Hai una prenotazione per il giorno: " . $row['data'] . ". <p>Cambia <a href=\"calendario.php\" >data</a></p>";
                    }

                    $pagina->modificaHTML("{prenotazione}", $text);
                }

            }else{
                $pagina->printErrorPage("Utente non trovato"); //Quasi impossibile accada
            }
        }else{
            $pagina->printErrorPage("Pagina riservata agli utenti");
        }
    }else{
        $pagina->printErrorPage("Pagina riservata ad utenti loggati, esegui il <a href=\"login.php\">login</a> per accedere");
    }    

    $functions->closeConnection();
    $pagina->printPage();