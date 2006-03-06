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
 * Image_Puzzle_Piece class
 *
 * @category    Image
 * @package     Image_Puzzle
 * @author      Michal Felski <fela@fela.pl>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link        http://pear.php.net/package/Image_Puzzle
 * @version     @package_version@
 */
class Image_Puzzle_Piece {

    /**
     * Initial value of X position
     *
     * @var integer
     */
    private $_left;

    /**
     * Initial value of Y position
     *
     * @var integer
     */
    private $_top;

    /**
     * Initial piece width
     *
     * @var integer
     */
    private $_width;

    /**
     * Initial piece height
     *
     * @var integer
     */
    private $_height;

    /**
     * GD resource image
     *
     * @var resource
     */
    private $_image;

    /**
     * Cached margins for each edge
     *
     * @var integer
     */
    private $_marginCache = array();

    /**
     * Array of Image_Puzzle_Edge objects
     *
     * @var array
     */
    private $_edges = array('Top' => null, 'Bottom' => null, 'Left' => null, 'Right' => null);

    /**
     * Cached transparent color allocated by GD
     *
     * @var integer color identifier representing a transparent color
     */
    private $_transparentColor;

    /**
     * Constructor
     *
     * @param integer $left
     * @param integer $top
     * @param integer $width
     * @param integer $height
     */
    public function __construct($left, $top, $width, $height) {
        $this->_left = $left;
        $this->_top = $top;
        $this->_width = $width;
        $this->_height = $height;
    }

    /**
     * Set left edge for this piece
     *
     * @param Image_Puzzle_Edge $edge
     */
    public function setLeftEdge(Image_Puzzle_Edge $edge) {
        $this->_setEdge('Left', $edge);
    }

    /**
     * Set right edge for this piece
     *
     * @param Image_Puzzle_Edge $edge
     */
    public function setRightEdge(Image_Puzzle_Edge $edge) {
        $this->_setEdge('Right', $edge);
    }

    /**
     * Set top edge for this piece
     *
     * @param Image_Puzzle_Edge $edge
     */
    public function setTopEdge(Image_Puzzle_Edge $edge) {
        $this->_setEdge('Top', $edge);
    }

    /**
     * Set bottom edge for this piece
     *
     * @param Image_Puzzle_Edge $edge
     */
    public function setBottomEdge(Image_Puzzle_Edge $edge) {
        $this->_setEdge('Bottom', $edge);
    }

    /**
     * Set piece image. It should be used only by Image_Puzzle class
     * to set image with needed margins
     *
     * @param resource $image
     */
    public function setImage($image) {
        if ($this->_image !== null) {
            throw new PEAR_Exception('Image already set');
        }
        $this->_image = $image;
    }

    /**
     * Returns X coordinate of this piece according to whole image
     *
     * @return integer
     */
    public function getLeft() {
        return $this->_left - $this->_marginCache['Left']['LeftTop'];
    }

    /**
     * Returns Y coordinate of this piece according to whole image
     *
     * @return integer
     */
    public function getTop() {
        return $this->_top - $this->_marginCache['Top']['LeftTop'];
    }

    /**
     * Returns width in pixels of the piece
     *
     * @return integer
     */
    public function getWidth() {
        return $this->_width + $this->_marginCache['Left']['LeftTop'] + $this->_marginCache['Right']['RightBottom'];
    }

    /**
     * Returns height in pixels of the piece
     *
     * @return integer
     */
    public function getHeight() {
        return $this->_height + $this->_marginCache['Top']['LeftTop'] + $this->_marginCache['Bottom']['RightBottom'];
    }

    /**
     * Saves image to the disk on given $filename
     *
     * @param string $filename
     */
    public function save($filename) {
        imagegif($this->_image, $filename);
    }

    /**
     * Make transparent pixels which should be not visible
     * Color parameter is a color accepted by Image_Color2
     * @see Image_Color2
     *
     * @param   array|string|Image_Color2_Model $src specifying a color.
     *          Non-RGB arrays should include the type element to specify a
     *          color model. Strings will be interpreted as hex if they
     *          begin with a #, otherwise they'll be treated as named colors.
     */
    public function addTransparent($color) {
        $this->_setTransparentColor($color);
        foreach (array_keys($this->_edges) as $edgeName) {
        	$this->_makeEdgeTransparent($edgeName);
        }
        $this->_makeCornersTransparent();
    }

