<?php 

 /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\
 *  frontend.php - Copyright 2003 Tamlyn Rhodes <tam@zenology.org>     *
 *                                                                     *
 *  This file is part of singapore v0.9.4a                             *
 *                                                                     *
 *  singapore is free software; you can redistribute it and/or modify  *
 *  it under the terms of the GNU General Public License as published  *
 *  by the Free Software Foundation; either version 2 of the License,  *
 *  or (at your option) any later version.                             *
 *                                                                     *
 *  singapore is distributed in the hope that it will be useful,       *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty        *
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.            *
 *  See the GNU General Public License for more details.               *
 *                                                                     *
 *  You should have received a copy of the GNU General Public License  *
 *  along with this; if not, write to the Free Software Foundation,    *
 *  Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA      *
 \* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

//display functions - produce XHTML output

function sgShowAdminBar()
{
  echo "<div class=\"sgAdminBar\">\n";
  echo "  <a href=\"admin.php\">Admin</a>\n";
  echo "  <a href=\"index.php\">Galleries</a>\n";
  echo "  <a href=\"admin.php?action=logout\">Log out</a>\n";
  echo "  <span class=\"sgAdminBarSeparator\"> </span>\n";
  
  if(isset($_REQUEST["image"]) && !(isset($_REQUEST["action"]) && strpos($_REQUEST["action"],"-confirmed"))) {
    echo "  <a href=\"admin.php?action=editimage&amp;gallery=$_REQUEST[gallery]&amp;image=$_REQUEST[image]\">Edit image</a>\n";
    echo "  <a href=\"admin.php?action=deleteimage&amp;gallery=$_REQUEST[gallery]&amp;image=$_REQUEST[image]\">Delete image</a>\n";
    echo "  <a href=\"admin.php?action=newimage&amp;gallery=$_REQUEST[gallery]\">New image</a>\n";
  } elseif(isset($_REQUEST["gallery"]) && !(isset($_REQUEST["action"]) && strpos($_REQUEST["action"],"-confirmed"))) {
    echo "  <a href=\"admin.php?action=editgallery&amp;gallery=$_REQUEST[gallery]\">Edit gallery</a>\n";
    echo "  <a href=\"admin.php?action=deletegallery&amp;gallery=$_REQUEST[gallery]\">Delete gallery</a>\n";
    echo "  <a href=\"admin.php?action=newimage&amp;gallery=$_REQUEST[gallery]\">New image</a>\n";
  } else {
    echo "  <a href=\"admin.php?action=newgallery\">New gallery</a>\n";
  }
  
  echo "</div>\n\n";
}

function sgShowIndex($gallery, $startat)
{
  $dir = sgGetListing($GLOBALS["sgConfig"]->pathto_galleries.$gallery);
  
  //get contaner xhtml
  $code = sgGetContainerCode();
  
  //container frame top (tab)
  echo $code->tab1;
  
  echo "Showing ".($startat+1)."-".($startat+$GLOBALS["sgConfig"]->gallery_thumb_number>count($dir->dirs)?count($dir->dirs):$startat+$GLOBALS["sgConfig"]->gallery_thumb_number)." of ".count($dir->dirs);
  
  if($startat>0) echo " | <a href=\"index.php?startat=".($startat-$GLOBALS["sgConfig"]->gallery_thumb_number)."\">Previous</a>";
  if(count($dir->dirs)>$startat+$GLOBALS["sgConfig"]->gallery_thumb_number) echo " | <a href=\"index.php?startat=".($startat+$GLOBALS["sgConfig"]->gallery_thumb_number)."\">Next</a>";
  
  //container frame middle (tab)
  echo $code->tab2;
  
  for($i=$startat;$i<$startat+$GLOBALS["sgConfig"]->gallery_thumb_number && $i<count($dir->dirs);$i++) {
    $gal = sgGetGalleryInfo($dir->dirs[$i]);
    
    echo "<div class=\"sgGallery\"><table class=\"sgGallery\">\n";
    echo "<tr valign=\"top\">\n";
    echo "  <td class=\"sgGallery\"><a href=\"index.php?gallery=$gal->id\">";
    
    switch($gal->filename) {
    case "__random__" :
      if(count($gal->img)>0) {
        srand(time());
        $index = rand(0,count($gal->img)-1);
        echo "<img src=\"thumb.php?gallery=$gal->id&amp;image={$gal->img[$index]->filename}&amp;size=".$GLOBALS["sgConfig"]->gallery_thumb_size."\" class=\"sgThumbnail\" alt=\"Sample image from gallery\" />";
        break;
      }
    case "__none__" :
      echo "No<br />thumbnail";
      break;
    default :
      echo "<img src=\"thumb.php?gallery=$gal->id&amp;image=$gal->filename&amp;size=".$GLOBALS["sgConfig"]->gallery_thumb_size."\" class=\"sgGallery\" alt=\"Sample image from gallery\" />";
    }
    echo "</a></td>\n";
    echo "  <td><p><strong><a href=\"index.php?gallery=$gal->id\">$gal->name</a></strong></p><p>$gal->desc<br />[".count($gal->img)." images]</p></td>\n";
    echo "</tr>\n";
    echo "</table></div>\n\n";
  }
  
  //container frame bottom
  echo $code->bottom;
}

