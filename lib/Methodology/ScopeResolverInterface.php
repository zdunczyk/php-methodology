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

/**
 * Interface for resolving variables in virtual scopes.
 *  
 * @author Tomasz Zduńczyk
 */
interface ScopeResolverInterface {
    
    /**
     * Resolves variable in the scope of implementatation.
     * 
     * @uses    forwardResolve
     * @param   string  $key    reference identifier             
     * @return  mixed   variable or callable bound to key in current scope  
     */
    public function resolve($key);

    /**
     * Preserves origin scope between parent - child calls. All expression 
     * dependencies are resolved in origin.
     * 
     * @internal
     * @param   ScopeResolverInterface  $origin     scope to resolve all following dependencies in
     * @param   ResolveChain            $chain
     */
    public function forwardResolve($key, ScopeResolverInterface $origin, ResolveChain $chain);
}