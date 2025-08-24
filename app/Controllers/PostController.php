<?php
declare(strict_types=1);

namespace Simy\App\Controllers;

use Simy\Core\Psr\Http\Message\ServerRequestInterface;
use Simy\Core\Response;

class PostController
{
    public function index()
    {
        return Response::json([
            'posts' => [
                ['id' => 1, 'title' => 'First Post', 'content' => 'Content here'],
                ['id' => 2, 'title' => 'Second Post', 'content' => 'More content']
            ]
        ]);
    }
    
    public function show(ServerRequestInterface $request)
    {
        $postId = $request->getAttribute('id');
        return Response::json([
            'post' => [
                'id' => $postId,
                'title' => 'Sample Post Title',
                'content' => 'This is the post content for ID ' . $postId,
                'author' => 'John Doe'
            ]
        ]);
    }
    
    public function store(ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
        return Response::json([
            'message' => 'Post created successfully',
            'data' => $data
        ], 201);
    }
    
    public function update(ServerRequestInterface $request)
    {
        $postId = $request->getAttribute('id');
        $data = $request->getParsedBody();
        return Response::json([
            'message' => 'Post updated',
            'id' => $postId,
            'data' => $data
        ]);
    }
    
    public function destroy(ServerRequestInterface $request)
    {
        $postId = $request->getAttribute('id');
        return Response::json([
            'message' => 'Post deleted',
            'id' => $postId
        ]);
    }
}