<?php
declare(strict_types=1);

namespace Simy\App\Controllers;

use Simy\Core\Psr\Http\Message\ServerRequestInterface;
use Simy\Core\Response;

class HomeController
{
    public function index()
    {
        return new Response('Welcome to Simy Framework');
    }
    
    public function about()
    {
        return new Response('About Simy Framework');
    }
    
    public function contact()
    {
        return Response::json(['message' => 'Contact us at info@example.com']);
    }
}