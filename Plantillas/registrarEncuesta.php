<?php

/*
 * Copyright (C) 2017 Derick Lagunes
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

include './EncuestaDao.php';

//usuario que ha creado la encuesta
$user = $_SESSION['user'];
$id = $_SESSION['userId'];

//cosas que regresa el formulario
$todo = $_POST;

//variables locales
$count = 0;
$idEncuesta=0;
$idPregunta=0;


echo '<br/>';
//primera iteracion: nombre encuesta
//segunda:           nombre pregunta
foreach ($todo as $name => $val) {

    if ($count == 0) {
        //crear encuesta
        newPoll($id, $val);
        $idEncuesta = getPollId($val, $id);

    } else {
        //iterando preguntas
        if (preg_match("/abierta.*/", $name)) {
            
            echo setPregunta($idEncuesta, 1, $val);
            $idPregunta = getQuestionId($idEncuesta, $val);
            
        } elseif (preg_match("/opcion.*/", $name)) {
            
            echo setPregunta($idEncuesta, 2, $val);
            $idPregunta = getQuestionId($idEncuesta, $val);
            
        } elseif (preg_match("/seleccion.*/", $name)) {
            
            echo setPregunta($idEncuesta, 3, $val);
            $idPregunta = getQuestionId($idEncuesta, $val);
            
        }  else {
            
            setOpcion($idPregunta, $val);
            
        }
        
    }

    $count++;
}

echo '<h1>Encuesta creada</h1>';
echo '<img alt="" src="../imagenes/oke.gif" width="20%" />' ;


header( "refresh:1; url=../index.php" );
