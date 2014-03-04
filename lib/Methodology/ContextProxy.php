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
 * Proxy object which manages access to Context inside closure.
 * 
 * @author Tomasz Zduńczyk 
 */
class ContextProxy {
    /**
     * @var Context
     */
    protected $context;

    public function __construct(Context $context) {
        $this->context = $context;
    }

    /**
     * Resolves key in current scope.
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        return $this->context->resolve($key);    
    }

    /**
     * When is able to resolve $key returns its value, otherwise returns default. 
     * 
     * @param string    $key
     * @param callable  $code_block
     * @return mixed
     */
    public function _placeholder($key, $default) {
        try {
            return $this->__get($key);
        } catch(\Exception $e) {
            return $default;
        }
    }

    public function _stopDependencyChain() {
        $this->context->report(Context::REPORT_DEPENDENCY_CHAIN_STOPPED);
    }

    /**
     * @throws CollectedNotification
     */
    public function _collect($value) {
        $this->context->savePartialResult($value);    
    }
}

