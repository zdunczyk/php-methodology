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

/**
 * @author Tomasz Zduńczyk
 */
class Context extends AbstractScope {
    /**
     * @var callable    function wrapped by context
     */
    protected $callable;
    
    public function __construct(callable $function) {
        $this->callable = $function;
    }

    /**
     * Invokes wrapped function.
     * 
     * @return mixed
     */
    public function __invoke() {
        $callable = $this->callable->bindTo(new ContextProxy($this));
        return call_user_func_array($callable, func_get_args()); 
    }
}
