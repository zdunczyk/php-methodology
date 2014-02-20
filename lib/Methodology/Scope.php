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

use Methodology\AbstractScope;

/**
 * Main class which represents virtual scope. 
 * 
 * @author Tomasz Zduńczyk 
 */
class Scope extends AbstractScope {

    /**
     * Returns new scope instance which is a child of current scope.
     * 
     * @see     AbstractScope::newChild()
     * @return  Scope    child instance
     */
    public function newChild() {
        return parent::newChild();
    }

    public function define($key, $value) {
        if(is_callable($value)) {
            $value = new Context($value);
            $value->setParent($this);
        }
        
        return parent::define($key, $value);
    }
}