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
use Methodology\Exception\CollectedNotification;

use Symfony\Component\ExpressionLanguage\TokenStream as SymfonyTokenStream;

/**
 * @author Tomasz Zduńczyk
 */
class Context extends AbstractScope implements CallableInterface {
    /**
     * @var callable    function wrapped by context
     */
    protected $callable;

    /**
     * @var ContextParam[]
     */
    protected $params = array();

    /**
     * @var CallableInterface[]
     */ 
    protected $precalls = array();

    /**
     * @var CallableInterface[]
     */ 
    protected $postcalls = array();
    
    /**
     * Values used by collectors.
     */
    protected $result;

    /**
     * Report of invocation.
     * 
     * @var Report     
     */    
    protected $report;
    
    public function __construct(callable $function) {
        $this->report = new Report;
        $this->result = new Result;
        
        $ref_function = new \ReflectionFunction($function);
        
        foreach ($ref_function->getParameters() as $param) {
            $this->params[] = new ContextParam( $param->getName(), 
                                                $param->isOptional(), 
                                                $param->isOptional() ? $param->getDefaultValue() : null);
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
        $this->report->clear();
        
        $arguments = func_get_args();
        $evaluated = array();
        $report = new Report;
        
        foreach($this->params as $key => $param) {
            if($param->isOptional()) {
                $report->clear();
                $evaluated[$key] = $param->getDefaultCallable()->call($arguments, $this, $report);
                    
                if($report->was(Report::DEPENDENCY_CHAIN_STOPPED))
                    return $evaluated[$key];     
                
            } else if(isset($arguments[$key])) {
                $evaluated[$key] = $arguments[$key];
            }
        }
        
        foreach($this->precalls as $precall) {
            $report->clear();
            $precall_result = $precall->call($arguments, $this, $report);
            
            if($report->was(Report::DEPENDENCY_CHAIN_STOPPED))
                return $precall_result;            
        }
        
        $callable = $this->callable->bindTo(new ContextProxy($this, $this->report, $this->result));
        
        $result = call_user_func_array($callable, $evaluated);

        if(($this->report->was(Report::COLLECT_MODE_ON) || is_null($result)) 
                                    && $this->report->was(Report::RESULT_COLLECTED))    
            return $this->result->get();
       
        $postcall_result = $result;
        
        foreach($this->postcalls as $postcall) {
            $postcall_arguments = $postcall_result;
            if(!is_array($postcall_arguments))
                $postcall_arguments = array($postcall_result);
            
            $report->clear();
            $postcall_result = $postcall->call($postcall_arguments, $this, $report);
            
            if($report->was(Report::PROPAGATION_CHAIN_STOPPED))
                return $postcall_result;
        }
        
        return $postcall_result;
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
        $this->precalls[] = DefinitionFactory::create($mixed, $this);    
    }

    public function inReport($action) {
        return $this->report->was($action);
    }

    public function getReportSummary() {
        return $this->report->getSummary();
    }
    
    /**
     * Returns array of n context evaluations.
     * 
     * @param type $n
     * @return array
     */
    public function collect($n, array $args = array()) {
        $this->result = new Result($n);
       
        $this->report->occurred(Report::COLLECT_MODE_ON);
        while(!$this->result->complete()) {
            try {
                $returned = call_user_func_array($this, $args);       

                if(!$this->report->was(Report::RESULT_COLLECTED))
                    $this->result->addPart($returned);
                
            } catch(CollectedNotification $cn) { }
        }

        return $this->result->get();
    }

    /**
     * {@inheritdoc} 
     */
    public function call(array $arguments = array(), ScopeResolverInterface $scope = null, Report &$report = null) { 
        if(!is_null($report))
            $this->report = $report;
        
        return call_user_func_array($this, $arguments);
    }

    /**
     * {@inheritdoc} 
     */
    public function raw(ScopeResolverInterface $scope = null, ResolveChain $chain = null) {
        return $this;
    }

    public function propagates($mixed) {
        $this->postcalls[] = $postcall = DefinitionFactory::create($mixed, $this);    
        
        if($postcall instanceof Context) 
            return $postcall;
       
        return $this;
    }
}