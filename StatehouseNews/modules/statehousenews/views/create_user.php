<form action='<?= BASE ?>&C=modules&M=statehousenews&P=create_user' method='post' name='create_user' id='create_user'>

<div class='hidden'><input type='hidden' name='process' value='1' /></div>

<div class='tableHeading' align='left' >
<?= lang('shn_breadcrumb_create_user') ?>
</div>

<?php $vars = array('user' => $user); ?>
<?= partial('_user_form.php', $vars, TRUE) ?>

<input name='submit' type='submit' class='submit' value='<?= lang('shn_create_user_button') ?>' />
</form>
