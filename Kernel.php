<?php

namespace Skypress\Core;

defined('ABSPATH') or die('Cheatin&#8217; uh?');

use Skypress\Core\Container\ManageContainer;
use Skypress\Core\Container\ContainerSymfony;
use Skypress\Core\Hooks\ExecuteHooksBackend;
use Skypress\Core\Hooks\ExecuteHooksFrontend;
use Skypress\Core\Hooks\ExecuteHooks;
use Skypress\Core\Hooks\ActivationHook;
use Skypress\Core\Hooks\DeactivationHook;

abstract class Kernel
{
    protected static $container = null;

    protected static $data = ['slug' => null, 'file' => null];

    protected static $options = [
        'custom-post-type' => false,
    ];

    public static function setContainer(ManageContainer $container)
    {
        self::$container = self::getDefaultContainer();
    }

    protected static function getDefaultContainer()
    {
        return new ContainerSymfony();
    }

    public static function getContainer()
    {
        if (null === self::$container) {
            self::$container = self::getDefaultContainer();
        }

        return self::$container;
    }

    public static function handleHooks()
    {
        foreach (self::getContainer()->getServicesByTag('hooks') as $id => $tags) {
            $class = self::getContainer()->getBuilder()->get($id);

            switch (true) {
                case $class instanceof ExecuteHooksBackend:
                    if (is_admin()) {
                        $class->hooks();
                    }
                    break;

                case $class instanceof ExecuteHooksFrontend:
                    if (!is_admin()) {
                        $class->hooks();
                    }
                    break;

                case $class instanceof ExecuteHooks:
                    $class->hooks();
                    break;
            }
        }
    }

    public static function handleHooksPlugin()
    {
        switch (current_filter()) {
            case 'plugins_loaded':
                self::handleHooks();
                break;
            case 'activate_' . $slug . '/' . $slug . '.php':
                foreach (self::getContainer()->getServicesByTag('hooks') as $id => $tags) {
                    $class = self::getContainer()->get($id);
                    if ($class instanceof ActivationHook) {
                        $class->activation();
                    }
                }
                break;
            case 'deactivate_' . $slug . '/' . $slug . '.php':
                $class = self::getContainer()->get($id);
                if ($class instanceof DeactivationHook) {
                    $class->activation();
                }
                break;
        }
    }

    /**
     * Build module custom post type.
     */
    protected static function buildCustomPostType()
    {
        self::getContainer()->set('LoaderCustomPostType', '\Skypress\CustomPostType\Configuration\Loader', [
            self::getContainer()->get('LoaderConfiguration'),
        ]);
        self::getContainer()->getBuilder()->getDefinition('LoaderCustomPostType')->setShared(false);

        self::getContainer()->set('RegisterPostType', '\Skypress\CustomPostType\Hooks\RegisterPostType', [
            self::getContainer()->get('LoaderCustomPostType'),
        ]);

        // @TODO : Too symfony related
        self::getContainer()->getBuilder()->getDefinition('RegisterPostType')
            ->addTag('hooks');
    }

    /**
     * Build module taxonomy.
     */
    protected static function buildTaxonomy()
    {
        self::getContainer()->set('LoaderTaxonomy', '\Skypress\Taxonomy\Configuration\Loader', [
            self::getContainer()->get('LoaderConfiguration'),
        ]);

        self::getContainer()->getBuilder()->getDefinition('LoaderTaxonomy')->setShared(false);

        self::getContainer()->set('RegisterTaxonomy', '\Skypress\Taxonomy\Hooks\RegisterTaxonomy', [
            self::getContainer()->get('LoaderTaxonomy'),
        ]);

        // @TODO : Too symfony related
        self::getContainer()->getBuilder()->getDefinition('RegisterTaxonomy')
            ->addTag('hooks');
    }

    /**
     * Build module headless.
     */
    protected static function buildHeadless()
    {
        self::getContainer()->set('ApiMenu', '\Skypress\Headless\Hooks\Api\Menu', [
            \Skypress\Headless\Settings::getBaseEndpoint(),
        ]);

        // @TODO : Too symfony related
        self::getContainer()->getBuilder()->getDefinition('ApiMenu')
            ->addTag('hooks');
    }

    /**
     * Build module menu.
     */
    protected static function buildMenu()
    {
        self::getContainer()->set('LoaderMenu', '\Skypress\Menu\Configuration\Loader', [
            self::getContainer()->get('LoaderConfiguration'),
        ]);

        self::getContainer()->getBuilder()->getDefinition('LoaderMenu')->setShared(false);

        self::getContainer()->set('RegisterMenu', '\Skypress\Menu\Hooks\RegisterMenu', [
            self::getContainer()->get('LoaderMenu'),
        ]);

        // @TODO : Too symfony related
        self::getContainer()->getBuilder()->getDefinition('RegisterMenu')
            ->addTag('hooks');
    }

    /**
     * Build Skypress Container.
     */
    protected static function buildContainer()
    {
        self::getContainer()->set('LoaderConfiguration', 'Skypress\Core\Configuration\Loader');
        self::getContainer()->getBuilder()->getDefinition('LoaderConfiguration')->setShared(false);

        if (true === self::$options['custom-post-type']) {
            self::buildCustomPostType();
        }

        if (true === self::$options['taxonomy']) {
            self::buildTaxonomy();
        }

        if (true === self::$options['menu']) {
            self::buildMenu();
        }

        if (true === self::$options['headless']) {
            self::buildHeadless();
        }
    }

    /**
     * @return Kernel
     */
    public static function execute($type = KernelTypeExecution::DEFAULT, $data, $options = [])
    {
        self::$options = array_merge(self::$options, $options);

        self::buildContainer();

        if (KernelTypeExecution::DEFAULT === $type) {
            self::handleHooks();

            return;
        }

        if (KernelTypeExecution::PLUGIN === $type && isset($data['file']) && null !== $data['file']) {
            self::$data = array_merge($data, self::$data);
            add_action('plugins_loaded', [__CLASS__, 'handleHooksPlugin']);
            register_activation_hook($data['file'], [__CLASS__, 'handleHooksPlugin']);
            register_deactivation_hook($data['file'], [__CLASS__, 'handleHooksPlugin']);
        }
    }
}
