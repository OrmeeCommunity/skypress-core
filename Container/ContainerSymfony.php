<?php

namespace Skypress\Core\Container;

defined('ABSPATH') or die('Cheatin&#8217; uh?');

use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContainerSymfony implements ManageContainer
{
    protected $builder;

    public function __construct()
    {
        $this->builder = new ContainerBuilder();
    }

    public function getBuilder()
    {
        return $this->builder;
    }

    public function get(string $name)
    {
        return $this->getBuilder()->get($name);
    }

    public function set(string $name, $value, $args = [])
    {
        $register = $this->getBuilder()->register($name, $value);
        if (!empty($args)) {
            foreach ($args as $item) {
                $register->addArgument($item);
            }
        }
    }

    public function getServicesByTag($tag)
    {
        return $this->getBuilder()->findTaggedServiceIds($tag);
    }
}
