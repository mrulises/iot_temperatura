<?php 
    session_start();

    require_once('conex.php');

    if($db_estado){
        if(isset($_SESSION['idUser'])){
            if(isset($_POST['Temp']) && isset($_POST['Dev']) && isset($_POST['Date']) && isset($_POST['Time'])){
                $temp = $_POST['Temp'];
                $id_dev = $_POST['Dev'];
                $date = $_POST['Date'];
                $time = $_POST['Time'];
                if(is_numeric($temp) && is_numeric($id_dev)){
                    $consultaEstado = "SELECT ESTADO FROM DISPOSITIVOS WHERE ID_DEV={$id_dev};";
                    $datoEstado = $conexion->query($consultaEstado);
                    if($datoEstado->num_rows == 1){
                        $estado = $datoEstado->fetch_object();
                        if($estado->ESTADO==1){
                            $sql = "INSERT INTO TEMPERATURAS(TEMP, ID_DEV, FECHA, HORA) VALUES ({$temp}, {$id_dev}, '{$date}', '{$time}');";
                            if($conexion->query($sql)){
                                echo 'OK';
                            }else{
                                echo 'ERROR';
                            }
                        }else{
                            echo "NOT AVAILABLE";
                        }
                    }else{
                        echo "NOT DEVICE";
                    }
                }else{
                    echo "DATA FAIL";
                }
            }else{
                echo "DATA LOSS";
            }
        }else{
            echo "LOGIN PLEASE";
        }
    }
?>