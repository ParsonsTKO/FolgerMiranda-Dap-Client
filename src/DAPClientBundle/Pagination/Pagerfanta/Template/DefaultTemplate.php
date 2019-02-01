<?php
/*
 * This file is part of the Pagerfanta package.
 *
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DAPClientBundle\Pagination\Pagerfanta\Template;

use Pagerfanta\View\Template\TwitterBootstrap4Template;
/**
 * DefaultTemplate
 */
class DefaultTemplate extends TwitterBootstrap4Template
{
    protected function linkLi($class, $href, $text, $rel = null)
    {
        $rel = $rel ? sprintf(' rel="%s"', $rel) : '';
        return sprintf('<li class="%s"><a href="%s"%s>%s</a></li>', $class, $href, $rel, $text);
    }
    protected function spanLi($class, $text)
    {
        return sprintf('<li class="%s"><a>%s</a></li>', $class, $text);
    }
}
