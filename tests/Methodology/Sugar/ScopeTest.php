<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

use Methodology\Sugar\Scope;

class SugarScopeTest extends PHPUnit_Framework_TestCase {

    protected $scope;
    
    public function setUp() {
        $this->scope = new Scope();    
    }
   
    /**
     * @return  SugarScope::__set
     */
    public function testSettingVariablesMagically() {
        $this->scope->var1 = $var1 = 23;
        $this->scope->var2 = $var2 = true;
        
        $testcase = $this;
        $this->scope->var3 = function($a = 'var1', $b = 'var2') use ($testcase, $var1, $var2)  {
            $testcase->assertEquals($a, $var1);
            $testcase->assertEquals($b, $var2);
            return $a;
        };
        
        $this->assertEquals($this->scope->var3(), $var1);
    }

    
    public function testRedefinitionBlocking() {
        $this->scope->foo = $bar = 123;
        $this->assertEquals($this->scope->foo, $bar);

        $this->scope->foo = $bar = 321;
        $this->assertEquals($this->scope->foo, $bar);

        $this->scope->__done();
        $this->scope->foo = 23;
        $this->assertEquals($this->scope->foo, $bar);
    }
}


