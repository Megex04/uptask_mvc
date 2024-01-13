<?php
namespace Controllers;

use Model\Proyecto;
use Model\Tarea;

class TareaController {

    public static function index() {
    
    }
    public static function crear() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            //echo json_encode($_POST); //TODO: DEBUG VER COMO ESTA LLEGANDO EL REQUEST
            session_start();

            $proyectoId = $_POST['proyectoId'];
            $proyecto = Proyecto::where('url', $proyectoId);
            if($proyecto instanceof Proyecto) {
                if(!$proyecto || $proyecto->propietarioId !== $_SESSION['id']) {    
                    $respuesta = [
                        'tipo' => 'error',
                        'mensaje' => 'Hubo un error al agregar la tarea'
                    ];
                    echo json_encode($respuesta);
                    return;
                } 
                // todo bien, instanciar y crear la tarea
                $tarea = new Tarea($_POST);
                $tarea->proyectoId = $proyecto->id;
                $resultado = $tarea->guardar();
                
                $respuesta = [
                    'tipo' => 'exito',
                    'id' => $resultado['id'],
                    'mensaje' => 'Tarea creada correctamente'
                ];
                echo json_encode($respuesta);
            }else {
                $respuesta = [
                    'tipo' => 'error',
                    'mensaje' => 'Hubo un error interno en el servidor'
                ];
                echo json_encode($respuesta);
                return;
            }
            
        }
    }

    public static function actualizar() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        }
    }
    public static function eliminar() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        }
    }
}