function sgShowThumbnails($gallery, $startat) 
{
  $gal = sgGetGallery($gallery);
  if(!$gal) {
    echo("<h1>gallery '$gallery' not found</h1>\n");
    sgShowIndex("",0);
    return;
  }
  
  //get contaner xhtml
  $code = sgGetContainerCode();
  
  //heading with gallery name and artist
  echo "<h1 style=\"margin-bottom: 0;\">$gal->name</h1>\n";
  if(!empty($gal->artist)) echo "<p style=\"margin-bottom: 40px; margin-top: 0;\">by $gal->artist</p>\n\n";
  
  //container frame top (tab)
  echo $code->tab1;
  
  echo "Showing ".($startat+1)."-".($startat+$GLOBALS["sgConfig"]->main_thumb_number>count($gal->img)?count($gal->img):$startat+$GLOBALS["sgConfig"]->main_thumb_number)." of ".count($gal->img)." | ";
  
  if($startat>0) echo "<a href=\"index.php?gallery=$gal->id&amp;startat=".($startat-$GLOBALS["sgConfig"]->main_thumb_number)."\">Previous</a> | ";
  echo "<a href=\"index.php\" title=\"Back to galleries list\">Up</a>";
  if(count($gal->img)>$startat+$GLOBALS["sgConfig"]->main_thumb_number) echo " | <a href=\"index.php?gallery=$gal->id&amp;startat=".($startat+$GLOBALS["sgConfig"]->main_thumb_number)."\">Next</a>";
  
  //container frame middle (tab)
  echo $code->tab2;
  
  for($i=$startat;$i<$startat+$GLOBALS["sgConfig"]->main_thumb_number && $i<count($gal->img);$i++) {
    echo "<div class=\"sgThumbnail\">\n";
    echo "  <div class=\"sgThumbnailContent\">\n";
    echo "    <img class=\"borderTL\" src=\"".$GLOBALS["sgConfig"]->pathto_current_theme."images/slide-tl.gif\" alt=\"\" />\n";
    echo "    <img class=\"borderTR\" src=\"".$GLOBALS["sgConfig"]->pathto_current_theme."images/slide-tr.gif\" alt=\"\" />\n";
    
    echo "    <table><tr><td>\n";
    echo "      <a href=\"index.php?gallery=$gal->id&amp;image={$gal->img[$i]->filename}\">";
    echo "<img src=\"thumb.php?gallery=$gal->id&amp;image={$gal->img[$i]->filename}&amp;size=";
    echo $GLOBALS["sgConfig"]->main_thumb_size."\" class=\"sgThumbnail\" alt=\"{$gal->img[$i]->name}";
    if(!empty($gal->img[$i]->artist)) echo " by {$gal->img[$i]->artist}";
    echo "\" /></a>\n";
    echo "    </td></tr></table>\n";
    
    echo "    <div class=\"roundedCornerSpacer\">&nbsp;</div>\n";
    echo "  </div>\n";
    echo "  <div class=\"bottomCorners\">\n";
    echo "    <img class=\"borderBL\" src=\"".$GLOBALS["sgConfig"]->pathto_current_theme."images/slide-bl.gif\" alt=\"\" />\n";
    echo "    <img class=\"borderBR\" src=\"".$GLOBALS["sgConfig"]->pathto_current_theme."images/slide-br.gif\" alt=\"\" />\n";
    echo "  </div>\n";
    if($GLOBALS["sgConfig"]->show_image_name_in_thumbnail_view) echo "  {$gal->img[$i]->name}\n";
    echo "</div>\n\n";
  }
  
  //contaner frame bottom
  echo $code->bottom;
  
  //image info as available
  echo "<p>\n";
  if($gal->email)    echo "<strong>Artist email:</strong> ".strtr($gal->email,array("@" => " <b>at</b> ", "." => " <b>dot</b> "))."<br />\n";
  if($gal->desc)     echo "<strong>Description:</strong> $gal->desc<br />\n";
  if($gal->copyright) echo "<strong>Copyright:</strong> $gal->copyright<br />\n";
  elseif($gal->artist) echo "<strong>Copyright:</strong> $gal->artist<br />\n";
  if($GLOBALS["sgConfig"]->track_views) $hits = sgLogView($gallery);
  if($GLOBALS["sgConfig"]->show_views) echo "<strong>Viewed:</strong> $hits times<br />\n";
  echo "</p>\n";
}

