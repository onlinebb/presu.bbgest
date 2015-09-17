<?php session_start(); ?>
<?php
/**
 * Created by PhpStorm.
 * User: judit
 * Date: 1/04/14
 * Time: 9:44
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
    <script type="text/javascript" src="js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="js/bootstrap-datepicker.es.js"></script>
    <script type="text/javascript" src="js/bootstrap-typeahead.js"></script>
    <script type="text/javascript" src="js/jquery.form.min.js"></script>
    <script type="text/javascript" src="js/main.js"></script>

    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/datepicker3.css">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" type="text/css" href="css/icons.css">
</head>
<body>
<div class="main-wrapper">

    <nav role="navigation" class="navbar navbar-default">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <button data-target="#bbgestnav" data-toggle="collapse" class="navbar-toggle" type="button">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="navbar-header">
                <a href="index.php" class="navbar-brand"><img src="img/logo-small.png" id="logo"></a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class=""><a href="index.php">Home</a></li>
                    <li class=""><a href="control.php">Control</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <form class="navbar-form navbar-right" role="logout" action="logout.php">
                        <button type="submit" class="btn btn-default"><b>Salir</b> <span class="glyphicon glyphicon-log-out"></span></button>
                    </form>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>

    <div class="container">
        <?php
            if(!isset($_SESSION['valid']))
            {
                header('Location: login.php');
            }
        ?>