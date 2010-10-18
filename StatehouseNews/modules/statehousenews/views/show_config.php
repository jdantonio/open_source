<div class='tableHeading' align='left' >
<?= lang('shn_breadcrumb_config') ?>
</div>

<table border='0'  cellspacing='0' cellpadding='0' style='width:100%;'  class='tableBorder' >

<tr> 
    <td  class='tableHeadingAlt' > 
        <?= lang('shn_config_item') ?>
    </td> 
    <td  class='tableHeadingAlt' > 
        <?= lang('shn_config_setting') ?>
    </td> 
</tr> 

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
            <?= $value ?>
        </td>
    </tr>
<?php endforeach; ?>

</table>
 
<form action='<?= BASE ?>&C=modules&M=statehousenews&P=update_config' method='post' name='update_config' id='update_config'> 
<input name='submit' type='submit' class='submit' value='<?= lang('shn_update_config_button') ?>' /> 
</form> 
