<?php
// Base Controller
class Controller {
    // Load model
    public function model($model) {
        require_once APPROOT . '/app/models/' . $model . '.php';
        return new $model();
    }

    // Load view
    public function view($view, $data = []) {
        if (file_exists(APPROOT . '/views/' . $view . '.php')) {
            require_once APPROOT . '/views/' . $view . '.php';
        } else {
            die("View does not exist: " . APPROOT . '/views/' . $view . '.php');
        }
    }
}
