<h1><?php echo $sg->translator->_g("User Management"); ?></h1>

<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table class="formTable">
<input type="hidden" name="action" value="saveuser" />
<input type="hidden" name="user" value="<?php echo $_REQUEST["user"]; ?>" />
<?php 
  $users = $sg->io->getUsers();
  for($i=0; $i<count($users); $i++)
    if($users[$i]->username == $_REQUEST["user"]) {
      $usr = $users[$i];
      break;
    }
  
  echo "<tr><td>".$sg->translator->_g("Username")."</td><td><strong>".$usr->username."</strong></td></tr>\n";
  if($sg->user->isAdmin())
    echo "<tr><td>".$sg->translator->_g("Type").'</td><td>'.
         '<label for="sgTypeAdmin"><input type="radio" class="radio" id="sgTypeAdmin" name="sgType" value="admin"'.($usr->permissions & SG_ADMIN ? ' checked="true"' : "").' />'.$sg->translator->_g("Administrator")."</label><br />\n".
         '<label for="sgTypeUser"><input type="radio" class="radio" id="sgTypeUser" name="sgType" value="user"'. ($usr->permissions & SG_ADMIN ? "" : ' checked="true"').' />'.$sg->translator->_g("User")."</label></td></tr>\n";
  if($sg->user->isAdmin())
    echo "<tr><td>".$sg->translator->_g("Groups").'</td><td><input type="input" name="sgGroups" value="'.$usr->groups."\" /></td></tr>\n";
  echo "<tr><td>".$sg->translator->_g("Email").'</td><td><input type="input" name="sgEmail" value="'.$usr->email."\" /></td></tr>\n";
  echo "<tr><td>".$sg->translator->_g("Full name").'</td><td><input type="input" name="sgFullname" value="'.$usr->fullname."\" /></td></tr>\n";
  echo "<tr><td>".$sg->translator->_g("Description").'</td><td><input type="input" name="sgDescription" value="'.$usr->description."\" /></td></tr>\n";
  if($sg->user->isAdmin() && !$usr->isGuest())
    echo "<tr><td>".$sg->translator->_g("Password").'</td><td><input type="input" name="sgPassword" value="**********" /></td></tr>'."\n";
  
?>
<tr>
  <td></td>
  <td><input type="submit" class="button" value="<?php /*"*/ echo $sg->translator->_g("Save Changes") ?>" /></td>
</tr>
</table>
</form>
