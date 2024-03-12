<?php

namespace HeimrichHannot\FilterBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use DateInterval;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Psr\Cache\InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use const JSON_FORCE_OBJECT;

/**
 * This is Polyfill of Utils v2 AbstractChoice
 * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Choice/AbstractChoice.php#L15}
 */
abstract class AbstractChoice
{
    protected array $data = [];
    protected ?FilesystemAdapter $cache = null;
    protected string $cacheKey;
    protected mixed $context;
    protected ContaoFramework $framework;
    protected RequestStack $requestStack;
    protected Utils $utils;
    protected KernelInterface $kernel;

    abstract protected function collect(): array;

    public function __construct(
        ContaoFramework $framework,
        RequestStack $requestStack,
        Utils $utils,
        KernelInterface $kernel
    )
    {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
        $this->utils = $utils;
        $this->kernel = $kernel;
    }

    public function getContext(): mixed
    {
        return $this->context;
    }

    public function setContext(mixed $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getChoices($context = []): array
    {
        if (!$context) {
            $context = [];
        }

        $this->setContext($context);

        return $this->collect();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getCachedChoices($context = [])
    {
        if (null === $context) {
            $context = [];
        }

        $request = $this->requestStack->getCurrentRequest();
        if (is_array($context) && !isset($context['locale']) && $request) {
            $context['locale'] = $request->getLocale();
        }

        $this->setContext($context);

        // disable cache while in debug mode or backend
        if ($this->kernel->isDebug() || $this->utils->container()->isBackend()) {
            return $this->getChoices($this->getContext());
        }

        $this->cacheKey = 'choice.'.preg_replace('#Choice$#', '', (new ReflectionClass($this))->getShortName());

        // add unique identifier based on context
        $json = json_encode($this->getContext(), JSON_FORCE_OBJECT);
        if (null !== $this->getContext() && false !== $json) {
            $this->cacheKey .= '.'.sha1($json);
        }

        if (!$this->cache) {
            $this->cache = new FilesystemAdapter('', 0, $this->kernel->getCacheDir());
        }

        $cache = $this->cache->getItem($this->cacheKey);

        if (!$cache->isHit() || empty($cache->get())) {
            $choices = $this->getChoices($this->getContext());

            // TODO: clear cache on delegated field save_callback
            $cache->expiresAfter(DateInterval::createFromDateString('4 hour'));
            $cache->set($choices);

            $this->cache->save($cache);
        }

        return $cache->get();
    }
}