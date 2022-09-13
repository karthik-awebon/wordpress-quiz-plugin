/*
 * Attaches the image uploader to the input field
 */
jQuery(document).ready(function($){
 
    // Instantiates the variable that holds the media library frame.
    var meta_image_frame;
 
    // Runs when the image button is clicked.
    $('.meta-image-button').live('click',function(e){
        
        // Prevents the default action from occuring.
        e.preventDefault();
        var thisRowCount = $(this).attr('rowcount');

 
        // Sets up the media library frame
        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text:  meta_image.button },
            library: { type: 'image' }
        });
 
        // Runs when an image is selected.
        meta_image_frame.on('select', function(){
 
            // Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = meta_image_frame.state().get('selection').first().toJSON();

			$('#show-ans-image-'+thisRowCount).append( '<img src="'+media_attachment.url+'" alt="" style="max-width:100%;"/>' );
        });
 
        // Opens the media library frame.
        meta_image_frame.open();
    });
    $('.layout-image-button').live('click',function(e){
        
        // Prevents the default action from occuring.
        e.preventDefault();
        var thisRowCount = $(this).attr('rowcount');

 
        // Sets up the media library frame
        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text:  meta_image.button },
            library: { type: 'image' }
        });
 
        // Runs when an image is selected.
        meta_image_frame.on('select', function(){
 
            // Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = meta_image_frame.state().get('selection').first().toJSON();

            // Sends the attachment URL to our custom image input field.
            $('#layou-image-'+thisRowCount).val(media_attachment.url);
        });
 
        // Opens the media library frame.
        meta_image_frame.open();
    });
    var rowCount = 1; 

    $('#add').click(function(e){ 

        rowCount ++;
     var recRow = '<p id="rowCount'+rowCount+'"><input type="hidden" name="answer['+rowCount+'][update_id]"  id="update_id" value="" /><label>Animal name</label><input type="text" name="answer['+rowCount+'][text]" id="answer['+rowCount+'][text]" value="" class="widefat" /><br><label for="meta-image" class="prfx-row-title"></label><label>Path</label><br><div id="show-ans-image-'+rowCount+'"></div><<input type="hidden" name="answer['+rowCount+'][meta-image]" id="meta-image-'+rowCount+'" value="" /><br><label>Animal image</label><br><input type="button" rowcount = '+rowCount+'  name="meta-image-button" class="button meta-image-button" value="" /><br><label>Description</label><br><textarea rows="4" id="img_description" name="answer['+rowCount+'][img_description]" cols="50"></textarea> <a href="javascript:void(0);" onclick="removeRow('+rowCount+');">Delete</a></p>'; jQuery('#addedRows').append(recRow);
     
        jQuery('.widefat').focus(); 

      }); 
      
        
});
 function removeRow(removeNum) { 
    var ansrow_id=removeNum;
    jQuery('#rowCount'+ansrow_id).remove(); 
    
   
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            'action':'answer_remove',
            'rowid' : ansrow_id
        },
        success: function  (result){

            console.log(result);

            jQuery('#addquiz'+ansrow_id).remove();
            // body...
        },
         error: function(errorThrown){
            console.log(errorThrown);
        }


    });

    
  

} 