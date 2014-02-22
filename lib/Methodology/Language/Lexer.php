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

use Symfony\Component\ExpressionLanguage\Lexer as SymfonyLexer;

class Lexer extends SymfonyLexer {

    /**
     * {@inheritdoc}
     * Allow special variables prefixed with dollar sign.
     * 
     * @param string    $expression
     */
    public function tokenize($expression) {
        return parent::tokenize(preg_replace('/[$](\d+)/','_${1}', $expression)); 
    }
}

