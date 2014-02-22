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

class ContextProxyTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers ContextProxy::_placeholder
     */
    function testUsingPlaceholders() { 
        $scope = new Scope();
        $scope->define('bar', function() {
            $elem = 124;
            return $this->_placeholder('foo', function($elem) {
                return $elem;
            })->__invoke($elem);    
        });
        
        $this->assertEquals($scope->resolve('bar')->__invoke(), 124);
        
        $scope->define('foo', function() {
            return 443;
        });
        $this->assertEquals($scope->resolve('bar')->__invoke(), 443);
    }
}