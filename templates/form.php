<form id="form-signin" class="form-signin" action="" method="post" accept-charset="utf-8">
  <h2>Get Your PHPMaster Membership Card Now!</h2>
  <p>Your picture will be fetched using the <a href="http://en.gravatar.com/">Gravatar</a> account associated with your email address.</p>
<?php
if (!empty($errors)) {
?>
  <div class="alert alert-error">
   <button type="button" class="close" data-dismiss="alert">&times;</button>
   <h4>Ops! There were some errors...</h4>
   <ul>
<?php
foreach ($errors as $message) {
    echo '<li>' . $message . '</li>';
}
?>
   </ul>
  </div>
<?php
}
?>
  <input required type="text" name="membername" class="input-block-level" placeholder="Your Name (eg. John Smith)" value="<?php echo (!empty($memberName)) ? $memberName : ''; ?>">
  <input required type="email" name="membermail" class="input-block-level" placeholder="Email address" value="<?php echo (!empty($memberMail)) ? $memberMail : '';?>">
  <input type="text" name="memberfunction" class="input-block-level" placeholder="Favorite function (eg. sprintf)" value="<?php echo (!empty($memberFavFunction)) ? $memberFavFunction : '';?>">
<?php
if (empty($_POST) || !empty($errors)) {
?>
  <button class="btn btn-large btn-primary" type="submit" name="action" value="preview">Preview Pass</button>
<?php
}

if (!empty($memberName) && empty($errors)) {
?>
  <button class="btn btn-large btn-primary" type="submit" name="action" value="getpass">Get Your Pass</button> 
  <button class="btn" type="submit" name="action" value="preview">Update Preview</button> 
   or <a href="./">Start Over</a>
<?php
}
?>
</form>
