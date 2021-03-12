<?php session_start(); ?>
<?php
/**
 * Created by PhpStorm.
 * User: Judit
 * Date: 6/05/14
 * Time: 9:47
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Presupuestos BB</title>
    <meta charset="utf-8">
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
<div class="main-wrapper">
    <div class="container login">

        <div class="page-header">
            <a class="logo" href="index.php">
                <h3>Presupuestos</h3>
                <img src="img/logo.png">
            </a>
        </div>

        <?php
        function access_log($msg){
            $logfile = 'access.log';
            file_put_contents($logfile,date("Y-m-d H:i:s")." | ".$msg."\n",FILE_APPEND);
        }

        if(isset($_POST['submit']))
        {
            $user = $_POST['user'];
            $pass = $_POST['passwd'];

            if($user == "" || $pass == "")
            {
                echo '<div class="alert alert-danger">Debe indicar Usuario y Contraseña</div>';
                echo '<a href="login.php">Volver</a>';
            }
            else
            {
                include 'lib/database.php';

                $pdo = Database::connect();
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // query
                $result = $pdo->prepare("SELECT * FROM users WHERE username= :hjhjhjh AND password= :asas");
                $result->bindParam(':hjhjhjh', $user);
                $result->bindParam(':asas', $pass);
                $result->execute();
                //$rows = $result->fetch(PDO::FETCH_NUM);
                $login = $result->fetch();
                if($login && count($login) > 0) {
                    $_SESSION['valid'] = $user;
                    $_SESSION['priv'] = $login['priv'];
                    $_SESSION['id_stack'] = $login['id_stack'];
                    access_log('login usuario: '.$user);
                    header("location: index.php");
                }
                else{
                    echo '<div class="alert alert-danger">Usuario o Contraseña incorrectos</div>';
                    echo '<a href="login.php">Volver</a>';
                }
            }
        }
        else
        {

        ?>

        <form class="form-horizontal" role="form" method="post" action="">
            <div class="form-group">
                <label for="user" class="col-md-2 col-md-offset-2 control-label">Usuario</label>
                <div class="col-md-4">
                    <input type="text" class="form-control" id="user" name="user" placeholder="Usuario">
                </div>
            </div>
            <div class="form-group">
                <label for="passwd" class="col-md-2 col-md-offset-2 control-label">Contraseña</label>
                <div class="col-md-4">
                    <input type="password" class="form-control" id="passwd" name="passwd" placeholder="Contraseña">
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-2 col-md-offset-6 text-right">
                    <input type="submit" name="submit" value="Acceder" class="btn btn-primary">
                </div>
            </div>
        </form>

        <?php
        }
        ?>

    </div>
</div>
</body>
