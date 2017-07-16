<?php

/* ------------------------------------------------------------------------
  # SecureImage
  # ------------------------------------------------------------------------
  # author   Mesut Timur
  # copyright Mesut Timur
  # date : 03/2008
  # @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Contact : mesut@h-labs.org
  # Version: v0.3
  ------------------------------------------------------------------------- */

class SecureImage
{
	//private vars
	var $_file;
	var $_image;
	var $_extension;
	//constructor
	function __construct($file_)
	{
		$this->_file = $file_;
        	// Get extension
        	$this->_extension = strrchr($this->_file, '.');
        	$this->_extension = strtolower($this->_extension);
    	}
	//check routine
    	function checkIt()
    	{ 
    		//if it can be opened
        	$this->_image=$this->_openImage($this->_file);
        	if($this->_image==false)
        		return false;
        	//removing EXIF	
        	$this->_convert();
        	return true;	
	 }
	function _openImage($file) 
	{
        	switch($this->_extension) 
        	{	
                	case '.jpg':
                	case '.jpeg':
                        	$im = @imagecreatefromjpeg($this->_file);
                        	break;
                	case '.gif':
                        	$im = @imagecreatefromgif($this->_file);
                        	break;
                	case '.png':
                		$im=@imagecreatefrompng($this->_file);
                                break;

                	default:
                        	$im = false;
                        	break;
        	}
        	return $im;
	}
	function _convert()
	{
		switch($this->_extension) 
		{
        		case '.jpg':
        		case '.jpeg':
                		imagegif($this->_image, $this->_file);
        			imagejpeg($this->_image, $this->_file);
                		$this->_jpgclean("clean.tmp");	
				rename("clean.tmp",$this->_file);
				break;
        		case '.gif':
                		imagejpeg($this->_image, $this->_file);
        			imagegif($this->_image, $this->_file);
                		break;
        		case '.png':
                		imagejpeg($this->_image, $this->_file);
        			imagepng($this->_image, $this->_file);
                		break;
        		default:
                		die("Bir Terslik Var");
		}
	}
	
	function _jpgclean($destination, $erstellen = TRUE)
	{
		//by Robert Beran
		//webmaster@robert-beran.de
		$handle = fopen($this->_file, "rb");
		$segment[] = fread($handle, 2);
		if($segment[0] === "\xFF\xD8")
		{
      			$segment[] = fread($handle, 1);
			if($segment[1] === "\xFF")
      			{
        			rewind ($handle);
        			while(!feof($handle))
        			{
          				$daten = fread($handle, 2);
          				if( (preg_match("/FFE[1-9a-zA-Z]{1,1}/i",bin2hex($daten))) || ($daten === "\xFF\xFE") )
          				{	
            					$position = ftell($handle);
            					$size = fread($handle, 2);
            					$newsize = 256 * ord($size{0}) + ord($size{1});
            					$newpos = $position + $newsize;
            					fseek($handle, $newpos);
          				}
          				else
          				{
           					$newfile[] = $daten;
          				}
				}
        			fclose($handle);
        			$newfile = implode('',$newfile);
        			if($erstellen === TRUE)
        			{
          				$handle = fopen($destination, "wb");
          				fwrite($handle, $newfile);
          				fclose($handle);
          				return TRUE;
        			}
        			else
        			{
          				return $newfile;
        			}
      			}
     			else
      			{
          			return FALSE;
      			}
    		}
    		else
    		{	
          		return FALSE;
    		}
  	}
}
?>
