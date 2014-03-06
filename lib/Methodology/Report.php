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
    
    const DEPENDENCY_CHAIN_STOPPED = 1;
    const RESULT_COLLECTED = 2;
    
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

    public function getList() {
        return $this->reported;
    }

    public function append(Report $report) {
        $this->reported += $report->getList();
    }
}