function sgShowImage($gallery, $image) 
{
  //get image object
  $img = sgGetImage($gallery, $image);
  if(!$img) {
    echo("<h1>image '$image' not found</h1>\n");
    sgShowThumbnails($gallery,0);
    return;
  }
  
  //get contaner xhtml
  $code = sgGetContainerCode();
  
  //top navigation bar and preview thumbnails
  echo "<div class=\"sgNavBar\"><p>\n";
  for($i=count($img->prev)-1;$i>=0;$i--)
    echo "<a href=\"index.php?gallery=$gallery&amp;image={$img->prev[$i]->filename}\">".
         "<img src=\"thumb.php?gallery=$gallery&amp;image={$img->prev[$i]->filename}&amp;size=".$GLOBALS["sgConfig"]->preview_thumb_size."\" alt=\"{$img->prev[$i]->name} by {$img->prev[$i]->artist}\" />".
         "</a>\n";
  echo "<img src=\"thumb.php?gallery=$gallery&amp;image={$img->filename}&amp;size=".$GLOBALS["sgConfig"]->preview_thumb_size."\" class=\"sgNavBarCurrent\" alt=\"{$img->name} by {$img->artist}\" />\n";
  for($i=0;$i<count($img->next);$i++)
    echo "<a href=\"index.php?gallery=$gallery&amp;image={$img->next[$i]->filename}\">".
         "<img src=\"thumb.php?gallery=$gallery&amp;image={$img->next[$i]->filename}&amp;size=".$GLOBALS["sgConfig"]->preview_thumb_size."\" alt=\"{$img->next[$i]->name} by {$img->next[$i]->artist}\" />".
         "</a>\n";
  echo "<br />\n";
  if(isset($img->prev[0])) echo "<a href=\"index.php?gallery=$gallery&amp;image={$img->prev[0]->filename}\">Previous</a> | \n"; //<img src=\"content/gallery/images/prev.gif\" alt=\"Previous image\" /> 
  echo "<a href=\"index.php?gallery=$gallery&amp;startat=".(floor($img->index/$GLOBALS["sgConfig"]->main_thumb_number)*$GLOBALS["sgConfig"]->main_thumb_number)."\">Thumbnails</a>\n";
  if(isset($img->next[0])) echo " | <a href=\"index.php?gallery=$gallery&amp;image={$img->next[0]->filename}\">Next</a>\n"; // <img src=\"content/gallery/images/next.gif\" alt=\"Next image\" />
  echo "</p></div>\n\n";
  
  //heading with image name and artist
  echo "<h1 style=\"margin-bottom: 0;\">$img->name</h1>\n";
  if(!empty($img->artist)) echo "<p style=\"margin-bottom: 40px; margin-top: 0;\">by $img->artist</p>\n\n";
  
  //container frame top
  echo $code->top;
  
  echo "      <img src=\"";
  
  //check if image is local (filename does not start with 'http://')
  if(substr($img->filename,0,7)!="http://") 
    echo $GLOBALS["sgConfig"]->pathto_galleries.rawurlencode($gallery)."/".rawurlencode($img->filename);
  else 
    echo $img->filename;
    
  echo "\" alt=\"$img->name";
  if(!empty($img->artist)) echo " by $img->artist";
  echo "\" />\n";
  
  //contaner frame bottom
  echo $code->bottom;
  
  //bottom navigation bar
  echo "<div class=\"sgNavBar\"><p>\n";
  if(isset($img->prev[0])) echo "<a href=\"index.php?gallery=$gallery&amp;image={$img->prev[0]->filename}\">Previous</a> | \n"; //<img src=\"content/gallery/images/prev.gif\" alt=\"Previous image\" /> 
  echo "<a href=\"index.php?gallery=$gallery&amp;startat=".(floor($img->index/$GLOBALS["sgConfig"]->main_thumb_number)*$GLOBALS["sgConfig"]->main_thumb_number)."\">Thumbnails</a>\n";
  if(isset($img->next[0])) echo " | <a href=\"index.php?gallery=$gallery&amp;image={$img->next[0]->filename}\">Next</a>\n"; // <img src=\"content/gallery/images/next.gif\" alt=\"Next image\" />
  echo "</p></div>\n\n";

  //image name and artist
  echo "<p><em>$img->name</em>";
  if(!empty($img->artist)) echo " by $img->artist";
  echo "</p>";
  
  //image info as available
  echo "<p>\n";
  if($img->email)    echo "<strong>Artist email:</strong> ".strtr($img->email,array("@" => " <b>at</b> ", "." => " <b>dot</b> "))."<br />\n";
  if($img->location) echo "<strong>Location:</strong> $img->location<br />\n";
  if($img->date)     echo "<strong>Date:</strong> $img->date<br />\n";
  if($img->desc)     echo "<strong>Description:</strong> $img->desc<br />\n";
  if($img->camera)   echo "<strong>Camera:</strong> $img->camera<br />\n";
  if($img->lens)     echo "<strong>Lens:</strong> $img->lens<br />\n";
  if($img->film)     echo "<strong>Film:</strong> $img->film<br />\n";
  if($img->darkroom) echo "<strong>Darkroom manipulation:</strong> $img->darkroom<br />\n";
  if($img->digital)  echo "<strong>Digital manipulation:</strong> $img->digital<br />\n";
  if($img->copyright) echo "<strong>Copyright:</strong> $img->copyright<br />\n";
  elseif($img->artist) echo "<strong>Copyright:</strong> $img->artist<br />\n";
  if($GLOBALS["sgConfig"]->track_views) $hits = sgLogView($gallery,$image);
  if($GLOBALS["sgConfig"]->show_views) echo "<strong>Viewed:</strong> $hits times<br />\n";
  echo "</p>\n";
}

