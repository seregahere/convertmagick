<?php

interface IConvertMagick {

	/**
	 * Clears all resources associated to ConvertMagick object
	 *
	 * @return boolean
	*/
	public function clear();

	/**
	 * Destroys the ConvertMagick object
	 * Destroys the ConvertMagick object and frees all resources associated with it. 
	 * This method is deprecated in favour of ConvertMagick::clear.
	 *
	 * @return boolean
	*/
	public function destroy();

	/**
	 * Checks if the current item is valid.
	 * 
	 * @return boolean
	 */
	public function valid();

	/**
	 * Set the iterator to the position in the image list specified with the index parameter. 
	 * This method is available if ConvertMagick has been compiled against ImageMagick version 6.2.9 or newer.
	 * 
	 * @param int $index The position to set the iterator to
	 * @return boolean
	 */
	public function setIteratorIndex($index);

	/**
	 * Returns the format of a particular image in a sequence.
	 *
	 * @return string
	 */
	public function getImageFormat();

	/**
	 * Returns the format of a particular image in a sequence.
	 *
	 * @param string $format String presentation of the image format. 
	 * @return boolean
	 */
	public function setImageFormat($format);

	/**
	 * Returns the width and height as an associative array.
	 *
	 * @return array
	 */
	public function getImageGeometry();

	/**
	 * Sets the image compression
	 *
	 * @param int $compression One of the COMPRESSION constants
	 * @return boolean
	 */
	public function setImageCompression($compression);

	/**
	 * Sets the image compression quality
	 * 
	 * @param int $quality The image compression quality as an integer
	 * @return boolean
	 */
	public function setImageCompressionQuality($quality);

	/**
	 * Scales the size of an image to the given dimensions. 
	 * The other parameter will be calculated if 0 is passed as either param.
	 * 
	 * @param int $cols 
	 * @param int $cols 
	 * @param boolean $bestfit optional 
	 * @return boolean
	 */
	public function scaleImage($cols, $rows, $bestfit = false);

	/**
	 * Returns a new ConvertMagick object with the current image sequence.
	 * @return IConvertMagick
	 */
	public function getImage();

	/**
	 * Extracts a region of the image
	 * 
	 * @param int $width The width of the crop 
	 * @param int $height The height of the crop 
	 * @param int $x The X coordinate of the cropped region's top left corner
	 * @param int $y The Y coordinate of the cropped region's top left corner
	 * @return boolean
	 */
	public function cropImage($width, $height, $x, $y);

	/**
	 * Sets the page geometry of the image.
	 * 
	 * @param int $width The width of the crop 
	 * @param int $height The height of the crop 
	 * @param int $x The X coordinate of the cropped region's top left corner
	 * @param int $y The Y coordinate of the cropped region's top left corner
	 * @return boolean
	 */
	public function setImagePage($width, $height, $x, $y);

	/**
	 * Rotates an image the specified number of degrees. 
	 * Empty triangles left over from rotating the image are filled with the background color.
	 * 
	 * @param string $background  A string representing the color as the first parameter. 
	 * @param float $degrees Rotation angle, in degrees. The rotation angle is interpreted as the number of degrees to rotate the image clockwise.
	 * @return boolean
	 */
	public function rotateImage($background, $degrees);

	/**
	 * Creates a vertical mirror image by reflecting the pixels around the central x-axis.
	 *
	 * @return boolean
	 */
	public function flipImage();

	/**
	 * Creates a horizontal mirror image by reflecting the pixels around the central y-axis.
	 *
	 * @return boolean
	 */
	public function flopImage();

	/**
	 * Implements direct to memory image formats. It returns the image sequence as a string. 
	 * The format of the image determines the format of the returned blob (GIF, JPEG, PNG, etc.). 
	 * To return a different image format, use ConvertMagick::setImageFormat().
	 *
	 * @return string Returns a string containing the image.
	 */
	public function getImageBlob();

	/**
	 * Writes an image to the specified filename. 
	 * If the filename parameter is NULL, the image is written to the filename set by ConvertMagick::readImage() 
	 * or ConvertMagick::setImageFilename().
	 *
	 * @param string filename Filename where to write the image. The extension of the filename defines the type of the file. Format can be forced regardless of file extension using format: prefix, for example "jpg:test.png".
	 * @return boolean
	 */
	public function	writeImage($filename);

}
