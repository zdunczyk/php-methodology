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

use Symfony\Component\ExpressionLanguage\TokenStream as SymfonyTokenStream;

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
     * @var Context[]
     */ 
    protected $precalls = array();

    /**
     * @var string[]
     */
    protected $preexpressions = array();
    
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
                    $report = array(); 
                    $evaluated[$key] = $this->evaluatePositional($param['value'], $param['dependencies'], $arguments, $report);
                        
                    if(self::wasDependencyChainStopped($report))
                        return $evaluated[$key];     
                }
            } else if(isset($arguments[$key])) {
                $evaluated[$key] = $arguments[$key];
            }
        }

        foreach($this->preexpressions as $exp) {
            $report = array(); 
            $expression_result = $this->evaluatePositional($exp['value'], $exp['dependencies'], $arguments, $report);
                
            if(self::wasDependencyChainStopped($report))
                return $expression_result;            
        }

        foreach($this->precalls as $call) {
            $call->clearReport();
            $precall_result = $call();
            
            if(self::wasDependencyChainStopped($call->getReport())) 
                return $precall_result;
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

    public function depends($mixed) {
        /** @todo when array is passed it should overwrite params default values */
        if($mixed instanceof Context) {
            $this->addPrecall($mixed);
            return $mixed;
            
        } else if(is_callable($mixed)) {
            $context = new Context($mixed);
            $context->setParent($this->parent);
            
            $this->addPrecall($context);
            return $context;
            
        } else if(is_string($mixed)) {
            list($preexp_value, $preexp_dep) = 
                    $this->tokenize($mixed);
            
            $this->addPreexpression($preexp_value, $preexp_dep);
            return null;
            
        } else {
            throw new Exception('Wrong dependency type');    
        }
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


    public static function wasDependencyChainStopped($report) {
        return in_array(Context::REPORT_DEPENDENCY_CHAIN_STOPPED, $report);        
    }


    /**
     * @throws \Methodology\OutOfBoundsException
     */
    protected function evaluatePositional(SymfonyTokenStream $expression, $dependencies, $arguments, &$report) {
        $positional_params = array();
        
        foreach($dependencies as $dependency) {
            if(!is_null($positional = Lexer::getPositionalParameter($dependency))) {
                $arg_position = (int)($positional-1);
                
                if(isset($arguments[$arg_position])) {
                    $positional_params[$dependency] = $arguments[$arg_position];    
                }
            }
        }
        try {
            return $this->evaluate($expression, $dependencies, $positional_params, $report);
            
        } catch(\OutOfBoundsException $e) {
            /** @todo add proper message */
            throw $e;
        }
    }

    private function addPrecall(Context $c) {
        $this->precalls[] = $c; 
    }

    private function addPreexpression(SymfonyTokenStream $value, $dependencies) {
        $this->preexpressions[] = array('value' => $value, 'dependencies' => $dependencies);
    }
}