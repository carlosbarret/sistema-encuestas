<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  
  <!-- Le styles -->
  <link href="<?php echo base_url('css/bootstrap.css')?>" rel="stylesheet">
  <style>body{padding-top:40px;}</style>
  <link href="<?php echo base_url('css/bootstrap-responsive.css')?>" rel="stylesheet" media="screen">
  <link href="<?php echo base_url('css/app.css')?>" rel="stylesheet">
  <link href="<?php echo base_url('css/imprimir-claves.css')?>" rel="stylesheet" media="print">
  
  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <script src="<?php echo base_url('js/jquery.js')?>"></script>
  <script src="<?php echo base_url('js/html5shiv.js')?>"></script>

  <title>Claves de acceso - <?php echo NOMBRE_SISTEMA?></title>
  <style>
    .clave{border-bottom: 1px dashed #000000; page-break-inside:avoid;}
  </style>
</head>
<body>
  <!-- Menu de opciones -->
  <div id="barra-herramientas">
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="<?php echo site_url()?>">Sistema Encuestas</a>
          <ul class="nav">
            <li><a href="#" onclick="window.print()">Imprimir...</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <!-- Informe -->
  <div class="container">
    <?php foreach ($lista as $i => $clave):?>
      <div class=" clave">
        <div class="row">
          <div class="span12">
            <h3 class="text-center" style="margin:0;">Encuesta para mejorar la calidad de la enseñanza</h3>
            <h5 class="text-center" style="margin:0;"><?php echo $clave['carrera']->nombre.' - '.$clave['departamento']->nombre?></h5>
          </div>
        </div>
        <div class="row">
          <div class="span12">
            <h4 style="float:left; margin:4px 0";><b><?php echo $clave['materia']->nombre?></b></h4>
            <h4 style="float:right; margin:4px 0";"><b><?php echo $clave['clave']->clave?></b></h4>
          </div>
        </div>      
        <div class="row">
          <div class="span12">
            <p style="text-align:justify;">Esta clave es necesaria para completar la encuesta desarrollada por la <?php echo NOMBRE_FACULTAD?> - <?php echo NOMBRE_UNIVERSIDAD?>, destinada a mejorar la calidad de la enseñanza. 
            Para utilizarla deberá acceder a la dirección web <?php echo base_url()?>. 
            Esta clave sirve para responder sobre una asignatura en particular y dejará de tener validez al completar el formulario.
            Usted debería solicitar una clave distinta por cada asignatura que esté cursando en forma regular.</p>
          </div>
        </div>
      </div>
    <?php endforeach?>
  </div>
  
  <?php //include 'templates/footer2.php'?>

  <script src="<?php echo base_url('js/bootstrap-modal.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-collapse.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-dropdown.js')?>"></script>
</body>
</html>