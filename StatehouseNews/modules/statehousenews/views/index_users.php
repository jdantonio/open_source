<div class='tableHeading' align='left' > 
<?= lang('shn_breadcrumb_users') ?>
</div> 
 
<?php $style_counter = 0; ?>
 
<table border='0'  cellspacing='0' cellpadding='0' style='width:100%;' > 

<tr> 
    <td  class='tableHeadingAlt' > 
        <?= lang('shn_user_attr_name') ?>
    </td> 
    <td  class='tableHeadingAlt' > 
        <?= lang('shn_user_attr_email') ?>
    </td> 
    <td  class='tableHeadingAlt' > 
        <?= lang('shn_user_attr_station') ?>
    </td> 
    <td  class='tableHeadingAlt' > 
        <?= lang('shn_user_attr_enabled') ?>
    </td> 
    <td  class='tableHeadingAlt' > 
        &nbsp;
    </td> 
</tr> 

<?php foreach ($users as $user) : ?>
<?php $style = ($style_counter++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ?> 

<tr> 
    <td  class='<?= $style ?>'  style='width:20%;' valign='top'> 
        <a href='<?= BASE ?>&C=modules&M=statehousenews&P=show_user&id=<?= $user->id() ?>' ><?= $user->name() ?></a> 
    </td> 
    <td  class='<?= $style ?>'  style='width:20%;' valign='top'> 
        <?= $user->email() ?>
    </td> 
    <td  class='<?= $style ?>'  style='width:20%;' valign='top'> 
        <?= $user->station() ?>
    </td> 
    <td  class='<?= $style ?>'  style='width:20%;' valign='top'> 
        <?= $user->enabled() ? lang('shn_yes') : lang('shn_no') ?>
    </td> 
    <td  class='<?= $style ?>'  style='width:20%;' valign='top'> 
        <a href='<?= BASE ?>&C=modules&M=statehousenews&P=update_user&id=<?= $user->id() ?>' >[<?= lang('shn_user_update_link') ?>]</a> 
        <?php $command = $user->enabled()  ? 'disable_user' : 'enable_user' ?>
        <?php $text = $user->enabled()  ? 'shn_user_disable_link' : 'shn_user_enable_link' ?>
        <a href='<?= BASE ?>&C=modules&M=statehousenews&P=<?= $command ?>&id=<?= $user->id() ?>' >[<?= lang($text) ?>]</a> 
    </td> 
</tr> 

<?php endforeach; ?>

</table> 
 
<form action='<?= BASE ?>&C=modules&M=statehousenews&P=create_user' method='post' name='create_user' id='create_user'> 
<input name='submit' type='submit' class='submit' value='<?= lang('shn_create_user_button') ?>' /> 
</form> 
