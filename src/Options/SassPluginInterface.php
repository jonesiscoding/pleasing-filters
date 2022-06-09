<?php

namespace XQ\Pleasing\Filter\Options;

use XQ\Pleasing\Filter\AbstractSassDriverFilter;

interface SassPluginInterface
{
  /**
   * @param array $paths
   *
   * @return AbstractSassDriverFilter|SassPluginInterface
   * @throws \Exception
   */
  public function setPluginPaths(array $paths): AbstractSassDriverFilter;

  /**
   * @param string $path
   * @param bool $prepend
   *
   * @return AbstractSassDriverFilter|SassPluginInterface
   */
  public function addPluginPath(string $path, bool $prepend = false): AbstractSassDriverFilter;
}
