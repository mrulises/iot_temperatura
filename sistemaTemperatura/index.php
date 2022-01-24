<?php 
    session_start();

    $state = isset($_SESSION['idUser']);
?>
<html>
<head>
    <title>Index</title>
</head>
<body>
    <h1>Sistema Temperaturas</h1>
    <img src="./logos.png" style="max-width:100%;width:auto;height:auto;">
    <br>
    <a href="./data.php">Temperaturas</a>
    <a href="./devices.php">Dispositivos</a>
    <a href="./login.php<?php echo $state?'?cerrar=1':''; ?>"><?php echo $state?'Cerrar sesión':'Iniciar sesión'; ?></a>
</body>
</html>