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
use Methodology\Language\TokenStream;

use Symfony\Component\ExpressionLanguage\TokenStream as SymfonyTokenStream;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\ExpressionLanguage\Parser;

class Expression implements CallableInterface {

    /**
     * @var array
     */
    protected $dependencies;
    
    /**
     * @var SymfonyTokenStream
     */ 
    protected $tokens;
    
    /**
     * @var Lexer
     */
    protected static $lexer = null;
   
    
    public function __construct($value) {
        if(!is_string($value))
            throw new Exception();    
            
        $this->tokens = $this->getLexer()->tokenize($value); 
        $this->dependencies = $this->fetchDependenciesFrom($this->tokens);
    }
   
    /**
     * {@inheritdoc} 
     */
    public function call(array $arguments = array(), ScopeResolverInterface $scope = null, Report &$report = null) {
        $positional_params = array();
        
        foreach($this->dependencies as $dependency) {
            if(!is_null($positional = Lexer::getPositionalParameter($dependency))) {
                $arg_position = (int)($positional-1);
                
                if(isset($arguments[$arg_position])) {
                    $positional_params[$dependency] = $arguments[$arg_position];    
                }
            }
        }
        
        try {
            if(is_object($scope))
                return $this->evaluate($scope, null, $positional_params, $report);
            
            return null;
        } catch(\OutOfBoundsException $e) {
            /** @todo add proper message */
            throw $e;
        }
    }
    
    /**
     * {@inheritdoc} 
     */
    public function raw(ScopeResolverInterface $scope = null, ResolveChain $chain = null) {
        return $this->forwardEvaluate($scope, $chain);
    }
    
    /**
     * Evaluates TokenStream in current scope, with specific dependencies and 
     * additional variables.
     *  
     * @param string $expression
     * @param array $dependencies
     * @param array $additionals    optional
     * @return mixed
     */
    public function evaluate(ScopeResolverInterface $origin, ResolveChain $chain = null, $additionals = array(), Report &$report = null) {
        if(is_null($chain))
            $chain = new ResolveChain;
        
        return $this->forwardEvaluate($origin, $chain, $additionals, $report);
    }
    
    /**
     * @return type
     * @throws \Methodology\OutOfBoundsException
     */
    public function forwardEvaluate(ScopeResolverInterface $origin, ResolveChain $chain, $additionals = array(), Report &$report = null) {
        if(is_null($report))
            $report = new Report;
        
        $variables = is_array($additionals) ? $additionals : array();
        $functions = array();
        
        foreach($this->dependencies as $dependency) {
            if(!isset($variables[$dependency])) {
                try {
                    $resolved = $origin->forwardResolve($dependency, $origin, $chain); 
                    
                    if($resolved instanceof Context) {
                        $functions[$dependency] = array(
                            'compiler' => NULL,
                            'evaluator' => function() use ($resolved, &$report) {
                                $result = call_user_func_array($resolved, array_slice(func_get_args(), 1));
                                $report->append($resolved->getReportSummary());
                                return $result;
                            }
                        );
                    } else                                 
                        $variables[$dependency] = $resolved;                             
                    
                } catch(\OutOfBoundsException $e) {
                    /** @todo specify problematic $dependency */
                    throw $e;               
                }
            }
        }   
        
        return $this->getParsedExpression($this->tokens, $this->dependencies, $functions)->getNodes()->evaluate($functions, $variables); 
    }
    
    /**
     * @return Lexer
     */
    private function getLexer() {
        if(is_null(self::$lexer))
            self::$lexer = new Lexer();

        return self::$lexer;
    }
    
    /**
     * Returns dependencies which will have to be resolved on parsing.
     * 
     * @param \Symfony\Component\ExpressionLanguage\TokenStream $stream
     * @return array        dependencies found in TokenStream
     */
    private function fetchDependenciesFrom(SymfonyTokenStream $stream) {
        $iterable_stream = new TokenStream($stream);
        
        $depends_on = array();
        foreach($iterable_stream as $name_token) {
            $depends_on[] = $name_token->value;
        }

        return $depends_on;
    }
    
    /**
     * Temporary solution for parsing expressions.
     * 
     * @param \Symfony\Component\ExpressionLanguage\TokenStream $stream
     * @param string $key
     * @return \Symfony\Component\ExpressionLanguage\ParsedExpression
     */
    private function getParsedExpression(SymfonyTokenStream $stream, $dependencies, $functions) {
        $parser = new Parser($functions);
        
        $nodes = $parser->parse(clone $stream, $dependencies);
        return new ParsedExpression('', $nodes);
    }
}
