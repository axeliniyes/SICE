<?php

include './EncuestaDao.php';

head("SICE-RegRes");

echo '<h1>estamos registrando tus resultados</h1>';
echo '<img alt="" src="../imagenes/goodman.jpg" width="30%" />' ;


foreach($_POST as $idPregunta => $idOpcion){

	//si es una pregunta de selección múltiple:
	if(is_array($idOpcion)) {
		//por cada casilla que se selecciono hay que registrar
		foreach($idOpcion as $idOp){
			registrarRespuesta($_SESSION['userId'], $idPregunta, $idOp);
		}
	// si no es pregunta de una solo respuesta, procede normal
	}else{
	   registrarRespuesta($_SESSION['userId'], $idPregunta, $idOpcion);
	}
    	    
}

header( "refresh:3; url=../index.php" );