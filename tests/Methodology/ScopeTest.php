<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

use Methodology\Scope;

class ScopeTest extends PHPUnit_Framework_TestCase {
    
    protected $scope;
   
    protected function setUp() {
        $this->scope = new Scope(); 
    }
    
    /**
     * @covers Methodology\Scope::resolve 
     */
    public function testIsParentScopeAccessed() {
        $parent = $this->getMock('Methodology\ScopeResolverInterface');

        $parent ->expects($this->once())
                ->method('resolve')
                ->with($this->equalTo('func'));

        $this->scope->setParent($parent);
        $this->scope->resolve('func');
    }

    /**
     * @covers Methodology\Scope::resolve 
     */    
    public function testAccessToParentScopeVariables() {
        $parent = new Scope();
        $parent->define('var', 123);
        
        $this->scope->setParent($parent);
        $this->assertEquals($this->scope->resolve('var'), 123);
        return $this->scope;
    }
    
    /**
     * @covers Methodology\Scope::resolve
     * @depends testAccessToParentScopeVariables 
     */    
    public function testHideParentVariableInChildScope($scope) {
        $scope->define('var', 12);
        $this->assertEquals($scope->resolve('var'), 12);
    }
    
    /**
     * @covers Methodology\Scope::resolve
     * @expectedException OutOfBoundsException
     */
    public function testExpectExceptionOnUndefinedKey() {
        $this->scope->resolve('R60g0ME7');
    }
}