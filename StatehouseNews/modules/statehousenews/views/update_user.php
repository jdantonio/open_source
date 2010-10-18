<form action='<?= BASE ?>&C=modules&M=statehousenews&P=update_user' method='post' name='update_user' id='update_user'>
<input type='hidden' name='id' id='id' value='<?= $user->id() ?>' />

<div class='hidden'><input type='hidden' name='process' value='1' /></div>

<div class='tableHeading' align='left' >
<?= lang('shn_breadcrumb_update_user') ?>
</div>

<?php $vars = array('user' => $user); ?>
<?= partial('_user_form.php', $vars, TRUE) ?>

<input name='submit' type='submit' class='submit' value='<?= lang('shn_update_user_button') ?>' />
</form>
