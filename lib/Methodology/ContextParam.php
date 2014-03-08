<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

namespace Methodology;


class ContextParam {

    private $callable = null;

    private $name;
    
    private $is_optional;

    public function __construct($name, $is_optional, $value = null) {
        $this->name = $name;        
        $this->is_optional = $is_optional;    

        if(!is_null($value))
            $this->callable = DefinitionFactory::create($value);
    }
    
    public function getDefaultCallable() {
        return $this->callable;         
    }

    public function isOptional() {
        return $this->is_optional;
    }

    public function getName() {
        return $this->name;
    }
}