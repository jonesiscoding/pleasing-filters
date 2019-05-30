<?php
/**
 * PleasingPrefixFilter.php
 */

namespace XQ\Pleasing\Filter;


use Assetic\Asset\AssetInterface;
use Assetic\Filter\FilterInterface;

/**
 * Pleasing and Assetic filter to prefix commonly used properties for usage in approximately the last four major browser
 * versions.
 *
 * This filter is simplistic in nature, and does not do fancy things like removing unneeded prefixes, looking up which
 * prefixes are needed, etc.  It is intended for situations in which you do not have the ability to use autoprefixer.
 *
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @version Pleasing Filters v1.1 (https://github.com/jonesiscoding/pleasing-filters)
 * @license MIT (https://github.com/jonesiscoding/pleasing-filters/blob/master/LICENSE)
 *
 * Class PleasingPrefixFilter
 * @package XQ\Pleasing\Filter
 */
class PleasingPrefixFilter implements FilterInterface
{
  const FIT_FILL = array(
      'fill-available' => array( '-webkit-fill-available', '-moz-available', 'fill-available' ),
      'fit-content'    => array( '-webkit-fit-content', '-moz-fit-content', 'fit-content' ),
      'max-content'    => array( '-webkit-max-content', '-moz-max-content', 'intrinsic', 'max-content' ),
      'min-content'    => array( '-webkit-min-content', '-moz-min-content', 'min-intrinsic', 'min-content' ),
  );

  /** @var array CSS Properties where the value is prefixed. */
  const PREFIX_VALUES = array(
      'display'    => array(
          'flex'        => array( '-webkit-flex', '-ms-flexbox', 'flex' ),
          'grid'        => array( '-ms-grid', 'grid' ),
          'inline-grid' => array( '-ms-inline-grid', 'inline-grid' ),
          'inline-flex' => array( '-webkit-inline-flex', '-ms-inline-flexbox', 'inline-flex' )
      ),
      'height'     => self::FIT_FILL,
      'max-height' => self::FIT_FILL,
      'min-height' => self::FIT_FILL,
      'max-width'  => self::FIT_FILL,
      'min-width'  => self::FIT_FILL,
      'width'      => self::FIT_FILL,
  );

  private $prefixValue = array();

  /** @var array CSS Properties where the property is prefixed. */
  const PREFIX_PROPERTY = array(
      'flex-direction'        => array( '-webkit-flex-direction', '-ms-flex-direction', 'flex-direction' ),
      'flex-grow'             => array( '-webkit-flex-grow', '-ms-flex-positive', 'flex-grow' ),
      'flex-shrink'           => array( '-webkit-flex-shrink', '-ms-flex-negative', 'flex-shrink' ),
      'flex-basis'            => array( '-webkit-flex-basis', '-ms-flex-preferred-size', 'flex-basis' ),
      'flex-wrap'             => array( '-webkit-flex-wrap', '-ms-flex-wrap', 'flex-wrap' ),
      'flex-flow'             => array( '-webkit-flex-flow', '-ms-flex-flow', 'flex-flow' ),
      'order'                 => array( '-webkit-order', '-ms-flex-order', 'order' ),
      'transition'            => array( '-webkit-transition', '-o-transition', 'transition' ),
      'box-sizing'            => array( '-webkit-box-sizing', 'box-sizing' ),
      'column-count'          => array( '-webkit-column-count', 'column-count' ),
      'column-gap'            => array( '-webkit-column-gap', 'column-gap' ),
      'column-width'          => array( '-webkit-column-width', 'column-width' ),
      'column-rule'           => array( '-webkit-column-rule', 'column-rule' ),
      'user-select'           => array( '-webkit-user-select', '-moz-user-select', '-ms-user-select', 'user-select' ),
      'transform'             => array( '-webkit-transform', '-ms-transform', 'transform' ),
      'transform-origin'      => array( '-webkit-transform-origin', '-ms-transform-origin', 'transform-origin' ),
      'appearance'            => array( '-webkit-appearance', '-moz-appearance', 'appearance' ),
      'filter'                => array( '-webkit-filter', 'filter' ),
      'grid-template-columns' => array( '-ms-grid-columns', 'grid-template-columns' ),
      'grid-template-rows'    => array( '-ms-grid-rows', 'grid-template-rows' ),
      'grid-row-start'        => array( '-ms-grid-row', 'grid-row-start' ),
      'grid-column-start'     => array( '-ms-grid-column', 'grid-column-start' ),
      'grid-column'           => array( '-ms-grid-column', 'grid-column' ),
      'grid-row'              => array( '-ms-grid-row', 'grid-row' ),
      'justify-self'          => array( '-ms-grid-row-align', 'justify-self' ),
      'justify-content'       => array( '-ms-flex-pack', '-webkit-justify-content', 'justify-content' ),
      'align-items'           => array( '-ms-flex-align', '-webkit-align-items', 'align-items' ),
      'align-self'            => array( '-ms-flex-item-align', '-webkit-align-self', 'align-self' ),
      'align-content'         => array( '-ms-flex-line-pack', '-webkit-align-content', 'align-content' ),
      'flex'                  => array( '-ms-flex', '-webkit-flex', 'flex' )
  );

