<?php

use Armor\Handle\Request;
use Armor\Handle\Route;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase {
    public function testNormallyCreatingInstance() {
        $req = new Request('get', '/users/1234/1', ['user' => '1234', 'userinfo' => 'profile']);

        $this->assertInstanceOf(Request::class, $req);

        
        $this->assertClassHasAttribute('path', Request::class);
        $this->assertClassHasAttribute('method', Request::class);
        // PHPUnit 9 doesn't support this anymore
        // $this->assertClassHasAttribute('_query', Request::class);

        return $req;
    }

    public function testCreatesNewRequestPathOnInjectionOfParameters() {
        $req = new Request('get', '/users/1234/1', []);
        
        $previousPath = $req->path;
        
        $route = new Route(
            "/users/$(user)/$(userinfo)",
            function($req, $res) {}
        );
        $this->assertTrue((bool)$route->match('/users/1234/profile'));

        $req->injectCustomParametersFromRoute($route);
        
        $this->assertNotSame($previousPath, $req->path);
        $this->assertNotEquals($previousPath, $req->path);
    }

    public function testOnlyAllowsQueryAttributeForGet() {
        $postReq = new Request('post',
                               '/users/insert/1234',
                               ['user' => 1234],
                               ['name' => 'AnyUserName', 'status' => 'Active Account']);
        $this->expectExceptionMessage('Method doesn\'t have query parameters');
        $postReq->query->name == 'admin';
    }

    public function testOnlyAllowsBodyAttributeForPost() {
        $getReq = new Request('get',
                              '/users/12345/10',
                              ['user' => 1234, 'userinfo' => 'filter_topics'],
                              ['topics' => ['programming', 'php']]);
        $this->expectExceptionMessage('Method doesn\'t have a request body');
        is_array($getReq->body->topics);
    }

    /**
     * @depends testNormallyCreatingInstance
     */
    public function testCanReceiveExternalAdditionalFieldsAndFunctions(Request $req) {
        $req->foo = "Foo";
        $this->assertNotNull($req->foo);
        $this->assertEquals($req->foo, "Foo");

        $req->bar = function() {
            return "Hello, World!";
        };
        $this->assertNotNull($req->bar);
        $this->assertTrue(is_callable($req->bar));
        $this->assertEquals($req->bar(), "Hello, World!");

        return $req;
    }

    /**
     * @depends testCanReceiveExternalAdditionalFieldsAndFunctions
     */
    public function testReturnsNullToNonExistentFieldsAndThrowsErrorToNonCallableField(Request $req) {
        $this->assertNull($req->boo);
        $this->assertNull($req->faa);

        $undefinedMethod1 = "amazingAction";
        $this->expectExceptionMessage("'$undefinedMethod1' is not a method, or does not exist");
        $req->{$undefinedMethod1}();

        $undefinedMethod2 = "veryAwesomeProcedure";
        $this->expectExceptionMessage("'$undefinedMethod2' is not a method, or does not exist");
        $req->{$undefinedMethod2}();
    }
}