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
 * @author Tomasz Zduńczyk
 */
class ResolveChain {

    private $chain = array();
   
    /**
     * @param   string
     */
    public function push($key) {
        $this->chain[] = $key; 
        return $this;
    }

    /**
     * @param   string 
     * @return  bool
     */
    public function has($key) {
        return in_array($key, $this->chain);
    }
}

