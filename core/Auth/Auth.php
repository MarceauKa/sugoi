<?php

namespace Core\Auth;

use Core\Http\Session;

class Auth
{
    /** @var Session $session */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }
}
