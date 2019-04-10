<div id="alpha-bar" style="border-bottom: 1px solid #666; padding-bottom: 5px; margin-bottom: 15px;">
   <div id="alpha">
      <div>
        <? foreach( $this->_mData[ 'rus' ] as $symbol ) :?>
           <span style="text-transform: capitalize"><?= $symbol ?></span>
        <? endforeach; ?>
      </div>
      <div>
        <? foreach( $this->_mData[ 'eng' ] as $symbol ) :?>
           <span style="text-transform: capitalize"><?= $symbol ?></span>
        <? endforeach; ?>
      </div>
   </div>
   <div id="create-link" style="cursor: pointer;">Создать</div>

</div>
<div id="glossary">
   <? if( sizeof( $this->_mData[ 'glossary' ] ) ) :?>
      <ul style="margin-top: 10px;" >
      <? foreach( $this->_mData[ 'glossary' ] as $index => $term_item ) :?>
         <li style="margin: 5px 0;">
            <span id="term-<?= $term_item[ 'term_id' ]; ?>" style="border-bottom: 1px dashed #666; cursor: pointer; font-weight: bold;"><?= $term_item[ 'term' ]; ?></span>
            <div id="term-<?= $term_item[ 'term_id' ]; ?>-def" style="display: none; margin: 5px 0;"><?= $term_item[ 'definition' ]; ?></div>
         </li>
      <? endforeach; ?>
      </ul>
   <? else : ?>
      <h3>Глоссарий пуст</h3>
  <? endif;?>
</div>
<div id="view-glossary-dialog">&nbsp;</div>
<div id="edit-glossary-dialog" >&nbsp;</div>