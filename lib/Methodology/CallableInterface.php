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

interface CallableInterface {
    
    public function call(array $arguments = array(), ScopeResolverInterface $scope = null, Report &$report = null); 

    public function raw(ScopeResolverInterface $scope = null, ResolveChain $chain = null);
}