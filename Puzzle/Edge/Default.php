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
 * @version     $Id$
 */

/**
 * Default edge.
 *
 * @category    Image
 * @package     Image_Puzzle
 * @author      Michal Felski <fela@fela.pl>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link        http://pear.php.net/package/Image_Puzzle
 * @version     @package_version@
 */
class Image_Puzzle_Edge_Default extends Image_Puzzle_Edge {

    private $_longRadius;

    private $_transRadius;

    private $_side = 0;

    public function __construct($longitude, $transversal) {
        parent::__construct($longitude, $transversal);
        $factor = rand(10, 12) / 100;
        $this->_longRadius = max($longitude, $transversal) * $factor;
        $this->_transRadius = min($longitude, $transversal) * $factor;
        $this->_side = rand() & 1;
    }

    public function getLeftTopMargin() {
        return $this->_transRadius * 2.2 * !$this->_side;
    }

    public function getRightBottomMargin() {
        return $this->_transRadius * 2.2 * $this->_side;
    }

    public function isTransparent($x, $y) {
        $x = $x - $this->longitude / 2;
        if ($this->_side) {
            return $this->_isInsideCircle($x, $y + 1.2 * $this->_transRadius)
                || $this->_isInsideSquare($x, $y);
        } else {
            return !($this->_isInsideCircle($x, $y - 1.2 * $this->_transRadius)
                || $this->_isInsideSquare($x, $y));
        }
    }

    private function _isInsideCircle($x, $y){
        return $x * $x / $this->_longRadius / $this->_longRadius + $y * $y
            / $this->_transRadius / $this->_transRadius < 1;
    }

    private function _isInsideSquare($x, $y){
        return (abs($x) < $this->_longRadius * 0.5)
            && (abs($y) < $this->_transRadius * 0.5);
    }
}
?>