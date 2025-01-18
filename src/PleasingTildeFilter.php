<?php

namespace DevCoding\Pleasing\Filters;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;

/**
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @version Pleasing Filters v3.0 (https://github.com/jonesiscoding/pleasing-filters)
 * @license MIT (https://github.com/jonesiscoding/pleasing-filters/blob/master/LICENSE)
 * @package DevCoding\Pleasing\Filters
 */
class PleasingTildeFilter implements FilterInterface
{
  /** @var string */
  protected $projectDir;

  public function filterLoad(AssetInterface $asset)
  {
    $assetPath = $asset->getSourcePath();
    $ext = ($assetPath) ? pathinfo($assetPath, PATHINFO_EXTENSION) : false;

    $content = $asset->getContent();

    switch ($ext)
    {
      case "less":
      case "sass":
      case "scss":
        $output = $this->fixTildePath($content, $ext);
        break;
      default:
        // Extension not recognized, or could not be read.  Leave content alone
        $output = $content;
        break;
    }

    $asset->setContent($output);
  }

  public function filterDump(AssetInterface $asset)
  {
  }

  /**
   * @param string $projectDir
   *
   * @return PleasingTildeFilter
   */
  public function setProjectDir(string $projectDir): PleasingTildeFilter
  {
    $this->projectDir = $projectDir;

    return $this;
  }

  /**
   * Replaces the ~ reference in any @import, @use, or @forward rules that uses one.
   *
   * @param string $content
   * @param string $ext
   *
   * @return array|string|string[]
   */
  private function fixTildePath(string $content, string $ext)
  {
    $lines = explode("\n", $content);
    foreach ($lines as $line)
    {
      if( substr( $line, 0, 2 ) != "//" )
      {
        if( preg_match( "/^\s*@(import|use|forward)\s*(url|reference|inline)?[\"';\s]+?(~([^'\";\s]+))[\"';\s]+?.*$/", $line, $matches ) )
        {
          // Exclude Remote Imports
          if ($matches[2] != "url")
          {
            $fullLine   = $matches[0];
            $importPath = trim($matches[3]);
            if (0 == strpos($importPath,'~'))
            {
              if ($pathWithNode = $this->getPathWithNode($importPath, $ext))
              {
                $newLine = str_replace($importPath, $pathWithNode, $fullLine);
                $content = str_replace($fullLine, $newLine, $content);
              }
              elseif($pathWithRoot = $this->getAbsoluteProjectPath($importPath, $ext))
              {
                $newLine = str_replace($importPath, $pathWithRoot, $fullLine);
                $content = str_replace($fullLine, $newLine, $content);
              }
            }
          }
        }
      }
    }

    return $content;
  }

  /**
   * Evaluates if the given path is a valid path to a sass or scss file.
   *
   * @param string $path
   * @param string $ext
   *
   * @return bool
   */
  private function isValid(string $path, string $ext)
  {
    if (is_file($path))
    {
      return true;
    }
    elseif (is_file($path.'.'.$ext))
    {
      return true;
    }
    else
    {
      $parts       = explode( DIRECTORY_SEPARATOR, $path );
      $oldFileName = array_pop( $parts );
      $parts[]     = '_' . $oldFileName . '.' . $ext;

      if (is_file(implode(DIRECTORY_SEPARATOR, $parts)))
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Replaces ~ reference to project root if the path exists within the project root, else returns null.
   *
   * @param string $path
   *
   * @return array|string|string[]|null
   */
  private function getAbsoluteProjectPath($path, $ext)
  {
    $pathWithRoot = preg_replace('#^~/?#', $this->projectDir.'/$1', $path);
    if ($this->isValid($pathWithRoot, $ext))
    {
      return $pathWithRoot;
    }

    return null;
  }

  /**
   * Replaces ~ reference to 'node_modules' if the path exists within node_modules, else returns null.
   *
   * @return string|null
   */
  private function getPathWithNode($path, $ext)
  {
    $dir = realpath($this->projectDir.'/node_modules');
    if ($dir && is_dir($dir))
    {
      $pathWithNode = preg_replace('#^~/?#', $dir.'/$1', $path);
      if ($this->isValid($pathWithNode, $ext))
      {
        return preg_replace('#^~/?#', '', $path);
      }
    }

    return null;
  }
}