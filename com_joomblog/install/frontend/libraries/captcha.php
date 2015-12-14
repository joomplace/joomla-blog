<?php
/**
* JoomBlog component for Joomla
* @package JoomBlog
* @author JoomPlace Team
* @Copyright Copyright (C) JoomPlace, www.joomplace.com
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die( 'Restricted access' );


function getCaptcha(){
  ob_clean();

  $count=5;
  $width=90; 
  $height=24;
  $font_size_min=20;
  $font_size_max=20; 
  $font_file = JB_COM_PATH.DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."Comic_Sans_MS.ttf";
  $char_angle_min=-10;
  $char_angle_max=10;
  $char_angle_shadow=5;
  $char_align=22;
  $start=1;
  $interval=16;
  $chars="0123456789";
  $noise=5;

  $image=imagecreatetruecolor($width, $height);

  $background_color=imagecolorallocate($image, 223, 207, 255);
  $font_color=imagecolorallocate($image, 32, 64, 96);

  imagefill($image, 0, 0, $background_color);

  $str="";

  $num_chars=strlen($chars);
  for ($i=0; $i<$count; $i++)
  {
    $char=$chars[rand(0, $num_chars-1)];
    $font_size=rand($font_size_min, $font_size_max);
    $char_angle=rand($char_angle_min, $char_angle_max);
    imagettftext($image, $font_size, $char_angle, $start, $char_align, $font_color, $font_file, $char);
    imagettftext($image, $font_size, $char_angle+$char_angle_shadow*(rand(0, 1)*2-1), $start, $char_align, $background_color, $font_file, $char);
    $start+=$interval;
    $str.=$char;
  }

  if ($noise)
  {
    for ($i=0; $i<$width; $i++)
    {
      for ($j=0; $j<$height; $j++)
      {
        $rgb=imagecolorat($image, $i, $j);
        $r=($rgb>>16) & 0xFF;
        $g=($rgb>>16) & 0xFF;
        $b=$rgb & 0xFF;
        $k=rand(-$noise, $noise);
        $rn=$r+255*$k/100;
        $gn=$g+255*$k/100;		
        $bn=$b+255*$k/100;
        if ($rn<0) $rn=0;
        if ($gn<0) $gn=0;
        if ($bn<0) $bn=0;
        if ($rn>255) $rn=255;
        if ($gn>255) $gn=255;
        if ($bn>255) $bn=255;
        $color=imagecolorallocate($image, $rn, $gn, $bn);
        imagesetpixel($image, $i, $j , $color);					
      }
    }
  }

  $sessions = JFactory::getSession();
  $sessions->set("captcha",$str);

  if (function_exists("imagepng"))
  {
    header("Content-type: image/png");
    imagepng($image);
  }
  elseif (function_exists("imagegif"))
  {
    header("Content-type: image/gif");
    imagegif($image);
  }
  elseif (function_exists("imagejpeg"))
  {
    header("Content-type: image/jpeg");
    imagejpeg($image);
  }

  imagedestroy($image);
}

?>
