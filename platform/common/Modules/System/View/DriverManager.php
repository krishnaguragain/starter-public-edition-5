<?php

namespace Common\Modules\System\View;

class DriverManager
{
    protected static $sharedConfig = [];

    public function __construct()
    {
        if (empty(self::$sharedConfig)) {

            $this->loadValidDrivers();
            $this->loadDriverTypes();
            $this->loadFileExtensions();
            $this->loadDriverClasses();
        }
    }

    protected function loadValidDrivers()
    {
        $config = config('Views')->config;

        $options = $config['validDrivers'] ?? [];

        if (empty($options)) {
            $options = [];
        }

        if (!is_array($options)) {
            $options = [$options];
        }

        $items = [];

        foreach ($options as $item) {

            if (is_array($item)) {

                $items = array_merge($items, $item);

            } else {

                $items[$item] = true;
            }
        }

        $result = [];

        foreach ($items as $item => $enabled) {

            if (!is_string($item)) {
                continue;
            }

            if (!empty($enabled)) {
                $result[] = $item;
            }
        }

        self::$sharedConfig['validDrivers'] = $result;
    }

    protected function loadDriverTypes()
    {
        $config = config('Views')->config;

        $options = $config['driverTypes'] ?? [];

        if (empty($options)) {
            $options = [];
        }

        $result = [];

        foreach ($options as $key => $value) {

            if (!is_string($key) && !is_string($value)) {
                continue;
            }

            if (!in_array($key, self::$sharedConfig['validDrivers'])) {
               continue;
            }

            $result[$key] = $value;
        }

        self::$sharedConfig['driverTypes'] = $result;
    }

