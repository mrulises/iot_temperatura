<?PHP 
    $host = "127.0.0.1";
    $user = "root";
    $pass = "";
    $bd = "sistema_temperatura";
    

    $conexion = new mysqli($host, $user, $pass, $bd);

    $db_estado = true;
    if($conexion->connect_error){
        echo "DB_ERROR";
        $db_estado = false;
    }

?>