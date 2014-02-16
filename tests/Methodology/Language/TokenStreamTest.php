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
        return array(
                /* #0 */
                array(
                    array(
                        new Token(Token::NUMBER_TYPE, '23', 1),
                        new Token(Token::NAME_TYPE, 'qwerty', 4),
                        new Token(Token::OPERATOR_TYPE, '+', 8)
                    ),
                    array(
                        '23',
                        'qwerty',
                        '+' 
                    )
                )
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
}