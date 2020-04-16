<?php

namespace Skypress\Core\Container;

defined('ABSPATH') or die('Cheatin&#8217; uh?');

interface ManageContainer
{
    public function get(string $name);

    public function set(string $name, $value);

    public function getServicesByTag($tag);
}
