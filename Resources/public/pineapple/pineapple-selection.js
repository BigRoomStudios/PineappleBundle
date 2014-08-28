jQuery(function($) {
	
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
	
	$('body').on('click', '.fecore-option', function() {
		
		// Selecting the new content block
		var $this = $(this);
		var type = $this.data('type');
		var request_type = 'GET';
		var block_option = false;
		var search_term = '.fecore-options';
		
		//we should figure out a way to store this info on the list item elements
		if($this.hasClass('fecore-container-option')) {
			
			var url = getURL("/api/pages/" + page_id + "/containers/"+type+"/new");
			
		} else if($this.hasClass('fecore-content-option')) {
			
			var url = getURL("/api/pages/" + page_id + "/contents/"+type+"/new");
			
		} else if($this.hasClass('fecore-block-option')) {
			
			var block = $this.closest('.fecore-block');
			var block_id = block.data('id');
			var url = getURL("/api/widgets/" + block_id + "/transforms/"+type+"/widgets");
			
			request_type = 'POST';
			
			block_option = true;
			search_term = '.fecore-block';
				
		}
		
		/*$.ajax({type: "GET", dataType: "html", url: url})
	      .done(function(data) {
	        
	        Pineapple.modal.update(data);
	      });
		*/
		$.ajax({dataType: "json", type: request_type, url: url})
		 .done(function(result) { // success
			
		    if(result.success) {
				
				remove_element = $this.closest(search_term);
				
				remove_element.fadeOut(200, function() {
					
					if(block_option) {
						$(result.html).insertBefore(remove_element);
					}
					
					remove_element.remove();
					
				});
				
				if(!block_option) {
					page_container.append(result.html);
				}
				
			}
		 });
		
	});
	
});

/*

jQuery(function($){

  var Editor = {

    editing: false,

    // Cycles through editor buttons, "activating" to match the selected styling
    checkEditor: function() {

      if(this.editing) {

        $('#pineapple-editor .pineapple-action').removeClass('active').each(function() {

          var actions = Pineapple.parseAction(true, $(this).data("action"));
          var mod = actions[2];

          if(document.queryCommandState(mod) || document.queryCommandValue("formatBlock") == mod || (mod=="link" && window.getSelection().anchorNode.parentNode.nodeName == "A")) {

            $(this).addClass('active');
          
          }
        });
      }
    },

    // Returns the currently highlighted HTML
    selectedHTML: function() {
      var html = "";
      if (typeof window.getSelection != "undefined") {
          var sel = window.getSelection();
          if (sel.rangeCount) {
              var container = document.createElement("div");
              for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                  container.appendChild(sel.getRangeAt(i).cloneContents());
              }
              html = container.innerHTML;
          }
      } else if (typeof document.selection != "undefined") {
          if (document.selection.type == "Text") {
              html = document.selection.createRange().htmlText;
          }
      }
      
      return html;
    },

  };

  // Custom listener for the editor submenu
  $(document).on("on:pineapple-editor", function(action, el, v) {

    if($('#'+v).is(":visible") && !Editor.editing) {

      $('#pineapple').attr('contenteditable', false);

      // Only non-images can be edited
      $('.fecore-editable').not('.fecore-image').attr('contenteditable', true);

      // Add editor border
      $('.fecore-editable').addClass('fecore-editor-on').first().focus();

      Editor.editing = true;

      $(document).on("mod", function(action, el, mod) {

        switch(mod) {
          case 'h1':
          case 'h2':
          case 'h3':
          case 'p':
            document.execCommand('formatBlock', false, mod);
            break;

          case 'link':

            var content = Editor.selectedHTML(), prefill;

            if(content) {

              if(window.getSelection().anchorNode.parentNode.nodeName == "A") {
                prefill = window.getSelection().anchorNode.parentNode.href;
              }

              document.execCommand('insertHTML', false, '<a href="#" id="pineapple-new-link">'+content+"</a>");

              var url = prompt("URL to be used", prefill);

              if(url) {

                $('#pineapple-new-link').attr('href', url).removeAttr('id');
              } else {
                
                if(!prefill) {

                  $('#pineapple-new-link').after(content).remove();
                }
              }
            }
            break;

          case "unlink":

            if(window.getSelection().anchorNode.parentNode.nodeName == "A") {

              var $link = $(window.getSelection().anchorNode.parentNode);

              $link.after($link.html()).remove();
            }
            break;

          default:
            document.execCommand(mod, false, null);
            break;
        }

        Editor.checkEditor();
        action.stopImmediatePropagation();
      });
    
    }
  }).on("off:pineapple-editor", function(action, el, v) {

    $('.fecore-editable').attr('contenteditable', false).removeClass('fecore-editor-on');

    Editor.editing = false;
  });

  // Prevent blurring of content editable
  $('body').on("mousedown", '#pineapple-editor .pineapple-action', function(e) {
    
    e.preventDefault();

  }).on("click keyup", '.fecore-editor-on', function() {
   
    Editor.checkEditor();
  
  });
});
*/