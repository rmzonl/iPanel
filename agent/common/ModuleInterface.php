<?php
namespace iPanel\Agent\Common;

interface ModuleInterface
{
    /**
     * Whitelisted method names this module will accept.
     * Protocol::handle() rejects any method not in this list.
     */
    public function allowedMethods(): array;

    /**
     * Check whether the given method is exposed.
     */
    public function isAllowed(string $method): bool;
}
