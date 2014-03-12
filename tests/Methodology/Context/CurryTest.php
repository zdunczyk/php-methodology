<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

use Methodology\Context\Curry;

class CurryTest extends PHPUnit_Framework_TestCase {
   
    /**
     * @covers  Methodology\Context\Curry::__invoke
     */
    public function testPartialInvoking() {
        $curry = new Curry(function($a, $b) {
            return $a + $b;        
        });
        
        $this->assertNull($curry(1));
        $this->assertEquals($curry(2), 1 + 2);
        $this->assertNull($curry(2));
        $this->assertEquals($curry(3), 2 + 3);
    }

    /**
     * @covers  Methodology\Context\Curry::__invoke
     */
    public function testNormalInvoking() {
        $curry = new Curry(function($a, $b) {
            return $a + $b;        
        });

        $this->assertEquals($curry(2, 2), 4);
    }
}

