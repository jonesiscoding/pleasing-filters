<?php
/**
 * PleasingSassFilter.php
 */

namespace DevCoding\Pleasing\Filters;

use XQ\Drivers\AbstractSassDriver;
use XQ\Drivers\Options\SourceMapTrait;
use XQ\Drivers\SasscDriver;
use DevCoding\Pleasing\Filters\Options\SassPluginInterface;
use DevCoding\Pleasing\Filters\Options\SassSourceMapInterface;

/**
 * Loads SASS/SCSS files using a driver for Sassc.
 *
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @version Pleasing Filters v3.0 (https://github.com/jonesiscoding/pleasing-filters)
 * @license MIT (https://github.com/jonesiscoding/pleasing-filters/blob/master/LICENSE)
 * @package DevCoding\Pleasing\Filters
 */
class PleasingSasscFilter extends AbstractSassDriverFilter implements SassSourceMapInterface, SassPluginInterface
{
  use SourceMapTrait;

  /** @var SasscDriver */
  protected $_Driver;

  /**
   * @param bool $sourceMap
   *
   * @return PleasingSasscFilter|AbstractSassDriverFilter
   * @throws \Exception
   */
  public function setSourceMap(bool $sourceMap): AbstractSassDriverFilter
  {
    $this->Driver()->setSourceMap( $sourceMap );

    return $this;
  }

  /**
   * @param array $paths
   *
   * @return PleasingSasscFilter|AbstractSassDriverFilter
   * @throws \Exception
   */
  public function setPluginPaths(array $paths): AbstractSassDriverFilter
  {
    $this->Driver()->setPluginPaths( $paths );

    return $this;
  }

  /**
   * @param string $path
   * @param bool   $prepend
   *
   * @return PleasingSasscFilter|AbstractSassDriverFilter
   * @throws \Exception
   */
  public function addPluginPath(string $path, bool $prepend = false): AbstractSassDriverFilter
  {
    $this->Driver()->addPluginPath( $path, $prepend );

    return $this;
  }

  /**
   * @param string $tmp
   *
   * @return SasscDriver|AbstractSassDriverFilter
   */
  public function setTmpPath(string $tmp): AbstractSassDriverFilter
  {
    // Reset the SASS Driver, as it may depend on this path
    $this->_Driver = null;

    return parent::setTmpPath($tmp);
  }

  // region //////////////////////////////////////////////// Helper Methods

  /**
   * @return SasscDriver|AbstractSassDriver
   * @throws \Exception
   */
  protected function Driver(): AbstractSassDriver
  {
    if ( !$this->_Driver )
    {
      if ( $this->hasSassc() )
      {
        $this->_Driver = new SasscDriver( $this->getDebugLevel(), $this->getBin(), $this->getTmpPath() );
      }
      else
      {
        throw new \Exception( 'No supported SASS compiler was found.' );
      }
    }

    return $this->_Driver;
  }

  /**
   * @return bool
   */
  private function hasSassc(): bool
  {
    return ( class_exists( SasscDriver::class ) && file_exists($this->getBin()) );
  }

  // endregion ///////////////////////////////////////////// End Private Helper Methods
}
