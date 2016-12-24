<?php
    namespace Grav\Plugin;

    use Grav\Common\Plugin;
    use Grav\Common\Utils;
    use Symfony\Component\Yaml\Yaml;

    class ImportPlugin extends Plugin
    {
        public static function getSubscribedEvents() {
            return [
                'onPageInitialized' => ['onPageInitialized', 0],
            ];
        }

        public function onPageInitialized()
        {
            if (property_exists($this->grav['page']->header(),'imports')) {

                $imports = $this->grav['page']->header()->imports;
                $parsed = [];

                if (is_array($imports)) {
                    foreach ($imports as $import) {
                        $import = static::sanitize($import);
                        if (Utils::endswith($import, '.yaml')) {
                            $key = basename($import, '.yaml');
                            $parsed[$key] = Yaml::parse($this->getContents($import));
                        } elseif (Utils::endswith($import, '.json')) {
                            $key = basename($import, '.json');
                            $parsed[$key] = json_decode($this->getContents($import));
                        }
                    }
                } else {
                    $import = static::sanitize($imports);
                    if (Utils::endswith($import, '.yaml')) {
                        $parsed = Yaml::parse($this->getContents($import));
                    } elseif (Utils::endswith($import, '.json')) {
                        $parsed = json_decode($this->getContents($import));
                    }
                }

                $this->grav['page']->header()->imports = $parsed;
            }
        }

        private function getContents($fn) {
            if (strpos($fn, '://') !== false ){
                $path = $this->grav['locator']->findResource($fn, true);
            } else {
                $path = $this->grav['page']->path() . DS . $fn;
            }
            if (file_exists($path)) {
                return file_get_contents($path);
            }
            return null;
        }

        private static function sanitize($fn) {
            $fn = trim($fn);
            $fn = str_replace('..', '', $fn);
            $fn = ltrim($fn, DS);
            return $fn;
        }

    }
