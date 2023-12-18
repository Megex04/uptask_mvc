<?php
namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public $nombre;
    public $email;
    public $password;
    public $password2;
    public $token;
    public $confirmado;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }
    // validar Login
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El e-mail del usuario es Obligatorio';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = "El email no válido";
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password del usuario es obligatorio';
        }
        return self::$alertas;
    }

    // validacion para cuentas nuevas
    public function validarNuevaCuenta() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre de Usuario es Obligatorio';
        }

        if(!$this->email) {
            self::$alertas['error'][] = 'El e-mail del usuario es Obligatorio';
        }
        
        if(!$this->password) {
            self::$alertas['error'][] = 'El password del usuario es obligatorio';
        }
        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
        }
        if($this->password !== $this->password2) {
            self::$alertas['error'][] = 'Los passwords son diferentes';
        }

        return self::$alertas;
    }

    // valida un email
    public function  validarEmail() {
        if(!$this->email){
            self::$alertas['error'][] = "El email es obligatorio";
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = "El email no válido";
        }
        return self::$alertas;
    }
    // valida el password
    public function validarPassword() {
        if(!$this->password) {
            self::$alertas['error'][] = 'El password del usuario es obligatorio';
        }
        if(strlen($this->password) < 6) {
            self::$alertas['error'][] = 'El password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }

    // hashea el password
    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // crear el token
    public function crearToken() {
        $this->token = md5( uniqid() );
    }

}

