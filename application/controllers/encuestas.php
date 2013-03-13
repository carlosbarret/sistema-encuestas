<?php

/**
 * 
 */
class Encuestas extends CI_Controller{
  
  var $data = array(); //datos para mandar a las vistas
  
  function __construct() {
    parent::__construct();
    $this->load->library(array('session', 'ion_auth', 'form_validation'));
    //doy formato al mensaje de error de validación de formulario
    $this->form_validation->set_error_delimiters(ERROR_DELIMITER_START, ERROR_DELIMITER_END);
    $this->data['usuarioLogin'] = $this->ion_auth->user()->row();
    $this->data['resultadoTipo'] = ALERT_ERROR;
    $this->data['resultadoOperacion'] = null;
  }
  
  
  //este metodo corresponde a la página por defecto del sistema
  public function index(){
    $this->load->view('index', $this->data);
  }
  
  
  /*
   * Muestra el listado de encuestas.
   */
  public function listar($pagInicio=0){
    if (!$this->ion_auth->logged_in()){
      redirect('usuarios/login');
    }
    //chequeo parámetros de entrada
    $pagInicio = (int)$pagInicio;
    
    //cargo modelos, librerias, etc.
    $this->load->library('pagination');
    $this->load->model('Encuesta');
    $this->load->model('Gestor_encuestas','ge');
    
    //obtengo lista de encuestas
    $lista = $this->ge->listar($pagInicio, PER_PAGE);
    
    //genero la lista de links de paginación
    $this->pagination->initialize(array(
      'base_url' => site_url("encuestas/listar"),
      'total_rows' => $this->ge->cantidad()
    ));
    
    //envio datos a la vista
    $this->data['lista'] = &$lista; //array de datos de las encuestas
    $this->data['paginacion'] = $this->pagination->create_links(); //html de la barra de paginación
    $this->data['resultadoOperacion'] = $this->session->flashdata('resultadoOperacion');
    $this->data['resultadoTipo'] = $this->session->flashdata('resultadoTipo');
    $this->load->view('lista_encuestas', $this->data);
  }
  
  
  /*
   * Recepción del formulario para agregar nueva encuesta
   * POST: idFormulario, anio, cuatrimestre, tipo
   */
  public function nueva(){
    //verifico si el usuario tiene permisos para continuar
    if (!$this->ion_auth->logged_in()){
      redirect('usuarios/login');
    }
    elseif (!$this->ion_auth->is_admin()){
      $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('encuestas/listar');
    }
    //verifico datos POST
    $this->form_validation->set_rules('idFormulario','Formulario','required|is_natural_no_zero');
    $this->form_validation->set_rules('anio','Año','required|is_natural_no_zero|less_than[2100]|greater_than[1900]');
    $this->form_validation->set_rules('cuatrimestre','Periodo/Cuatrimestre','required|is_natural_no_zero|less_than[12]');
    $this->form_validation->set_rules('tipo','Tipo Acceso','required|alpha|exact_length[1]');
    echo $this->input->post('tipo');
    if($this->form_validation->run()){
      $this->load->model('Gestor_encuestas','ge');
      //agrego encuesta y cargo vista para mostrar resultado
      $res = $this->ge->alta( (int)$this->input->post('idFormulario'),
                              $this->input->post('tipo'), 
                              (int)$this->input->post('anio'), 
                              (int)$this->input->post('cuatrimestre'));
      if (is_numeric($res)){
        $this->session->set_flashdata('resultadoOperacion', "La operación se realizó con éxito. El ID de la nueva encuesta es $res.");
        $this->session->set_flashdata('resultadoTipo', ALERT_SUCCESS);
        redirect('encuestas/listar');
      }
      $this->data['resultadoOperacion'] = $res;
      $this->data['resultadoTipo'] = ALERT_ERROR;
    }
    //en caso de que los datos sean incorrectos, vuelvo a la pagina de edicion
    $this->load->model('Encuesta');
    $this->Encuesta->año = date('Y');
    $this->load->model('Formulario');
    $this->data['encuesta'] = &$this->Encuesta; //datos por defecto de una nueva encuesta
    $this->data['formulario'] = &$this->Formulario;
    $this->data['tituloFormulario'] = 'Nueva Encuesta';
    $this->data['urlFormulario'] = site_url('encuestas/nueva');
    $this->load->view('editar_encuesta', $this->data);
  }


  /*
   * Recepción del formulario para finalizar una Encuesta
   * POST: idEncuesta, idFormulario
   */
  public function finalizar(){
    //verifico si el usuario tiene permisos para continuar
    if (!$this->ion_auth->logged_in()){
      redirect('usuarios/login');
    }
    elseif (!$this->ion_auth->is_admin()){
      $this->session->set_flashdata('resultadoOperacion', 'No tiene permisos para realizar esta operación.');
      $this->session->set_flashdata('resultadoTipo', ALERT_WARNING);
      redirect('encuestas/listar');
    }
    //verifico datos POST
    $this->form_validation->set_rules('idEncuesta','Encuesta','is_natural_no_zero|required');
    $this->form_validation->set_rules('idFormulario','Formulario','is_natural_no_zero|required');
    if($this->form_validation->run()){
      $this->load->model('Encuesta');
      $this->Encuesta->idEncuesta = (int)$this->input->post('idEncuesta'); 
      $this->Encuesta->idFormulario = (int)$this->input->post('idFormulario');
      
      //finalizo la encuesta y cargo vista para mostrar resultado
      $res = $this->Encuesta->finalizar();
      if (strcmp($res, 'ok')==0){
        $this->session->set_flashdata('resultadoOperacion', "La operación se realizó con éxito.");
        $this->session->set_flashdata('resultadoTipo', ALERT_SUCCESS);
        redirect('encuestas/listar');
      }
      $this->session->set_flashdata('resultadoOperacion', $res);
      $this->session->set_flashdata('resultadoTipo', ALERT_ERROR);
    }
    redirect('encuestas/listar');
  }


