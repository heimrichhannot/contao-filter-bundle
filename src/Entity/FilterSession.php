<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\FilterBundle\Entity;


use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FilterSession
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Symfony session object
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework, SessionInterface $session)
    {
        $this->framework = $framework;
        $this->session   = $session;
    }

    /**
     * Set the filter data for a given filter key
     * @param string $key
     * @param array $data
     */
    public function setData(string $key, array $data = [])
    {
        $this->session->set($key, $data);
    }

    /**
     * Get the filter data for a given key
     * @param string $key
     * @return array
     */
    public function getData(string $key): array
    {
        $data = [];

        if ($this->session->has($key)) {
            $data = $this->session->get($key);
        }

        return !is_array($data) ? [$data] : $data;
    }

    /**
     * Has the filter data for a given key
     * @param string $key
     * @return bool
     */
    public function hasData(string $key): bool
    {
        return !empty($this->getData($key));
    }

    /**
     * Reset the filter data for a given key
     * @param string $key
     */
    public function reset(string $key)
    {
        if ($this->session->has($key)) {
            $this->session->remove($key);
        }
    }
}