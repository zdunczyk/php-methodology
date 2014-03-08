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

class Variable implements CallableInterface {
    
    protected $var;
    
    public function __construct($var) {
        $this->var = $var;
    }
    
    /**
     * {@inheritdoc} 
     */
    public function call(array $arguments = array(), ScopeResolverInterface $scope = null, Report &$report = null) {
        return $this->var; 
    }
    
    /**
     * {@inheritdoc} 
     */
    public function raw(ScopeResolverInterface $scope = null, ResolveChain $chain = null) {
        return $this->var;
    }
}