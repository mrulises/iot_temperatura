<?php
    session_start();

    require_once('conex.php');
    if($db_estado){
        if(isset($_SESSION['idUser'])){
            if(isset($_GET['eliminar'])){
                if(is_numeric($_GET['eliminar'])){
                    $consultaEliminar = "DELETE FROM DISPOSITIVOS WHERE ID_DEV={$_GET['eliminar']};";
                    if($conexion->query($consultaEliminar)){
                        header("Location: ./devices.php");
                    }else{
                        header("Location: ./devices.php?mensaje=Fallo al eliminar");    
                    }
                }else{
                    header("Location: ./devices.php");
                }
            }else if(isset($_POST['btnAgregar'])){
                if($_POST['btnAgregar']=='Agregar'){
                    if(isset($_POST['idAgregar']) && isset($_POST['ubicacionAgregar']) && isset($_POST['gmtAgregar']) && isset($_POST['deltaTimeAgregar']) && isset($_POST['deltaTempAgregar']) && isset($_POST['estadoAgregar'])){
                        $id = $_POST['idAgregar'];
                        $ubicacion = $_POST['ubicacionAgregar'];
                        $gmt = $_POST['gmtAgregar'];
                        $deltaTime = $_POST['deltaTimeAgregar'];
                        $deltaTemp = $_POST['deltaTempAgregar'];
                        $estado = $_POST['estadoAgregar'];
                        if($id!= "" && $ubicacion!="" && $gmt!="" && $deltaTime!="" && $deltaTemp!="" && $estado!=""){
                            $consultaVerificacion = "SELECT UBICACION FROM DISPOSITIVOS WHERE ID_DEV={$id};";
                            if($conexion->query($consultaVerificacion)->num_rows == 0){
                                $consultaAgregar = "INSERT INTO DISPOSITIVOS (ID_DEV, UBICACION, AJUSTE_GMT, DELTA_SEGUNDOS, DELTA_TEMP, ESTADO) VALUES ({$id}, '{$ubicacion}', {$gmt}, {$deltaTime}, {$deltaTemp}, {$estado});";
                                if($conexion->query($consultaAgregar)){
                                    header("Location: ./devices.php");
                                }else{
                                    header("Location: ./devices.php?mensaje=Fallo al agregar");
                                }
                            }else{
                                header("Location: ./devices.php?mensaje=El dispositivo ya existe");        
                            }
                        }else{
                            header("Location: ./devices.php?mensaje=Formulario incompleto");    
                        }
                    }else{
                        header("Location: ./devices.php?mensaje=Formulario incompleto");
                    }
                }else{
                    header("Location: ./devices.php");
                }
            }else if(isset($_POST['btnEdit'])){
                if($_POST['btnEdit']=='Editar'){
                    if(isset($_POST['idEdit']) && isset($_POST['gmtEdit']) && isset($_POST['deltaTimeEdit']) && isset($_POST['deltaTempEdit']) && isset($_POST['estadoEdit'])){
                        $consultaVerificacion = "SELECT UBICACION FROM DISPOSITIVOS WHERE ID_DEV={$_POST['idEdit']};";
                        if($conexion->query($consultaVerificacion)->num_rows == 1){
                            $id = $_POST['idEdit'];
                            $gmt = $_POST['gmtEdit'];
                            $deltaTime = $_POST['deltaTimeEdit'];
                            $deltaTemp = $_POST['deltaTempEdit'];
                            $estado = $_POST['estadoEdit'];
                            if($gmt!="" && $deltaTime!="" && $deltaTemp!="" && $estado!=""){
                                $consultaEditar = "UPDATE DISPOSITIVOS SET AJUSTE_GMT={$gmt}, DELTA_SEGUNDOS={$deltaTime}, DELTA_TEMP={$deltaTemp}, ESTADO={$estado} WHERE ID_DEV={$id};";
                                if($conexion->query($consultaEditar)){
                                    header("Location: ./devices.php");
                                }else{
                                    header("Location: ./devices.php?mensaje=Edición fallida");
                                }
                            }else{
                                header("Location: ./devices.php?mensaje=Formulario incompleto");
                            }
                        }else{
                            header("Location: ./devices.php?mensaje=El dispositivo no existe");
                        }
                    }else{
                        header("Location: ./devices.php?mensaje=Formulario incompleto");
                    }
                }else{
                    header("Location: ./devices.php");
                }
            }else{
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>DISPOSITIVOS</title>
</head>

<body>
<?php
                if(isset($_GET['mensaje'])){
                    echo $_GET['mensaje'];
                }
                if(isset($_GET['agregar'])){
?>
    <form action="./devices.php" method="POST">
        ID_DEV <input type="number" name="idAgregar"><br>
        UBICACION <input type="text" name="ubicacionAgregar"><br>
        GMT(seg) <input type="number" name="gmtAgregar"><br>
        INTERVALO TIEMPO(seg) <input type="number" min="1" name="deltaTimeAgregar"><br>
        INTERVALO TEMPERATURA <input type="number" min="1" step="any" name="deltaTempAgregar"><br>
        ESTADO <select name="estadoAgregar">
                    <option value="false">Inactivo</option>
                    <option value="true">Activo</option>
            </select><br>
        <input type="submit" name="btnAgregar" value="Agregar">
        <input type="submit" name="btnAgregar" value="Cancelar">
    </form>
<?php
                }
                if(isset($_GET['editar']) && is_numeric($_GET['editar'])){
                    $consultaVerificacion = "SELECT AJUSTE_GMT, DELTA_SEGUNDOS, DELTA_TEMP, ESTADO FROM DISPOSITIVOS WHERE ID_DEV={$_GET['editar']};";
                    $datosVefificacion = $conexion->query($consultaVerificacion);
                    if($datosVefificacion->num_rows == 1){
                        $datoV = $datosVefificacion->fetch_object();
?>
    <form action="./devices.php" method="POST">
        <?php echo 'Editar ID_DEV ' . $_GET['editar']; ?> <input type="hidden" name="idEdit" value="<?php echo $_GET['editar']; ?>"><br>
        GMT(seg) <input type="number" name="gmtEdit" value="<?php echo $datoV->AJUSTE_GMT; ?>"><br>
        INTERVALO TIEMPO(seg) <input type="number" min="1" name="deltaTimeEdit" value="<?php echo $datoV->DELTA_SEGUNDOS; ?>"><br>
        INTERVALO TEMPERATURA <input type="number" min="1" step="any" name="deltaTempEdit" value="<?php echo $datoV->DELTA_TEMP; ?>"><br>
        ESTADO <select name="estadoEdit">
                    <option value="0" <?php echo $datoV->ESTADO?'':'selected'; ?> >Inactivo</option>
                    <option value="1" <?php echo $datoV->ESTADO?'selected':''; ?> >Activo</option>
            </select><br>
        <input type="submit" name="btnEdit" value="Editar">
        <input type="submit" name="btnEdit" value="Cancelar">
    </form>
<?php
                    }
                }
                if(isset($_GET['ver']) && is_numeric($_GET['ver'])){
                    $consulta = "SELECT ID_DEV, UBICACION, AJUSTE_GMT, DELTA_SEGUNDOS, DELTA_TEMP, ESTADO FROM DISPOSITIVOS WHERE ID_DEV={$_GET['ver']};";
                }else{
                    $consulta = "SELECT ID_DEV, UBICACION, AJUSTE_GMT, DELTA_SEGUNDOS, DELTA_TEMP, ESTADO FROM DISPOSITIVOS;";
                }
                $datos = $conexion->query($consulta);
?>
                
    <h1>DISPOSITIVOS</h1>
    <a href="./login.php?cerrar=1">Cerrar sesión</a>
    <a href="./index.php">Index</a>
    <a href="./devices.php?agregar=0">Agregar nuevo</a>
    <?php echo isset($_GET['ver'])?"<a href='./devices.php'>Ver todos</a>":""; ?>
    <table border="1px">
        <tr>
            <th>ID</th>
            <th>UBICACION</th>
            <th>GMT</th>
            <th>INTERVALO TIEMPO</th>
            <th>INTERVALO TEMPERATURA</th>
            <th>ESTADO</th>
            <th></th>
            <th></th>
        </tr>
        <?php while($dato = $datos->fetch_object()){ ?>
            <tr>
                <td><a href="./data.php?ver=<?php echo $dato->ID_DEV; ?>"><?php echo $dato->ID_DEV; ?></a></td>
                <td><?php echo $dato->UBICACION; ?></td>
                <td><?php echo $dato->AJUSTE_GMT; ?></td>
                <td><?php echo $dato->DELTA_SEGUNDOS; ?></td>
                <td><?php echo $dato->DELTA_TEMP; ?></td>
                <td><?php echo $dato->ESTADO?"Activo":"Inactivado"; ?></td>
                <td><a href="./devices.php?eliminar=<?php echo $dato->ID_DEV; ?>">Eliminar</a></td>
                <td><a href="./devices.php?editar=<?php echo $dato->ID_DEV; ?>">Editar</a></td>
            </tr>
        <?php } ?>        
    </table>
</body>
</html>
<?php
            }
        }else{
            header('Location: ./login.php');
        }
    }
?>