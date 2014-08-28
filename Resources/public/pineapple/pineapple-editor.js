jQuery(function($){

  var Editor = {

    editing: false,

    // Cycles through editor buttons, "activating" to match the selected styling
    checkEditor: function($el) {

      if(this.editing) {

        $('#pineapple-editor .pineapple-action').removeClass('active').each(function() {

          var actions = Pineapple.parseAction(true, $(this).data("action"));
          var mod = actions[2];

          if(document.queryCommandState(mod) || document.queryCommandValue("formatBlock") == mod || (mod=="link" && window.getSelection().anchorNode.parentNode.nodeName == "A")) {

            $(this).addClass('active');
          
          }

          if( (mod.match(/color/) || mod.match(/size/)) && $el) {
            
            var color = $el.css('color');
            var size;

            if(window.getSelection().anchorNode.parentNode.nodeName == "FONT") {

              var $node = $(window.getSelection().anchorNode.parentNode);
              color = $node.attr('color');
              size = $node.attr('size');
            }
            

            if(mod.match(/color/)) {
              $(this).find('i.fa').css('color', color);
            }

            if(mod.match(/size/)) {

              $('#pineapple-editor-fontsize').find('.pineapple-action').removeClass('active');

              var title;

              switch(size) {
                case "1":
                  title="smallest";
                  break;
                case "3":
                  title="small";
                  break;
                case "4":
                  title = "medium";
                  break;
                case "5":
                  title="large";
                  break;
                case "7":
                  title="extra-large";
                  break;
                default:
                  title="small";
                  break;
              }

              $('#pineapple-editor-fontsize').find('.pineapple-action[title="'+title+'"]').addClass('active');
            }
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
    }

  };

  // Custom listener for the editor submenu
  $(document).on("on:pineapple-editor", function(action, el, v) {

    if($('#'+v).is(":visible") && !Editor.editing) {

      $('#pineapple').attr('contenteditable', false);

      // Only non-images can be edited
      $('.pineapple-editable').attr('contenteditable', true);

      // Add editor border
      $('.pineapple-editable').addClass('pineapple-editor-on pineapple-no-actions').first().focus();

      Editor.editing = true;

      $(document).on("mod", function(action, el, mod) {

        var content = Editor.selectedHTML();
        Pineapple.changed(true);
        var $el;

        switch(mod) {
          case 'h1':
          case 'h2':
          case 'h3':
          case 'p':
            document.execCommand('formatBlock', false, mod);
            break;

          case 'link':

            var prefill;

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

          case "media":

            // Prevent the creating of extra empty images
            if(!Pineapple.Browser.is_open) {

              // Check for selected image
              if(content && content.match(/img/)) {

                var our_id = content.match(/mediaid[0-9]{13}/);

                // Verify it's one we can edit
                if(our_id) {

                  // Edit the media
                  console.log($('#'+our_id));
                }

              } else {

                // Insert dummy image to be filled later
                document.execCommand('insertHTML', false, '<img src="" id="pineapple-new-image">');

                // Request the image media
                Pineapple.requestMedia('image', function(v) {

                  if(v) {

                    // Add the image and add a unique id to be referenced later
                    $('#pineapple-new-image').attr("src", v.src).attr('id', 'mediaid' + (new Date()).getTime());

                  } else {

                    // Canceled
                    $('#pineapple-new-image').remove();
                  }

                });
              }
            }

            break;

          case "font-color":

            var $fc = $(el).find('.pineapple-color-picker');

            $fc.on("change", function() {

              document.execCommand('foreColor', false, $(this).val());

              $(el).find('i.fa').css('color', $(this).val());
            });

            break;

          case "font-size":

            var size;
            var title = $(el).attr('title');

            switch(title) {

              case "smallest":
                size = 1;
                break;
              case "small":
                size = 3;
                break;
              case "medium":
                size = 4;
                break;
              case "large":
                size = 5;
                break;
              case "extra-large":
                size = 7;
                break;
              default:
                size = 3;
                break;
            }

            document.execCommand('fontSize', false, size);

            $el = $(el);

            break;
            
          default:
            document.execCommand(mod, false, null);
            break;
        }

        Editor.checkEditor($el);
        action.stopImmediatePropagation();
      });
    
    }
  }).on("off:pineapple-editor", function(action, el, v) {

    $('.pineapple-editable').attr('contenteditable', false).removeClass('pineapple-editor-on pineapple-no-actions');

    Editor.editing = false;
  });

  // Prevent blurring of content editable
  $('body').on("mousedown", '#pineapple-editor .pineapple-action, #pineapple-editor-fontsize .pineapple-action', function(e) {
    
    e.preventDefault();

  }).on("click keyup", '.pineapple-editor-on', function() {
    
    Pineapple.changed(true);
    Editor.checkEditor($(this));
  
  });
});