function sgGetContainerCode()
{
  $code->tab1 = 
  "<div class=\"sgShadow\"><table class=\"sgShadow\" cellspacing=\"0\">\n".
  "  <tr>\n".
  "    <td><img src=\"".$GLOBALS["sgConfig"]->pathto_current_theme."images/shadow-tabl.gif\" alt=\"\" /></td>\n".
  "    <td class=\"tabm\"><table class=\"sgShadowTab\" cellspacing=\"0\"><tr><td>";
  
  $code->tab2 = 
  "</td><td><img src=\"".$GLOBALS["sgConfig"]->pathto_current_theme."images/shadow-tabr.gif\" alt=\"\" /></td></tr></table></td>\n".
  "    <td class=\"tabr\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "  </tr>\n".
  "  <tr>\n".
  "    <td class=\"tl\"><img src=\"images/blank.gif\" width=\"16\" height=\"16\" alt=\"\" /></td>\n".
  "    <td class=\"tm\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "    <td class=\"tr\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "  </tr>\n".
  "  <tr>\n".
  "    <td class=\"ml\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "    <td class=\"mm\">\n\n";
  
  $code->top = 
  "<div class=\"sgShadow\"><table class=\"sgShadow\" cellspacing=\"0\">\n".
  "  <tr>\n".
  "    <td class=\"tl\"><img src=\"images/blank.gif\" width=\"16\" height=\"16\" alt=\"\" /></td>\n".
  "    <td class=\"tm\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "    <td class=\"tr\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "  </tr>\n".
  "  <tr>\n".
  "    <td class=\"ml\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "    <td class=\"mm\">\n\n";
  
  $code->bottom =
  //uncommenting the following line provides a partial workaround to an Opera bug/feature 
  //"&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ".
  "\n    </td>\n".
  "    <td class=\"mr\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "  </tr>\n".
  "  <tr>\n".
  "    <td class=\"bl\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "    <td class=\"bm\"><img src=\"images/blank.gif\" alt=\"\" /></td>\n".
  "    <td class=\"br\"><img src=\"images/blank.gif\" width=\"32\" height=\"32\" alt=\"\" /></td>\n".
  "  </tr>\n".
  "</table></div>\n";
  
  return $code;
}


?>
