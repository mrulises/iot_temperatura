<?php
    session_start();

    require_once('conex.php');
    if($db_estado){
        if(isset($_GET['cerrar'])){
            session_unset();
            if(isset($_GET['origen'])){
                echo "CLOSE";
            }else{
                header('Location: ./index.php');
            }
        }else{
            if(isset($_POST['btnLogin'])){
                $user = $_POST['usuarioLogin'];
                $pass = $_POST['passLogin'];
                $consulta = "SELECT ID_USER FROM USUARIOS WHERE USUARIO='{$user}' AND PASS=md5('{$pass}');";
                $acceso = $conexion->query($consulta);
                if($acceso->num_rows == 1){
                    $usuario = $acceso->fetch_object();
                    $_SESSION['idUser'] = $usuario->ID_USER;
                    if(isset($_POST['origen'])){
                        echo "OK";
                    }else{
                        header('Location: ./index.php');
                    }
                }else{
                    if(isset($_POST['origen'])){
                        echo "FAIL";
                    }else{
                        header('Location: ./login.php?mensajeLogin=Usuario o contraseña incorrecto');
                    }
                }
            }else if(isset($_POST['btnSignup'])){
                $user = $_POST['usuarioSignup'];
                $pass1 = $_POST['passSignup1'];
                $pass2 = $_POST['passSignup2'];
                if($user != "" && $pass1 != "" && $pass2 != ""){
                    if($pass1 == $pass2){
                        $consulta = "INSERT INTO USUARIOS (USUARIO, PASS) VALUES ('{$user}', md5('{$pass1}'));";
                        if($conexion->query($consulta)){
                            header('Location: ./login.php?mensajeSingup=Registro exitoso');
                        }else{
                            header('Location: ./login.php?mensajeSingup=El usuario ya existe');
                        }
                    }else{
                        header('Location: ./login.php?mensajeSingup=La contraseña no coincide');
                    }
                }else{
                    header('Location: ./login.php?mensajeSingup=Formulario incompleto');
                }
            }else if(!isset($_POST['origen']) && !isset($_GET['origen'])){
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <?php echo isset($_GET['mensajeLogin'])?$_GET['mensajeLogin']:""; ?>
    <form action="./login.php" method="POST">
        Usuario: <input type="text" name="usuarioLogin"><br>
        Contraseña: <input type="password" name="passLogin"><br>
        <input type="submit" name="btnLogin" value="Login">
    </form>

    <h1>Signup</h1>
    <?php echo isset($_GET['mensajeSingup'])?$_GET['mensajeSingup']:""; ?>
    <form action="./login.php" method="POST">
        Usuario: <input type="text" name="usuarioSignup"><br>
        Contraseña: <input type="password" name="passSignup1"><br>
        Cofirmar contraseña: <input type="password" name="passSignup2"><br>
        <input type="submit" name="btnSignup" value="Signup">
    </form>
</body>
</html>
<?php 
            }
        }
    }
?>