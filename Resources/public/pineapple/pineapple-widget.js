jQuery(function($){

  /*
   * This is meant to be temporary functionality to deal with the dev environment (app_dev.php)
   * I think there is a better way to do this.
   */
  function getURL(url) {

    // turn the location into a string
    var loc_string = window.location + '';

    // test for app_dev.php
    if(loc_string.search('app_dev.php') > 0) {
      return '/app_dev.php' + url;
    }

    return url;

  }

  /*
   * Widget object
   * Helps by providing necessary functions and acting as a storage bin for elements
  */
  var Widget = {

    // Widget and row actions
    el: {
      row_extras: $('<row-extras><row-delete><i class="fa fa-times"></i></row-delete><row-settings><i class="fa fa-cog"></i></row-settings><row-up><i class="fa fa-chevron-up"></i></row-up><row-down><i class="fa fa-chevron-down"></i></row-down></row-extras>'),
      left: $('<div class="pineapple-left">'),
      right: $('<div class="pineapple-right">'),
      actions: $('<actions><move><i class="fa fa-arrows"></i></move><settings><i class="fa fa-cog"></i></settings><delete-one><i class="fa fa-times"></i></delete-one></actions>'),
    },

    // The swapping of two widgets
    moving: {
      $block: null,
      now: false
    },

    // Parses the column class names (col-1, or col-12 to [col-1, 1], or [col-12, 12])
    parseCols: function($el) {
      var classes = $el.attr("class");
      return (classes ? classes.match(/col-([0-9]{1,2})/) : false);
    }
  };

  // All event listeners pertaining to widgets
  // Row hover listening to dynamically append actions
  $(document).on("mouseenter", '.pineapple-row', function() {

    var $me = $(this);
    $me.append(Widget.el.row_extras);

    // Update actions with proper container id
    Widget.el.row_extras.children().each(function() {

      $(this).data("container-id", $me.data("container-id"));
    });

    // Also add the expanding right and left actions to all blocks
    var $children = $(this).find('.pineapple-block');

    // Loop through blocks
    $children.each(function(i) {

      var prev_i = i-1;
      var next_i = i+1;

      // Only add the left arrow if it's not the first block in the row
      if(prev_i>=0) {
        $(this).append(Widget.el.left.clone());
      }

      // Only add the right arrow if it's not the last block in the row
      if(next_i!=$children.length) {
        $(this).append(Widget.el.right.clone());
      }
    });

  // Remove the actions from the row on mouse leave
  }).on("mouseleave", '.pineapple-row', function() {

    Widget.el.row_extras.remove();

    var $children = $(this).find('.pineapple-block');

    $children.each(function(i) {

      $(this).find('.pineapple-left').remove();
      $(this).find('.pineapple-right').remove();
    });

  // Adding an individual widget's actions
  }).on("mouseenter", ".pineapple-block", function() {

    // Only actual widgets can have actions so make sure it's not a placeholder
    if(!$(this).find('.pineapple-widget-placeholder').length && $(this).parent('.pineapple-row').length && !$(this).find('.pineapple-no-actions').length) {

      var $me = $(this);
      $(this).append( Widget.el.actions );
      
      // Loop through and add the proper id to the actions
      // this prevents traversing the parent tree to fetch ids
      Widget.el.actions.children().each(function() {
        $(this).data("id", $me.data("id"))
      })
    }

  // Remove a widget's actions on the mouse leave
  }).on("mouseleave", ".pineapple-block", function() {

    if(!$(this).find('.pineapple-widget-placeholder').length) {

      $(this).find( 'actions' ).remove();
    }

  // Deleting a row
  // This might be moved to an action (delete:row) listener, but the functionality will remain
  }).on("click", "row-delete", function() {

    // Pertinent row info
    var container_id = $(this).data("container-id");
    var $el = $('#content-container-'+container_id);

    if($el.length) {

      // Open the pane to confirm deletion
      Pineapple.Browser.open({
        confirm:true, 
        "title":"Delete the row?", 
        "content":"All widget and contents within the row will be deleted.", 
        onConfirm: function() {

          // Confirmed. Toggle busy
          Pineapple.Browser.open({title:"Deleting row", busy: true});

          // Request to delete
          $.ajax({type: "DELETE", dataType: "json", url: getURL("/api/contents/"+container_id)})
            .done(function(data) {
              
              // It's gone in the DB, so remove it from the DOM
              // this also avoids errors when saving
              $el.fadeOut(200, function() {
                $el.remove();
              });

              // Close the pane
              Pineapple.Browser.destroy();
            
            }).fail(function() {

              // Something went wrong, so fail gracefully with a silly message
              Pineapple.Browser.update({ content: "There was an error deleting the row. Please try again in a few moments." });
            });

      }})
    }
  
  // Row settings
  }).on("click", "row-settings", function() {

    var row_id = $(this).data("container-id");

    if(row_id) {

      // Toggle busy pane
      Pineapple.Browser.open({title:"Row settings", busy: true});
      
      // Fetch this widgets specific settings
      $.ajax({type: "GET", dataType: "html", url: getURL('/api/widgets/'+row_id+'/settings/edit.html')})
        .done(function(data) {
          
          Pineapple.Browser.update({content: data});
        
        }).fail(function() {

          Pineapple.Browser.update({content: "There was an error retrieving the row settings. Please try again in a few moments."});
        });
    }

  // Moving a row up
  }).on("click", "row-up", function() {

    // Get pertinent info
    var container_id = $(this).data("container-id");
    var $me = $('#content-container-'+container_id);
    var $prev = null;

    // Make sure there's a spot to move to
    var $rows = $me.closest('.pineapple-container').find('.pineapple-row');

    $rows.each(function(i) {

      if($(this).attr("id")===$me.attr("id")) {

        if(i>0) {
          $prev = $( $rows[(i-1)] );
        }
      }
    });

    if($prev) {

      // Swap with a simple hide and fade
      $prev.hide().fadeIn(200).before($me.hide().fadeIn(200));
    }

  // Moving a row down
  // exactly the same as the up, just in reverse
  }).on("click", "row-down", function() {

    var container_id = $(this).data("container-id");
    var $me = $('#content-container-'+container_id);
    var $prev = null;

    var $rows = $me.closest('.pineapple-container').find('.pineapple-row');

    $rows.each(function(i) {

      if($(this).attr("id")===$me.attr("id")) {

        if((i+1)<$rows.length) {
          $prev = $( $rows[(i+1)] );
        }
      }
    });

    if($prev) {

      $prev.hide().fadeIn(200).after($me.hide().fadeIn(200));
    }

  // Adding a widget
  }).on("click", ".pineapple-widget-placeholder", function() {

    // Make sure a move isn't happening
    // widgets can be swapped with placeholders
    if(!Widget.moving.now) {

      var $me = $(this);

      $('.pineapple-widget-placeholder').removeClass('pineapple-widget-goes-here');

      // Set the destination class
      $me.addClass('pineapple-widget-goes-here');

      // Open the pane to display selectable widgets
      // we could prevent this if the pane is already open but they could have the pane open for another purpose
      Pineapple.Browser.open({ title: "Select a widget", busy: true, onCancel: function() {

        // If they close the pane, remove destination class
        $('.pineapple-widget-goes-here').removeClass('pineapple-widget-goes-here');
      } });
      
      // Info needed to display widget list
      var block_id = $(this).parent('.pineapple-block').data('id');
      var url = getURL('/api/widgets/brs.block.service.widget_list/edit.html?block_id='+block_id);
      
      // Request widget list
      $.ajax({type: "GET", dataType: "html", url: url})
          .done(function(data) {
            
            // Update the pane with the list of widgets
            // we'll listen for the selection of a widget later
            Pineapple.Browser.update({content: data});
          
          }).fail(function() {

            // Failing gracefully with silly message
            Pineapple.Browser.update({content: "There was an error fetching the list of widgets. Please try again in a few moments." });
          });
      
    }

  // Expanding a widget block left
  }).on("click", ".pineapple-left", function(e) {

    // Traverse up to the block
    var $parent = $(this).closest('.pineapple-block');
    // Grab the previous element
    var $prev = $parent.prev();

    // Parse the column class names
    var my = Widget.parseCols($parent);
    var prev = Widget.parseCols($prev);

    var prev_col = (parseInt(prev[1])-1);

    // Make sure the previous column can be shrunk (col-1 is the min)
    if(prev_col>0) {

      // Update classes
      $parent.removeClass(my[0]).addClass('col-'+(parseInt(my[1])+1));
      $prev.removeClass(prev[0]).addClass('col-'+prev_col);
    }

    e.stopImmediatePropagation();

  // Expanding a widget block right
  // Same as the left just in reverse
  }).on("click", ".pineapple-right", function(e) {

    var $parent = $(this).closest('.pineapple-block');
    var $next = $parent.next();

    var my = Widget.parseCols($parent);
    var next = Widget.parseCols($next);
    var next_col = (parseInt(next[1])-1);

    if(next_col>0) {

      $parent.removeClass(my[0]).addClass('col-'+(parseInt(my[1])+1));
      $next.removeClass(next[0]).addClass('col-'+next_col);
    }

    e.stopImmediatePropagation();
  
  // Selecting a widget from the widget list
  }).on("click", ".pineapple-widget-transform li", function() {
    
    // Transform URL for converting the placeholder to a real widget
    var url = $(this).data('transform');

    // Toggle loading pane
    Pineapple.Browser.open({title:"Loading widget", busy: true});
    
    // Fetch widget
    $.ajax({type: "POST", dataType: "json", url: url})
        .done(function(data) {
          
          // Grab the destination
          var replace = $('.pineapple-widget-goes-here').closest('.pineapple-block');
          
          // Quickly hide it, and switch it
          replace.fadeOut(200, function() {
            
            $(data.html).insertBefore(replace);
            
            replace.remove();
            
          });
          
          // Close the pane
          Pineapple.Browser.destroy();
        
        }).fail(function() {

          // Failing gracefully and display another silly message
          Pineapple.Browser.update({ content: "There was an error selecting the widget. Please try again in a few moments." });
        });
    
    return;
    
    $('.pineapple-widget-placeholder').removeClass('pineapple-widget-goes-here');

    $(this).addClass('pineapple-widget-goes-here');

    Pineapple.Browser.open({ title: "Select a widget", busy: true });
    
    var block_id = $(this).data('id');
    var url = getURL('/api/widgets/brs.block.service.widget_list/edit.html?block_id='+block_id);
    
    $.ajax({type: "GET", dataType: "html", url: url})
        .done(function(data) {
          
          Pineapple.Browser.update({content: data});
        });
  
  // An individual widget's settings
  }).on("click", ".pineapple-block actions > settings", function() {

    // Really glad we set this info on each action
    var widget_id = $(this).data("id");

    if(widget_id) {

      // Toggle busy pane
      Pineapple.Browser.open({title:"Widget settings", busy: true});
      
      // Fetch this widgets specific settings
      $.ajax({type: "GET", dataType: "html", url: getURL('/api/widgets/'+widget_id+'/settings/edit.html')})
        .done(function(data) {
          
          Pineapple.Browser.update({content: data});
        
        }).fail(function() {

          Pineapple.Browser.update({content: "There was an error retrieving the widget settings. Please try again in a few moments."});
        });
    }
  
  // Toggling the moving of a widget
  }).on("click", ".pineapple-block actions > move", function() {

    // Find the block and set it as the element to be moved
    Widget.moving.$block = $(this).closest('.pineapple-block');

    // If it was already set, unset it.
    if(Widget.moving.$block.hasClass('pineapple-widget-moving')) {

      $Widget.moving.$block.removeClass('pineapple-widget-moving');
      Widget.moving.now = false;
      Widget.moving.$block = null;

    } else {

      // Add the moving class
      Widget.moving.$block.addClass('pineapple-widget-moving');

      // Make sure other events are prevented during the move
      Widget.moving.now = true;

      // All blocks become "droppable" locations
      $('.pineapple-block').addClass('pineapple-widget-drop-here');

    }

  // Selecting the moving widget's destination
  }).on("click", ".pineapple-widget-drop-here", function(e) {

    var $me = $(this);

    // Make sure we're actually moving and we're not the original widget to be moved
    if(Widget.moving.now && !$(this).hasClass('pineapple-widget-moving')) {

      // Clone elements, swapping each other's class names
      $me.before(Widget.moving.$block.clone().attr('class', $me.attr('class')));
      Widget.moving.$block.before($me.clone().attr('class', Widget.moving.$block.attr('class')));

      // Remove the old elements
      Widget.moving.$block.remove();
      $me.remove();

      // Cancel the moving event
      $('.pineapple-block').removeClass('pineapple-widget-drop-here').removeClass('pineapple-widget-moving');
      Widget.moving.now = false;
      Widget.moving.$block = null;

      // May be unnecessary
      e.stopImmediatePropagation();
    }

  });
  
});