<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusGDPRPlugin\PHPUnit\Processor;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

trait WithSessionTrait
{
    private function createSession(): void
    {
        /** @var \Symfony\Component\HttpFoundation\RequestStack $requestStack */
        $requestStack = self::getContainer()->get(RequestStack::class);
        $request = Request::createFromGlobals();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack->push($request);
    }
}