    /**
     * Allocates transparent color to resource image
     *
     * @param   array|string|Image_Color2_Model $src specifying a color.
     *          Non-RGB arrays should include the type element to specify a
     *          color model. Strings will be interpreted as hex if they
     *          begin with a #, otherwise they'll be treated as named colors.
     */
    private function _setTransparentColor($color) {
        require_once 'Image/Color2.php';
        $conversion = new Image_Color2($color);
        $rgb = $conversion->getRgb();
        $this->_transparentColor = imagecolorallocate($this->_image, $rgb[0], $rgb[1], $rgb[2]);
        imagecolortransparent($this->_image, $this->_transparentColor);
    }

    /**
     * One edge process for adding transparent pixels
     *
     * @param string $edgeName
     */
    private function _makeEdgeTransparent($edgeName) {
        for ($i = 0; $i <= $this->_getEdgeLength($edgeName); $i++){
            for ($j = - $this->_marginCache[$edgeName]['RightBottom']; $j <= $this->_marginCache[$edgeName]['LeftTop']; $j++) {
                $this->_makeEdgePointTransparent($edgeName, $i, $j);
            }
        }
    }

    /**
     * Fills died corners with transparent color
     *
     */
    private function _makeCornersTransparent() {
        $top = $this->_marginCache['Top']['LeftTop'];
        $left = $this->_marginCache['Left']['LeftTop'];
        imagefilledrectangle($this->_image, 0, 0, $left - 1, $top - 1, $this->_transparentColor);
        imagefilledrectangle($this->_image, $left + $this->_width, 0, $this->getWidth(), $top, $this->_transparentColor);
        imagefilledrectangle($this->_image, 0, $top + $this->_height, $left, $this->getHeight(), $this->_transparentColor);
        imagefilledrectangle($this->_image, $left + $this->_width, $top + $this->_height, $this->getWidth(), $this->getHeight(), $this->_transparentColor);
    }

    /**
     * Returns length of edge without margins
     *
     * @param string $edgeName
     * @return integer
     */
    private function _getEdgeLength($edgeName) {
        if ($edgeName == 'Top' || $edgeName == 'Bottom') {
            return $this->_width;
        }
        return $this->_height;
    }

    /**
     * Returns offsest value of edge points according to resource image
     * X-coordinate
     *
     * @param string $edgeName
     * @param integer $i
     * @param integer $j
     * @return integer
     */
    private function _getXOfset($edgeName, $i, $j) {
        switch ($edgeName) {
        	case 'Top':
        	case 'Bottom':
        		return $i;
        	case 'Left':
        	    return - $j;
        	case 'Right':
        	    return $this->_width - $j;
        }
    }

    /**
     * Returns offsest value of edge points according to resource image
     * Y-coordinate
     *
     * @param string $edgeName
     * @param integer $i
     * @param integer $j
     * @return integer
     */
    private function _getYOfset($edgeName, $i, $j) {
        switch ($edgeName) {
        	case 'Top':
        		return - $j;
        	case 'Left':
        	case 'Right':
        	    return $i;
        	case 'Bottom':
        	    return $this->_height - $j;
        }
    }

    /**
     * Returns true is point should be transparent
     *
     * @param string $edgeName
     * @param integer $i
     * @param integer $j
     * @return boolean
     */
    private function _pointShouldBeTransparent($edgeName, $i, $j) {
        $transparent = $this->_edges[$edgeName]->isTransparent($i,$j);
        if ($edgeName == 'Right' || $edgeName == 'Bottom') {
            return !$transparent;
        }
        return $transparent;
    }

    /**
     * Set point as transparent
     * i,j coordinates on edge $edgeName
     *
     * @param string $edgeName
     * @param integer $i
     * @param integer $j
     */
    private function _makeEdgePointTransparent($edgeName, $i, $j) {
        if (!$this->_pointShouldBeTransparent($edgeName, $i, $j)) {
            return;
        }
        $this->_setPixelTransparent(
            $this->_marginCache['Left']['LeftTop'] + $this->_getXOfset($edgeName, $i, $j),
            $this->_marginCache['Top']['LeftTop'] + $this->_getYOfset($edgeName, $i, $j)
        );
    }

    /**
     * Puts edge into array on $edgeName index
     *
     * @param string $edgeName
     * @param Image_Puzzle_Edge $edge
     */
    private function _setEdge($edgeName, Image_Puzzle_Edge $edge) {
        $this->_edges[$edgeName] = $edge;
        $this->_marginCache[$edgeName]['LeftTop'] = $edge->getLeftTopMargin();
        $this->_marginCache[$edgeName]['RightBottom'] = $edge->getRightBottomMargin();
    }

    /**
     * set pixel as transparent. Now it uses GD library
     * x,y point coordinates
     *
     * @param integer $x
     * @param integer $y
     */
    private function _setPixelTransparent($x, $y) {
        imagesetpixel($this->_image, $x, $y, $this->_transparentColor);
    }
}
?>