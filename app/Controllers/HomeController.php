<?php
declare(strict_types=1);

namespace Simy\App\Controllers;

use Simy\Core\Psr\Http\Message\ServerRequestInterface;
use Simy\Core\Response;

class HomeController
{
    public function index(ServerRequestInterface $request)
    {
        return new Response('Home Controller - Index Action');
    }
    
    public function about()
    {
        return new Response('Home Controller - About Action');
    }
    
    public function contact()
    {
        return Response::json(['message' => 'Contact us at info@example.com']);
    }
}