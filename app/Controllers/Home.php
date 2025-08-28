<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Home extends BaseController
{
    public function index(): string
    {
        $dbStatus = false;
        $dbError  = null;

        try {
            // Get the default database connection
            $db = \Config\Database::connect();

            // Try a simple query
            $db->query("SELECT 1");

            $dbStatus = true;
        } catch (\Throwable $e) {
            $dbStatus = false;
            $dbError  = $e->getMessage();
        }

        return view('welcome_message', [
            'dbStatus' => $dbStatus,
            'dbError'  => $dbError
        ]);
    }
}
