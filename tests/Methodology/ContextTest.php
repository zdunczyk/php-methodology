<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

use Methodology\Context;
use Methodology\Scope;

class ContextTest extends PHPUnit_Framework_TestCase {
    
    protected $scope;
   
    protected function setUp() {
        $this->scope = new Scope(); 
    }
    
    /**
     * @covers Context::__invoke
     */
    public function testIsWrappedFunctionInvoked() {
        $foo = null;
        
        $context = new Context(function($arg) use (&$foo) {
            return ($foo = $arg);
        });
        $result = $context('bar');
        
        $this->assertEquals($foo, 'bar');
        $this->assertEquals($result, 'bar');
    }
    
    /**
     * @covers Context::__invoke
     */
    public function testResolvingParentVariableByThis() {
        $this->scope->define('bar', 765);

        $child = $this->scope->newChild();
        $child->define('foo', function() {
            return $this->bar;    
        });
        
        $foo = $child->resolve('foo');
        
        $this->assertEquals($foo(), 765);
    }
    
    /**
     * Provides foo function inside scope with bar variable. 
     */
    public function bar123Provider() {
        $this->scope = new Scope();
        $this->scope->define('bar', 123);
        
        $child = $this->scope->newChild();
        
        $child->define('foo', function() {
            return $this->bar;    
        });

        return array(
            array($child)
        );
    }
   
    /**
     * @dataProvider bar123Provider
     */
    public function testVariableOverriding($child) {
        $child->resolve('foo')->override('bar', 567);
        $this->assertEquals($child->resolve('foo')->__invoke(), 567);
        
        $child->resolve('foo')->override(array(
            'bar' => 12,
            'foo' => 234
        ));
        $this->assertEquals($child->resolve('foo')->__invoke(), 12);
    }
    
    /**
     * @dataProvider bar123Provider
     */
    public function testVariableOvercloning($child) {
        $cloned = $child->resolve('foo')->overclone('bar', 567);
        
        $this->assertNotEquals($child->resolve('foo')->__invoke(), 567);
        $this->assertEquals($cloned(), 567);
    }
}
