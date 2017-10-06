<?php
/**
 * CssRule.php
 */

namespace XQ\Pleasing\Filter;

/**
 * An entity representing a single CSS rule.
 *
 * Class CssRule
 *
 * @author  Aaron M Jones <am@jonesiscoding.com>
 * @version Pleasing Filters v1.0.6 (https://github.com/exactquery/pleasing-filters)
 * @license MIT (https://github.com/exactquery/pleasing-filters/blob/master/LICENSE)
 *
 * @package XQ\Pleasing\Filter
 */
class CssRule
{
  /** @var  string */
  protected $bang;
  /** @var  int */
  protected $indent;
  /** @var  string */
  protected $property;
  /** @var string */
  protected $raw;
  /** @var  string */
  protected $rule;
  /** @var  string */
  protected $template;
  /** @var  string */
  protected $value;

  /**
   * @param string $property
   * @param string $value
   * @param string $bang
   */
  public function __construct( $property = null, $value = null, $bang = null)
  {
    $this->property = $property;
    $this->value    = $value;
    $this->bang     = $bang;
  }

  /**
   * Create a CssRule object from an existing CSS string.
   *
   * @param string $string
   *
   * @return null|CssRule
   */
  public static function fromString( $string )
  {
    if( preg_match( '#(\s+)?([^:]+):([^!;]+)([^;]+)?#', trim( $string, "\n\r" ), $matches ) )
    {
      $rule = new self();
      $rule
          ->setRaw($string)
          ->setProperty( ( !empty( $matches[ 2 ] ) ) ? trim( $matches[ 2 ] ) : null )
          ->setValue( ( !empty( $matches[ 3 ] ) ) ? trim( $matches[ 3 ] ) : null )
          ->setBang( ( !empty( $matches[ 4 ] ) ) ? trim( $matches[ 4 ] ) : null )
          ->setIndent( ( isset( $matches[ 1 ] ) ) ? strlen( $matches[ 1 ] ) : 0 )
      ;

      $template = preg_replace( "#(" . $rule->getProperty() . "([\s:]+))#", "%s$2", $matches[ 0 ].';' );
      $template = preg_replace( "#(:([\s]+))" . $rule->getValue() . "#", "$1%s", $template );
      $template = preg_replace( "#([^!]+)" . $rule->getBang() . ";#", "$1%s;", $template );

      return $rule->setTemplate( $template );
    }

    return null;
  }

  /**
   * Any !important, !default, !global addition to the CSS Rule, complete with !
   *
   * @return string
   */
  public function getBang()
  {
    return $this->bang;
  }

  /**
   * The number of spaces that the CSS rule should be indented.
   *
   * @return int
   */
  public function getIndent()
  {
    return $this->indent;
  }

  /**
   * The properly formatted CSS rule.
   * @return string
   */
  public function getOutput()
  {
    return sprintf( $this->getTemplate(), $this->getProperty(), $this->getValue(), $this->getBang() );
  }

  /**
   * The property uhm.. property.
   * @return string
   */
  public function getProperty()
  {
    return $this->property;
  }

  /**
   * The raw CSS string, prior to parsing, if set.
   *
   * @return string
   */
  public function getRaw()
  {
    return $this->raw;
  }

  /**
   * An sprintf template for recreating the CSS rule.  Arguments are property, value, bang.
   *
   * @return string
   */
  public function getTemplate()
  {
    return $this->template;
  }

  /**
   * The value of the CSS rule.
   *
   * @return string
   */
  public function getValue()
  {
    return $this->value;
  }

  /**
   * @param string $bang
   *
   * @return CssRule
   */
  public function setBang( $bang )
  {
    $this->bang = $bang;

    return $this;
  }

  /**
   * @param int $indent
   *
   * @return CssRule
   */
  public function setIndent( $indent )
  {
    $this->indent = $indent;

    return $this;
  }

  /**
   * @param string $property
   *
   * @return CssRule
   */
  public function setProperty( $property )
  {
    $this->property = $property;

    return $this;
  }

  /**
   * @param string $raw
   *
   * @return CssRule
   */
  public function setRaw( $raw )
  {
    $this->raw = $raw;

    return $this;
  }

  /**
   * @param string $rule
   *
   * @return CssRule
   */
  public function setRule( $rule )
  {
    $this->rule = $rule;

    return $this;
  }

  /**
   * @param string $template
   *
   * @return CssRule
   */
  public function setTemplate( $template )
  {
    $this->template = $template;

    return $this;
  }

  /**
   * @param string $value
   *
   * @return CssRule
   */
  public function setValue( $value )
  {
    $this->value = $value;

    return $this;
  }
}