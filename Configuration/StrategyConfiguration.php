<?php

namespace Skypress\Core\Configuration;

defined('ABSPATH') or die('Cheatin&#8217; uh?');

interface StrategyConfiguration
{
    public function setFinder($finder);

    public function getData(): array;
}
