<?php

namespace HeimrichHannot\FilterBundle\Util;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\System;
use Contao\ThemeModel;
use HeimrichHannot\UtilsBundle\Util\Utils;
use InvalidArgumentException;

class Polyfill
{
    public static function retrieveGlobalPageFromCurrentPageId(int $id): ?PageModel
    {
        $page = PageModel::findByPk($id);
        if (null === $page) {
            return null;
        }

        if ($page->type === 'root') {
            return $page;
        }

        $parentPages = PageModel::findParentsById($id);
        if (null === $parentPages) {
            return $page;
        }

        // get inherited values from parent pages
        foreach ($parentPages as $parentPage)
        {
            $diffValues = array_diff_assoc($parentPage->row(), $page->row());

            if (empty($diffValues)) {
                continue;
            }

            foreach ($diffValues as $key => $value) {
                if ($page->{$key}) {
                    continue;
                }
                $page->{$key} = $value;
            }
        }

        // retrieve parameters which don't come from parent pages
        $page->dateFormat = $GLOBALS['TL_CONFIG']['dateFormat'];
        $page->timeFormat = $GLOBALS['TL_CONFIG']['timeFormat'];
        $page->datimFormat = $GLOBALS['TL_CONFIG']['datimFormat'];
        static::setParametersFromLayout($page);

        return $page;
    }

    protected static function setParametersFromLayout(PageModel &$page): void
    {
        if (!$page->layout) {
            return;
        }

        // get values from layout
        $layout = LayoutModel::findByPk($page->layout);
        if (null === $layout) {
            return;
        }

        $page->template = $layout->template;
        $page->outputFormat = $layout->doctype;

        // get values from theme
        $theme = ThemeModel::findByPk($layout->pid);
        if (null === $theme) {
            return;
        }

        $page->templateGroup = $theme->templates;
    }


