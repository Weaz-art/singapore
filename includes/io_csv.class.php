<?php 

/**
 * IO class.
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 * @version $Id: io_csv.class.php,v 1.12 2004/09/28 00:51:36 tamlyn Exp $
 */

/**
 * Class used to read and write data to and from CSV files.
 * @see sgIO_iifn
 * @package singapore
 * @author Tamlyn Rhodes <tam at zenology dot co dot uk>
 * @copyright (c)2003, 2004 Tamlyn Rhodes
 */
class sgIO_csv {
  
  /**
   * instance of a {@link sgConfig} object representing the current 
   * script configuration
   * @var sgConfig
   */
  var $config;
  
  /**
   * @param sgConfig pointer to a {@link sgConfig} object representing 
   *   the current script configuration
   */
  function sgIO_csv(&$config)
  {
    $this->config =& $config;
  }

  /**
   * Fetches gallery info for the specified gallery and immediate children.
   * @param string gallery id
   * @param string language code spec for this request (optional)
   */
  function getGallery($galleryId, $language = null) 
  {
    $gal = new sgGallery($galleryId);

    if($language == null) $language = $this->config->default_language;
    
    //try to open language specific metadata
    $fp = @fopen($this->config->base_path.$this->config->pathto_galleries.$galleryId."/metadata.$language.csv","r");
    
    //if fail then try to open generic metadata
    if(!$fp) 
      $fp = @fopen($this->config->base_path.$this->config->pathto_galleries.$galleryId."/metadata.csv","r");
    if($fp) {

      while($temp[] = fgetcsv($fp,2048));
      fclose($fp);
      
      list(
        $gal->filename,
        ,
        $gal->owner,
        $gal->groups,
        $gal->permissions,
        $gal->categories,
        $gal->name,
        $gal->artist,
        $gal->email,
        $gal->copyright,
        $gal->desc,
        $gal->summary,
        $gal->date
      ) = $temp[1];
      
      
      for($i=0;$i<count($temp)-3;$i++) {
        $gal->images[$i] = new sgImage();
        list(
          $gal->images[$i]->filename,
          $gal->images[$i]->thumbnail,
          $gal->images[$i]->owner,
          $gal->images[$i]->groups,
          $gal->images[$i]->permissions,
          $gal->images[$i]->categories,
          $gal->images[$i]->name,
          $gal->images[$i]->artist,
          $gal->images[$i]->email,
          $gal->images[$i]->copyright,
          $gal->images[$i]->desc,
          $gal->images[$i]->location,
          $gal->images[$i]->date,
          $gal->images[$i]->camera,
          $gal->images[$i]->lens,
          $gal->images[$i]->film,
          $gal->images[$i]->darkroom,
          $gal->images[$i]->digital
        ) = $temp[$i+2];
      
        //get image size and type
        list(
          $gal->images[$i]->width, 
          $gal->images[$i]->height, 
          $gal->images[$i]->type
        ) = substr($gal->images[$i]->filename, 0, 7)=="http://"
            ? @GetImageSize($gal->images[$i]->filename)
            : @GetImageSize($this->config->base_path.$this->config->pathto_galleries.$gal->id."/".$gal->images[$i]->filename);
      }
        
    } elseif(file_exists($this->config->base_path.$this->config->pathto_galleries.$galleryId)) { 
      //no metadata so use iifn

      $bits = explode("/",$gal->id);
      $temp = strtr($bits[count($bits)-1], "_", " ");
      if($temp == ".")
        $gal->name = $this->config->gallery_name;
      elseif($this->config->enable_iifn && strpos($temp, " - ")) 
        list($gal->artist,$gal->name) = explode(" - ", $temp);
      else
        $gal->name = $temp;
      
      $dir = Singapore::getListing($this->config->base_path.$this->config->pathto_galleries.$gal->id."/", "images");
      
      //set gallery thumbnail to first image in gallery (if any)
      if(isset($dir->files[0])) $gal->filename = $dir->files[0];
      
      for($i=0;$i<count($dir->files);$i++) {
        $gal->images[$i] = new sgImage();
        $gal->images[$i]->filename = $dir->files[$i];
        //trim off file extension and replace underscores with spaces
        $temp = strtr(substr($gal->images[$i]->filename, 0, strrpos($gal->images[$i]->filename,".")-strlen($gal->images[$i]->filename)), "_", " ");
        //split string in two on " - " delimiter
        if($this->config->enable_iifn && strpos($temp, " - ")) 
          list($gal->images[$i]->artist,$gal->images[$i]->name) = explode(" - ", $temp);
        else
          $gal->images[$i]->name = $temp;
      
        //get image size and type
        list(
          $gal->images[$i]->width, 
          $gal->images[$i]->height, 
          $gal->images[$i]->type
        ) = @GetImageSize($this->config->base_path.$this->config->pathto_galleries.$gal->id."/".$gal->images[$i]->filename);
      }
    } else {
      //selected gallery does not exist
      return null;
    }
    
    $dir = Singapore::getListing($this->config->base_path.$this->config->pathto_galleries.$gal->id."/", "dirs");

    foreach($dir->dirs as $gallery) 
      $gal->galleries[] = $this->getSubGallery($this->config->base_path.$this->config->pathto_galleries, $gal->id."/".$gallery, $language);
    
    return $gal;
  }
  
