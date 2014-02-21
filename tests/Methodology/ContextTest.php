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
        $scope = new Scope();
        $scope->define('bar', 765);

        $child = $scope->newChild();
        $child->define('foo', function() {
            return $this->bar;    
        });
        
        $foo = $child->resolve('foo');
        
        $this->assertEquals($foo(), 765);
    }
}
