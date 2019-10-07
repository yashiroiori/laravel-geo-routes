<?php

namespace LaraCrafts\GeoRoutes\Tests\Unit;

use LaraCrafts\GeoRoutes\GeoRoute;
use LaraCrafts\GeoRoutes\Tests\TestCase;

class PackageTest extends TestCase
{
    /** @var \Illuminate\Routing\Router */
    protected $router;

    public function setUp(): void
    {
        parent::setUp();
        $this->router = $this->app->make('router');
    }

    public function testMacros()
    {
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->from('it'));
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->allowFrom('ch'));
        $this->assertInstanceOf(GeoRoute::class, $this->router->get('/foo', 'BarController@baz')->denyFrom('ru'));
        $this->performConvenienceMethodsAssertions();
    }

    protected function performConvenienceMethodsAssertions()
    {
        $this->assertGetGeoConstraintReturnsValidValue();
        $this->assertGetConstraintCountriesReturnsValidValue();
        $this->assertGetGeoCallbackReturnsNullIfNoCallbackIsPresent();
        $this->assertGetGeoCallbackReturnsCallbackArray();
        $this->assertIsAccessibleFromReturnsTrueForAllowedCountries();
        $this->assertIsAccessibleFromReturnsTrueForDisallowedCountries();
    }

    protected function assertGetGeoConstraintReturnsValidValue()
    {
        $route = $this->router->get('/foo', 'BarController@baz')->allowFrom('cz');
        # We are explicitly destructing the route instance because we are still
        # invoking another method on this same instance, while in real life scenarios
        # the route definitions are separate from the rest of this code.
        $route->__destruct();

        $constraint = $route->getGeoConstraint();
        $expected = [
            'strategy' => 'allow',
            'countries' => ['CZ'],
            'callback' => null,
        ];

        $this->assertEquals($expected, $constraint);
    }

    protected function assertGetConstraintCountriesReturnsValidValue()
    {
        $route = $this->router->get('/foo', 'BarController@baz')->allowFrom('us', 'ca');

        $route->__destruct();

        $countries = $route->getConstraintCountries();
        $expected = ['US', 'CA'];

        $this->assertEquals($expected, $countries);
    }

    protected function assertGetGeoCallbackReturnsCallbackArray()
    {
        $route = $this->router->get('/foo', 'BarController@baz')
            ->allowFrom('dz', 'as')->orRedirectTo('qux');

        $route->__destruct();

        $callback = $route->getGeoCallback();
        $expected = ['LaraCrafts\GeoRoutes\Callbacks::redirectTo', ['qux']];

        $this->assertEquals($expected, $callback);
    }

    protected function assertGetGeoCallbackReturnsNullIfNoCallbackIsPresent()
    {
        $route = $this->router->get('/foo', 'BarController@baz')->allowFrom('us', 'ca');

        $route->__destruct();

        $this->assertNull($route->getGeoCallback());
    }

    protected function assertIsAccessibleFromReturnsTrueForAllowedCountries()
    {
        $route = $this->router->get('/foo', 'BarController@baz')->allowFrom('bd', 'ky');

        $route->__destruct();

        $this->assertTrue($route->isAccessibleFrom('bd'));
        $this->assertTrue($route->isAccessibleFrom('ky'));
    }

    protected function assertIsAccessibleFromReturnsTrueForDisallowedCountries()
    {
        $route = $this->router->get('/foo', 'BarController@baz')->denyFrom('bd', 'ky');

        $route->__destruct();

        $this->assertFalse($route->isAccessibleFrom('bd'));
        $this->assertFalse($route->isAccessibleFrom('ky'));
    }
}
