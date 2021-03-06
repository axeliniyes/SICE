<?php

/*
 * Copyright (C) 2017 USUARIO
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

include 'tools.php';

function newPoll($idUser, $nombre) {

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());

    $query = "CALL nuevaEncuesta('$idUser','$nombre');";

    if (mysqli_query($con, $query)) {
        $con->close();
        return "done";
    } else {
        $con->close();
        return "error";
    }
}

function getPollId($nombreEncuesta, $userId) {

    $mysqli = new mysqli('localhost', 'php', 'php', 'encuestas');

    $call = $mysqli->prepare('CALL getEncuestaId(?, ?, @idEnc)');
    $call->bind_param('si', $nombreEncuesta, $userId);
    $call->execute();

    $select = $mysqli->query('SELECT @idEnc');
    $result = $select->fetch_assoc();

    $encuestaId = $result['@idEnc'];

    $mysqli->close();
    return $encuestaId;
}

function getQuestionId($idEncuesta, $descripcion) {

    $mysqli = new mysqli('localhost', 'php', 'php', 'encuestas');

    $call = $mysqli->prepare('CALL getPreguntaId(?, ?, @id)');
    $call->bind_param('is', $idEncuesta, $descripcion);
    $call->execute();

    $select = $mysqli->query('SELECT @id');
    $result = $select->fetch_assoc();

    $preguntaId = $result['@id'];

    $mysqli->close();
    return $preguntaId;
}

function setPregunta($idEncuesta, $tipoPregunta, $descripcion) {
    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $query = "CALL nuevaPregunta($idEncuesta,$tipoPregunta,'$descripcion');";

    if (!mysqli_query($con, $query)) { 
        printf("error: %s\n", mysqli_error($con));
    }
    $con->close();
}

function setOpcion($idPregunta, $descripcion) {
    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $query = "CALL nuevasOpciones('$idPregunta','$descripcion');";

    if (mysqli_query($con, $query)) {
        $con->close();
        return "done";
    } else {
        $con->close();
        return "error";
    }
}

/**
 * Metodo que regrsa todas las encuestas para los usuarios
 * @return string
 */
function popularEncuestas() {

    echo '<ul>';

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $sql = "SELECT * from encuesta;";

    if ($result = mysqli_query($con, $sql)) {

        // Fetch one and one row
        while ($row = mysqli_fetch_row($result)) {
            echo '<li><a href="contestarEncuesta.php?nombre=' . $row[1] . '&id=' . $row[0] . '">' . $row[1] . '</a></li>';
        }

        // Free result set
        mysqli_free_result($result);
    }
    mysqli_close($con);

    echo '</ul>';
}

/**
 * Metodo que regresa todas las las preguntas de una encuesta
 * @return string
 */
function popularPreguntas($idEncuesta) {

    $count = 1;

    //primera consulta, get pregunta
    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $sql = "SELECT * from pregunta where fk_idEncuesta='$idEncuesta';";

    if ($result = mysqli_query($con, $sql)) {

        while ($row = mysqli_fetch_row($result)) {

            echo '<div class="form-group">';
            echo '<h4>Pregunta' . $count . '</h4>';
            echo '<label class="col-md-4 control-label" for="' . $row[0] . '">' . $row[3] . '</label>';
            echo '<div class="col-md-4">';

            //si es de tipo opción o selección				
            if ($row[2] == 2 || $row[2] == 3) {
                //segunda consulta, get opciones
                $con2 = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
                $sql2 = "SELECT * from opciones where fk_idPregunta='$row[0]';";

                if ($result2 = mysqli_query($con2, $sql2)) {
                    $optionCount = 0;
                    while ($row2 = mysqli_fetch_row($result2)) {
                        //manejar opciones de la pregunta opción o selección
                        //opción
                        if ($row[2] == 2) {
                            echo '<div class="radio" name="' . $row[0] . '">';
                            echo '<input type="radio" name="' . $row[0] . '" value="' . $row2[0] . '">';
                            echo $row2[2];
                            echo '</div>';
                        }
                        //selección
                        if ($row[2] == 3) {
                            echo '<div class="checkbox" name="' . $row[0] . '">';
                            echo '<input type="checkbox" name="' . $row[0] . '[]" value="' . $row2[0] . '">';
                            echo $row2[2];
                            echo '</div>';
                        }
                    }

                    mysqli_free_result($result2);
                }
                    mysqli_close($con2);
            } else { //es abierta: nombre= respuestax
                echo '<input id="respuesta' . $count . '" name="' . $row[0] . '" class="form-control input-md" type="text">';
            }

            echo '</div>';
            echo '</div>';
            echo '<hr>';


            $count++;
        }

        // Free result set
        mysqli_free_result($result);
    }
    mysqli_close($con);
}

/**
 * Método que regresa todas las encuestas para el usuario
 * solo cambia la condición de la consulta :v
 * @return string
 */
