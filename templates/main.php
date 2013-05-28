<?php
require_once '_header.php';
?>
<div class="container">
 <div class="alert alert-warning">
  <strong>Please Note</strong>: This is not a production application. This is a proof of concept for <a href="http://phpmaster.com">PHPMaster</a>. All of the passes issued are for testing purposes.
 </div>
 <div class="row-fluid">
  <!-- Pass Preview -->
  <div id="pass-preview" class="pass span3">
   <div class="row-fluid pass-header">
    <h2><img src="img/logo.php" alt="">PHPMaster.com</h2>
   </div>
   <div class="row-fluid pass-primary">
    <div class="span8">
     <div class="pass-member-name"><?php echo (!empty($memberName)) ? $memberName : 'John Smith'; ?></div>
    </div>
    <div class="span4">
<?php
if (!empty($memberThumbnail)) {
?>
     <img class="pass-thumbnail" src="<?=$memberThumbnail?>">
<?php
}
else {
?>
     <img class="pass-thumbnail" data-src="holder.js/60x60">
<?php
}
?>
    </div>
   </div>
   <div class="row-fluid pass-secondary">
    <span class="label">Member Since</span>
    <span class="pass-field secondary-field member-subscription-date"><?php echo (!empty($memberSubscription)) ? date('Y', $memberSubscription) : 'YYYY';?></span>
   </div>
   <div class="row-fluid pass-auxiliary">
    <span class="label">Favorite Function</span>
    <span class="pass-field aux-field member-favorite-function"><?php echo (!empty($memberFavFunction)) ? $memberFavFunction . '()' : 'n.a.';?></span>
   </div>
   <div class="pass-footer">
    <img src="img/barcode_sample.gif" width="255" height="72" alt="">
   </div>
  </div>

  <!-- Pass Form -->
  <div class="span9">
<?php
require 'form.php';
?>
  </div>
 </div>
</div>
<?php
require '_footer.php';