  /**
   * @private
   */
  function getSubGallery($path, $galleryId, $language)
  {
    
    $gallery = new sgGallery($galleryId);
    
    $fp = @fopen($path.$galleryId."/metadata.$language.csv","r");
    if(!$fp) 
      $fp = @fopen($path.$galleryId."/metadata.csv","r");
    
    if($fp) { //get info from metadata file

      while($temp[] = fgetcsv($fp,2048));
      fclose($fp);
      
      list(
        $gallery->filename,
        ,
        $gallery->owner,
        $gallery->groups,
        $gallery->permissions,
        $gallery->categories,
        $gallery->name,
        $gallery->artist,
        $gallery->email,
        $gallery->copyright,
        $gallery->desc,
        $gallery->summary,
        $gallery->date
      ) = $temp[1];
      
      for($i=0;$i<count($temp)-3;$i++) {
        $gallery->images[$i] = new sgImage();
        list(
          $gallery->images[$i]->filename,
          $gallery->images[$i]->thumbnail,
          $gallery->images[$i]->owner,
          $gallery->images[$i]->groups,
          $gallery->images[$i]->permissions,
          $gallery->images[$i]->categories,
          $gallery->images[$i]->name,
          $gallery->images[$i]->artist,
          $gallery->images[$i]->email,
          $gallery->images[$i]->copyright,
          $gallery->images[$i]->desc,
          $gallery->images[$i]->location,
          $gallery->images[$i]->date,
          $gallery->images[$i]->camera,
          $gallery->images[$i]->lens,
          $gallery->images[$i]->film,
          $gallery->images[$i]->darkroom,
          $gallery->images[$i]->digital
        ) = $temp[$i+2];
      }
    
    } else { //get info from file name (iifn)
   
      $bits = explode("/",$galleryId);
      $temp = strtr($bits[count($bits)-1], "_", " ");
      if($this->config->enable_iifn && strpos($temp, " - ")) 
        list($gallery->artist,$gallery->name) = explode(" - ", $temp);
      else
        $gallery->name = $temp;
      
      $dir = Singapore::getListing($path.$galleryId."/", "images");
      
      //set gallery thumbnail to first image in gallery (if any)
      if(isset($dir->files[0])) $gallery->filename = $dir->files[0];
      
      for($i=0;$i<count($dir->files);$i++) {
        $gallery->images[$i] = new sgImage;
        $gallery->images[$i]->filename = $dir->files[$i];
        //trim off file extension and replace underscores with spaces
        $temp = strtr(substr($gallery->images[$i]->filename, 0, strrpos($gallery->images[$i]->filename,".")-strlen($gallery->images[$i]->filename)), "_", " ");
        //split string in two on " - " delimiter
        if($this->config->enable_iifn && strpos($temp, " - ")) 
          list($gallery->images[$i]->artist,$gallery->images[$i]->name) = explode(" - ", $temp);
        else
          $gallery->images[$i]->name = $temp;
      }
      
    }
    
    $dir = Singapore::getListing($path.$galleryId."/", "dirs");
    
    foreach($dir->dirs as $gal) 
      $gallery->galleries[] = $gal;
   
    return $gallery;
  }
  
