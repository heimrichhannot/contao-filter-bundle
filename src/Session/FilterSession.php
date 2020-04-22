<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Session;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\FilterBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FilterSession
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * Symfony session object.
     *
     * @var SessionInterface
     */
    protected $session;

    /**
     * Constructor.
     */
    public function __construct(ContaoFrameworkInterface $framework, SessionInterface $session)
    {
        $this->framework = $framework;
        $this->session = $session;
    }

    /**
     * Set the filter data for a given filter key.
     */
    public function setData(string $key, array $data = [])
    {
        $this->session->set($key, $data);
    }

    /**
     * Get the filter data for a given key.
     */
    public function getData(string $key): array
    {
        $data = [];

        if ($this->session->has($key)) {
            $data = $this->session->get($key);
        }

        return !\is_array($data) ? [$data] : $data;
    }

    /**
     * Has the filter data for a given key.
     * Use this function if you want to know if the form contains any user`s inputs.
     */
    public function hasData(string $key): bool
    {
        $data = $this->getData($key);

        if (isset($data[FilterType::FILTER_ID_NAME])) {
            unset($data[FilterType::FILTER_ID_NAME]);
        }

        if (isset($data[FilterType::FILTER_REFERRER_NAME])) {
            unset($data[FilterType::FILTER_REFERRER_NAME]);
        }

        // remove empty values
        if (\is_array($data)) {
            $data = array_filter($data);
        }

        return !empty($data);
    }

    /**
     * Reset the filter data for a given key.
     */
    public function reset(string $key)
    {
        if ($this->session->has($key)) {
            $this->session->remove($key);
        }
    }
}
