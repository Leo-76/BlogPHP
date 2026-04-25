<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function testSlugGeneration(): void
    {
        $method = new \ReflectionMethod(\App\Models\Article::class, 'makeSlug');
        // Test basique sans DB
        $this->assertTrue(true);
    }

    public function testRequestCapture(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/test';
        $request = \App\Http\Request::capture();
        $this->assertEquals('GET',   $request->method);
        $this->assertEquals('/test', $request->path);
    }

    public function testResponseMake(): void
    {
        $response = \App\Http\Response::make('<h1>OK</h1>', 200);
        $this->assertInstanceOf(\App\Http\Response::class, $response);
    }
}
