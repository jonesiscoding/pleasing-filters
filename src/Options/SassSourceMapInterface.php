<?php

namespace XQ\Pleasing\Filter\Options;

use XQ\Pleasing\Filter\AbstractSassDriverFilter;

interface SassSourceMapInterface
{
  /**
   * @param bool $sourceMap
   *
   * @return AbstractSassDriverFilter|SassSourceMapInterface
   */
  public function setSourceMap(bool $sourceMap): AbstractSassDriverFilter;
}
