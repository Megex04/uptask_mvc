<?php
namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController {

    public static function index(Router $router) {
    
        session_start();
        isAuth();

        $id = $_SESSION['id'];
        $proyectos = Proyecto::belongsTo('propietarioId',$id);


        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);
            
            // validacion
            $alertas = $proyecto->validarProyecto();

            if(empty($alertas)) {
                // generar una url unica
                $hash = md5(uniqid());
                $proyecto->url = $hash;
                // almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];
                // guardar el proyecto
                $proyecto->guardar();
                // redireccionar a la pagina principal
                header('Location: /proyecto?id='. $proyecto->url);
            }
        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router) {
        session_start();
        isAuth();

        $token = $_GET['id'];
        if(!$token) header('Location: /dashboard');
        // revisar que la persona que esta creando el proyecto sea el propietario y no otro usuario
        $proyecto = Proyecto::where('url', $token);
        if($proyecto instanceof Proyecto) {
            if($proyecto->propietarioId !== $_SESSION['id']){
                header('Location: /dashboard');
            }

            $router->render('dashboard/proyecto', [
                'titulo' => $proyecto->proyecto
            ]);
        } else{
            header('Location: /dashboard');
        }
        
    }

    public static function perfil(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            
            if($usuario instanceof Usuario) {
                $alertas = $usuario->validar_perfil();

                if(empty($alertas)) {

                    $existeUsuario = Usuario::where('email', $usuario->email);
                    if($existeUsuario && $existeUsuario->id !== $usuario->id) {
                        Usuario::setAlerta('error', 'Email no valido, ya pertence a un/a usuario o cuenta');
                        $alertas = $usuario->getAlertas();
                    } else {
                        //guardar el usuario
                        $usuario->guardar();

                        Usuario::setAlerta('exito', 'Guardado Correctamente');
                        $alertas = $usuario->getAlertas();
                        //ASIGNAR EL NOMBRE NUEVAMENTE A LA SESION (SE VE UNA EL HEADER DEL SITIO)
                        $_SESSION['nombre'] = $usuario->nombre;
                    }
                }
            }
        }

        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_password(Router $router) {
        session_start();
        isAuth();

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);

            $usuario->sincronizar($_POST);
            if($usuario instanceof Usuario) {
                $alertas = $usuario->nuevo_password();

                if(empty($alertas)) {
                    
                    $resultado = $usuario->comprobar_password();
                    if($resultado) {
                        $usuario->password = $usuario->password_nuevo;

                        // eliminar propiedades no necesarias
                        unset($usuario->password_actual);
                        unset($usuario->password_nuevo);
                        // hashear el password
                        $usuario->hashPassword();
                        
                        $resultado = $usuario->guardar();
                        if($resultado) {
                            Usuario::setAlerta('exito', 'Password guardado correctamente');
                            $alertas = $usuario->getAlertas(); 
                        }
                    } else {
                        Usuario::setAlerta('error', 'El password actual es incorrecto');
                        $alertas = $usuario->getAlertas();
                    }
                }
            }
        }

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas    
        ]);
    }
}

?>