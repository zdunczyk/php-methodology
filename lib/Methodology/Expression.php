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
    public function call(array $arguments = array(), Scope $scope = null, Report &$report = null) {
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
                return $scope->evaluate($this->tokens, $this->dependencies, $positional_params, $report);
            
            return null;
        } catch(\OutOfBoundsException $e) {
            /** @todo add proper message */
            throw $e;
        }
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
}
