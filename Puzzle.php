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
 * @author      Michal Felski <fela@php.net>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link        http://pear.php.net/package/Image_Puzzle
 * @version     $Id$
 */

/**
 * Require PEAR_Exception
 *
 */
require_once 'PEAR/Exception.php';

/**
 * Puzzle piece class required
 *
 */
require_once 'Image/Puzzle/Piece.php';

/**
 * Edge factory required
 *
 */
require_once 'Image/Puzzle/Edge.php';

/**
 * Image_Puzzle generate puzzle pieces from any image. There is an edges factory
 * to make puzzle in different shapes. An example of using this is as bellow:
 *
 * <code>
 * require_once 'Image/Puzzle.php';
 * $options = array(
 *   'cols'  => 4,
 *   'rows'  => 4,
 *   'edge'  => 'default'
 * );
 * $puzzle = new Image_Puzzle($options);
 * $puzzle->createFromFile('image.jpg');
 * $puzzle->saveAll('piece_[row]_[col].gif');
 * </code>
 *
 * @category    Image
 * @package     Image_Puzzle
 * @author      Michal Felski <fela@php.net>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 * @link        http://pear.php.net/package/Image_Puzzle
 * @version     @package_version@
 */
class Image_Puzzle
{

    /**
     * Options for puzzle maker
     *
     * @var array
     *
     */
    private $_options = array (
        'transparentColor'  => 'white',
        'cols'              => 5,
        'rows'              => 5,
        'edge'             => 'default',
    );

    /**
     * GD resource of source puzzle image recieved by imagecreate
     *
     * @var resource
     *
     */
    private $_source;

    /**
     * Width of source puzzle image
     *
     * @var int
     *
     */
    private $_sourceWidth;

    /**
     * Height of source puzzle image
     *
     * @var int
     *
     */
    private $_sourceHeight;

    /**
     * List of pieces
     *
     * @var array
     *
     */
    private $_pieces;

    /**
     * Array of supported image types
     *
     * @var array
     */
    private $_supportedTypes = array(
        IMAGETYPE_GIF       => 'gif',
        IMAGETYPE_JPEG      => 'jpeg',
        IMAGETYPE_JPEG2000  => 'jpeg',
        IMAGETYPE_PNG       => 'png',
        IMAGETYPE_WBMP      => 'wbmp',
        IMAGETYPE_XBM       => 'xbm'
    );

    /**
     * Puzzle object constructor. Options parameter is optional.
     * It allow to set how puzzle will be generated.<br />
     * Available options are :<br />
     * transparentColor - Color name or hex string or an RGB array of color
     * wich will be used as transparent. Default is white (string | array)<br />
     * cols - Number of columns to create. Default is 5 (integer)<br />
     * rows - Number of rows to create. Default is 5 (integer)<br />
     * edge - Name of edge wich will be used. For example default, sinus (string)
     *
     * @param array $options an associative array of option names and values
     * @return Image_Puzzle a new puzzle object
     */
    public function __construct($options = array()) {
        foreach ($options as $key => $value){
            if (in_array($key, array_keys($this->_options))) {
                $this->_options[$key] = $value;
            }
        }
    }

    /**
     * Create puzzle directly from file.
     * Currently it is only one avaliable method to create puzzle.
     *
     * @param string $filename Filename of source image
     */
    public function createFromFile($filename) {
        if (!is_readable($filename)) {
            throw new PEAR_Exception('cannot read from ' . $filename);
        }
        $info = getimagesize($filename);
        $this->_sourceWidth = $info[0];
        $this->_sourceHeight = $info[1];
        $type = $info[2];
        if (!$this->_isFileTypeSuported($type)) {
            throw new PEAR_Exception('unsuported image type ' . $filename);
        }
        $imageFunction = 'imagecreatefrom' . $this->_supportedTypes[$type];
        $this->_source = $imageFunction($filename);
        $this->_createPuzzle();
    }

    /**
     * Returns puzzle piece at row $row and col $col.
     *
     * @param int $row Number of row started from 1
     * @param int $col Number og col started from 1
     * @return Image_Puzzle_Piece
     */
    public function getPiece($row, $col) {
        if ($row < 1 || $row > $this->_options['rows']) {
            throw new PEAR_Exception('Illegal row number');
        }
        if ($col < 1 || $col > $this->_options['cols']) {
            throw new PEAR_Exception('Illegal col number');
        }
        return $this->_pieces[$row - 1][$col - 1];
    }

