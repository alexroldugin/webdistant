<ul>
  <? if( sizeof( $this->_mErrors ) ) :?>
    <li class="errors"><?= $this->getErrorsBlock(); ?></li>
  <? endif; ?>
  <? if( sizeof( $this->_mMessages ) ) :?>
    <li class="messages"><?= $this->getMessagesBlock(); ?></li>
  <? endif; ?>
  <li>
    <form method="post" action="" id="create-dialog-form">
      <table width="100%" cellpadding="5" cellspacing="0">
        <tr>
          <th valign="top" width="70"><label for="term">Термин:</label></th>
          <td><input type="text" name="term" id="term" value="" style="width: 100%"/></td>
        </tr>
        <tr>
          <th valign="top" width="70"><label for="definition">Определение:</label></th>
          <td><textarea name="definition" id="definition" class="wymeditor" style="width: 100%; height: 100%"  rows="10"></textarea></td>
        </tr>
      </table>
    </form>
  </li>
</ul>
