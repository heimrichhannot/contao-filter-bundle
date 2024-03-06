<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\FilterBundle\Util\TwigSupportPolyfill;

class TemplateContext
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $pathname;
    /**
     * @var string|null
     */
    protected $bundle;

    /**
     * TemplateSource constructor.
     */
    public function __construct(string $name, string $path, array $pathContext)
    {
        $this->name = $name;
        $this->path = $path;
        $this->bundle = $pathContext['bundle'];
        $this->pathname = $pathContext['pathname'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Return the absolute path to the template file.
     */
    public function getPathname(): string
    {
        return $this->pathname;
    }

    /**
     * Return the name of the bundle containing the template. If null is returned, the template is within the global templates folder.
     */
    public function getBundle(): ?string
    {
        return $this->bundle;
    }
}