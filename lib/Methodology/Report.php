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

class Report {
    
    const RESULT_COLLECTED = 1;
    const COLLECT_MODE_ON = 2;
    const DEPENDENCY_CHAIN_STOPPED = 3;
    const PROPAGATION_CHAIN_STOPPED = 4;
    
    protected $reported;
    
    public function __construct() {
        $this->clear();
    }
    
    public function occurred($action) {
        $this->reported[] = $action;
    }

    public function clear() {
        $this->reported = array();
    }

    public function was($action) {
        return in_array($action, $this->reported);
    }

    public function getSummary() {
        return $this->reported;
    }

    public function append(array $report) {
        $this->reported += $report;
    }
}

