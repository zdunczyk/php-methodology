<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

namespace Methodology\Exception;

class CollectedNotification extends \Exception { 

    protected $collection;

    public function __construct($collection) {
        $this->collection = $collection;
        parent::__construct();
    }

    public function getCollection() {
        return $this->collection;
    }
}