  function putGallery($gallery) {
    
    $fp = fopen($this->config->base_path.$this->config->pathto_galleries.$gallery->id."/metadata.csv","w");
    
    if(!$fp) return false;
    
    $success = true;
  
    $success &= (bool) fwrite($fp,"filename,thumbnail,owner,group(s),permissions,catergories,image name,artist name,artist email,copyright,image description,image location,date taken,camera info,lens info,film info,darkroom manipulation,digital manipulation");
    $success &= (bool) fwrite($fp,"\n\"".
      $gallery->filename."\",,".
      $gallery->owner.",".
      $gallery->groups.",".
      $gallery->permissions.",".
      $gallery->categories.',"'.
      str_replace('"','""',$gallery->name).'","'.
      str_replace('"','""',$gallery->artist).'","'.
      str_replace('"','""',$gallery->email).'","'.
      str_replace('"','""',$gallery->copyright).'","'.
      str_replace('"','""',$gallery->desc).'","'.
      str_replace('"','""',$gallery->summary).'","'.
      str_replace('"','""',$gallery->date).'"'
    );
    
    for($i=0;$i<count($gallery->images);$i++)
      $success &= (bool) fwrite($fp,"\n\"".
        $gallery->images[$i]->filename."\",".
        $gallery->images[$i]->thumbnail.",".
        $gallery->images[$i]->owner.",".
        $gallery->images[$i]->groups.",".
        $gallery->images[$i]->permissions.",".
        $gallery->images[$i]->categories.',"'.
        str_replace('"','""',$gallery->images[$i]->name).'","'.
        str_replace('"','""',$gallery->images[$i]->artist).'","'.
        str_replace('"','""',$gallery->images[$i]->email).'","'.
        str_replace('"','""',$gallery->images[$i]->copyright).'","'.
        str_replace('"','""',$gallery->images[$i]->desc).'","'.
        str_replace('"','""',$gallery->images[$i]->location).'","'.
        str_replace('"','""',$gallery->images[$i]->date).'","'.
        str_replace('"','""',$gallery->images[$i]->camera).'","'.
        str_replace('"','""',$gallery->images[$i]->lens).'","'.
        str_replace('"','""',$gallery->images[$i]->film).'","'.
        str_replace('"','""',$gallery->images[$i]->darkroom).'","'.
        str_replace('"','""',$gallery->images[$i]->digital).'"'
      );
    $success &= (bool) fclose($fp);
  
    return $success;
  }
  
  function getHits($galleryId) {
    
    
    $fp = @fopen($this->config->base_path.$this->config->pathto_logs.strtr("views".$galleryId,":/?\\","----").".log","r");
    
    if($fp) {
      while($temp[] = fgetcsv($fp,255));
      fclose($fp);
    } else $temp = array();
    
    $hits = new stdClass;
    
    if(isset($temp[0]))
      list(
        ,
        $hits->hits,
        $hits->lasthit
      ) = $temp[0];
    else {
      $hits->hits = 0;
      $hits->lasthit = 0;
    }
    
    $hits->images = array();
    
    for($i=0;$i<count($temp)-2;$i++) {
      $hits->images[$i] = new stdClass;
      list(
        $hits->images[$i]->filename,
        $hits->images[$i]->hits,
        $hits->images[$i]->lasthit
      ) = $temp[$i+1];
    }
    
    return $hits;
  }
  
  function putHits($galleryId, $hits) {
  
    $fp = fopen($this->config->base_path.$this->config->pathto_logs.strtr("views".$galleryId,":/?\\","----").".log","w");
    if(!$fp) return false;
    
    fwrite($fp, ",".
      $hits->hits.",".
      $hits->lasthit
    );
      
    if(isset($hits->images)) 
      for($i=0;$i<count($hits->images);$i++)
        if(!empty($hits->images[$i]->filename)) fwrite($fp, "\n".
          $hits->images[$i]->filename.",".
          $hits->images[$i]->hits.",".
          $hits->images[$i]->lasthit
        );
      
    return true;
  }
  
  function getUsers() {
    $fp = fopen($this->config->base_path.$this->config->pathto_data_dir."users.csv","r");
    
    //strip off description line
    fgetcsv($fp,1024);
    
    for($i=0;$entry = fgetcsv($fp,1000,",");$i++) {
      $users[$i] = new sgUser(null,null);
      list(
        $users[$i]->username,
        $users[$i]->userpass,
        $users[$i]->permissions,
        $users[$i]->groups,
        $users[$i]->email,
        $users[$i]->fullname,
        $users[$i]->description,
        $users[$i]->stats
      ) = $entry;
    }
    
    fclose($fp);
    return $users;
  }
  
  function putUsers($users) {
    $fp = fopen($this->config->base_path.$this->config->pathto_data_dir."users.csv","w");
    if(!$fp) return false;
    
    $success = (bool) fwrite($fp,"username,md5(pass),permissions,group(s),email,name,description,stats\n");
    for($i=0;$i<count($users);$i++) 
      $success &= (bool) fwrite($fp,$users[$i]->username.",".$users[$i]->userpass.",".$users[$i]->permissions.",\"".$users[$i]->groups."\",\"".$users[$i]->email."\",\"".$users[$i]->fullname."\",\"".$users[$i]->description."\",\"".$users[$i]->stats."\"\n");
    
    fclose($fp);
    return $success;
  }
}

?>
