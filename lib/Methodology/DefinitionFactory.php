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
    
    public static function create($mixed) {
        
        if($mixed instanceof Closure) 
            return new Context($mixed);
        
        if(is_string($mixed))
            return new Expression($mixed);

        return $mixed;
    }
}