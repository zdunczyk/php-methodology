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
    
    /**
     * @covers Context::__construct
     */
    public function testFetchingArgumentsOfClosure() {
        $context = new Context(function($a, 
                                        $foo = 'foo($1)*23', 
                                        $bar = 'bar(asd)', 
                                        $nth = null) {
            // test only arguments 
        });
        
        $params = $context->getParams();
        $this->assertCount(4, $params);
        
        $this->assertEquals($params[0]['name'], 'a');
        $this->assertEquals($params[2]['name'], 'bar'); 

        $this->assertTrue($params[1]['expression']);
        $this->assertFalse($params[0]['expression']);

        $this->assertTrue($params[2]['value'] instanceof 
                \Symfony\Component\ExpressionLanguage\TokenStream);

        $this->assertNull($params[3]['value']);
    }

    /**
     * @covers Context::__invoke
     */
    public function testResolvingArgumentsVariableDependency() {
        $c = 4; $b = true; $a = 2;
        
        $scope = new Scope();
        $scope->define('c', $c);
        $scope->define('b', $b);
       
        $this->assertEquals($scope->resolve('c'), $c);
        $this->assertEquals($scope->resolve('b'), $b); 
       
        $testcase = $this;
        $scope->define('foo', function($x, $y = 'c+2', $z = '!b') use ($testcase, $a, $b, $c) {
            $testcase->assertEquals($x, $a);
            $testcase->assertEquals($y, $c+2);
            $testcase->assertEquals($z, !$b);
        });

        $scope->resolve('foo')->__invoke($a);
    }

    /**
     * @covers Context::__invoke
     */
    public function testResolvingPositionalParameters() {
        $a1 = 3; $a2 = 4; $a3 = 5; $a4 = 12;
        $testcase = $this;
        $context = new Context(function($x, $y = '-$2', $z = '($1+$2)*$4') use ($testcase, $a1, $a2, $a3, $a4) {
            $testcase->assertEquals($x, $a1);
            $testcase->assertEquals($y, -$a2);
            $testcase->assertEquals($z, ($a1+$a2)*$a4);
        });

        $context($a1, $a2, $a3, $a4);
    }
    
    /**
     * @covers Context::__invoke
     */
    public function testResolvingArgumentsFunctionDependency() {
        $scope = new Scope();

        $sub = function($a, $b) {
            return $a - $b;
        };
        
        $val = function($val) {
            return 'val: ' . $val;        
        };
        
        $scope->define('sub', $sub);
        $scope->define('val', $val);

        $testcase = $this;
        $scope->define('dependent', function($a, $b = 'val(sub(5,sub(2,3)))') use ($testcase, $val, $sub) {
            $testcase->assertEquals($b, $val($sub(5,$sub(2,3))));                        
        });

        $scope->resolve('dependent')->__invoke(3);
    }
    
    /**
     * @covers Context::__invoke
     * @covers ContextProxy::_stopDependencyChain
     */
    public function testStopDependencyChain() {
        $scope = new Scope(); 
        
        $scope->define('foo', function() {
            $this->_stopDependencyChain();
            return 'foo';
        });
        
        $testcase = $this;
        $scope->define('bar', function($a = 'foo()') use ($testcase) {
            $testcase->assertTrue(false);
            return 'bar'; 
        });

        $this->assertEquals($scope->resolve('bar')->__invoke(), 'foo');
    }

    /**
     * @context Context::depends
     */
    public function testDependsOnCallable() {
        $scope = new Scope();
        $scope->define('test', ($test = 33));
        $child = $scope->newChild();
        
        $child->define('foo', function() {
            return $this->test;                                        
        });

        $child->resolve('foo')->depends(function() {
            $this->_stopDependencyChain();
            return $this->test + 1;
        });

        $this->assertEquals($child->resolve('foo')->__invoke(), $test + 1);                        
    }

    /**
     * @context Context::depends
     */
    public function testDependsOnContext() {
        $scope = new Scope();
        
        $testcase = $this;
        $scope->define('foo', function() use ($testcase) {
            $testcase->assertFalse(true);        
        });

        $bar = new Context(function() {
            $this->_stopDependencyChain();    
        });

        $scope->resolve('foo')->depends($bar);
        $scope->resolve('foo')->__invoke();
    }

    /**
     * @context Context::depends 
     */
    public function testDependsOnExpression() {
        $scope = new Scope();

        $scope->define('foo', function() {
            $this->_stopDependencyChain(); 
        });
        
        $testcase = $this; 
        $scope->define('bar', function() use ($testcase) {
            $testcase->assertFalse(true); 
        });

        $bar = $scope->resolve('bar');
                
        $bar->depends('foo()*4');
        $bar->__invoke();
    }

    /**
     * @covers Context::collect
     */
    public function testCollectReturnValues() {
        $context = new Context(function() {
            foreach(range(0, 2) as $r)
                return $r;
        });

        $this->assertEquals($context->collect(3), array(0, 0, 0));
    }

    public function rangeCollectorProvider() {
        return array(
            array(new Context(function() {
                foreach(range(0, 2) as $r)
                    $this->_collect($r);
            }))
        );
    }

    /**
     * @covers Context::collect
     * @dataProvider rangeCollectorProvider
     */
    public function testCollectOnCollector($context) {
        $this->assertEquals($context->collect(7), array(0, 1, 2, 0, 1, 2, 0));
    }
    
    /**
     * @covers Context::collect
     * @dataProvider rangeCollectorProvider
     */
    public function testInvokeCollector($context) {
        $this->assertEquals($context(), array(0, 1, 2));
    }
}