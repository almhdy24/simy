<?php
namespace Simy\Core;

use League\Container\Container as LeagueContainer;

class Container extends LeagueContainer
{
    public function __construct()
    {
        parent::__construct();
        $this->delegate(new \League\Container\ReflectionContainer());
    }
}