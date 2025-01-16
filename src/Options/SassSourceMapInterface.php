<?php

namespace DevCoding\Pleasing\Filters\Options;

use DevCoding\Pleasing\Filters\AbstractSassDriverFilter;

interface SassSourceMapInterface
{
  /**
   * @param bool $sourceMap
   *
   * @return AbstractSassDriverFilter|SassSourceMapInterface
   */
  public function setSourceMap(bool $sourceMap): AbstractSassDriverFilter;
}