  private $prefixProperty = array();

  /** @var array CSS properties where a custom method is used to properly prefix. */
  private $prefixMethod = array(
      'flex'            => 'prefixFlex',
      'justify-content' => 'prefixJustifyContent',
      'align-items'     => 'prefixAlignItems',
      'align-content'   => 'prefixAlignContent',
      'align-self'      => 'prefixAlignSelf'
  );

  private $preconfigured = true;

  // region //////////////////////////////////////////////// Filter Interface Methods

  /**
   * {@inheritdoc}
   */
  public function filterDump( AssetInterface $asset )
  {
  }

  /**
   * {@inheritdoc}
   */
  public function filterLoad( AssetInterface $asset )
  {
    $assetPath = $asset->getSourcePath();
    $ext       = ( $assetPath ) ? pathinfo( $assetPath, PATHINFO_EXTENSION ) : false;

    $content = $asset->getContent();

    switch( $ext )
    {
      case "less":
      case "scss":
      case "css":
        $output = $this->prefixCss( $content );
        break;
      default:
        // Extension not recognized, or could not be read.  Leave content alone
        $output = $content;
        break;
    }

    $asset->setContent( $output );
  }

  // endregion ///////////////////////////////////////////// End Filter Interface Methods

  // region //////////////////////////////////////////////// Main Method

  /**
   * Finds individual CSS rules and evaluates them for needed prefixes, then replaces the rules with the prefixed rule
   * as needed.
   *
   * @param string $content   The CSS, without prefixes.
   *
   * @return string           The CSS with prefixes.
   */
  protected function prefixCss( $content )
  {
    $replaced = array();
    if( $rules = $this->getRules( $content ) )
    {
      foreach( $rules as $rule )
      {
        $prefixed = array();
        if( strpos( $content, $rule->getRaw() ) !== false && !in_array( $rule->getRaw(), $replaced ) )
        {
          if( array_key_exists( $rule->getProperty(), $this->prefixMethod ) )
          {
            $method = $this->prefixMethod[ $rule->getProperty() ];
            $prefixed  = $this->$method( $rule->getValue(), $rule->getBang() );
          }
          elseif( $prefixes = $this->getPrefixesForValuesInProperty( $rule->getValue(), $rule->getProperty() ) )
          {
            $prefixed = $this->getPrefixRules( $rule->getProperty(), $prefixes, $rule->getBang() );
          }
          elseif( $prefixes = $this->getPrefixesForProperty( $rule->getProperty() ) )
          {
            $prefixed = $this->getPrefixRules( $prefixes, $rule->getValue(), $rule->getBang() );
          }

          if( !empty( $prefixed ) )
          {
            $newRules = array();
            foreach( $prefixed as $pRule )
            {
              /** @var CssRule $pRule */
              $pRule->setTemplate( $rule->getTemplate() );
              $newRules[] = $pRule->getOutput();
            }

            $replaced[] = $rule->getRaw();
            $replacement = str_replace( $rule->getOutput(), implode( "\n", $newRules ), $rule->getRaw() );
            $content = str_replace($rule->getRaw(),$replacement,$content);

          }
        }
      }
    }

    return $content;
  }

  /**
   * @param $content
   *
   * @return CssRule[]
   */
  protected function getRules( $content )
  {
    $rules = array();
    if( preg_match_all( '#{((?:[^{}]++|(?R))*+)}#', $content, $groups, PREG_SET_ORDER ) )
    {
      foreach( $groups as $group )
      {
        if( strpos( $group[ 1 ], '{' ) === false )
        {
          $set = preg_split( '#(\r|\n)#', $group[ 1 ] );

          foreach( $set as $s )
          {
            if( !empty($s) && $rule = CssRule::fromString( $s ) )
            {
              $rules[] = $rule;
            }
          }
        }
        else
        {
          $rules = array_merge( $rules, $this->getRules( $group[ 1 ] ) );
        }
      }
    }

    return (!empty($rules)) ? $rules : null;
  }

  // endregion ///////////////////////////////////////////// End Main Method

  // region //////////////////////////////////////////////// Prefix Methods

