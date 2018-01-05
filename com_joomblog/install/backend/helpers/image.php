<?php
/**
* JoomBlog component for Joomla 3.x
* @version $Id: image.php 2011-03-16 17:30:15
* @package JoomBlog
* @subpackage image.php
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.utilities.utility');


class JBImageHelper
{
	// Resize the given image to a dest path. Src must exist
	// If original size is smaller, do not resize just make a copy
	static public function resize($srcPath, $destPath, $destType, $destWidth, $destHeight, $sourceX	= 0, $sourceY	= 0, $currentWidth=0, $currentHeight=0)
	{

		// Set output quality
		$config		= JFactory::getConfig();
		$imgQuality	= 100;
		$pngQuality = 9;
		
		// See if we can grab image transparency
		$image				= JBImageHelper::open( $srcPath , $destType );
		$transparentIndex	= imagecolortransparent( $image );
	
		// Create new image resource
		$image_p			= ImageCreateTrueColor( $destWidth , $destHeight );
		$background			= ImageColorAllocate( $image_p , 255, 255, 255 );
		
		// test if memory is enough
		if($image_p == FALSE)
		{
			echo 'Image resize fail. Please increase PHP memory';
			return false;
		} 
		
		// Set the new image background width and height
		$resourceWidth		= $destWidth;
		$resourceHeight		= $destHeight;
		
		if(empty($currentHeight) && empty($currentWidth))
		{
			list($currentWidth , $currentHeight) = getimagesize( $srcPath );
		}

		$size = array($currentWidth, $currentHeight); //create array from image size

		$src_aspect = $size[0] / $size[1];
		$thumb_aspect = $resourceWidth / $resourceHeight;

		if ($src_aspect < $thumb_aspect)
		{
			$scale = $resourceWidth / $size[0];
			$new_size = array($resourceWidth, $resourceWidth / $src_aspect);
			$src_pos = array(0, ($size[1] * $scale - $resourceHeight) / $scale / 2);
		}
		else if ($src_aspect > $thumb_aspect)
		{
			$scale = $resourceHeight / $size[1];
			$new_size = array($resourceWidth * $src_aspect, $resourceHeight);
			$src_pos = array(($size[0] * $scale - $resourceWidth) / $scale / 2, 0);
		}
		else
		{
			$new_size = array($resourceWidth, $resourceHeight);
			$src_pos = array(0,0);
		}

		$destWidth = max($new_size[0], 1);
		$destHeight = max($new_size[1], 1);

		$sourceX = $src_pos[0];
		$sourceY = $src_pos[1];

		// If image is smaller, just copy to the center
		$targetX = 0;
		$targetY = 0;
	
		// Resize GIF/PNG to handle transparency
		if( $destType == 'image/gif' )
		{
			$colorTransparent = imagecolortransparent($image);
			imagepalettecopy($image, $image_p);
			imagefill($image_p, 0, 0, $colorTransparent);
			imagecolortransparent($image_p, $colorTransparent);
			imagetruecolortopalette($image_p, true, 256);
			imagecopyresized($image_p, $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth , $destHeight , $currentWidth , $currentHeight );
		}
		else if( $destType == 'image/png' || $destType == 'image/x-png')
		{
			// Disable alpha blending to keep the alpha channel
			imagealphablending( $image_p , false);
			imagesavealpha($image_p,true);
			$transparent		= imagecolorallocatealpha($image_p, 255, 255, 255, 127);
			
			imagefilledrectangle($image_p, 0, 0, $resourceWidth, $resourceHeight, $transparent);
			imagecopyresampled($image_p , $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth, $destHeight, $currentWidth, $currentHeight);
		}
		else
		{
			// Turn off alpha blending to keep the alpha channel
			imagealphablending( $image_p , false );
			imagecopyresampled( $image_p , $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth , $destHeight , $currentWidth , $currentHeight );
		}
		
		// Output
		ob_start();
		
		// Test if type is png
		if( $destType == 'image/png' || $destType == 'image/x-png' )
		{
			imagepng($image_p, null, $pngQuality);
		}
		elseif ( $destType == 'image/gif')
		{		
			imagegif( $image_p );
		}
		else
		{
			// We default to use jpeg
			imagejpeg($image_p, null, $imgQuality);
		}
		
		$output = ob_get_contents();
		ob_end_clean();
		
		// @todo, need to verify that the $output is indeed a proper image data
		return JFile::write( $destPath , $output );
	}

	// if dest height/width is empty, then resize propotional to origianl width/height	
	static public function resizeProportional($srcPath, $destPath, $destType, $destWidth=0, $destHeight=0)
	{
		list($currentWidth, $currentHeight) = getimagesize( $srcPath );


		if($destWidth == 0)
		{
			// Calculate the width if the width is not set.
			$destWidth = intval($destHeight/$currentHeight * $currentWidth);
		}
		else
		{
			// Calculate the height if the width is set.
			$destHeight = intval( $destWidth / $currentWidth * $currentHeight);
		}
		$destHeight = 100;
		$destWidth = 100;
		
		$magickPath		= array();
	
		// Use imageMagick if available
		if( class_exists('Imagick') && !empty( $magickPath ) && ($imageEngine == 'auto' || $imageEngine == 'imagick') )
		{
			
			$jconfig	= JFactory::getConfig();
			$tmpPath	= $jconfig->getValue('config.tmp_path') . DIRECTORY_SEPARATOR . JFile::getName($srcPath);
			
			$thumb = new Imagick();
			$thumb->readImage($srcPath);    
			$thumb->resizeImage($destWidth,$destHeight, MAGICK_FILTER ,1);
			$thumb->writeImage($tmpPath);
			$thumb->clear();
			$thumb->destroy();
			
			JFile::move($tmpPath, $destPath);

			return true;
		}
		else if( !class_exists( 'Imagick' ) && function_exists( 'exec') )
		{			
			/*if( JFile::exists( $file ) && function_exists( 'exec') )
			{
				$execute	= $command . ' -resize ' . $destWidth . 'x' . $destHeight . ' ' . $srcPath . ' ' . $destPath;
				exec( $execute );
					
				// Test if the files are created, otherwise we know the exec failed.
				if( JFile::exists( $destPath ) )
				{
					return true;
				}
			}*/
		}

