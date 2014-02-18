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

use Methodology\ScopeResolverInterface;
use Methodology\Language\TokenStream;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\TokenStream as SymfonyTokenStream;
use Symfony\Component\ExpressionLanguage\Lexer;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\ExpressionLanguage\Parser;

/**
 * Abstract class which supports basic scope manipulation.
 * 
 * @author Tomasz Zduńczyk 
 * @implements ScopeResolverInterface
 */
abstract class AbstractScope implements ScopeResolverInterface {
    /**
     * Holds values defined in current scope.
     * 
     * @var mixed   
     */
    protected $values = array();
    
    /**
     * Reference to the parent virtual scope.
     * 
     * @var ScopeResolverInterface
     */
    protected $parent = null;

    /**
     * Holds name dependencies from defined expressions. 
     * 
     * @var array   
     */
    protected $dependencies = array();
  
    /**
     * @var Lexer
     */
    protected static $lexer = null;
   
    /**
     * {@inheritdoc}
     * 
     * @throws \InvalidArgumentException    when key is not valid  
     * @throws \OutOfBoundsException        when key is not found in current scope and there is no parent
     * 
     * @todo    Disallow infinite recursive calls.
     * @todo    Register functions of scope in ExpressionLanguage. 
     */
    public function resolve($key) {
        if($this->isNameValid($key) 
            && (isset($this->values[$key]) || array_key_exists($key, $this->values))) {
            
            $value = $this->values[$key];
            
            if($this->isExpression($value)) {
                $dependencies = array();
                
                if($this->hasDependencies($key)) {
                    foreach($this->getDependencies($key) as $dependency) {
                        try {
                            $dependencies[$dependency] = $this->resolve($dependency);
                            
                        } catch(\OutOfBoundsException $e) {
                            throw new \OutOfBoundsException("Could not resolve dependency `$dependency` of `$key` key!");
                        }
                    }   
                }
                
                $value = $this->getParsedExpression($value, $key)->getNodes()->evaluate(array(), $dependencies); 
            }
            
            return $value;
        }
        
        if(!is_null($this->parent))
            return $this->parent->resolve($key);             

        throw new \OutOfBoundsException("Could not resolve `$key` key!");
    }

    /**
     * Defines new function or variable in scope.
     *  
     * @throws \InvalidArgumentException    when key is not valid 
     * @param string    $key                identifier of value
     * @param mixed     $value              value of variable to define 
     */
    public function define($key, $value) {
        $this->isNameValid($key);
       
        if(is_string($value)) {
            $value = $this->getLexer()->tokenize($value); 
            
            $this->dependencies[$key] = $this->fetchDependenciesFrom($value);
        }
        
        $this->values[$key] = $value;
    }
    
    /**
     * Sets a new parent scope for current virtual-scope object. 
     *  
     * @param ScopeResolverInterface   $resolver   reference to new parent scope
     * @return  ScopeResolverInterface $resolver   new parent scope 
     */
    public function setParent(ScopeResolverInterface $resolver) {
        return ($this->parent = $resolver);
    }
  
    /**
     * @return bool 
     */
    public function hasDependencies($key) {
        return $this->isNameValid($key) && isset($this->dependencies[$key]);
    }
    
    /**
     * @private
     */
    public function getDependencies($key) {
        return $this->hasDependencies($key) ? $this->dependencies[$key] : null;
    }

    /**
     * Creates a new child scope and assigns it to current one.
     * 
     * @param   mixed           params passed to constructor            
     * @return  AbstractScope   child instance
     */
    protected function newChild() {
        $self_instance = (new \ReflectionClass($this))->newInstanceArgs(func_get_args());
        $self_instance->setParent($this);
        
        return $self_instance;
    }
    
    /**
     * Validates variable identifier. 
     * 
     * @throws \InvalidArgumentException    when key is not valid 
     * @param   $name                       identifier for validation 
     * @return  boolean                     true when name is valid 
     */
    private function isNameValid($name) {
        if(!is_string($name))
            throw new \InvalidArgumentException("Scope's variable name must be a string!");
        
        return true;
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
     * @return Lexer
     */
    private function getLexer() {
        if(is_null(self::$lexer))
            self::$lexer = new Lexer();

        return self::$lexer;
    }

    /**
     * Temporary solution for parsing expressions.
     * 
     * @param \Symfony\Component\ExpressionLanguage\TokenStream $stream
     * @param string $key
     * @return \Symfony\Component\ExpressionLanguage\ParsedExpression
     */
    private function getParsedExpression(SymfonyTokenStream $stream, $key) {
        $parser = new Parser(array());
        
        $nodes = $parser->parse(clone $stream, $this->getDependencies($key));
        return new ParsedExpression('', $nodes);
    }
    
    /**
     * @return bool
     */ 
    private function isExpression($value) {
        return $value instanceof SymfonyTokenStream;
    }
}
