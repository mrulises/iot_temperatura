<?php 
    session_start();

    require_once('conex.php');
    if($db_estado){
        if(isset($_SESSION['idUser'])){
            if(isset($_GET['id']) && is_numeric($_GET['id'])){
                $id = $_GET['id'];
                $consulta = "SELECT ID_DEV, UBICACION, AJUSTE_GMT, DELTA_SEGUNDOS, DELTA_TEMP, ESTADO FROM DISPOSITIVOS WHERE ID_DEV={$id};";
                $dispositivoQuery = $conexion->query($consulta);
                if($dispositivoQuery->num_rows == 1){
                    $config = $dispositivoQuery->fetch_object();
                    echo "OK " . $config->AJUSTE_GMT . " " . $config->DELTA_SEGUNDOS . " " . $config->DELTA_TEMP . " " . $config->ESTADO;
                }else{
                    echo "UNKNOWN DEVICE";
                }
            }else{
                echo "INVALID DEVICE";
            }
        }else{
            echo "LOGIN PLEASE";
        }
    }
?>