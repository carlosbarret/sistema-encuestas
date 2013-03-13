<!DOCTYPE html>
<!-- Última revisión: 2012-02-03 4:33 p.m. -->

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="">
  <meta name="author" content="">
  
  <!-- Le styles -->
  <link href="<?php echo base_url('css/bootstrap.css')?>" rel="stylesheet">
  <link href="<?php echo base_url('css/bootstrap-responsive.css')?>" rel="stylesheet" media="screen">
  <link href="<?php echo base_url('css/app.css')?>" rel="stylesheet">
  <link href="<?php echo base_url('css/imprimir.css')?>" rel="stylesheet" media="print">
  
  <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
  <script src="<?php echo base_url('js/jquery.js')?>"></script>
  <script src="<?php echo base_url('js/html5shiv.js')?>"></script>

  <title>Formulario de Encuesta - <?php echo NOMBRE_SISTEMA?></title>
  <style>
    h5.separador{border-bottom: 3px solid #2BA6CB;}
    .item{min-height: 50px}
  </style>
</head>
<body>
  <div class="container">
    <div id="cabecera" class="row">
      <div class="span12">
        <h2 class="text-center"><?php echo $formulario->titulo?></h2>
        <h4 class="text-center">Asignatura: <?php echo $materia->nombre?></h4>
        <h5 class="text-center"><?php echo $carrera->nombre?></h5>
      </div>
    </div>
    <div id="cuerpo">
      <form id="formulario" action="<?php echo site_url('claves/responder')?>" method="post">
        <input type="hidden" name="clave" value="<?php echo $clave->clave?>" />
        <?php foreach ($secciones as $itemSeccion): ?>
          <div class="row">
            <div class="span12">
              <h5 class="separador"><?php echo $itemSeccion['seccion']->texto?></h5>
              <h6><?php echo $itemSeccion['seccion']->descripcion?></h6>
              <?php 
              
              //SUBSECCIONES
              
              foreach ($itemSeccion['subsecciones'] as $subseccion){
                $docente = $subseccion['docente'];
                printf('
                <h3>%s %s</h3>', $docente->nombre, $docente->apellido);
                
                //ITEMS
                
                echo '
                <div class="row preguntas">';
                foreach ($subseccion['items'] as $k => $i){
                  $item = &$i['item'];
                  $opciones = &$i['opciones'];
                  if ($k%2==0){
                    echo'
                    </div>
                    <div class="row">';
                  }
                  //genero el html de la ayuda contextual
                  $tip = ($item->descripcion!='')?'<span class="badge badge-info" data-toggle="tooltip" title="'.$item->descripcion.'">!</span>':'';
                  //para las preguntas con opciones
                  if($item->tipo == 'S'){
                    $html_opciones = '';
                    foreach($opciones as $opcion){
                      $html_opciones .= '<option value="'.$opcion->idOpcion.'">'.$opcion->texto.'</option>';
                    }
                    printf('
                    <div class="item span6">
                      <div class="row-fluid">
                        <div class="span8">
                          <p>%s %s</p>
                        </div>
                        <div class="span4">
                          <select name="idPregunta_%d_%s_%d">
                            <option value="">(No Contesta)</option>%s
                          </select>
                        </div>
                      </div>
                    </div>', $item->texto, $tip, $item->idPregunta, $item->tipo, $docente->id, $html_opciones);
                  }
                  //para las preguntas numericas
                  elseif($item->tipo == 'N'){
                    printf ('
                    <div class="item span6">
                      <div class="row-fluid">
                        <div class="span8">
                          <p>%s %s</p>
                        </div>
                        <div class="span4">
                          <input class="input-block-level" type="number" name="idPregunta_%d_%s_%d" min="%f" max="%f" step="%f"/>
                        </div>
                      </div>
                    </div>',  $item->texto, $tip, $item->idPregunta, $item->tipo, $docente->id, 
                              $item->limiteInferior, $item->limiteSuperior, $item->paso);
                  }
                  //texto de una linea
                  elseif($i['item']->tipo == 'T'){
                    printf ('
                    <div class="item span6">
                      <p>%s %s<input class="input-block-level" type="text" name="idPregunta_%d_%s_%d"/></p>
                    </div>', $item->texto, $tip, $item->idPregunta, $item->tipo, $docente->id);
                  }
                  //texto multilinea
                  elseif($item->tipo == 'X'){
                    printf ('
                    <div class="item span12">
                      <p>%s %s</p>
                      <textarea class="input-block-level" name="idPregunta_%d_%s_%d"></textarea>
                    </div>', $item->texto, $tip, $item->idPregunta, $item->tipo, $docente->id);
                  }
                }//foreach items
                echo '</div>';
              }//foreach subsecciones
              ?>
            </div>
          </div>
        <?php endforeach?>
        
        <div class="span12">
          <a class="btn btn-primary" href="#modalConfirmarEnvio" role="button" data-toggle="modal">Enviar</a>
        </div>
        
        <!-- ventana modal para confirmar envio -->
        <div id="modalConfirmarEnvio" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h3 id="myModalLabel">Confirmación</h3>
          </div>
          <div class="modal-body">
            <p>Esta a punto de enviar sus respuestas, ¿desea continuar?</p>      
          </div>
          <div class="modal-footer">
            <button class="btn" data-dismiss="modal" aria-hidden="true">Cerrar</button>
            <input class="btn btn-primary" type="submit" name="submit" value="Aceptar" />
          </div>
        </div>
          
      </form>
    </div>
  </div>
  <?php //include 'templates/footer2.php'?>

  <script src="<?php echo base_url('js/bootstrap-modal.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-collapse.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-dropdown.js')?>"></script>
  <script src="<?php echo base_url('js/bootstrap-tooltip.js')?>"></script>
  <script>
    $('span.badge').tooltip();
  </script>
</body>
</html>