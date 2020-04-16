<?php

namespace Skypress\Core\Configuration;

defined('ABSPATH') or die('Cheatin&#8217; uh?');

use Symfony\Component\Finder\Finder;

class Loader implements LoaderConfiguration
{
    public function __construct(Finder $finder)
    {
        $this->setDirectoryConfiguration(WPMU_PLUGIN_DIR . '/skypress');
        $this->typeStrategy = TypeStrategy::JSON;
        $this->finder = $finder;
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

        return $typeData;
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
        $this->finder->files()->in($this->getDirectoryData())->name(sprintf('*%s', $this->getExtension()));

        if (!$this->finder->hasResults()) {
            return null;
        }

        $strategy = null;
        if (TypeStrategy::JSON === $this->typeStrategy) {
            $strategy = new JsonStrategy();
        }

        $strategy->setFinder($this->finder);

        return $strategy;
    }

    public function getData()
    {
        return $this->getStrategy()->getData();
    }
}
