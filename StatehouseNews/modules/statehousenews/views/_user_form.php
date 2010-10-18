<table border='0' cellspacing='0' cellpadding='0' style='width:100%;' class='tableBorder' >

<tr>
  <td class='tableCellTwo' style='width:20%;' valign='middle'>
    <?= lang('shn_user_attr_name') ?>
  </td>
  <td class='tableCellTwo' style='width:80%;' valign='bottom'>
    <input dir='ltr' style='width:40' type='text' name='name' id='name' value='<?= $user->name() ?>' size='40' maxlength='100' class='' />
  </td>
</tr>

<tr>
  <td class='tableCellOne' style='width:20%;' valign='middle'>
    <?= lang('shn_user_attr_email') ?>
  </td>
  <td class='tableCellOne' style='width:80%;' valign='bottom'>
    <input dir='ltr' style='width:40' type='text' name='email' id='email' value='<?= $user->email() ?>' size='40' maxlength='100' class='' />
  </td>
</tr>

<tr>
  <td class='tableCellTwo' style='width:20%;' valign='middle'>
    <?= lang('shn_user_attr_station') ?>
  </td>
  <td class='tableCellTwo' style='width:80%;' valign='bottom'>
    <input dir='ltr' style='width:40' type='text' name='station' id='station' value='<?= $user->station() ?>' size='40' maxlength='100' class='' />
  </td>
</tr>

<tr>
  <td class='tableCellOne' style='width:20%;' valign='middle'>
    <?= lang('shn_user_attr_password') ?>
  </td>
  <td class='tableCellOne' style='width:80%;' valign='bottom'>
    <input dir='ltr' style='width:40' type='password' name='password' id='password' value='' size='40' maxlength='100' class='' />
  </td>
</tr>

<tr>
  <td class='tableCellTwo' style='width:20%;' valign='middle'>
    <?= lang('shn_user_attr_password_confirmation') ?>
  </td>
  <td class='tableCellTwo' style='width:80%;' valign='bottom'>
    <input dir='ltr' style='width:40' type='password' name='password_confirmation' id='password_confirmation' value='' size='40' maxlength='100' class='' />
  </td>
</tr>

</table>
