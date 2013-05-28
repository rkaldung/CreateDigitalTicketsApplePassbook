<?php require_once '_header.php'; ?>

<div class="container">

    <div class="row-fluid">

        <?php if (!empty($errors)): ?><div class="alert alert-error">
        
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        
            <h4>Ops! There were some errors...</h4>
        
            <ul>
        
            <?php foreach ($errors as $field => $message): ?>
                <li><?=$message?></li>
            <?php endforeach; ?>
        
            </ul>
        </div><?php else: ?><div class="alert alert-success">
            
            <h4>Congratulations <?=$memberName?>! </h4>
            
            <p>We've just sent your pass to '<?=$memberMail?>', but if you're impatient 
                <a href="<?=$passDownloadUrl;?>">download it now</a></p>
            
        </div><?php endif; ?>

    </div>
</div>

<?php require_once '_footer.php';
