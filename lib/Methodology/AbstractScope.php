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
     * @throws \InvalidArgumentException    when key is not valid  
     * @throws \OutOfBoundsException        when key is not found in current scope and there is no parent
     */
    public function resolve($key) {
        if($this->isNameValid($key) 
            && (isset($this->values[$key]) || array_key_exists($key, $this->values))) {
                
            return $this->values[$key];
        }
        
        if(!is_null($this->parent))
            return $this->parent->resolve($key);             

        throw new \OutOfBoundsException("Could not resolve `$key` key!");
    }

    /**
     * Defines new function or variable in scope.
     *  
     * @throws \InvalidArgumentException    when key is not valid 
     * @param string    $key                identifier of value
     * @param mixed     $value              value of variable to define 
     */
    public function define($key, $value) {
        $this->isNameValid($key);
        
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

    /**
     * Validates variable identifier. 
     * 
     * @throws \InvalidArgumentException    when key is not valid 
     * @param   $name                       identifier for validation 
     * @return  boolean                     true when name is valid 
     */
    private function isNameValid($name) {
        if(!is_string($name))
            throw new \InvalidArgumentException("Scope's variable name must be a string!");
        
        return true;
    }
}
