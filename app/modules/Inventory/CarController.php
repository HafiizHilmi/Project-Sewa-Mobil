<?php
// app/modules/Inventory/CarController.php

class CarController {
    
    // Fungsi default yang dipanggil saat user membuka halaman utama
    public function index() {
        // (Nanti anak Database & Backend akan mengambil data asli dari DB di sini)
        // $cars = $this->model->getAllCars();

        // Memanggil file HTML/Bootstrap dari folder views
        // __DIR__ memastikan path mengarah ke folder modul saat ini
        require_once __DIR__ . '/views/user_catalog.php';
    }
}