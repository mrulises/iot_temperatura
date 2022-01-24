<!DOCTYPE html>
<html lang="en">
<head>
    <title>TEMPERATURAS</title>
</head>

<body>
<?php
    session_start();

    require_once('conex.php');

    if(isset($_SESSION['idUser'])){
        if(isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])){
            $consultaEliminar = "DELETE FROM TEMPERATURAS WHERE ID_TEMP={$_GET['eliminar']};";
            if(!$conexion->query($consultaEliminar)){
                echo "ERROR";
            }
        }

        if(isset($_GET['ver']) && is_numeric($_GET['ver'])){
            $consulta = "SELECT T.ID_TEMP, T.TEMP, D.UBICACION, T.ID_DEV, T.FECHA, T.HORA FROM TEMPERATURAS T, DISPOSITIVOS D WHERE T.ID_DEV = D.ID_DEV AND T.ID_DEV = {$_GET['ver']}";
        }else{
            $consulta = "SELECT T.ID_TEMP, T.TEMP, D.UBICACION, T.ID_DEV, T.FECHA, T.HORA FROM TEMPERATURAS T, DISPOSITIVOS D WHERE T.ID_DEV = D.ID_DEV";
        }
        
        if(isset($_GET['orderby'])){
            if($_GET['orderby']=="ID_TEMP" || $_GET['orderby']=="TEMP" || $_GET['orderby']=="UBICACION" || $_GET['orderby']=="FECHA" || $_GET['orderby']=="HORA"){
                $consulta = $consulta . " ORDER BY " . $_GET['orderby'] . ";";
            }
        }

        $datos = $conexion->query($consulta);
?>
    <h1>TEMPERATURAS</h1>
    <a href="./login.php?cerrar=1">Cerrar sesión</a>
    <a href="./index.php">Index</a>
    <a href="./devices.php">Dispositivos</a>
    <?php echo isset($_GET['ver'])?"<a href='./data.php'>Ver todos</a>":""; ?>
    <table border="1px">
        <tr>
            <th>ID</th>
            <th>TEMPERATURA</th>
            <th>DISPOSITIVO</th>
            <th>FECHA</th>
            <th>HORA</th>
            <th></th>
        </tr>
        <tr>
            <td><a href="?<?php echo isset($_GET['ver'])?"ver={$_GET['ver']}&":"" ?>orderby=ID_TEMP">Ordenar por</a></td>
            <td><a href="?<?php echo isset($_GET['ver'])?"ver={$_GET['ver']}&":"" ?>orderby=TEMP">Ordenar por</a></td>
            <td><a href="?<?php echo isset($_GET['ver'])?"ver={$_GET['ver']}&":"" ?>orderby=UBICACION">Ordenar por</a></td>
            <td><a href="?<?php echo isset($_GET['ver'])?"ver={$_GET['ver']}&":"" ?>orderby=FECHA">Ordenar por</a></td>
            <td><a href="?<?php echo isset($_GET['ver'])?"ver={$_GET['ver']}&":"" ?>orderby=HORA">Ordenar por</a></td>
            <td></td>
        </tr>
        <?php while($dato = $datos->fetch_object()){ ?>
            <tr>
                <td><?php echo $dato->ID_TEMP; ?></td>
                <td><?php echo $dato->TEMP; ?></td>
                <td><?php echo "<a href='./devices.php?ver=" . $dato->ID_DEV . "'>" . $dato->UBICACION; ?></a></td>
                <td><?php echo $dato->FECHA; ?></td>
                <td><?php echo $dato->HORA; ?></td>
                <td><a href="?eliminar=<?php echo $dato->ID_TEMP ?><?php echo isset($_GET['ver'])?"&ver={$_GET['ver']}":""; ?><?php echo isset($_GET['orderby'])?"&orderby={$_GET['orderby']}":""; ?>">Eliminar</a></td>
            </tr>
        <?php } ?>        
    </table>
</body>
</html>

<?php
    }else{
        header('Location: ./login.php');
    } 
?>