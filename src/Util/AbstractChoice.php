<?php

namespace HeimrichHannot\FilterBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * This is Polyfill of Utils v2 AbstractChoice
 * @internal https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Choice/AbstractChoice.php#L15
 */
abstract class AbstractChoice
{
    protected array $data = [];
    protected ?FilesystemAdapter $cache = null;
    protected string $cacheKey;
    protected mixed $context;
    protected ContaoFramework $framework;

    abstract protected function collect(): array;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
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

        if (\is_array($context) && !isset($context['locale']) && ($request = System::getContainer()->get('request_stack')->getCurrentRequest())) {
            $context['locale'] = $request->getLocale();
        }

        $this->setContext($context);

        // disable cache while in debug mode or backend
        if (true === System::getContainer()->getParameter('kernel.debug') || System::getContainer()->get('huh.utils.container')->isBackend()) {
            return $this->getChoices($this->getContext());
        }

        $this->cacheKey = 'choice.'.preg_replace('#Choice$#', '', (new \ReflectionClass($this))->getShortName());

        // add unique identifier based on context
        if (null !== $this->getContext() && false !== ($json = json_encode($this->getContext(), \JSON_FORCE_OBJECT))) {
            $this->cacheKey .= '.'.sha1($json);
        }

        if (!$this->cache) {
            $this->cache = new FilesystemAdapter('', 0, System::getContainer()->get('kernel')->getCacheDir());
        }

        $cache = $this->cache->getItem($this->cacheKey);

        if (!$cache->isHit() || empty($cache->get())) {
            $choices = $this->getChoices($this->getContext());

            // TODO: clear cache on delegated field save_callback
            $cache->expiresAfter(\DateInterval::createFromDateString('4 hour'));
            $cache->set($choices);

            $this->cache->save($cache);
        }

        return $cache->get();
    }
}