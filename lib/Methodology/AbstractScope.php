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

use Methodology\ScopeResolverInterface;

/**
 * Abstract class which supports basic scope manipulation.
 * 
 * @author Tomasz Zduńczyk 
 * @implements ScopeResolverInterface
 */
abstract class AbstractScope implements ScopeResolverInterface {
    /**
     * Holds values defined in current scope.
     * 
     * @var mixed   
     */
    protected $values = array();
    
    /**
     * Reference to the parent virtual scope.
     * 
     * @var ScopeResolverInterface
     */
    protected $parent = null;

    /**
     * {@inheritdoc}
     * 
     * @throws \OutOfBoundsException    when key is not found in current scope and there is no parent
     */
    public function resolve($key) {
        if(array_key_exists($key, $this->values))
            return $this->values[$key];
        
        if(!is_null($this->parent))
            return $this->parent->resolve($key);             

        throw new \OutOfBoundsException("Could not resolve `$key` key!");
    }

    /**
     * Defines new function or variable in scope.
     * 
     * @param string    $key    identifier of value
     * @param mixed     $value  value of variable to define 
     */
    public function define($key, $value) {
        $this->values[$key] = $value;
    }
    
    /**
     * Sets a new parent scope for current virtual-scope object. 
     *  
     * @param ScopeResolverInterface   $resolver   reference to new parent scope
     * @return  ScopeResolverInterface $resolver   new parent scope 
     */
    public function setParent(ScopeResolverInterface $resolver) {
        return ($this->parent = $resolver);
    }

}
