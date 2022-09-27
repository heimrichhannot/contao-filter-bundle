<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Session;

use HeimrichHannot\FilterBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FilterSession
{
    protected SessionInterface $session;

    /**
     * Constructor.
     */
    public function __construct(SessionInterface $session)
    {
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

        if ($this->session->isStarted() && $this->session->has($key)) {
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