    /**
     * Save all pieces to separate files with given name pattern.
     * $pattern parameter is used to define filename for each piece.
     *
     * @param string Name pattern of files. It can contain [row] and [col]
     *      symbols which are replaced to current row and col number
     */
    public function saveAll($pattern = 'piece[row]_[col].gif') {
        for ($col = 0; $col < $this->_options['cols']; $col++){
			for ($row = 0; $row < $this->_options['rows']; $row++){
                $filename = str_replace('[row]', $row + 1, $pattern);
                $filename = str_replace('[col]', $col + 1, $filename);
                $this->_pieces[$row][$col]->save($filename);
			}
		}
    }

    /**
     * Check supporting image type by GD library
     *
     * @param integer $type
     * @return boolean
     */
    private function _isFileTypeSuported($type) {
        return ((imagetypes() & $type) == $type && isset($this->_supportedTypes[$type]));
    }

    /**
     * Private method which create puzzle. Before it is called _source,
     * _sourceWidth and _sourceHeight properties need to be set.
     */
    private function _createPuzzle() {
        $pieceWidth = round($this->_sourceWidth / $this->_options['cols']);
		$pieceHeight = round($this->_sourceHeight / $this->_options['rows']);
        $this->_createPieces($pieceWidth, $pieceHeight);
        $this->_createEdges($pieceWidth, $pieceHeight);
        $this->_setPictures();
    }

    /**
     * Fills _pieces array with piece objects.
     *
     * @param int $width Piece width
     * @param int $height Piece height
     */
    private function _createPieces($width, $height) {
        $this->_pieces = array();
        for ($col = 0; $col < $this->_options['cols']; $col++) {
            for ($row = 0; $row < $this->_options['rows']; $row++) {
                $this->_pieces[$row][$col] = new Image_Puzzle_Piece(
                    $width * $col, $height * $row, $width, $height);
            }
        }
    }

    /**
     * Sets edges for each piece
     *
     * @param int $width Piece width
     * @param int $height Piece height
     */
    private function _createEdges($pieceWidth, $pieceHeight) {
        $edge = Image_Puzzle_Edge::factory('line', $pieceWidth, $pieceHeight);
        for ($col = 0; $col < $this->_options['cols']; $col++){
            $this->_pieces[0][$col]->setTopEdge($edge);
            $this->_pieces[$this->_options['rows'] - 1][$col]->setBottomEdge($edge);
        }
        $edge = Image_Puzzle_Edge::factory('line', $pieceHeight, $pieceWidth);
        for ($row = 0; $row < $this->_options['rows']; $row++){
            $this->_pieces[$row][0]->setLeftEdge($edge);
            $this->_pieces[$row][$this->_options['cols'] - 1]->setRightEdge($edge);
        }
        for ($col = 0; $col < $this->_options['cols'] - 1; $col++){
            for ($row = 0; $row < $this->_options['rows']; $row++){
                $edge = Image_Puzzle_Edge::factory($this->_options['edge'], $pieceHeight, $pieceWidth);
                $this->_pieces[$row][$col + 1]->setLeftEdge($edge);
                $this->_pieces[$row][$col]->setRightEdge($edge);
            }
        }
        for ($col = 0; $col < $this->_options['cols']; $col++){
            for ($row = 0; $row < $this->_options['rows'] - 1; $row++){
                $edge = Image_Puzzle_Edge::factory($this->_options['edge'], $pieceWidth, $pieceHeight);
                $this->_pieces[$row + 1][$col]->setTopEdge($edge);
                $this->_pieces[$row][$col]->setBottomEdge($edge);
            }
        }
    }

    /**
     * creating small pictures from source and adding transparent color on edges
     * for each piece.
     */
    private function _setPictures() {
        for ($col = 0; $col < $this->_options['cols']; $col++) {
            for ($row = 0; $row < $this->_options['rows']; $row++) {
                $piece = $this->_pieces[$row][$col];
                $image = imagecreatetruecolor($piece->getWidth(), $piece->getHeight());
                imagecopy($image, $this->_source, 0, 0, $piece->getLeft(),
                    $piece->getTop(), $piece->getWidth(), $piece->getHeight());
                $piece->setImage($image);
                $piece->addTransparent($this->_options['transparentColor']);
            }
        }
    }
}
?>