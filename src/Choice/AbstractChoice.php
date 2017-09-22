<?php
/**
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Choice;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

abstract class AbstractChoice
{
    /**
     * Context data
     * @var array
     */
    protected $data = [];

    /**
     * Current file cache
     *
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * Current cache key name
     *
     * @var string
     */
    protected $cacheKey;


    public function __construct()
    {
        $this->cache = new FilesystemAdapter('', 0, \System::getContainer()->get('kernel')->getCacheDir());
    }

    public static function create()
    {
        return new static();
    }

    public function getChoices()
    {
        return $this->collectChoices();
    }

    public function getCachedChoices()
    {
        $this->cacheKey = 'choice.' . str_replace('Choice', '', (new \ReflectionClass($this))->getShortName());

        $cache = $this->cache->getItem($this->cacheKey);

        if (!$cache->isHit() || empty($cache->get())) {
            $choices = $this->getChoices();

            if (!is_array($choices)) {
                $choices = [];
            }

            // TODO: clear cache on delegated field save_callback
            $cache->expiresAfter(\DateInterval::createFromDateString('4 hour'));
            $cache->set($choices);

            $this->cache->save($cache);
        }

        return $cache->get();
    }

    /**
     * @return array
     */
    abstract protected function collectChoices();
}