//		return JBImageHelper::create_thumbnail($srcPath, $destPath, $destWidth, $destHeight);
		// IF all else fails, we try to use GD
		return JBImageHelper::resize($srcPath, $destPath, $destType, $destWidth, $destHeight);
	}

	/**
	 * Method to create a thumbnail for an image
	 *
	 * @param	$srcPath	The original source of the image.
	 * @param	$destPath	The destination path for the image
	 * @param	$destType	The destination image type.
	 * @param	$destWidth	The width of the thumbnail.
	 * @param	$destHeight	The height of the thumbnail.
	 * 
	 * @return	bool		True on success.
	 */ 
	static public function createThumb($srcPath, $destPath, $destType, $destWidth=64, $destHeight=64)
	{
		// Get the image size for the current original photo
		list( $currentWidth , $currentHeight )	= getimagesize( $srcPath );
		
		$jconfig = JFactory::getConfig();
		
		// Find the correct x/y offset and source width/height. Crop the image squarely, at the center.
		if( $currentWidth == $currentHeight )
		{
			$sourceX = 0;
			$sourceY = 0;
		}
		else if( $currentWidth > $currentHeight )
		{
			$sourceX			= intval( ( $currentWidth - $currentHeight ) / 2 );
			$sourceY 			= 0;
			$currentWidth		= $currentHeight;
		}
		else
		{
			$sourceX		= 0;
			$sourceY		= intval( ( $currentHeight - $currentWidth ) / 2 );
			$currentHeight	= $currentWidth;
		}
		
		
		// Use imageMagick if available
		if( class_exists('Imagick') )
		{
			// Put the new image in temporary dest path, and move them using
			// Joomla API to ensure new folder is created
			$tempFilename = $jconfig->get('tmp_path'). DIRECTORY_SEPARATOR . md5($destPath);
			
			$thumb = new Imagick();
			$thumb->readImage($srcPath);
			$thumb->cropThumbnailImage($destWidth, $destHeight); 
			$thumb->writeImage($tempFilename);
			$thumb->clear();
			$thumb->destroy(); 
			
			// Move to the correct path
			JFile::move($tempFilename,$destPath);
			return true;
		}
		else if( !class_exists( 'Imagick' ) && function_exists( 'exec'))
		{			
			/*if( JFile::exists( $file ) && function_exists( 'exec') )
			{
				$execute	= $command . ' -convert ' . $destWidth . 'x' . $destHeight . '+' . $sourceX . '+' . $sourceY . ' ' . $srcPath . ' ' . $destPath;
				exec( $execute );
	
				// Test if the files are created, otherwise we know the exec failed.
				if( JFile::exists( $destPath ) )
				{
					return true;
				}
			}*/
		}
		
		// IF all else fails, we try to use GD
		return JBImageHelper::resize( $srcPath , $destPath , $destType , $destWidth , $destHeight , $sourceX , $sourceY , $currentWidth , $currentHeight);
	}

	static public function getExtension( $type )
	{
		$type = JString::strtolower($type);
	
		if( $type == 'image/png' || $type == 'image/x-png' )
		{
			return '.png';
		}
		elseif ( $type == 'image/gif')
		{
			return '.gif';
		}
		
		// We default to use jpeg
		return '.jpg';
	}
	
	static public function isValidType( $type )
	{
        $type = JString::strtolower($type);
        $validType = array('image/png', 'image/x-png', 'image/gif', 'image/jpeg', 'image/pjpeg');

        return in_array($type, $validType );
	}
	
	public function isValid( $file )
	{
				
		
		# JPEG:
		if( function_exists( 'imagecreatefromjpeg' ) )
		{
			$im = @imagecreatefromjpeg($file);
			if ($im !== false){ return true; }
		}
	
		if( function_exists( 'imagecreatefromgif' ) )
		{
			# GIF:
			$im = @imagecreatefromgif($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefrompng' ) )
		{
			# PNG:
			$im = @imagecreatefrompng($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromgd' ) )
		{
			# GD File:
			$im = @imagecreatefromgd($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromgd2' ) )
		{
			# GD2 File:
			$im = @imagecreatefromgd2($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromwbmp' ) )
		{
			# WBMP:
			$im = @imagecreatefromwbmp($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromxbm' ) )
		{
			# XBM:
			$im = @imagecreatefromxbm($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromxpm' ) )
		{
			# XPM:
			$im = @imagecreatefromxpm($file);
			if ($im !== false) { return true; }
		}
		
		// If all failed, this photo is invalid
		return false;
	}
	
	static public function open($file , $type)
	{
		// @rule: Test for JPG image extensions
		if( function_exists( 'imagecreatefromjpeg' ) && ( ( $type == 'image/jpg') || ( $type == 'image/jpeg' ) || ( $type == 'image/pjpeg' ) ) )
		{
			$im	= @imagecreatefromjpeg( $file );
	
			if( $im !== false ) { return $im; }
		}
		
		// @rule: Test for png image extensions
		if( function_exists( 'imagecreatefrompng' ) && ( ( $type == 'image/png') || ( $type == 'image/x-png' ) ) )
		{
			$im	= @imagecreatefrompng( $file );
	
			if( $im !== false ) { return $im; }
		}
	
		// @rule: Test for png image extensions
		if( function_exists( 'imagecreatefromgif' ) && ( ( $type == 'image/gif') ) )
		{
			$im	= @imagecreatefromgif( $file );
	
			if( $im !== false ) { return $im; }
		}
		
		if( function_exists( 'imagecreatefromgd' ) )
		{
			# GD File:
			$im = @imagecreatefromgd($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromgd2' ) )
		{
			# GD2 File:
			$im = @imagecreatefromgd2($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromwbmp' ) )
		{
			# WBMP:
			$im = @imagecreatefromwbmp($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromxbm' ) )
		{
			# XBM:
			$im = @imagecreatefromxbm($file);
			if ($im !== false) { return true; }
		}
	
		if( function_exists( 'imagecreatefromxpm' ) )
		{
			# XPM:
			$im = @imagecreatefromxpm($file);
			if ($im !== false) { return true; }
		}
		
		// If all failed, this photo is invalid
		return false;
	}
	
	static public function getSize( $source )
	{
		$obj		= new stdClass();
		list( $obj->width , $obj->height) = getimagesize( $source );
		return $obj;
	}
	
	/*
	 * Resize the thumbnail to respect the aspect ratio
	 */
	static public function resizeAspectRatio($source,$destination,$thumb_width,$thumb_height){
		$image = imagecreatefromjpeg($source);
		$filename = $destination;

		$width = imagesx($image);
		$height = imagesy($image);

		$original_aspect = $width / $height;
		$thumb_aspect = $thumb_width / $thumb_height;

		if($original_aspect >= $thumb_aspect) {
		   // If image is wider than thumbnail (in aspect ratio sense)
		   $new_height = $thumb_height;
		   $new_width = $width / ($height / $thumb_height);
		} else {
		   // If the thumbnail is wider than the image
		   $new_width = $thumb_width;
		   $new_height = $height / ($width / $thumb_width);
		}

		$thumb = imagecreatetruecolor($thumb_width, $thumb_height);

		// Resize and crop
		imagecopyresampled($thumb,
						   $image,
						   0 - ($new_width - $thumb_width) / 2, // Center the image horizontally
						   0 - ($new_height - $thumb_height) / 2, // Center the image vertically
						   0, 0,
						   $new_width, $new_height,
						   $width, $height);
		imagejpeg($thumb, $filename, 80);
	}
		
	/**
	 * Detect image Orientation. Return false if not found	 
	 */
	static public function getOrientation($srcPath)
	{
		
		// Make sure the function exist
		if(!function_exists('exif_read_data')){
			return false;
		}

		$exif = array();
		
		try {
			$exif = @exif_read_data($srcPath);
		} catch (Exception $e) {
			return false;
		}

		// See if orientation data is there
		if(!isset($exif['Orientation'])){
			return false;
		}
		return $exif['Orientation'];
	}
	
	
	/**
	 * Retrieve the proper x and y position depending on the user's choice of the watermark position.
	 **/
	static public function getPositions( $location , $imageWidth , $imageHeight , $watermarkWidth , $watermarkHeight )
	{
		$position	= new stdClass();
		
		// @rule: Get the appropriate X/Y position for the avatar
		switch( $location )
		{
			case 'top':
				$position->x	= ($imageWidth / 2) - ( $watermarkWidth / 2 );
				$position->y	= 0;
				break;
			case 'bottom':
				$position->x	= ($imageWidth / 2) - ( $watermarkWidth / 2 );
				$position->y	= $imageHeight - $watermarkHeight;
				break;
			case 'left':
				$position->x	= 0;
				$position->y	= ( $imageHeight / 2 ) - ($watermarkHeight / 2);
				break;
			case 'right':
				$position->x 	= $imageWidth - $watermarkWidth;
				$position->y	= ( $imageHeight / 2 ) - ($watermarkHeight / 2);
				break;
		}
		return $position;
	}
	
	/**
	 * Retrieves the appropriate image file name which is already hashed.
	 * 
	 * @param	string	$data	A unique data to be hashed
	 * 	 	 	 
	 **/	 	
	static public function getHashName( $data )
	{
		$name	= JUtility::getHash( $data );
		$name	= JString::substr( $name , 0 , 24 );
		
		return $name;
	}
}

/**
 * Deprecated since 1.8
 * Use JBImageHelper::resize instead. 
 */
function cImageResize($srcPath, $destPath, $destType, $destWidth, $destHeight, $sourceX	= 0, $sourceY	= 0, $currentWidth=0, $currentHeight=0)
{
	return JBImageHelper::resize($srcPath, $destPath, $destType, $destWidth, $destHeight, $sourceX , $sourceY, $currentWidth, $currentHeight );
}

/**
 * Deprecated since 1.8
 * Use JBImageHelper::resizeProportional instead. 
 */
function cImageResizePropotional($srcPath, $destPath, $destType, $destWidth=0, $destHeight=0)
{
	return JBImageHelper::resizeProportional( $srcPath , $destPath , $destType , $destWidth , $destHeight );
}

/**
 * Deprecated since 1.8
 * Use JBImageHelper::createThumb instead. 
 */
function cImageCreateThumb($srcPath, $destPath, $destType, $destWidth=64, $destHeight=64)
{
	return JBImageHelper::createThumb($srcPath, $destPath, $destType, $destWidth, $destHeight);
}

/**
 * Deprecated since 1.8
 * Use JBImageHelper::getExtension instead. 
 */
function cImageTypeToExt($type)
{
	return JBImageHelper::getExtension( $type );
}

/**
 * Deprecated since 1.8
 * Use JBImageHelper::isValidType instead. 
 */
function cValidImageType( $type )
{
	return JBImageHelper::isValidType( $type );
}

/**
 * Deprecated since 1.8
 * Use JBImageHelper::isValid instead. 
 */
function cValidImage( $file )
{
	return JBImageHelper::isValid( $file );
}

/**
 * Deprecated since 1.8
 * Use JBImageHelper::open instead. 
 */
function cImageOpen( $file , $type )
{
	return JBImageHelper::open( $file , $type );
}

/**
 * Deprecated since 1.8
 * Use JBImageHelper::getSize instead. 
 */
function cImageGetSize( $source )
{
	return JBImageHelper::getSize( $source );
}
