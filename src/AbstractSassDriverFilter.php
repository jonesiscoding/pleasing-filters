<?php

namespace DevCoding\Pleasing\Filters;

use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;
use DevCoding\Pleasing\Filters\Options\ImportPathsInterface;
use XQ\Drivers\AbstractSassDriver;
use XQ\Drivers\DartSassDriver;
use XQ\Drivers\Message\DartSassException;
use XQ\Drivers\SasscDriver;

/**
 * Class AbstractSassDriverFilter
 *
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @version Pleasing Filters v3.0 (https://github.com/jonesiscoding/pleasing-filters)
 * @license MIT (https://github.com/jonesiscoding/pleasing-filters/blob/master/LICENSE)
 * @package DevCoding\Pleasing\Filters
 */
abstract class AbstractSassDriverFilter implements FilterInterface, ImportPathsInterface
{
  /** @var string */
  protected $_tmp = '/tmp/pleasing';
  /** @var int */
  protected $_debug = 0;
  /** @var string */
  protected $_bin;

  /**
   * @return AbstractSassDriver|DartSassDriver|SasscDriver
   */
  abstract protected function Driver(): AbstractSassDriver;

  /**
   * @param int|bool    $debug
   * @param string|null $tmpPath
   */
  public function __construct($debug = false, string $tmpPath = null)
  {
    $this->_debug = $debug;

    if (!empty($tmpPath))
    {
      $this->setTmpPath($tmpPath);
    }
  }

  // region //////////////////////////////////////////////// Implemented Methods

  /**
   * @param AssetInterface $asset
   *
   * @return void
   * @throws DartSassException
   */
  public function filterLoad(AssetInterface $asset)
  {
    // Add the source folder as the FIRST import path
    if ( $dir = $asset->getSourceDirectory() )
    {
      $this->addImportPath( $dir, true );
    }

    // Run the Process
    $asset->setContent( $this->Driver()->compile( $asset->getContent()) );
  }

  /**
   * {@inheritDoc}
   */
  public function filterDump(AssetInterface $asset)
  {
  }

  // endregion ///////////////////////////////////////////// End Implemented Methods

  // region //////////////////////////////////////////////// Configuration Setters

  /**
   * @param string $path
   * @param bool $prepend
   *
   * @return $this
   */
  public function addImportPath(string $path, bool $prepend = false): AbstractSassDriverFilter
  {
    $this->Driver()->addImportPath( $path, $prepend);

    return $this;
  }

  /**
   * @param string $_bin
   *
   * @return $this
   */
  public function setBin(string $_bin): AbstractSassDriverFilter
  {
    $this->_bin = $_bin;

    return $this;
  }

  /**
   * @param string $style
   *
   * @return AbstractSassDriverFilter
   * @throws \Exception
   */
  public function setOutputStyle(string $style): AbstractSassDriverFilter
  {
    $this->Driver()->setOutputStyle( $style );

    return $this;
  }

  /**
   * @param string[] $paths
   *
   * @return AbstractSassDriverFilter
   */
  public function setImportPaths(array $paths): AbstractSassDriverFilter
  {
    $this->Driver()->setImportPaths( $paths );

    return $this;
  }

  /**
   * @param string $tmp
   *
   * @return AbstractSassDriverFilter
   */
  public function setTmpPath(string $tmp): AbstractSassDriverFilter
  {
    $this->_tmp = $tmp.DIRECTORY_SEPARATOR.'pleasing';

    return $this;
  }

  /**
   * @param bool|int $debug
   *
   * @return AbstractSassDriverFilter
   */
  public function setDebugLevel($debug): AbstractSassDriverFilter
  {
    if (is_bool($debug))
    {
      $this->_debug = $debug ? 1 : 0;
    }
    elseif (is_int($debug))
    {
      $this->_debug = $debug;
    }
    else
    {
      $this->_debug = is_int($debug) ? $debug : 0;
    }

    return $this;
  }

  // endregion ///////////////////////////////////////////// End Configuration Setters

  // region //////////////////////////////////////////////// Helper Methods

  /**
   * @return string
   */
  public function getBin(): string
  {
    return $this->_bin;
  }

  /**
   * @return string
   */
  protected function getTmpPath(): string
  {
    return $this->_tmp;
  }

  /**
   * @return int
   */
  protected function getDebugLevel(): int
  {
    return $this->_debug;
  }

  // endregion ///////////////////////////////////////////// End Helper Methods
}
