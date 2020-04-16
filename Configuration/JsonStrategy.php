<?php

namespace Skypress\Core\Configuration;

defined('ABSPATH') or die('Cheatin&#8217; uh?');

class JsonStrategy implements StrategyConfiguration
{
    public function setFinder($finder)
    {
        $this->finder = $finder;
    }

    public function getData(): array
    {
        $data = [];
        try {
            foreach ($this->finder as $file) {
                if (!$file->isReadable()) {
                    continue;
                }

                $absoluteFilePath = $file->getRealPath();
                $json = json_decode(file_get_contents($absoluteFilePath), true);
                if (null === $json) {
                    continue;
                }
                $data[$file->getFilename()] = $json;
            }

            return $data;
        } catch (\Exception $e) {
            return $data;
        }
    }
}
