<?php
namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {

    public static function login(Router $router) {
        
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarLogin();
            if(empty($alertas)) {
                // verificar que el user exista
                $auth = Usuario::where('email', $auth->email);
                
                if(!$auth) {
                    Usuario::setAlerta('error', 'El Usuario no existe');
                } else {
                    if($auth instanceof Usuario) {
                        if(!$auth->confirmado) {
                            Usuario::setAlerta('error', 'El Usuario no esta confirmado');
                        } else {
                            if(password_verify($_POST['password'], $auth->password)) {
                                // INICIA LA SESION
                                session_start();
                                $_SESSION['id'] = $auth->id;
                                $_SESSION['nombre'] = $auth->nombre;
                                $_SESSION['email'] = $auth->email;
                                $_SESSION['login'] = true;
                                
                                header('Location: /dashboard');

                            } else {
                                Usuario::setAlerta('error', 'Password incorrecto');
                            }
                        }
                        
                    }
                }
            
            }
        }
        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);
    }

    public static function logout() {
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function crear(Router $router) {
        $alertas = [];
        $usuario = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);

            $alertas = $usuario->validarNuevaCuenta();

            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya esta registrado !');
                    $alertas = Usuario::getAlertas();
                } else {
                    // hashea el nuevo password
                    $usuario->hashPassword();

                    // elimina el password2
                    unset($usuario->password2);

                    // crea el token
                    $usuario->crearToken();

                    // despues de la construccion con seguridad del usuario se puede crearlo
                    $resultado = $usuario->guardar();

                    // Enviar e-mail al usuario
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);

                    $email->enviarConfirmacion();

                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }

        // Render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crea tu cuenta en UpTask',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router) {
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                // buscar el usuario
                $usuario = Usuario::where('email', $usuario->email);
                
                if($usuario && $usuario instanceof Usuario) {
                    // encontro al usuario
                    if($usuario->confirmado) {
                        // valida si esta confirmado
                        // generar token
                        $usuario->crearToken();
                        unset($usuario->password2);
                        // actualizar usuario
                        $usuario->guardar();
                        // ENVIAR EMAIL
                        $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                        $email->enviarInstrucciones();
                        // imprimir la alerta
                        Usuario::setAlerta('exito', 'Hemos enviado instrucciones a tu correo electronico');
                    }

                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                }
            }
        }
        $alertas = Usuario::getAlertas();

        // Muestra la vista
        $router->render('auth/olvide', [
            'titulo' => 'Olvide mi Password',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {

        $token = s($_GET['token']);
        $mostrar = true;

        if(!$token) header('Location: /');

        // identificar al usuario por su token
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token No valido');
            $mostrar = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if($usuario instanceof Usuario) {
                // añadir el nuevo password
                $usuario->sincronizar($_POST);
                // VALIDA EL PASSWORD
                $usuario->validarPassword();

                if(empty($alertas)) {
                    // HASHEAR EL NUEVO PASSWORD
                    $usuario->hashPassword();
                    // eliminar el TOKEN
                    $usuario->token = null;
                    // guardar el usuario en la BD
                    $resultado = $usuario->guardar();
                    // redirect
                    if($resultado) {
                        header('Location: /');
                    }
                }
            }
             

        }
        $alertas = Usuario::getAlertas();
        // Muestra la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Password',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router) {
        
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada exitosamente'
        ]);
    }

    public static function confirmar(Router $router) {

        $token = s($_GET['token']);

        if(!$token) header('Location: /');

        // encontrar al usuario con ese token
        $usuario= Usuario::where('token', $token);

        if(empty($usuario)) {
            // no se encontro a usuario con ese token
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            // confirmar la cuenta:
            if($usuario instanceof Usuario){
                $usuario->confirmado = 1;
                $usuario->token = NULL;
                unset($usuario->password2);
            }
            $usuario->guardar();
            Usuario::setAlerta('exito', 'Cuenta comprobada correctamente');
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta UpTask',
            'alertas' => $alertas
        ]);
    
    }
}


