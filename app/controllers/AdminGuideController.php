<?php
class AdminGuideController extends Controller {
    public function __construct() {
        AuthAdminMiddleware::check();
    }

    public function index() {
        $this->view('admin/guide/index', [
            'title' => 'Petunjuk Penggunaan Aplikasi'
        ]);
    }
}
