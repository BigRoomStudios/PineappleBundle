String.prototype.capitalize = function() {
  return this.charAt(0).toUpperCase() + this.slice(1);
}

var pineapple_admin_json;

jQuery(function($){

  // Global element
  var $pineapple = $('pineapple > toolbar');

  // Global object
  var Pineapple = {

    config: {
      json: pineapple_admin_json,
      classPrefix: "pineapple",
      id: { page: $('pineapple > current').attr('page-id'), site: $('pineapple current').attr('site-id') }
    },

    // Selectors of the current actions
    actions: {
      $active: [],
      $submenu: [],
      $editor: [],
      editing: false,
      busy: false,
      changed: false
    },

    // Allows widgets to toggle Pineapple as busy
    busy: function(bool) {
      Pineapple.actions.busy = bool;
    },

    // Allows widgets to notify of unsaved content
    changed: function(bool) {
      Pineapple.actions.changed = bool;
    },

    // Loads json for rendering
    init: function() {
      
      if(this.config.json) {

        // render the toolbar
        Pineapple.render();
      }
      
    },

    // Renders the toolbar and submenus
    render: function() {
      
      // Clear everything out
      //$pineapple.html('');

      // Actions to loop through
      var actions = this.config.json.actions;

      // Render the toolbar(s)
      $.each(actions, function(i) {
        
        // Create and append the action list item
        Pineapple.li(actions[i], Pineapple.config.json.id);
      })
    },

    // Builds a list item, splitting off any submenus
    li: function(json, el, appendTo) {

      // Create the list item element
      var $li = $('<li class="'+Pineapple.config.classPrefix+'-action">')
        .addClass(json.className)
        .attr("data-action", json.action)
        .attr("title", json.label);

      // Adding the icon
      if(json.icon) {
        if((json.icon).match(/\./)) {
          $li.append( $('<img src="'+json.icon+'" class="'+Pineapple.config.classPrefix+'-icon">') );
        } else {
          $li.append( $('<i class="'+json.icon+'">') );
        }
      }

      // Labeling
      if(!json.hide_label) {
        $li.append( $('<span class="'+Pineapple.config.classPrefix+'-label">'+json.label+'</span>') );
      }

      // Appending custom elements
      if(json.append) {
        $li.append( $(json.append) );
      }

      // Get the holder element
      var $el = ( appendTo instanceof jQuery ? appendTo : this.checkAppend(el, appendTo) );

      // Append the list item to the holder element
      $el.append($li);

      // Loop through and append submenus
      if(json.submenu) {

        var submenu = json.submenu;

        var $ul = $('<ul id="'+submenu.id+'">');

        $li.after($ul);

        $.each(submenu.actions, function(o) {
          Pineapple.li(submenu.actions[o], submenu.id, $ul);
        });
      }

      // Listen for actions
      //$li.on("click", Pineapple.parseAction);

    },

    // Parses the data-action attribute into a useful command
    parseAction: function(no_event, _action) {

      // Get the action attribute value
      var action = (no_event===true) ? _action : $(this).data("action");

      // Separate key from value
      var matches = action.match(/((?:"[^"]*"|[^:,])*):((?:"[^"]*"|.)*)/);

      if(no_event===true) {

        return matches;
      }

      if(matches.length) {

        var me = this;

        Pineapple.actions.$active.unshift($(this));

        var $parent = $(this).parent();

        $parent.find('.pineapple-action').each(function() {

          if($(this).data("action")!==$(me).data("action")) {

            $(this).removeClass('active');

            var action = Pineapple.parseAction(true, $(this).data("action"));

            if(action.length && action[1]=="menu" && $('#'+action[2]).is(':visible')) {

              $('#'+action[2]).hide();
              //$('html').css('margin-top', $pineapple.outerHeight());
              $.event.trigger("off:"+action[2], [me, action[2]]);
            }
          }
        });

        $(this).addClass('active');

        // Trigger the jQuery events
        $.event.trigger(matches[1], [me, matches[2]]);
        $.event.trigger("on:"+matches[2], [me, matches[2]]);

        return;

      } else {

        return false;
      }
    },

    // Checks for the existence of an sub-menu, creating a new one if needed
    checkAppend: function(el, appendTo) {
      var $el = $('#'+el);

      // Create if it doesn't exist
      if(!$el.length) {
        $el = $('<div id="'+el+'">');
        
        // Append to a specific parent
        if(appendTo) {
          
          var $appendTo = Pineapple.checkAppend(appendTo);

          $appendTo.append( $el );
     
        } else {
          
          // Append to the pineapple global element
          $pineapple.append( $el );
        }
      }

      return $el;
    },

    // Fires the jQuery event when an action was turned 'off'
    triggerOff: function(el) {

      var actions = Pineapple.parseAction(true, el.data("action"));

      $.event.trigger("off:"+actions[2], [el, actions[2]]);
    },

    // Cycles through all actions, triggering the 'off' event
    triggerAll: function() {

      $.each(Pineapple.actions.$active, function(i) {

        var actions = Pineapple.parseAction(true, $(this).data("action"));

        $.event.trigger("off:"+actions[2], [Pineapple.actions.$active[i], actions[2]]);
      });

      Pineapple.actions.$active = [];
    },

    // Pineapple's radio wave-length enhancer
    receive: function(name, callback) {

      $(document).on("pineapple-broadcast:"+name, function(action, evt, v) {
        
        callback(evt,v,action);
      });
    },

    broadcast: function(name, data) {

      if(!Array.isArray(data)) {
        data = [data];
      }

      $.event.trigger("pineapple-broadcast:"+name, data);
    },

    // Pineapple's super-fly media library
    requestMedia: function(type, callback) {

      Pineapple.broadcast('pane', [null,'brs.block.service.media_library']);

      Pineapple.receive('media-selected', function(el, v, action) {

        callback(v);

        Pineapple.Browser.destroy();

        action.stopImmediatePropagation();
      });

      Pineapple.receive('pane-closed', function() {

        callback(false);
      });
    },

    // Pineapple's custom browser pane
    Browser: {

      active: null,

      el: { 
        confirm: $('pineapple > pane > confirm'),
        content: $('pineapple > pane > content'),
        controls: $('pineapple > pane > controls'),
        dimmer: $('pineapple > dimmer'),
        inner: $('pineapple > pane > content > inner'),
        loader: $('<div class="pineapple-loader pineapple-loader-5">'),
        pane: $('pineapple > pane'),
        title: $('pineapple > pane > content > title')
      },

      defaults: {
        title: null,
        confirm: false,
        content: null,
        hide_confirm: false,
        busy: false
      },

      is_open: false,
      is_dimmed: false,

      history: [],
      history_at: 0,

      destroy: function() {

        // Close the pane via CSS transitions
        this.el.pane.removeClass('pineapple-pane-open');
        this.history = [];
        this.history_at = 0;
        this.is_open = false;
        this.is_dimmed = false;

        $('.pineapple-pane-forward').addClass('pineapple-pane-disabled');
        $('.pineapple-pane-back').addClass('pineapple-pane-disabled');

        Pineapple.Browser.el.title.html('');
        Pineapple.Browser.el.inner.html('');
        Pineapple.Browser.el.controls.show();

        Pineapple.actions.busy = false;
        Pineapple.actions.$active[0].removeClass('active');

        Pineapple.broadcast('pane-closed', null);
      },

      open: function(options) {

        this.view(options);
        this.is_open = true;

        if(!this.el.pane.hasClass('pineapple-pane-open')) {

          // Opening the pane via CSS transitions
          this.el.pane.addClass('pineapple-pane-open');

          // Listen for the close button
          this.el.pane.on("click", '.pineapple-pane-close', function() {

            if(Pineapple.Browser.history[0] && Pineapple.Browser.history[0].onCancel) {
              Pineapple.Browser.history[0].onCancel();
            }

            Pineapple.Browser.destroy();

          }).on("click", '.pineapple-pane-back', function() {

            if(Pineapple.Browser.history.length>1 && !$(this).hasClass('pineapple-pane-disabled')) {

              ++Pineapple.Browser.history_at;
              Pineapple.Browser.view(Pineapple.Browser.history[Pineapple.Browser.history_at], true, 'right');

              if((Pineapple.Browser.history_at+1) == Pineapple.Browser.history.length) {

                $(this).addClass('pineapple-pane-disabled');
              }

              $('.pineapple-pane-forward').removeClass('pineapple-pane-disabled');
            }
          }).on("click", '.pineapple-pane-forward', function() {

            if(Pineapple.Browser.history.length>1 && !$(this).hasClass('pineapple-pane-disabled')) {

              --Pineapple.Browser.history_at;
              Pineapple.Browser.view(Pineapple.Browser.history[Pineapple.Browser.history_at], true);

              if(Pineapple.Browser.history_at==0) {

                $(this).addClass('pineapple-pane-disabled');
              }

              $('.pineapple-pane-back').removeClass('pineapple-pane-disabled');
            }
          });
          
        }
      },

      update: function(options) {

        Pineapple.actions.busy = false;

        var options = $.extend( {}, this.defaults, options );
        this.history[0].content = options.content;
        this.history[0].busy = false;

        // Fade out the loader
        this.el.inner.fadeOut(200, function() {
          // Add new content, then fade it in
          $(this).html(options.content).fadeIn(200);  
        })
      },

      view: function(options, skip, direction) {

        var options = $.extend( {}, this.defaults, options );

        if(!options.confirm) {
          
          this.el.controls.show();

          if(this.active && this.active.confirm && this.active.onCancel) {

            this.active.onCancel();
          }

          if(!skip) {
            this.history = this.history.slice(this.history_at);
            
            this.history.unshift(options);

            if(this.history.length > 1) {
              $('.pineapple-pane-back').removeClass('pineapple-pane-disabled');
            }

            this.history_at = 0;
            $('.pineapple-pane-forward').addClass('pineapple-pane-disabled');

          }
        }

        this.active = options;

        if(options.confirm) {
          
          this.el.controls.hide();
          this.el.dimmer.fadeIn(200);
          this.is_dimmed = true;

          Pineapple.actions.busy = true;

          $('body').on("click", '.pineapple-cancel', function() {

            Pineapple.actions.busy = false;

            if(options.onCancel) {

              Pineapple.Browser.el.controls.show();
              Pineapple.Browser.el.dimmer.fadeOut(200);
              Pineapple.Browser.el.title.html('');
              Pineapple.Browser.el.inner.html('');

              options.onCancel();

            } else {

              Pineapple.Browser.destroy();
            }

            return;

          }).on("click", '.pineapple-confirm', function() {
            
            Pineapple.actions.busy = false;

            if(options.onConfirm) {

              Pineapple.Browser.el.controls.show();
              Pineapple.Browser.el.dimmer.fadeOut(200);
              Pineapple.Browser.el.title.html('');
              Pineapple.Browser.el.inner.html('');

              options.onConfirm();

            } else {

              Pineapple.Browser.destroy();
            }

            return;
          });
        
        } 

        this.el.content.addClass('pineapple-pane-opening-'+(direction||"left")).on("webkitAnimationEnd oanimationend msAnimationEnd animationend", function() {
          $(this).removeClass('pineapple-pane-opening-'+(direction||"left"));
        });

        window.setTimeout(function() {

          if(options.title) {
            Pineapple.Browser.el.title.html(options.title);
          }

          if(options.busy) {
            Pineapple.Browser.el.inner.html(Pineapple.Browser.el.loader);
            Pineapple.actions.busy = true;
          }

          if(options.content) {
            Pineapple.Browser.el.inner.html(options.content);
          }

          if(options.confirm && !options.hide_confirm) {
            Pineapple.Browser.el.inner.append(Pineapple.Browser.el.confirm.css('display', 'block'));
          }
        }, 150);
        
      }
    },

  };

  Pineapple.init();

  // Global helpers for widgets
  window.Pineapple = {
    parseAction: Pineapple.parseAction,
    Browser: Pineapple.Browser,
    busy: Pineapple.busy,
    changed: Pineapple.changed,
    broadcast: Pineapple.broadcast,
    receive: Pineapple.receive,
    requestMedia: Pineapple.requestMedia,
  };
  
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
  
  // Pineapple's top-level events
  $(document)
  

  /*
    
    Menu actions

  */
  .on("menu", function(action, el, v) {
    
    var $active = Pineapple.actions.$active[0];
    var $submenu = $('#'+v);

    if($submenu.is(":visible")) {

      $active.removeClass('active');
      $submenu.hide();
      Pineapple.triggerOff($active);

      if($active.parent().attr('id') == 'pineapple-main') {
        
        $('.pineapple-action').removeClass('active');
        $('#pineapple-submenus > div').hide();

        Pineapple.triggerAll();
        Pineapple.actions.$submenu = [];
      }

    } else {

      if($active.parent().attr('id') == 'pineapple-main') {
        
        $('.pineapple-action').removeClass('active');
        $active.addClass('active');
        $('#pineapple-submenus > div').hide();

        Pineapple.triggerAll();
        Pineapple.actions.$active = [$active];
        Pineapple.actions.$submenu = [];
      
      }

      Pineapple.actions.$submenu.unshift($submenu);
      $submenu.show();
    }    
    
    //$('html').css('margin-top', $pineapple.outerHeight());

    action.stopImmediatePropagation();
  
  })
  
  // Processing every and all actions, not just toolbar actions
  .on("click", ".pineapple-action", Pineapple.parseAction)


  .on("pane pineapple-broadcast:pane workspace pineapple-broadcast:workspace", function(action, el, v) {

    if(v) {
      
      var params = '';
      
      if(Pineapple.config.id.site) {
      	params += 'site_id='+Pineapple.config.id.site+'&';
      }
      
      if(Pineapple.config.id.page) {
      	params += 'page_id='+Pineapple.config.id.page+'&';
      }
      
      var path = v.split("/");
      
      if(path[1]) {
      	params += path[1];
      }
      
      var url = getURL("/api/widgets/"+path[0]+"/edit.html?"+params);
      
      if(action.type=="pane" || action.type=="pineapple-broadcast:pane") {

        // Show loading pane
        Pineapple.Browser.open({ title: path[0].capitalize()+" "+path[1], busy: true });
      }

      $.ajax({type: "GET", dataType: "html", url: url})
        .done(function(data) {
          
          if(action.type=="pane" || action.type=="pineapple-broadcast:pane") {
            Pineapple.Browser.update({content: data});
          }
        })
        .fail(function() {
          
          if(action.type=="pane" || action.type=="pineapple-broadcast:pane") {
            Pineapple.Browser.update({content: '<p>Unknown error. Please try again shortly.</p>'});
          }
        });
    }

    action.stopImmediatePropagation();
  
  })


  .on("add", function(action, el, v) {

    var path = v.split("/");

    if(path.length) {

      switch(path[0]) {

        case "row":

          var $container = $('.pineapple-container');

          // Set the row listener before broadcasting them below
          Pineapple.receive("add-row", function(evt) {
            
            var container_id = evt.container.data('id');
            var url = getURL("/api/pages/" + Pineapple.config.id.page + "/containers/"+container_id+"/new");

            $.ajax({dataType: "json", type: "GET", url: url, data: { "num_cols": evt.row }})
              .done(function(data) {

                evt.container.append(data.html);
                $('.pineapple-container').find('shim').remove();
                $('html,body').animate({scrollTop: evt.container.offset().top}, 200);
              });
              
          });

          if($container.length>1) {

            $container.each(function() { 
              
              var $shim = $('<shim>');

              $(this).append( $shim.data("container-id", $(this).data("container-id")) );

              $shim.on("click", function() {

                $container = $('#content-container-'+$(this).data("container-id"));

                Pineapple.broadcast("add-row", {container: $container, row: path[1]});

              });
            });

          } else {

            Pineapple.broadcast("add-row", {container: $container, row: path[1]});
          }

          break;
      }

    } else {

    }
  })
  

  /*
    
    Saving actions

  */
  .on("save", function(action, el, v) {

    var site_id = $('#page_container').data("site-id");

    // Show loading pane
    Pineapple.Browser.open({ title: "Saving", busy: true, confirm: true, hide_confirm: true });

    switch(v) {

      // Saving the page as a draft
      case "draft":
        
        var editable_length = $('.pineapple-block').length + $('.pineapple-row').length;
        
        $('.pineapple-container').each(function(order) {
	        
	        $(this).find('.pineapple-row').each(function(order) {
	
	          var _id = $(this).data("id"), i=1;
	          
	          /*
	          var prefix = "pineapple";
	          var classes = $(this).attr("class").split(" ").filter(function(c) {
	              return c.lastIndexOf(prefix, 0) !== 0;
	          });
	          var _class = classes.join(" ");
	          */
	          
	          if(_id) {
	            
	            $.ajax({
	              type: "POST",
	              dataType: "json",
	              data: { "order": order+1 },
	              url: getURL("/api/contents/"+_id+".json")
	            })
	            .done(function(data) {
	
	              ++i;
	              
	              if(i=editable_length) {
	                Pineapple.Browser.destroy();
	              }
	            });
	            
		        // Save all content before publishing
		        $(this).find('.pineapple-block').each(function(order) {
		
		          var _id = $(this).data("id"), i=1;
		          
		          var settings =$(this).data('settings');
		          
		          /*
		          var prefix = "pineapple";
		          var classes = $(this).attr("class").split(" ").filter(function(c) {
		              return c.lastIndexOf(prefix, 0) !== 0;
		          });
		          var _class = classes.join(" ");
		          */
		          
		          //set the width on the settings
		          var width = $(this).attr("class").split(" ").filter(function(c) {
		              return c.lastIndexOf('col', 0) === 0;
		          });
		          settings['width'] = width[0];
		          
		          $(this).find('.pineapple-editable').each(function() {
		          	
		          	var key = $(this).data('settings-key');
		          	settings[key] = $(this).html();
		          	
		          });
		          
		          $(this).data('settings', settings);
		          
		          var row = $(this).closest('.pineapple-row');
		          
		          if(_id) {
		            
		            $.ajax({
		              type: "POST",
		              dataType: "json",
		              data: { "settings": settings, /*"classes": _class,*/ "order": order+1, "parent_id": row.data('id') },
		              url: getURL("/api/contents/"+_id+".json")
		            })
		            .done(function(data) {
		
		              ++i;
		              
		              if(i=editable_length) {
		                Pineapple.Browser.destroy();
		
		                Pineapple.actions.busy = Pineapple.actions.changed = false;
		              }
		            });
		            
		          }
		        });
		        
	          }
	        });
	        
	      });
        break;

      // Publishing changes to public
      case "publish":

        $.ajax({type: "GET", dataType: "json", url: getURL("/api/pages/"+Pineapple.config.id.site+"/publish")})
          .done(function(data) {

            Pineapple.Browser.destroy();
          });
        break;
    }

    action.stopImmediatePropagation();

  })
  

  /*
    
    Link actions

  */
  .on("href", function(action, el, v) {
    
    var url = v;
    
    if(url.search('/') == 0) {
      url = getURL(url);
    }
    
    window.location = url;
    action.stopImmediatePropagation();
    
  })
  
  /*
    
    User actions
    
  */
  .on("on:invite", function(action, el, v) {
    
    //var url = "/app_dev.php/api/sites/"+Pineapple.config.id.site+".html";
    
    // Show loading modal
    Pineapple.Browser.open({ title: "User List", busy: true });
    
    /*
    $.ajax({type: "GET", dataType: "html", url: url})
      .done(function(data) {
        
        Pineapple.Browser.update({content: data});
      });
    */
    
    action.stopImmediatePropagation();
    
  })

  // Toggle the side bar open
  .on("click", ".pineapple-toggle-width", function() {
console.log(1);
    if($('.pineapple-minimize').is(':visible')) {

      $('html').css('margin-left', '10px');
      $('pineapple > toolbar').css('right', $(window).width()-10);
    }
  });

  window.onbeforeunload = function() { 
    
    if(Pineapple.actions.busy) {
      return "Hold on a second, we're busy processing something. If you navigate away, the process will be canceled and an error could occur";
    } else if (Pineapple.actions.changed) {
      return "You have made changes on this page that you have not yet saved. If you navigate away from this page you will lose your unsaved changes";
    }
  }

});