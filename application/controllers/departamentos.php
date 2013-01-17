<?php

/**
 * 
 */
class Departamentos extends CI_Controller{
  
  function __construct() {
    parent::__construct();
  }
  
  
  public function index(){
    $this->listar();
  }
  
  
  public function listar($pagInicio=0){
    if (!is_numeric($pagInicio)){
      show_error('El número de página es inválido.');
      return;
    }
    
    //VERIFICAR QUE EL USUARIO TIENE PERMISOS PARA CONTINUAR!!!!
    
    //cargo modelos, librerias, etc.
    $this->load->library('pagination');
    $this->load->model('Departamento');
    $this->load->model('Gestor_departamentos','gd');
       
    //genero la lista de links de paginación
    $config['base_url'] = "departamentos/listar/";
    $config['total_rows'] = $this->gd->cantidad();
    $config['per_page'] = 5;
    $config['uri_segment'] = 3;
    $this->pagination->initialize($config);
    
    //obtengo lista de departamentos
    $departamentos = $this->gd->listar($pagInicio, $config['per_page']);
    $tabla = array();
    foreach ($departamentos as $i => $departamento) {
      $tabla[$i]['idDepartamento'] = $departamento->IdDepartamento;
      $tabla[$i]['nombre'] = $departamento->Nombre;
    }

    //envio datos a la vista
    $data['tabla'] = $tabla; //array de datos de los Departamentos
    $data['paginacion'] = $this->pagination->create_links(); //html de la barra de paginación
    $data['usuarioLogin'] = unserialize($this->session->userdata('usuarioLogin')); //objeto Persona (usuario logueado)
    $this->load->view('lista_departamentos', $data);
  }
  
  
  public function nuevo(){
    
    //VERIFICAR QUE EL USUARIO TIENE PERMISOS PARA CONTINUAR!!!!

    //si no recibimos ningún valor proveniente del formulario
    if(!$this->input->post('submit')){
      $data['usuario'] = unserialize($this->session->userdata('usuario')); //datos de session
      $data['departamento'] = array(
        'idDepartamento' => 0,
        'nombre' => '');
      $data['link'] = "departamentos/nuevo"; //hacia donde mandar los datos      
      $this->load->view('editar_departamento',$data); 
    }
    else{
      //verifico si los datos son correctos
      $this->form_validation->set_rules('nombre','Nombre','required');
      $this->form_validation->set_error_delimiters('<small class="error">', '</small>'); //doy formato al mensaje de error      
      if($this->form_validation->run()==FALSE){
        //en caso de que los datos sean incorrectos, cargo el formulario nuevamente
        $data['usuario'] = unserialize($this->session->userdata('usuario')); //datos de session
        $data['departamento'] = array(
          'idDepartamento' => 0,
          'nombre' => $this->input->post('nombre')); //datos del departamento
        $data['link'] = "departamentos/nuevo"; //hacia donde mandar los datos
        $this->load->view('editar_departamento',$data);
      }
      else{
        //agrego departamento y cargo vista para mostrar resultado
        $this->load->model('Gestor_departamentos','gd');
        $res = $this->gd->alta($this->input->post('nombre',TRUE));
        $data['usuario'] = unserialize($this->session->userdata('usuario')); //datos de session
        $data['mensaje'] = (is_numeric($res))?"La operación se realizó con éxito. El ID del nuevo departamento es $res.":$res;
        $data['link'] = "departamentos"; //hacia donde redirigirse
        $this->load->view('resultado_operacion', $data);
      }
    }
  }


  public function eliminar($IdDepartamento=0){ //PASAR DATOS POR POST!!!!
    if (!is_numeric($IdDepartamento)){
      show_error('El ID Departamento es inválido.');
      return;
    }

    //VERIFICAR QUE EL USUARIO TIENE PERMISOS PARA CONTINUAR!!!!

    //doy de baja y cargo vista para mostrar resultado
    $this->load->model('Gestor_departamentos','gd');
    $res = $this->gd->baja($IdDepartamento);
    $data['usuario'] = unserialize($this->session->userdata('usuario')); //datos de session
    $data['mensaje'] = (strcmp($res, 'ok')==0)?'La operación se realizó con éxito.':$res;
    $data['link'] = "departamentos"; //link para boton aceptar/continuar
    $this->load->view('resultado_operacion', $data);
  }
  
  
  public function modificar($IdDepartamento=0){ //PASAR DATOS POR POST!!!!
    if (!is_numeric($IdDepartamento)){
      show_error('El ID Departamento es inválido.');
      return;
    }
    
    //VERIFICAR QUE EL USUARIO TIENE PERMISOS PARA CONTINUAR!!!!
    
    //cargo modelos, librerias, etc.
    $this->load->model('Departamento');
    $this->load->model('Gestor_departamentos','gd');
    
    //si no recibimos ningún valor proveniente del formulario
    if(!$this->input->post('submit')){  
      //si el departamento no existe mostrar mensaje
      $depto = $this->gd->dame($IdDepartamento);
      if ($depto != FALSE){
          $data['usuario'] = unserialize($this->session->userdata('usuario')); //datos de session
          $data['departamento'] = array(
            'idDepartamento' => $depto->IdDepartamento,
            'nombre' => $depto->Nombre);
          $data['link'] = "departamentos/modificar"; //hacia donde mandar los datos      
          $this->load->view('editar_departamento',$data);  
      }
      else{
        show_error('El ID Departamento es inválido.');
      }
    }
    else{
      //verifico si los datos son correctos
      $this->form_validation->set_rules('nombre','Nombre','required');
      $this->form_validation->set_error_delimiters('<small class="error">', '</small>'); //doy formato al mensaje de error      
      if($this->form_validation->run()==FALSE){
        //en caso de que los datos sean incorrectos, cargo nuevamente el formulario
        $data['usuario'] = unserialize($this->session->userdata('usuario')); //datos de session
        $data['departamento'] = array(
          'idDepartamento' => $this->input->post('idDepartamento'),
          'nombre' => $this->input->post('nombre'));
        $data['link'] = "departamentos/modificar"; //hacia donde mandar los datos
        $this->load->view('editar_departamento',$data);
      }
      else{
        //modifico departamento y cargo vista para mostrar resultado
        $res = $this->gd->modificar($this->input->post('idDepartamento',TRUE), $this->input->post('nombre',TRUE));
        $data['usuario'] = unserialize($this->session->userdata('usuario')); //datos de session
        $data['mensaje'] = (strcmp($res, 'ok')==0)?'La operación se realizó con éxito.':$res;
        $data['link'] = "departamentos";
        $this->load->view('resultado_operacion', $data);
      }
    }
  }
  
}

?>