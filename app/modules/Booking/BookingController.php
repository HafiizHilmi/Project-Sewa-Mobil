<?php

class BookingController {
    public function checkout() {
        require_once __DIR__ . '/views/booking.php';
    }   
    public function process() {
        require_once __DIR__ . '/views/payment_success.php';
    }
}

