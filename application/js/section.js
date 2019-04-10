  $(document).ready(function(){
      $( "#create" ).click( 
        function() {

           $( '#section-dialog' ).
                  load( '/', {},
                       function(responseText, textStatus, XMLHttpRequest) {
                         
                         $( "#section-dialog" ).dialog( {
                                 'title': 'Управление разделом',
                                 'width': '50%',
                                 'open': function() { 
                                 }
                         } );
                       }
                  ); // load
           
        } 
      );
  });