function popularEncuestasDeUser($idUser) {

    echo '<ul>';

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $sql = "SELECT * FROM encuesta WHERE fk_idUsuario='$idUser';";

    if ($result = mysqli_query($con, $sql)) {

        // Fetch one and one row
        while ($row = mysqli_fetch_row($result)) {
            echo '<li><a href="resultadoEncuesta.php?nombre=' . $row[1] . '&id=' . $row[0] . '">' . $row[1] . '</a></li>';
        }

        // Free result set
        mysqli_free_result($result);
    }
    mysqli_close($con);

    echo '</ul>';
}

/**
 * Método para registrar las respuestas del usuario en la base de datos
 * la variable respuesta puede ser una id de opción o una string si es
 * abierta
 * @return true or false
 */
function registrarRespuesta($idUsuario, $idPregunta, $respuesta) {

    $estado = false;

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());

    if (is_numeric($respuesta)) {
        $query = "CALL registrarRespuestaOpcion($idUsuario,$idPregunta,$respuesta);";
    } else {
        $query = "CALL registrarRespuestaAbierta($idUsuario,$idPregunta,'$respuesta');";
    }

    if (mysqli_query($con, $query)) {
        $estado = true;
    } else {
        printf("error: %s\n", mysqli_error($con));
        echo '<br />';
    }


    $con->close();
    return $estado;
}

/**
 * Método para devolver un arreglo asociativo con las respuestas de la encuesta
 * para poder mandarlo a la función que gráfica
 */
function getResultados($idEncuesta, $idPregunta) {

    $arreglo = array();

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $sql = "select pregunta.descripcion,opciones.descripcion,COUNT(resultadoopcion.fk_idUsuario) from opciones join resultadoopcion on resultadoopcion.respuesta=opciones.idOpciones join pregunta on pregunta.idPregunta=opciones.fk_idPregunta JOIN encuesta on encuesta.idEncuesta=pregunta.fk_idEncuesta WHERE encuesta.idEncuesta=$idEncuesta AND pregunta.idPregunta=$idPregunta AND pregunta.fk_tipoPregunta != 1 GROUP BY opciones.descripcion ORDER BY pregunta.descripcion;";


    if ($result = mysqli_query($con, $sql)) {

        while ($row = mysqli_fetch_row($result)) {

            $arreglo[$row[1]] = $row[2];
        }

        mysqli_free_result($result);
    }
    mysqli_close($con);

    return $arreglo;
}

/**
 * 
 */
function getPreguntas($idEncuesta) {

    $arreglo = array();

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $sql = "select pregunta.idPregunta from pregunta WHERE fk_idEncuesta=$idEncuesta AND pregunta.fk_tipoPregunta != 1;";


    if ($result = mysqli_query($con, $sql)) {

        while ($row = mysqli_fetch_row($result)) {

            array_push($arreglo, $row[0]);
        }

        mysqli_free_result($result);
    }
    mysqli_close($con);

    return $arreglo;
}

/**
 * 
 */
function getNombres($idEncuesta) {

    $arreglo = array();

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $sql = "select pregunta.descripcion from pregunta WHERE fk_idEncuesta=$idEncuesta AND pregunta.fk_tipoPregunta != 1;";


    if ($result = mysqli_query($con, $sql)) {

        while ($row = mysqli_fetch_row($result)) {

            array_push($arreglo, $row[0]);
        }

        mysqli_free_result($result);
    }
    mysqli_close($con);

    return $arreglo;
}

/**
 * Método para imprimir una lista de respuestas de las preguntas abiertas
 */
function getResultadosAbiertos($idEncuesta,$nombre) {

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $sql = "select pregunta.descripcion, resultadoabierta.respuesta from pregunta JOIN encuesta on encuesta.idEncuesta=pregunta.fk_idEncuesta JOIN resultadoabierta on resultadoabierta.fk_idPregunta=pregunta.idPregunta where encuesta.idEncuesta=$idEncuesta AND pregunta.descripcion='$nombre' ORDER BY pregunta.descripcion LIMIT 9";

    echo '<div class="col-md-12">';
    echo '<ul>';
    
    if ($result = mysqli_query($con, $sql)) {


        while ($row = mysqli_fetch_row($result)) {
            echo '<li style="font-size: larger">' . $row[1] . '</li>';
        }

        mysqli_free_result($result);
    }
    mysqli_close($con);

    echo '</ul>';
    echo '<br />';
    echo '</div>';
}


/**
 * 
 */
function getNombresAbiertas($idEncuesta) {

    $arreglo = array();

    $con = mysqli_connect('localhost', 'php', 'php', 'encuestas') or die('Connection failed' . mysqli_error());
    $sql = "select pregunta.descripcion from pregunta JOIN encuesta on encuesta.idEncuesta=pregunta.fk_idEncuesta where encuesta.idEncuesta=$idEncuesta AND pregunta.fk_tipoPregunta = 1 ORDER BY pregunta.descripcion LIMIT 9";


    if ($result = mysqli_query($con, $sql)) {

        while ($row = mysqli_fetch_row($result)) {

            array_push($arreglo, $row[0]);
        }

        mysqli_free_result($result);
    }
    mysqli_close($con);

    return $arreglo;
}
