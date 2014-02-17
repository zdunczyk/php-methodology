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

    /**
     * Provides invalid names of scope variables. 
     */
    public function invalidNameProvider() {
        return array(
            array(123),
            array(function() { }),
            array(array()),
            array(NULL),
            array(true),
            array(new \StdClass)
        );
    }
    
    /**
     * @dataProvider invalidNameProvider
     * @covers Methodology\Scope::resolve
     * @expectedException InvalidArgumentException 
     */
    public function testResolvingVariableValidation($invalid) {
        $this->scope->resolve($invalid);
    }
    
    /**
     * @dataProvider invalidNameProvider
     * @covers Methodology\Scope::resolve
     * @expectedException InvalidArgumentException 
     */
    public function testDefiningVariableValidation($invalid) {
        $this->scope->define($invalid, 0);
    }
    
    /**
     * @covers Methodology\Scope::childScope
     */
    public function testChildScopeFromParent() {
        $this->scope->define('var', 123); 
        $this->assertEquals($this->scope->newChild()->resolve('var'), 123);
    }

    /**
     * @covers Methodology\Scope::define
     * @covers Methodology\Scope::getDependencies
     */
    public function testFetchingExpressionDependencies() {
        $deps = array('a', 'b', 'foo');

        $this->scope->define('var', "-({$deps[0]}+{$deps[1]})*bar({$deps[2]})");
       
        $defined_deps = $this->scope->getDependencies('var');
        
        foreach($deps as $d)
            $this->assertContains($d, $defined_deps);
    }

    /**
     * @covers Methodology\Scope::getDependencies
     */
    public function testExpectNullWhenNoDependencies() {
        $this->assertNull($this->scope->getDependencies('boo'));
        
        $this->scope->define('number', 12);
        $this->assertNull($this->scope->getDependencies('number'));
    }
}