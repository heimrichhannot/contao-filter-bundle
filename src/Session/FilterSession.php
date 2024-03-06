<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Session;

use HeimrichHannot\FilterBundle\Form\FilterType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FilterSession
{
    protected RequestStack $requestStack;

    /**
     * Constructor.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Set the filter data for a given filter key.
     */
    public function setData(string $key, array $data = []): void
    {
        $this->requestStack->getSession()->set($key, $data);
    }

    /**
     * Get the filter data for a given key.
     */
    public function getData(string $key): array
    {
        $session = $this->requestStack->getSession();
        $data = [];

        if ($session->isStarted() && $session->has($key)) {
            $data = $session->get($key);
        }

        return !is_array($data) ? [$data] : $data;
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
        if (is_array($data)) {
            $data = array_filter($data);
        }

        return !empty($data);
    }

    /**
     * Reset the filter data for a given key.
     */
    public function reset(string $key): void
    {
        $session = $this->requestStack->getSession();
        if ($session->has($key)) {
            $session->remove($key);
        }
    }
}
