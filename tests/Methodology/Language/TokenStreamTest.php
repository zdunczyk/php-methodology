<?php

/*
 * This file is part of Methodology.
 * 
 * (c) Tomasz Zduńczyk <tomasz@zdunczyk.org>
 * 
 * For the full copyright and license information, please view the LICENSE 
 * file that was distributed with this source code.
 */

use Methodology\Language\TokenStream;
use Symfony\Component\ExpressionLanguage\TokenStream as SymfonyTokenStream;
use Symfony\Component\ExpressionLanguage\Token;

class TokenStreamTest extends PHPUnit_Framework_TestCase {
    
    /**
     * Provides tokens for SEL's stream. 
     */
    public function tokenProvider() {
        $vals = array(
            '23' => Token::NUMBER_TYPE,
            'qwerty' => Token::NAME_TYPE,
            '+' => Token::OPERATOR_TYPE,
            '.' => Token::PUNCTUATION_TYPE,
            'asdasd' => Token::NAME_TYPE
        );
        
        $tokens = array();
        $i = 1;
        
        foreach($vals as $val => $type) {
            $tokens[] = new Token($type, $val, $i++); 
        }
        
        return array(
                /* #0 */
                array($tokens, array_keys($vals))
        );
    }

    /**
     * @dataProvider    tokenProvider
     * @covers          Methodology\Language\TokenStream::__construct
     * @covers          Methodology\Language\TokenStream::getTokens
     */
    public function testConstruction($tokens, $values) {
        $token_stream = new TokenStream(new SymfonyTokenStream($tokens));

        foreach($token_stream->getTokens() as $key => $token) {
            $this->assertEquals($token->value, $values[$key]);
        }
    } 

    /**
     * @dataProvider    tokenProvider
     * @covers          Methodology\Language\TokenStream::getIterator 
     */ 
    public function testIteratingThroughNames($tokens) {
        $token_stream = new TokenStream(new SymfonyTokenStream($tokens)); 
        
        foreach($token_stream->getIterator() as $token) {
            $this->assertEquals($token->type, strtoupper(Token::NAME_TYPE));        
        }  
    }
}