  /**
   * Properly prefixes the 'align-items' property.
   *
   * @param string  $value  The value of the property.
   * @param null    $extra  !important or null
   *
   * @return CssRule[]      The prefixed rules to replace the 'align-items' rule.
   */
  protected function prefixAlignItems( $value, $extra = null )
  {
    $rules = $this->prefixFlexProperties( 'align-items', $value, $extra );

    foreach( $rules as $rule )
    {
      if( '-ms-flex-align' == $rule->getProperty() )
      {
        if( 'start' !== $rule->getValue() && 'end' !== $rule->getValue() )
        {
          $rules[] = new CssRule( '-ms-flex-align', 'center', $rule->getBang() );
        }
      }
    }

    return $rules;
  }

  /**
   * Properly prefixes the 'align-content' property.
   *
   * @param string  $value  The value of the property.
   * @param null    $extra  !important or null
   *
   * @return CssRule[]      The prefixed rules to replace the 'align-content' rule.
   */
  protected function prefixAlignContent( $value, $extra = null )
  {
    return $this->prefixFlexProperties( 'align-content', $value, $extra );
  }

  /**
   * Properly prefixes the 'align-self' property.
   *
   * @param string  $value  The value of the property.
   * @param null    $extra  !important or null
   *
   * @return CssRule[]      The prefixed rules to replace the 'align-self' rule.
   */
  protected function prefixAlignSelf( $value, $extra = null )
  {
    return $this->prefixFlexProperties( 'align-self', $value, $extra );
  }

  /**
   * Properly prefixes the 'flex' property & value.
   *
   * @param string  $value  The value of the property.
   * @param null    $extra  !important or null
   *
   * @return CssRule[]      The prefixed rules to replace the 'flex' rule.
   */
  protected function prefixFlex( $value, $extra = null )
  {
    $parts = explode( " ", $value );
    if( count( $parts ) == 3 )
    {
      // Make sure there's a % after the basis to avoid IE10/11 Bugs.
      // 0px does not work because a minifier would remove it.
      //
      // https://github.com/philipwalton/flexbugs#4-flex-shorthand-declarations-with-unitless-flex-basis-values-are-ignored
      if( $parts[ 2 ] === 0 || $parts[ 2 ] === '0px' )
      {
        $parts[ 2 ] = '0%';
      }
    }

    $prop = $this->getPrefixesForProperty( 'flex' );
    $val  = implode( ' ', $parts );

    return $this->getPrefixRules( $prop, $val, $extra );
  }

  /**
   * Properly prefixes the 'justify-content' property.
   *
   * @param string  $value  The value of the property.
   * @param null    $extra  !important or null
   *
   * @return CssRule[]      The prefixed rules to replace the 'justify-content' rule.
   */
  protected function prefixJustifyContent( $value, $extra = null )
  {
    return $this->prefixFlexProperties( 'justify-content', $value, $extra );
  }

  /**
   * Prefixes a flexbox alignment property, such as align-* and justify-*
   *
   * @param string $property
   * @param string $value
   * @param null   $extra
   *
   * @return CssRule[]
   */
  protected function prefixFlexProperties( $property, $value, $extra = null )
  {
    $properties = $this->getPrefixesForProperty( $property );
    $values     = array();

    $getValue = function( $prop, $val ) use ( $property ) {
      // Short Circuit to not alter for original property.
      if( $property == $prop ) { return $val; }

      // Set up matches array - slightly different for 'justify' properties.
      $matches = array( 'flex-start' => 'start', 'flex-end' => 'end' );
      if( strpos( $val, 'justify' ) !== false )
      {
        $matches[ 'space-between' ] = 'justify';
        $matches[ 'space-around' ]  = 'distribute';
      }

      return ( !empty( $matches[ $val ] ) ) ? $matches[ $val ] : $val;
    };

    // Loop through properties and add appropriate values
    foreach( $properties as $tProp )
    {
      $values[] = $getValue( $tProp, $value );
    }

    return $this->getPrefixRules( $properties, $values, $extra );
  }

  // region //////////////////////////////////////////////// Configuration Methods

  protected function getPrefixesForProperty( $property )
  {
    if( array_key_exists( $property, $this->prefixProperty ) )
    {
      return $this->prefixProperty[ $property ];
    }
    elseif( array_key_exists( $property, self::PREFIX_PROPERTY ) )
    {
      return self::PREFIX_PROPERTY[ $property ];
    }

    return null;
  }

  /**
   * @param string $val
   * @param string $prop
   *
   * @return array|null
   */
  protected function getPrefixesForValuesInProperty( $val, $prop )
  {
    if( array_key_exists( $prop, $this->prefixValue ) && array_key_exists( $val, $this->prefixValue[ $prop ] ) )
    {
      return $this->prefixValue[ $prop ][ $val ];
    }
    elseif( array_key_exists( $prop, self::PREFIX_VALUES ) && array_key_exists( $val, self::PREFIX_VALUES[ $prop ] ) )
    {
      return self::PREFIX_VALUES[ $prop ][ $val ];
    }

    return null;
  }

