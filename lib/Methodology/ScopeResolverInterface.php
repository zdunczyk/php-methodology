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
     * @param   string  $key    reference identifier             
     * @return  mixed   variable or callable bound to key in current scope  
     */
    public function resolve($key);
}