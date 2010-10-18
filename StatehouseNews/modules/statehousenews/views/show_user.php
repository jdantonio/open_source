<div class='tableHeading' align='left' >
<?= lang('shn_breadcrumb_show_user') ?>
</div>

<table border='0'  cellspacing='0' cellpadding='0' style='width:100%;'  class='tableBorder' >

<?php $style_counter = 0; ?>

<?php $attributes = $user->to_a(); ?>
<?php foreach ($attributes as $attr => $value) : ?>
    <?php if (preg_match('/^pwd/', $attr) == 0) : ?>
        <?php $label = lang('shn_user_attr_'.$attr); ?>
        <?php $style = ($style_counter++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ?> 
        <?php if ($attr == 'enabled') $value = $value ? lang('shn_yes') : lang('shn_no'); ?>
        <tr>
            <td  class='<?= $style ?>'  style='width:50%;' valign='top'>
                <?= $label ?>
            </td>
            <td  class='<?= $style ?>'  style='width:50%;' valign='top'>
                <?= $value ?>
            </td>
        </tr>
    <?php endif; ?>
<?php endforeach; ?>

</table>
 
<form action='<?= BASE ?>&C=modules&M=statehousenews&P=update_user' method='post' name='update_user' id='update_user'> 
<input type='hidden' name='id' id='id' value='<?= $user->id() ?>' />
<input name='submit' type='submit' class='submit' value='<?= lang('shn_update_user_button') ?>' /> 
</form> 
