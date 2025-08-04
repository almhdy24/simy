<?php
namespace Simy\App\Controllers;

use Simy\Core\Request;
use Simy\Core\Response;

class HomeController
{
    public function index(Request $request)
    {
        return new Response('Hello World');
    }
}