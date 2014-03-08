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

class DefinitionFactory {
    
    public static function create($mixed, ScopeResolverInterface $scope = null) {
        
        if($mixed instanceof \Closure) {
            $context = new Context($mixed);
            
            if(!is_null($scope))
                $context->setParent($scope);

            return $context;
        }
        
        if(self::expressionPossible($mixed))
            return new Expression($mixed);

        if(!is_object($mixed))
            return new Variable($mixed);

        return $mixed;
    }

    public static function expressionPossible($value) {
        return is_string($value);
    }
}