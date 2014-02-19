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
                ->method('forwardResolve')
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

    /**
     * @group expressions
     * @covers Methodology\Scope::resolve 
     */
    public function testResolveStaticExpressionVariablesOnly() {
        $this->scope->define('a', 12);
        $this->scope->define('b', -3);
        
        $this->scope->define('add', 'a+b');
        $this->scope->define('mul', 'a*add');
        $this->scope->define('foo', 'a*add+b*mul*(-2)');
        
        $this->assertEquals($this->scope->resolve('add'), 9);
        $this->assertEquals($this->scope->resolve('mul'), 12 * 9);
        $this->assertEquals($this->scope->resolve('foo'), 12*9+(-3)*(12*9)*(-2));
    }

    /**
     * @group expressions
     * @covers Methodology\Scope::resolve
     */
    public function testResolveDynamicExpressionVariablesOnly() {
        $this->scope->define('a', 12);
        $child = $this->scope->newChild();
        
        $grandchild = $child->newChild();   
        $child->define('add', 'a*a');
        $grandchild->define('a', 24);
        
        $this->assertEquals($child->resolve('add'), 12*12);
        $this->assertEquals($grandchild->resolve('add'), 24*24);
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testExpectExceptionWhenDependencyNotResolved() {
        $this->scope->define('foo', 'bar*bar2');
        $this->scope->resolve('foo');
    }
}