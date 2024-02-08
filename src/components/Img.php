<?php

namespace siripravi\gallery\components;

/**
 * Image helper class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license New BSD License
 * @since 0.5
 */
class Img
{
	const METHOD_RESIZE = 'resize';
	const METHOD_RESIZE_PERCENT = 'resizePercent';
	const METHOD_ADAPTIVE_RESIZE = 'adaptiveResize';
	const METHOD_CROP = 'crop';
	const METHOD_CROP_CENTER = 'cropFromCenter';
	const METHOD_ROTATE = 'rotate';
	const METHOD_ROTATE_DEGREES = 'rotateDegrees';

	const DIRECTION_CLOCKWISE = 'CW';
	const DIRECTION_COUNTER_CLOCKWISE = 'CCW';
}
