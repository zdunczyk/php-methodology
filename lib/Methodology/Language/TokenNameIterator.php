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

use Symfony\Component\ExpressionLanguage\Token;

/**
 * Allows iterating through name-tokens in TokenStream.
 * 
 * @author Tomasz Zduńczyk
 */
class TokenNameIterator extends \ArrayIterator {
  
    /**
     * Moves pointer to first name-token.
     */
    public function rewind() {
        parent::rewind();
        $this->gotoName();
    } 

    /**
     * Moves pointer to next name-token. 
     */
    public function next() {
        parent::next();
        $this->gotoName();
    }

    /**
     * Calls next until current element is name-token.
     */
    private function gotoName() {
        if($this->valid() && ($this->current()->type !== strtoupper(Token::NAME_TYPE)))
            $this->next();
    }
}