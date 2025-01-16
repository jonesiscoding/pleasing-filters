<?php

namespace DevCoding\Pleasing\Filters\Options;

interface ImportPathsInterface
{
  /**
   * @param string $path
   * @param bool $prepend
   * @return ImportPathsInterface
   */
  public function addImportPath(string $path, bool $prepend = false);
}