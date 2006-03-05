<?php
/**
 * PEAR::Image_Puzzle
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category    Image
 * @package     Image_Puzzle
 * @author      Michal Felski <fela@fela.pl>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link        http://pear.php.net/package/Image_Puzzle
 * @version     $id$
 */

/**
 * Arc edge
 *
 * @category    Image
 * @package     Image_Puzzle
 * @author      Michal Felski <fela@fela.pl>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link        http://pear.php.net/package/Image_Puzzle
 * @version     @package_version@
 */
class Image_Puzzle_Edge_Arc extends Image_Puzzle_Edge {

    private $_radius = 0;

    private $_side = 0;

    private $_factor;

    public function __construct($longitude, $transversal) {
        parent::__construct($longitude, $transversal);
        $this->_factor = rand(85, 95) / 100;
        $this->_radius = $longitude / 2 / sqrt(1 - $this->_factor * $this->_factor);
        $this->_side = rand() & 1;
    }

    public function getLeftTopMargin() {
        return (1 - $this->_factor) * $this->_radius * !$this->_side;
    }

    public function getRightBottomMargin() {
        return (1 - $this->_factor) * $this->_radius * $this->_side;
    }

    public function isTransparent($x, $y) {
        $x = $x - $this->longitude / 2;
        if ($this->_side) {
            $y = $y - $this->_factor * $this->_radius;
            return $x * $x + $y * $y < $this->_radius * $this->_radius;
        } else {
            $y = $y + $this->_factor * $this->_radius;
            return $x * $x + $y * $y > $this->_radius * $this->_radius;
        }
    }
}
?>