  //funcion para responder solicitudes AJAX
  public function buscarEncuestasAJAX(){
    if (!$this->ion_auth->logged_in()){return;}
    $this->form_validation->set_rules('buscar','Buscar','required|is_natural_no_zero');
    if($this->form_validation->run()){
      $this->load->model('Encuesta');
      $this->load->model('Gestor_encuestas','ge');
      $encuestas = $this->ge->buscar($this->input->post('buscar'));
      echo "\n";
      foreach ($encuestas as $encuesta) {
        echo  "$encuesta->idEncuesta\t".
              "$encuesta->idFormulario\t".
              "$encuesta->año\t".
              "$encuesta->cuatrimestre\t".
              "$encuesta->fechaInicio\t\n";
      }
    }
  }

/*
  //funcion para responder solicitudes AJAX
  public function listarClavesAJAX(){
    if (!$this->ion_auth->logged_in()){return;}
    $this->form_validation->set_rules('idMateria','Materia','required|is_natural_no_zero');
    $this->form_validation->set_rules('idCarrera','Carrera','required|is_natural_no_zero');
    $this->form_validation->set_rules('idEncuesta','Encuesta','required|is_natural_no_zero');
    $this->form_validation->set_rules('idFormulario','Formulario','required|is_natural_no_zero');
    if($this->form_validation->run()){
      $this->load->model('Clave');
      $this->load->model('Encuesta');
      $this->Encuesta->idEncuesta = $this->input->post('idEncuesta');
      $this->Encuesta->idFormulario = $this->input->post('idFormulario');
      $claves = $this->Encuesta->listarClavesMateria($this->input->post('idMateria'), $this->input->post('idCarrera'), 0,1000);
      echo '\n';
      foreach ($claves as $clave) {
        echo  "$clave->idClave\t".
              "$clave->clave\t".
              "$clave->tipo\t".
              "$clave->generada\t".
              "$clave->utilizada\t\n";
      }
    }
  }

  
  
  public function ver($idEncuesta=null, $idFormulario=null, $pagInicio=0){
    //verifico si el usuario tiene permisos para continuar
    if (!$this->ion_auth->in_group('admin')){
      show_error('No tiene permisos para ingresar a esta sección.');
      return;
    }
    //chequeo parámetros de entrada
    $pagInicio = (int)$pagInicio;
    $idEncuesta = (int)$idEncuesta;
    $idFormulario = (int)$idFormulario;
    
    //cargo modelos, librerias, etc.
    $this->load->library('pagination');
    $this->load->model('Clave');
    $this->load->model('Encuesta');
    $this->load->model('Gestor_encuestas','ge');
    
    //obtengo datos de la encuesta
    $encuesta = $this->ge->dame($idEncuesta, $idFormulario);
    if ($encuesta){
      $cantidadClaves = 0;//$encuesta->cantidadClaves();
      $claves = array();//$encuesta->listarClaves($pagInicio, 5);
      $data['encuesta'] = array(
        'IdEncuesta' => $encuesta->IdEncuesta,
        'idFormulario' => $encuesta->idFormulario,
        'Año' => $encuesta->Año,
        'Cuatrimestre' => $encuesta->Cuatrimestre,
        'FechaInicio' => $encuesta->FechaInicio,
        'FechaFin' => $encuesta->FechaFin
      );
      //genero la lista de links de paginación
      $config['base_url'] = site_url("encuestas/ver/$idEncuesta/$idFormulario");
      $config['total_rows'] = $cantidadClaves;
      $config['per_page'] = 5;
      $config['uri_segment'] = 5;
      $this->pagination->initialize($config);
      //obtengo lista de claves
      $tabla = array();
      foreach ($claves as $i => $clave) {
        $tabla[$i]=array(
          'IdClave' => $clave->IdClave,
          'Clave' => $clave->Clave,
          'Tipo' => $clave->Tipo,
          'Generada' => $clave->Generada,
          'Utilizada' => $clave->Utilizada
         );
      }
      //envio datos a la vista
      $data['tabla'] = $tabla; //array de datos de los Departamentos
      $data['encuesta'] = array(
        'IdEncuesta' => $encuesta->IdEncuesta, 
        'idFormulario' => $encuesta->idFormulario,
        'Año' => $encuesta->Año,  
        'Cuatrimestre' => $encuesta->Cuatrimestre,
        'FechaInicio' => $encuesta->FechaInicio,
        'FechaFin' => $encuesta->FechaFin
      );
      $data['paginacion'] = $this->pagination->create_links(); //html de la barra de paginación
      $data['usuarioLogin'] = $this->ion_auth->user()->row(); //datos de sesion
      $this->load->view('ver_encuesta', $data);
    }
    else{
      show_error('El Identificador de Encuesta no es válido.');
    }
  }
 */
}

?>