  /**
   * @param array $prefixProperties
   *
   * @return PleasingPrefixFilter
   */
  public function setPrefixProperties( $prefixProperties )
  {
    foreach( $prefixProperties as $property => $prefixes )
    {
      $defaults = array_key_exists( $property, self::PREFIX_PROPERTY ) ? self::PREFIX_PROPERTY[$property] : array();
      $tPrefixes = array();
      foreach( $prefixes as $prefix )
      {
        // Deal with wildcards
        if( substr($prefix,-1,1) === '*' )
        {
          $pre = substr( $prefix, 0, -1 );
          $len = strlen($pre);
          foreach( $defaults as $default )
          {
            if( substr( $default, 0, $len ) == $pre )
            {
              $tPrefixes[] = $default;
            }
          }
        }
        elseif( $this->preconfigured )
        {
          if( in_array( $prefix, $defaults ) )
          {
            $tPrefixes[] = $prefix;
          }
        }
        else
        {
          $tPrefixes[] = $prefix;
        }
      }

      // If we have prefixes, add it to the property.
      if( !empty( $tPrefixes ) )
      {
        if( !in_array( $property, $tPrefixes ) )
        {
          $tPrefixes[] = $property;
        }

        $this->prefixProperty[ $property ] = $tPrefixes;
      }
    }

    return $this;
  }

  /**
   * @param array $prefixValue
   *
   * @return PleasingPrefixFilter
   */
  public function setPrefixValue( array $prefixValue )
  {
    foreach( $prefixValue as $prop => $values )
    {
      $propDefaults = array_key_exists($prop,self::PREFIX_VALUES) ? self::PREFIX_VALUES[$prop] : array();
      foreach( $values as $value => $prefixes )
      {
        $defaults  = array_key_exists( $value, $propDefaults ) ? $propDefaults[ $value ] : array();
        $tPrefixes = array();
        foreach( $prefixes as $prefix )
        {
          if( substr($prefix,-1,1) === '*' )
          {
            $pre = substr( $prefix, 0, -1 );
            $len = strlen($pre);
            foreach( $defaults as $default )
            {
              if( substr( $default, 0, $len ) === $pre )
              {
                $tPrefixes[] = $default;
              }
            }
          }
          elseif( $this->preconfigured )
          {
            if( in_array( $prefix, $defaults ) )
            {
              $tPrefixes[] = $prefix;
            }
          }
          else
          {
            $tPrefixes[] = $prefix;
          }
        }

        if( !empty( $tPrefixes ) )
        {
          if( !in_array( $value, $tPrefixes ) )
          {
            $tPrefixes[] = $value;
          }

          $this->prefixValue[ $prop ][ $value ] = $tPrefixes;
        }

      }
    }

    return $this;
  }

  /**
   * Sets the usage of preconfigured prefixes.
   *
   * @param bool|int|string $use
   *
   * @return $this
   */
  public function setUsePreconfigured( $use )
  {
    if( !is_bool( $use ) )
    {
      if( is_string( $use ) )
      {
        $use = ( $use == 'false' ) ? false : true;
      }
      elseif( is_int( $use ) )
      {
        $use = ( $use == 0 ) ? false : true;
      }
      else
      {
        $use = true;
      }
    }

    $this->preconfigured = $use;

    return $this;
  }

  // region //////////////////////////////////////////////// Private Helper Methods

  /**
   * Interprets the given parameters into a set of CSS rules to insert in replacement of the rule being evaluated.
   *
   * The number of properties and values given will be matched up to the other.  For instance, if one property
   * is given, it will be duplicated to match the count of the number of values given and vice versa.
   *
   * @param array|string         $prop    The property or properties to use with the rules.
   * @param array|string|float   $val     The value or values to use with the rules.
   * @param string               $extra   Any extra additions to the rules, such as !important
   *
   * @return array                        The CSS rules to insert
   */
  private function getPrefixRules( $prop, $val, $extra = null )
  {
    $extra = ( empty( $extra ) ) ? null : ' ' . $extra;
    if( !is_array( $prop ) && is_array( $val ) )
    {
      $properties = array_fill( 0, count( $val ), $prop );
      $values     = $val;
    }
    elseif( !is_array( $val ) && is_array( $prop ) )
    {
      $values     = array_fill( 0, count( $prop ), $val );
      $properties = $prop;
    }
    else
    {
      $properties = $prop;
      $values     = $val;
    }

    $property = null;
    $value    = null;
    do
    {
      $property = ( !empty( $properties ) ) ? array_shift( $properties ) : $property;
      $value    = ( !empty( $values ) ) ? array_shift( $values ) : $property;

      $rules[] = new CssRule( $property, $value, $extra );
    }
    while( !empty( $properties ) && !empty( $values ) );

    return $rules;
  }

  // endregion ///////////////////////////////////////////// End Private Helper Methods
}