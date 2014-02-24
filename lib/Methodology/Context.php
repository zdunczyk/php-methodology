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

use Methodology\Language\Lexer;

/**
 * @author Tomasz Zduńczyk
 */
class Context extends AbstractScope {
    /**
     * @var callable    function wrapped by context
     */
    protected $callable;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * Report of invoke call.
     * 
     * @var array
     */    
    protected $report = array();

    const REPORT_DEPENDENCY_CHAIN_STOPPED = 1;
    
    public function __construct(callable $function) {
        $ref_function = new \ReflectionFunction($function);
        
        foreach ($ref_function->getParameters() as $param) {
            $next_param = &$this->params[];
            $next_param = array(
                'name' => $param->getName(),
                'optional' => $param->isOptional()
            );

            if($next_param['optional'])
                $next_param['value'] = $param->getDefaultValue();
           
            $next_param['expression'] = $next_param['optional'] && is_string($next_param['value']);
            
            if($next_param['expression']) {
                list($next_param['value'], $next_param['dependencies']) = 
                    $this->tokenize($param->getDefaultValue());
            }
        }
        
        $this->callable = $function;
    }

    public function getParams() {
        return $this->params;    
    }
    
    /**
     * Invokes wrapped function.
     * 
     * @return mixed
     */
    public function __invoke() {
        $arguments = func_get_args();
        $evaluated = array();
        
        foreach($this->params as $key => $param) {
            if($param['optional']) {
                $evaluated[$key] = $param['value'];
            
                if($param['expression']) {
                    $positional_params = array();
                    
                    foreach($param['dependencies'] as $dependency) {
                        if(!is_null($positional = Lexer::getPositionalParameter($dependency))) {
                            $arg_position = (int)($positional-1);
                            
                            if(isset($arguments[$arg_position])) {
                                $positional_params[$dependency] = $arguments[$arg_position];    
                            }
                        }
                    }
                    
                    try {
                        $report = array();
                        $evaluated[$key] = $this->evaluate($param['value'], $param['dependencies'], $positional_params, $report);
                        
                        if(in_array(Context::REPORT_DEPENDENCY_CHAIN_STOPPED, $report))
                            return $evaluated[$key];
                        
                    } catch(\OutOfBoundsException $e) {
                        /** @todo add proper message */
                        throw $e;
                    }
                }
            } else if(isset($arguments[$key])) {
                $evaluated[$key] = $arguments[$key];
            }
        }
        
        $callable = $this->callable->bindTo(new ContextProxy($this));
        return call_user_func_array($callable, $evaluated); 
    }

    /**
     * @see     AbstractScope::define
     * @param   string $key
     * @param   mixed $value
     * @return  $this
     */
    public function override($mixed, $value = null) {
        if(is_array($mixed) && is_null($value)) {
            
            foreach($mixed as $key => $value)
                $this->override($key, $value);
            
        } else {
            parent::define($mixed, $value); 
        }
    
        return $this;
    }

    /**
     * Shortcut for sequential clone and override.
     * 
     * @see Context::override 
     */
    public function overclone($mixed, $value = null) {
        $cloned = clone $this;
        
        return $cloned->override($mixed, $value);
    }

    /**
     * Reports action in running Context.
     * 
     * @param type $action
     */
    public function report($action) {
        $this->report[] = $action; 
    }
    
    /**
     * @see Context::report
     */
    public function clearReport() {
        $this->report = array();
    }

    /**
     * @return array
     */
    public function getReport() {
        return $this->report;
    }
}