    /**
     * Filter an Array by given prefixes.
     *
     * @internal {@see https://github.com/heimrichhannot/contao-utils-bundle/blob/ee122d2e267a60aa3200ce0f40d92c22028988e8/src/Arrays/ArrayUtil.php#L40}
     */
    public static function filterByPrefixes(array $data = [], array $prefixes = []): array
    {
        $extract = [];

        if (!is_array($prefixes) || empty($prefixes)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($key, $prefix)) {
                    $extract[$key] = $value;
                }
            }
        }

        return $extract;
    }

    /**
     * Returns the file name without the extension from a file path.
     *
     * @param string      $path      The path string.
     * @param string|null $extension If specified, only that extension is cut
     *                               off (may contain leading dot).
     *
     * @return string The file name without extension.
     *
     * @internal {@see https://github.com/webmozart/path-util/blob/6099b5238073f87f246863fd58c2e447acfc0d24/src/Path.php#L345}
     */
    public static function getFilenameWithoutExtension(string $path, ?string $extension = null): string
    {
        if ('' === $path) {
            return '';
        }

        if (null !== $extension) {
            // remove extension and trailing dot
            return rtrim(basename($path, $extension), '.');
        }

        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Turns a path into a relative path.
     *
     * If the base path is not an absolute path, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @param string $path     A path to make relative.
     * @param string $basePath A base path.
     *
     * @return string A relative path in canonical form.
     *
     * @throws InvalidArgumentException If the base path is not absolute or if
     *                                  the given path has a different root
     *                                  than the base path.
     *
     * @since 1.0 Added method.
     * @since 2.0 Method now fails if $path or $basePath is not a string.
     */
    public static function makePathRelative(string $path, string $basePath): string
    {
        $path = static::canonicalizePath($path);
        $basePath = static::canonicalizePath($basePath);

        [$root, $relativePath] = self::splitPath($path);
        [$baseRoot, $relativeBasePath] = self::splitPath($basePath);

        // If the base path is given as absolute path and the path is already
        // relative, consider it to be relative to the given absolute path
        // already
        if ('' === $root && '' !== $baseRoot) {
            // If base path is already in its root
            if ('' === $relativeBasePath) {
                $relativePath = ltrim($relativePath, './\\');
            }

            return $relativePath;
        }

        // If the passed path is absolute, but the base path is not, we
        // cannot generate a relative path
        if ('' !== $root && '' === $baseRoot) {
            throw new InvalidArgumentException(
                sprintf(
                    'The absolute path "%s" cannot be made relative to the relative path "%s". '
                    . 'You should provide an absolute base path instead.',
                    $path, $basePath
                )
            );
        }

        // Fail if the roots of the two paths are different
        if ($baseRoot && $root !== $baseRoot) {
            throw new InvalidArgumentException(
                sprintf(
                    'The path "%s" cannot be made relative to "%s", because they have different roots ("%s" and "%s").',
                    $path, $basePath, $root, $baseRoot
                )
            );
        }

        if ('' === $relativeBasePath) {
            return $relativePath;
        }

        // Build a "../../" prefix with as many "../" parts as necessary
        $parts = explode('/', $relativePath);
        $baseParts = explode('/', $relativeBasePath);
        $dotDotPrefix = '';

        // Once we found a non-matching part in the prefix, we need to add
        // "../" parts for all remaining parts
        $match = true;

        foreach ($baseParts as $i => $basePart) {
            if ($match && isset($parts[$i]) && $basePart === $parts[$i]) {
                unset($parts[$i]);

                continue;
            }

            $match = false;
            $dotDotPrefix .= '../';
        }

        return rtrim($dotDotPrefix.implode('/', $parts), '/');
    }

    private static array $canonPathsBuffer = [];
    private static int $canonPathsBufferSize = 0;

    /**
     * Canonicalizes the given path.
     * This method is able to deal with both UNIX and Windows paths.
     *
     * @param string $path A path string.
     * @return string The canonical path.
     *
     * @internal {@see https://github.com/webmozart/path-util/blob/6099b5238073f87f246863fd58c2e447acfc0d24/src/Path.php#L245}
     */
    public static function canonicalizePath(string $path): string
    {
        if ('' === $path) {
            return '';
        }

        // This method is called by many other methods in this class. Buffer
        // the canonicalized paths to make up for the severe performance
        // decrease.
        if (isset(self::$canonPathsBuffer[$path])) {
            return self::$canonPathsBuffer[$path];
        }

        $path = str_replace('\\', '/', $path);

        [$root, $pathWithoutRoot] = self::splitPath($path);

        $parts = explode('/', $pathWithoutRoot);
        $canonicalParts = [];

        // Collapse "." and "..", if possible
        foreach ($parts as $part) {
            if ('.' === $part || '' === $part) {
                continue;
            }

            // Collapse ".." with the previous part, if one exists
            // Don't collapse ".." if the previous part is also ".."
            if ('..' === $part && count($canonicalParts) > 0
                && '..' !== $canonicalParts[count($canonicalParts) - 1]) {
                array_pop($canonicalParts);

                continue;
            }

            // Only add ".." prefixes for relative paths
            if ('..' !== $part || '' === $root) {
                $canonicalParts[] = $part;
            }
        }

        // Add the root directory again
        self::$canonPathsBuffer[$path] = $canonicalPath = $root.implode('/', $canonicalParts);
        ++self::$canonPathsBufferSize;

        $CLEANUP_THRESHOLD = 1250;
        $CLEANUP_SIZE = 1000;

        // Clean up regularly to prevent memory leaks
        if (self::$canonPathsBufferSize > $CLEANUP_THRESHOLD) {
            self::$canonPathsBuffer = array_slice(self::$canonPathsBuffer, -$CLEANUP_SIZE, null, true);
            self::$canonPathsBufferSize = $CLEANUP_SIZE;
        }

        return $canonicalPath;
    }


    /**
     * Splits a part into its root directory and the remainder.
     *
     * @param string $path The canonical path to split.
     *
     * @return string[] An array with the root directory and the remaining
     *                  relative path.
     *
     * @internal {@see https://github.com/webmozart/path-util/blob/6099b5238073f87f246863fd58c2e447acfc0d24/src/Path.php#L954}
     */
    private static function splitPath(string $path): array
    {
        if ('' === $path) {
            return array('', '');
        }

        // Remember scheme as part of the root, if any
        if (false !== ($pos = strpos($path, '://'))) {
            $root = substr($path, 0, $pos + 3);
            $path = substr($path, $pos + 3);
        } else {
            $root = '';
        }

        $length = strlen($path);

        // Remove and remember root directory
        if ('/' === $path[0]) {
            $root .= '/';
            $path = $length > 1 ? substr($path, 1) : '';
        } elseif ($length > 1 && ctype_alpha($path[0]) && ':' === $path[1]) {
            if (2 === $length) {
                // Windows special case: "C:"
                $root .= $path.'/';
                $path = '';
            } elseif ('/' === $path[2]) {
                // Windows normal case: "C:/"..
                $root .= substr($path, 0, 3);
                $path = $length > 3 ? substr($path, 3) : '';
            }
        }

        return [$root, $path];
    }
}