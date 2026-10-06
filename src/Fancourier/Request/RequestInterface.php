<?php

declare(strict_types=1);

namespace Fancourier\Request;

use Fancourier\Auth;
use Fancourier\Response\ResponseInterface;

interface RequestInterface
{
    public function authenticate(Auth $auth): static;
    public function setVerify(bool $verifyHost = true, bool $verifyPeer = true): static;
    public function setTimeout(int $conTimeout = 3, int $timeout = 6): static;
    public function send(): ResponseInterface;
    public function pack(): array;
}
