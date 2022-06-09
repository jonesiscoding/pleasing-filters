<?php
/**
 * PleasingSassFilter.php
 */

namespace XQ\Pleasing\Filter;

use XQ\Drivers\AbstractSassDriver;
use XQ\Drivers\DartSassDriver;
use XQ\Pleasing\Filter\Options\SassSourceMapInterface;

/**
 * Loads SASS/SCSS files using a driver for Dart Sass
 *
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @version Pleasing Filters v2.0 (https://github.com/jonesiscoding/pleasing-filters)
 * @license MIT (https://github.com/jonesiscoding/pleasing-filters/blob/master/LICENSE)
 * @package XQ\Pleasing\Filter
 */
class PleasingDartSassFilter extends AbstractSassDriverFilter implements SassSourceMapInterface
{
  /** @var DartSassDriver */
  protected $_Driver;

  /**
   * @param bool $sourceMap
   *
   * @return PleasingDartSassFilter|AbstractSassDriverFilter
   * @throws \Exception
   */
  public function setSourceMap(bool $sourceMap): AbstractSassDriverFilter
  {
    $this->Driver()->setSourceMap( $sourceMap );

    return $this;
  }

  /**
   * @param string $tmp
   *
   * @return AbstractSassDriverFilter
   */
  public function setTmpPath(string $tmp): AbstractSassDriverFilter
  {
    // Reset the SASS Driver, as it may depend on this path
    $this->_Driver = null;

    return parent::setTmpPath($tmp);
  }

  // region //////////////////////////////////////////////// Implemented Methods

  /**
   * @return DartSassDriver|AbstractSassDriver
   * @throws \Exception
   */
  protected function Driver(): AbstractSassDriver
  {
    if ( !$this->_Driver )
    {
      $this->_Driver = new DartSassDriver($this->getDebugLevel(), $this->getBin(), $this->getTmpPath());
    }

    return $this->_Driver;
  }

  // endregion ///////////////////////////////////////////// End Implemented Methods
}
