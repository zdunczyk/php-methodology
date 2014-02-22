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
class Context extends AbstractScope {
    /**
     * @var callable    function wrapped by context
     */
    protected $callable;

    /**
     * @var array
     */
    protected $params;
    
    public function __construct(callable $function) {
        $ref_function = new \ReflectionFunction($function);
        
        foreach ($ref_function->getParameters() as $param) {
            $next_param = &$this->params[];
            $next_param = array(
                'name' => $param->getName(),
                'expression' => $param->isOptional() 
            );
            
            if($param->isOptional()) {
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
        $callable = $this->callable->bindTo(new ContextProxy($this));
        return call_user_func_array($callable, func_get_args()); 
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
}