    protected function loadFileExtensions()
    {
        $config = config('Views')->config;

        $options = $config['fileExtensions'] ?? [];

        if (empty($options)) {
            $options = [];
        }

        $result = [];

        foreach ($options as $key => $value) {

            if (!is_string($key)) {
                continue;
            }

            if (!in_array($key, self::$sharedConfig['validDrivers'])) {
               continue;
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            foreach ($value as & $item) {
                $item = ltrim($item, '.');
            }

            unset($item);

            $result[$key] = $value;
        }

        self::$sharedConfig['fileExtensions'] = $result;
    }

    protected function loadDriverClasses()
    {
        $config = config('Views')->config;

        $options = $config['driverClasses'] ?? [];

        if (empty($options)) {
            $options = [];
        }

        $result = [];

        foreach ($options as $key => $value) {

            if (!is_string($key) && !is_string($value)) {
                continue;
            }

            if (!in_array($key, self::$sharedConfig['validDrivers'])) {
               continue;
            }

            $result[$key] = $value;
        }

        self::$sharedConfig['driverClasses'] = $result;
    }

    public function getValidDrivers() {

        return self::$sharedConfig['validDrivers'];
    }

    public function isValidDriver($driverName) {

        return in_array((string) $driverName, self::$sharedConfig['validDrivers']);
    }

    public function getDriverTypes()
    {
        return self::$sharedConfig['driverTypes'];
    }

    public function getDriverType($driverName)
    {
        $driverName = (string) $driverName;

        return isset(self::$sharedConfig['driverTypes'][$driverName])
            ? self::$sharedConfig['driverTypes'][$driverName]
            : null;
    }

    public function getFileExtensions($driverName = null)
    {
        $driverName = (string) $driverName;

        if ($driverName == '') {

            return self::$sharedConfig['fileExtensions'];
        }

        return isset(self::$sharedConfig['fileExtensions'][$driverName])
           ? self::$sharedConfig['fileExtensions'][$driverName]
           : [];
    }

    public function hasFileExtension($driverName)
    {
        $extensions = $this->getFileExtensions($driverName);

        return !empty($extensions);
    }

    public function getDriversByFileExtensions($extension = null)
    {
        static $drivers = null;

        if ($drivers === null) {

            $drivers = [];
            $allExtensions = $this->getFileExtensions();

            foreach ($allExtensions as $driverName => $extensions) {

                foreach ($extensions as $ext) {
                    $drivers[$ext] = $driverName;
                }
            }
        }

        if ($extension != '') {

            return $drivers[$extension] ?? null;
        }

        return $drivers;
    }

    public function getDriverClasses()
    {
        return self::$sharedConfig['driverClasses'];
    }

    public function getDriverClass($driverName)
    {
        $driverName = (string) $driverName;

        return isset(self::$sharedConfig['driverClasses'][$driverName])
            ? self::$sharedConfig['driverClasses'][$driverName]
            : null;
    }

    public function parseDriverOptions($options)
    {
        if (!is_array($options)) {

            $options = (string) $options;
            $options = $options != '' ? [$options] : [];
        }

        $driverOptions = [];

        if (!empty($options)) {

            foreach ($options as $key => $value) {

                if (is_string($key)) {

                    if (in_array($key, self::$sharedConfig['validDrivers'])) {

                        $driverOptions[] = [
                            'name' => $key,
                            'type' => $this->getDriverType($key),
                            'hasFileExtension' => $this->hasFileExtension($key),
                            'options' => $value
                        ];

                    } else {

                        $driverOptions[$key] = $value;
                    }

                } elseif (is_string($value)) {

                    if (in_array($value, self::$sharedConfig['validDrivers'])) {

                        $driverOptions[] = [
                            'name' => $value,
                            'type' => $this->getDriverType($value),
                            'hasFileExtension' => $this->hasFileExtension($value),
                            'options' => []
                        ];

                    } else {

                        $driverOptions[] = $value;
                    }
                }
            }
        }

        return $driverOptions;
    }

    protected function detectDriverFromFilename($view, & $detectedExtension = null, & $detectedName = null)
    {
        $drivers = $this->getDriversByFileExtensions();

        $view = (string) $view;
        $detectedExtension = null;
        $detectedName = null;

        // Test whether a pure extension was given.
        if (isset($drivers[$view])) {

            $detectedExtension = $view;

            return $drivers[$view];
        }

        foreach ($drivers as $key => $value) {

            $k = preg_quote($key);

            if (preg_match('/.*\.('.$k.')$/', $view, $matches)) {

                $detectedExtension = $matches[1];
                $detectedName = preg_replace('/(.*)\.'.$k.'$/', '$1', pathinfo($view, PATHINFO_BASENAME));

                return $value;
            }
        }

        $detectedExtension = pathinfo($view, PATHINFO_EXTENSION);
        $detectedName = pathinfo($view, PATHINFO_FILENAME);

        return null;
    }

    public function parseViewOptions(string $view, array $options = null, bool $saveData = null)
    {
        $originalOptions = $options;
        $driverChain = $this->parseDriverOptions($options);

        // This is to be the first driver from the chain.
        $driver = null;
        $driverName = null;

        if (!empty($driverChain)) {

            if ($driverChain[0]['hasFileExtension']) {

                $driver = $driverChain[0];
                $driverName = $driver['name'];
                $driverChain = array_slice($driverChain, 1);
            }
        }

        $viewExtension = pathinfo($view, PATHINFO_EXTENSION);
        $viewHasExtension = $viewExtension != '' && $viewExtension != 'html';

        $detectedExtension = null;
        $detectedName = null;
        $detectedDriverName = $this->detectDriverFromFilename($view, $detectedExtension, $detectedName);

        if (
            $viewHasExtension
            &&
            $detectedDriverName != ''
            &&
            $driverName != ''
            &&
            $detectedDriverName != $driverName
        ) {

            // Filename and options target different drivers.
            throw \CodeIgniter\View\Exceptions\ViewException\ViewException::forInvalidFile($view);
        }

        $fileName = $detectedName;

        if ($detectedDriverName != '' && $detectedExtension != '') {

            $extensions = [$detectedExtension];

            if ($driverName == '') {

                $driver = [
                    'name' => $detectedDriverName,
                    'type' => $this->getDriverType($detectedDriverName),
                    'hasFileExtension' => $this->hasFileExtension($detectedDriverName),
                    'options' => []
                ];
            }

        } elseif ($driverName != '') {

            $extensions = $this->getFileExtensions($driverName);

        } else {

            $extensions = [];

            if ($detectedExtension != 'php') {

                $allExtensions = $this->getFileExtensions();

                foreach ($allExtensions as $key => $value) {

                    foreach ($value as $ext) {
                        $extensions[] = $ext;
                    }
                }
            }

            $extensions[] = 'php';
        }

        $result = compact(
            'originalOptions',
            'options',
            'saveData',
            'driverChain',
            'driver',
            'fileName',
            'extensions'
        );

        return $result;
    }

    public function createRenderer($driverName)
    {
        $driverName = (string) $driverName;

        if ($driverName == '') {
            throw new \InvalidArgumentException('No renderer-driver name has been provided.');
        }

        if (!in_array($driverName, self::$sharedConfig['validDrivers'])) {
            throw new \InvalidArgumentException('Invalid renderer-driver name has been provided.');
        }

        $class = (string) $this->getDriverClass($driverName);

        if ($class == '') {
            throw new \RuntimeException('No class name of renderer-driver has been configured.');
        }

        return new $class();
    }

}
