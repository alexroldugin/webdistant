<ul>
  <? if( sizeof( $this->_mErrors ) ) :?>
    <li class="errors"><?= $this->getErrorsBlock(); ?></li>
  <? endif; ?>
  <? if( sizeof( $this->_mMessages ) ) :?>
    <li class="messages"><?= $this->getMessagesBlock(); ?></li>
  <? endif; ?>
  <? if( !sizeof( $this->_mErrors ) ) :?>
  <li>
    <table>
      <tr>
        <th>Термин:</th>
        <td><?= $this->_mData[ 'term' ] ?></td>
      </tr>
      <tr>
        <th>Определение:</th>
        <td><?= $this->_mData[ 'definition' ] ?></td>
      </tr>
    </table>
  </li>
  <? endif ?>
</ul>
