<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Osynapsy\Kernel\Router;

final class RouterTest extends TestCase
{
    private function getRouter() : Router
    {
        $Router = new Router();
        $Router->addRoute('routeTestHome', '/', 'Test:Home', 'modal', 'Test:App', []);
        $Router->addRoute('routeTestInteger', '/test/{i}', 'Test:Integer', 'modal', 'Test:App', []);
        $Router->addRoute('routeTestWord', '/test2/{w}', 'Test:Word', 'modal', 'Test:App', []);
        $Router->addRoute('routeTestIntegerOptional', '/testi/{i*}', 'Test:Integer', 'modal', 'Test:App', []);
        $Router->addRoute('routeTestWordOptional', '/testw/{w*}', 'Test:Word', 'modal', 'Test:App', []);
        return $Router;
    }
	
    public function testRouteWithHome(): void
    {       	
        $router = $this->getRouter();		
        $this->assertEquals(
            $this->getRouter()->dispatchRoute('/')->__toString(),
            $router->get('routes.routeTestHome')->__toString()
        );
    }

    public function testRouteWithIntegerParameter(): void
    {       	
        $router = $this->getRouter();
        $this->assertEquals(
                $router->get('routes.routeTestInteger')->__toString(),
                $this->getRouter()->dispatchRoute('/test/1')->__toString()
        );
    }
	
    public function testRouteWithWordParameter(): void
    {       	
        $router = $this->getRouter();
        $this->assertEquals(
                $router->get('routes.routeTestWord')->__toString(),
                $this->getRouter()->dispatchRoute('/test2/ciao')->__toString()
        );
    }
	
    public function testRouteWithoutIntegerOptionalParameter(): void
    {       	
        $router = $this->getRouter();
        $this->assertEquals(
                $router->get('routes.routeTestIntegerOptional')->__toString(),
                $this->getRouter()->dispatchRoute('/testi/')->__toString()
        );
    }
	
    public function testRouteWithoutWordOptionalParameter(): void
    {       	
        $router = $this->getRouter();
        $this->assertEquals(
                $router->get('routes.routeTestWordOptional')->__toString(),
                $this->getRouter()->dispatchRoute('/testw/')->__toString()
        );
    }
}
