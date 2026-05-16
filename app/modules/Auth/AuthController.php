<?php

class AuthController {

    public function login() {
        require_once __DIR__ . '/views/login.php';
    }

    public function register() {
        require_once __DIR__ . '/views/register.php';
    }

    public function processLogin() {
    }

    public function processRegister() {
    }
}
?>