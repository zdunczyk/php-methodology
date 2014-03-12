<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

namespace Methodology\Context;

use Methodology\Context;

class CurryEmpty { }

class Curry extends Context {

    public static $_;
    
    protected $params_required = 0;

    private $args_to_call = array();

    public static function _init() {
        self::$_ = new CurryEmpty();
    }
    
    public function __construct(callable $function) {
        parent::__construct($function);

        foreach($this->params as $param) {
            if($param->isOptional())
                break;
            
            $this->params_required++;
        }
    }
    
    public function __invoke() {
        $this->appendArguments(func_get_args());
        
        if(count($this->getArguments()) >= $this->params_required) {
            $args_sliced = array_slice($this->getArguments(), 0, $this->params_required);
            $this->clearArguments();
            return call_user_func_array('parent::__invoke', $args_sliced);        
        }
    }

    private function clearArguments() {
        $this->args_to_call = array();
    }

    private function appendArguments($args) {
        foreach($args as $a) {
            array_push($this->args_to_call, $a); 
        }
    }

    private function getArguments() {
        return $this->args_to_call;
    }

}

Curry::_init();