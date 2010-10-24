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
 * Default edge shape. It is like original puzzle shape which is combined of
 * square and ellipse
 *
 * @category    Image
 * @package     Image_Puzzle
 * @author      Michal Felski <fela@fela.pl>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link        http://pear.php.net/package/Image_Puzzle
 * @version     @package_version@
 */
class Image_Puzzle_Edge_Default extends Image_Puzzle_Edge {

    /**
     * radius of ellipse which is parallel to given edge
     *
     * @var float
     */
    private $_longRadius;

    /**
     * radius of ellipse which is perpendicullar to given edge
     *
     * @var float
     */
    private $_transRadius;

    private $_side = 0;

    /**
     * @param integer $longitude
     * @param integer $transversal
     * @see Image_Puzzle_Edge::__construct()
     */
    public function __construct($longitude, $transversal) {
        parent::__construct($longitude, $transversal);
        $factor = rand(10, 12) / 100;
        $this->_longRadius = max($longitude, $transversal) * $factor;
        $this->_transRadius = min($longitude, $transversal) * $factor;
        $this->_side = rand() & 1;
    }

    /**
     * @return integer
     * @see Image_Puzzle_Edge::getLeftTopMargin()
     */
    public function getLeftTopMargin() {
        return $this->_transRadius * 2.2 * !$this->_side;
    }

    /**
     * @return integer
     * @see Image_Puzzle_Edge::getRightBottomMargin()
     */
    public function getRightBottomMargin() {
        return $this->_transRadius * 2.2 * $this->_side;
    }

    /**
     * @param integer $x
     * @param integer $y
     * @return boolean
     * @see Image_Puzzle_Edge::isTransparent()
     */
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

    /**
     * returns if point is inside ellipse
     *
     * @param integer $x
     * @param integer $y
     * @return boolean
     */
    private function _isInsideCircle($x, $y){
        return $x * $x / $this->_longRadius / $this->_longRadius + $y * $y
            / $this->_transRadius / $this->_transRadius < 1;
    }

    /**
     * returns if point is inside square
     *
     * @param integer $x
     * @param integer $y
     * @return boolean
     */
    private function _isInsideSquare($x, $y){
        return (abs($x) < $this->_longRadius * 0.5)
            && (abs($y) < $this->_transRadius * 0.5);
    }
}
?>