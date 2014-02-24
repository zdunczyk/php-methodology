<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

namespace Methodology\Sugar;

use Methodology\Scope as RawScope;

/**
 * Adds some syntatic sugar to basic Scope interface.
 * 
 * @author Tomasz Zduńczyk 
 */
class Scope {
    /**
     * @var Methodology\Scope 
     */
    protected $raw;
   
    /**
     * Prevents future redefinitions in scope.
     */
    private $done = false;        

    public function __construct() {
        $this->raw = new RawScope();
    }

    
    public function __set($name, $value) {
        if(!$this->done)
            $this->raw->define($name, $value);
        
        return $value;
    }

    
    public function __get($name) {
        return $this->raw->resolve($name);
    }

    
    public function __call($name, $args) {
        return call_user_func_array($this->$name, $args);        
    }

    
    public function __done() {
        $this->done = true;        
    }
}
