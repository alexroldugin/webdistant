  $(document).ready(function(){
      create_dialog_id = '#edit-glossary-dialog';
      view_dialog_id = '#view-glossary-dialog';
      glossary_id = '#glossary'
      
      form_url = '/glossary/create/';
      view_url_tpl = '/glossary/view-:term_id/';
      view_url = '';

      /**
       * Диалог создания термина глоссария
       */
      $( create_dialog_id ).dialog( {
          autoOpen: false,
          title: '«Создать определение»',
          width: '50%',
          height: '300',
          minWidth: '400',
          modal: true,
          overlay: {
            backgroundColor: '#000',
            opacity: 0.5
          },
          open: function() {
            $( this ).html( '<span class="ajax-loading">Loading...</span>' );
            $.get( form_url, {},
                     function( result ) {
                         $( create_dialog_id ).html( result.data.html );
                         $( create_dialog_id + ' .wymeditor' ).wymeditor({
                             skin: 'default'
                           });
                     },
                     'json'
            );
          },
          buttons: { 
            'Сохранить': function() {
              $( '#create-dialog-form' ).ajaxForm();
              $( '#create-dialog-form' ).ajaxSubmit({
                  url: form_url,
                  type: 'post',
                  dataType: 'json',
                  clearForm: true,
                  success: function( result ) {
                    $( create_dialog_id ).html( result.data.html );
                    if( result.result == true ) {                      
                      $( create_dialog_id ).dialog( 'close' );

                      view_url = view_url_tpl.replace( /:term_id/, result.data.term.id );
                      //console.log( view_url );
                      $( view_dialog_id ).dialog( 'open' );
                      $( glossary_id ).append( result.data.term.html );
                    }
                  }
                    
              });

            },
            'Отмена': function() {
              $( this ).dialog( 'close' );
            }

          }
      } );

      /**
       * Диалог просмотра термина глоссария
       */
      $( view_dialog_id ).dialog( {
          autoOpen: false,
          title: '«Просмотр термина»',
          width: '50%',
          height: '300',
          minWidth: '400',
          modal: true,
          overlay: {
            backgroundColor: '#000',
            opacity: 0.5
          },
          open: function() {
            $( this ).html( '<span class="ajax-loading">Loading...</span>' );
            console.log( 'view dialog: ' + view_url );
            $.get( view_url, {},
                     function( result ) {
                         $( view_dialog_id ).html( result.data.html );
                     },
                     'json'
            );
          },
          buttons: { 
            'Изменить': function() {
              $( this ).dialog( 'close' );
              //$( edit_dialog_id ).dialog( 'open' );
            },
            'Закрыть': function() {
              $( this ).dialog( 'close' );
            }

          }
      } );
      
      
      $( "#create-link" ).click( 
        function() {
            $( create_dialog_id ).dialog( 'open' );            
        } 
      );
      $( "span[id]" ).click(function(){
          span_id = $( this ).attr( "id" );
          console.log( span_id );
          if( span_id.match( '^term-[0-9]+$' ) != null ) {
            //console.log( '#' + span_id + '-def' );
            $( '#' + span_id + '-def' ).toggle( 'normal' );
            
          }
      });
  });
