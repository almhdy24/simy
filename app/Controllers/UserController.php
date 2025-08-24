<?php
declare(strict_types=1);

namespace Simy\App\Controllers;

use Simy\Core\Psr\Http\Message\ServerRequestInterface;
use Simy\Core\Response;

class UserController
{
    public function profile(ServerRequestInterface $request)
    {
        $userId = $request->getAttribute('id');
        return Response::json([
            'user' => [
                'id' => $userId,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'profile' => 'This is user profile data'
            ]
        ]);
    }
    
    public function list()
    {
        return Response::json([
            'users' => [
                ['id' => 1, 'name' => 'Alice'],
                ['id' => 2, 'name' => 'Bob'],
                ['id' => 3, 'name' => 'Charlie']
            ]
        ]);
    }
}