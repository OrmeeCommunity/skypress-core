<?php

namespace Skypress\Core\Configuration;

defined('ABSPATH') or die('Cheatin&#8217; uh?');

use Symfony\Component\Finder\Finder;

class Loader implements LoaderConfiguration
{
    public function __construct()
    {
        $this->setDirectoryConfiguration(WPMU_PLUGIN_DIR . '/skypress');
        $this->typeStrategy = TypeStrategy::JSON;
        $this->setDefaultFinder();
    }

    protected function setDefaultFinder()
    {
        $this->setFinder(new Finder());
    }

    public function setFinder($finder)
    {
        $this->finder = $finder;

        return $this;
    }

    public function getFinder()
    {
        return $this->finder;
    }

    public function setDirectoryConfiguration(string $directory)
    {
        $this->directoryConfiguration = $directory;

        return $this;
    }

    public function getDirectoryConfiguration()
    {
        return $this->directoryConfiguration;
    }

    public function getDirectoryTypeData()
    {
        return $this->typeData;
    }

    public function setDirectoryTypeData(string $typeData)
    {
        $this->typeData = $typeData;

        return $this;
    }

    protected function getDirectoryData()
    {
        return sprintf('%s/%s', $this->getDirectoryConfiguration(), $this->getDirectoryTypeData());
    }

    public function getExtension()
    {
        switch ($this->typeStrategy) {
            case TypeStrategy::JSON:
                return '.json';
                break;
        }
    }

    public function getStrategy()
    {
        try {
            $this->getFinder()->files()->in($this->getDirectoryData())->name(sprintf('*%s', $this->getExtension()));
        } catch (\Exception $e) {
            return null;
        }

        if (!$this->getFinder()->hasResults()) {
            return null;
        }

        $strategy = null;
        if (TypeStrategy::JSON === $this->typeStrategy) {
            $strategy = new JsonStrategy();
        }

        $strategy->setFinder($this->getFinder());

        return $strategy;
    }

    public function getData()
    {
        $strategy = $this->getStrategy();
        if (null === $strategy) {
            return [];
        }

        return $strategy->getData();
    }
}
