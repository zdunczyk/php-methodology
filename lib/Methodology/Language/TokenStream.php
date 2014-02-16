<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

namespace Methodology\Language;

use Symfony\Component\ExpressionLanguage\TokenStream as TokenStreamSymfony;

/**
 * Wraps Symfony's TokenStream to get tokens from its __toString.
 * Temporary hack.
 *
 * @author  Tomasz Zduńczyk
 * @see     Symfony\Component\ExpressionLanguage\TokenStream::__toString() 
 */
class TokenStream { 
    /**
     * Size of data chunk in stringified SEL's TokenStream.
     */
    const CHUNK_SIZE = 3;
    
    /**
     * Index of type id in chunk.
     */
    const CHUNK_TYPE_POS = 1;
    
    /**
     * Index of value in chunk.
     */
    const CHUNK_VALUE_POS = 2;
    
    /**
     * Holds tokens of wrapped TokenStream.
     * @var array   
     */
    private $tokens = array(); 
   
    /**
     * Initializes $tokens array with values from stringified stream.
     * 
     * @param \Symfony\Component\ExpressionLanguage\TokenStream $stream
     */
    public function __construct(TokenStreamSymfony $stream) {
        $cnter = 0;
        
        foreach(preg_split('/\s+/', trim((string)$stream)) as $word) {
            $chunk_idx = (int)($cnter / self::CHUNK_SIZE);
            
            if(!isset($this->tokens[$chunk_idx])) {
                $this->tokens[$chunk_idx] = new \stdClass();
            }
            
            switch(($cnter++) % self::CHUNK_SIZE) {
                case self::CHUNK_TYPE_POS: {
                    if($word === 'END') {
                        unset($this->tokens[$chunk_idx]);
                        return;
                    }
                    
                    $this->tokens[$chunk_idx]->type = $word;
                    break;
                }
                case self::CHUNK_VALUE_POS: {
                    $this->tokens[$chunk_idx]->value = $word;
                }
            }
        } 
    }

    /**
     * Getter for tokens array.
     *  
     * @return array    wrapped stream's tokens
     */
    public function getTokens() {
        return $this->tokens;
    }

}

