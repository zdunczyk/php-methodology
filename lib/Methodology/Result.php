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

use Methodology\Exception\CollectedNotification;

class Result {
    
    protected $limit = 0;
    
    protected $values = array();
    
    public function __construct($limit = 0) {
        $this->limit = $limit;
    }
    
    public function addPart($value) {
        $this->values[] = $value;
        if($this->limit > 0 && count($this->values) >= $this->limit)
            throw new CollectedNotification; 
    }

    public function get() {
        return $this->values;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function complete() {
        return count($this->values) >= $this->getLimit();
    }
}
