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
 * Abstract class for puzzle edge
 *
 * @category    Image
 * @package     Image_Puzzle
 * @author      Michal Felski <fela@fela.pl>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link        http://pear.php.net/package/Image_Puzzle
 * @version     @package_version@
 */
abstract class Image_Puzzle_Edge {

    /**
     * Longitude length of edge in pixels
     *
     * @var int
     */
    protected $longitude;

    /**
     * Transversal length of edge in pixels
     *
     * @var int
     */
    protected $transversal;

    /**
     * Edge constructor.
     * Creates new edge object.
     *
     * @param int $longitude Longitude length of edge in pixels
     * @param int $transversal Transversal length of edge in pixels
     */
    public function __construct($longitude, $transversal) {
        $this->longitude = $longitude;
        $this->transversal = $transversal;
    }

    /**
     * Returns left margin for vertical edge
     * or top margin for horizontal edge.
     *
     */
    abstract public function getLeftTopMargin();

    /**
     * Returns right margin for vertical edge
     * or bottom margin for horizontal edge.
     *
     */
    abstract public function getRightBottomMargin();

    /**
     * Returns true if point x,y on the edge should be transparent.
     *
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @return boolean Returns true if point x,y should be transparent.
     */
    public function isTransparent($x, $y){
        return false;
    }

    /**
     * Edge factory for creating new edge objects in the basis of their names.
     *
     * @param string $edgeName Name of the edge class
     * @param int $longitude Longitude length of edge in pixels
     * @param int $transversal Transversal length of edge in pixels
     * @return Image_Puzzle_Edge Returns new edge object
     */
    static public function factory($edgeName, $longitude, $transversal) {
        $edgeName = ucfirst($edgeName);
        $edgeFile = 'Image/Puzzle/Edge/' . $edgeName . '.php';
        $edgeClass = 'Image_Puzzle_Edge_' . $edgeName;
        if (!file_exists($edgeFile)) {
            throw new PEAR_Exception('Unknown puzzle edge ' . $edgeName);
        }
        require_once $edgeFile;
        if (!class_exists($edgeClass)) {
            throw new PEAR_Exception('Edge ' . $edgeName . ' not found in ' . $edgeFile);
        }
        $edge = new $edgeClass($longitude, $transversal);
        if (!$edge instanceof Image_Puzzle_Edge) {
            throw new PEAR_Exception('Edge ' . $edgeName . ' does not implements edge interface');
        }
        return $edge;
    }
}
?>