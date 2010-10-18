<form action='<?= BASE ?>&C=modules&M=statehousenews&P=update_config' method='post' name='update_config' id='update_config'>

<div class='hidden'><input type='hidden' name='process' value='1' /></div>

<div class='tableHeading' align='left' >
<?= lang('shn_breadcrumb_config') ?>
</div>

<table border='0'  cellspacing='0' cellpadding='0' style='width:100%;'  class='tableBorder' >

<?php $style_counter = 0; ?>

<?php $attributes = $config->to_a(); ?>
<?php foreach ($attributes as $attr => $value) : ?>
    <?php $label = lang('shn_config_attr_'.$attr); ?>
    <?php $style = ($style_counter++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ?> 
    <?php if ($attr == 'enabled') $value = $value ? lang('shn_yes') : lang('shn_no'); ?>
    <tr>
        <td  class='<?= $style ?>'  style='width:50%;' valign='top'>
            <?= $label ?>
        </td>
        <td  class='<?= $style ?>'  style='width:50%;' valign='top'>
            <input dir='ltr' style='width:20' type='text' name='<?= $attr ?>' id='<?= $attr ?>' value='<?= $value ?>' size='20' maxlength='100' class='' />
        </td>
    </tr>
<?php endforeach; ?>

</table>

<input name='submit' type='submit' class='submit' value='<?= lang('shn_update_config_button') ?>' />
</form>
