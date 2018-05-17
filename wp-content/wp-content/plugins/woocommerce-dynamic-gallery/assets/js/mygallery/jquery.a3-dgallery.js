/**
 * Copyright (c) 2010 Anders Ekdahl (http://coffeescripter.com/)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Version: 1.2.4
 *
 * Demo and documentation: http://coffeescripter.com/code/ad-gallery/
 */
(function($) {
  $.fn.adGallery = function(options) {
    var defaults = { loader_image: 'loader.gif',
                     start_at_index: 0,
					 gallery_ID: 0,
					 lightbox_class: 'lightbox',
                     description_wrapper: false,
                     thumb_opacity: 0.7,
                     animate_first_image: false,
                     animation_speed: 400,
                     width: false,
                     height: false,
                     display_next_and_prev: true,
                     display_back_and_forward: true,
                     scroll_jump: 0, // If 0, it jumps the width of the container
                     slideshow: {
                       enable: true,
                       autostart: false,
                       speed: 5000,
                       start_label: 'START SLIDESHOW',
                       stop_label: 'STOP SLIDESHOW',
					   zoom_label: 'ZOOM +',
                       stop_on_scroll: true,
                       countdown_prefix: '(',
                       countdown_sufix: ')',
                       onStart: false,
                       onStop: false
                     },
                     effect: 'slide-hori', // or 'slide-vert', 'fade', or 'resize', 'none'
                     enable_keyboard_move: true,
                     cycle: true,
                     callbacks: {
                       init: false,
                       afterImageVisible: false,
                       beforeImageVisible: false
                     }
    };
    var settings = $.extend(false, defaults, options);
	
    if(options && options.slideshow) {
      settings.slideshow = $.extend(false, defaults.slideshow, options.slideshow);
    };
    if(!settings.slideshow.enable) {
      settings.slideshow.autostart = false;
    };
    var galleries = [];
    $(this).each(function() {
      var gallery = new AdGallery(this, settings);
      galleries[galleries.length] = gallery;
    });
    // Sorry, breaking the jQuery chain because the gallery instances
    // are returned so you can fiddle with them
    return galleries;
  };

  function VerticalSlideAnimation(img_container, direction, desc) {
	  
    var current_top = parseInt(img_container.css('top'), 10);
    if(direction == 'left') {
      var old_image_top = '-'+ this.image_wrapper.height() +'px';
      img_container.css('top', this.image_wrapper.height() +'px');
    } else {
      var old_image_top = this.image_wrapper.height() +'px';
      img_container.css('top', '-'+ this.image_wrapper.height() +'px');
    };
    if(desc) {
      //desc.css('bottom', '-'+ desc[0].offsetHeight +'px');
      //desc.animate({bottom: 0}, this.settings.animation_speed * 2);
    };
    if(this.current_description) {
      this.current_description.animate({bottom: '-'+ this.current_description[0].offsetHeight +'px'}, this.settings.animation_speed * 2);
    };
    return {old_image: {top: old_image_top},
            new_image: {top: current_top}};
  };

  function HorizontalSlideAnimation(img_container, direction, desc) {
	img_container.css({left: 0});  
    var current_left = parseInt(img_container.css('left'), 10);
    if(direction == 'left') {
      var old_image_left = '-'+ this.image_wrapper.width() +'px';
      img_container.css('left',this.image_wrapper.width() +'px');
    } else {
      var old_image_left = this.image_wrapper.width() +'px';
      img_container.css('left','-'+ this.image_wrapper.width() +'px');
    };
	
    if(desc) {
      desc.css('bottom', '-'+ desc[0].offsetHeight +'px');
      desc.animate({bottom: 0}, this.settings.animation_speed * 2);
    };
    if(this.current_description) {
      this.current_description.animate({bottom: '-'+ this.current_description[0].offsetHeight +'px'}, this.settings.animation_speed * 2);
    };
    return {old_image: {left: old_image_left},
            new_image: {left: current_left}};
  };

  function ResizeAnimation(img_container, direction, desc) {
	
    var image_width = img_container.width();
    var image_height = img_container.height();
    var current_left = parseInt(img_container.css('left'), 10);
    var current_top = parseInt(img_container.css('top'), 10);
    img_container.css({width: 0, height: 0, top: parseInt( this.image_wrapper.height() ) / 2, left: 0});
    return {old_image: {width: 0,
                        height: 0,
                        top: parseInt( this.image_wrapper.height() ) / 2,
                        left: 0},
            new_image: {width: image_width,
                        height: image_height,
                        top: current_top,
                        left: 0}};
  };

  function FadeAnimation(img_container, direction, desc) {
    img_container.css('opacity', 0);
    return {old_image: {opacity: 0},
            new_image: {opacity: 1}};
  };

  // Sort of a hack, will clean this up... eventually
  function NoneAnimation(img_container, direction, desc) {
    img_container.css('opacity', 0);
    return {old_image: {opacity: 0},
            new_image: {opacity: 1},
            speed: 0};
  };

  function AdGallery(wrapper, settings) {
    this.init(wrapper, settings);
  };
  AdGallery.prototype = {
    // Elements
	first_load:true,
    wrapper: false,
    image_wrapper: false,
    nav_control: false,
    nav: false,
    loader: false,
    preloads: false,
    thumbs_wrapper: false,
    scroll_back: false,
    scroll_forward: false,
    next_link: false,
    prev_link: false,

    slideshow: false,
    image_wrapper_width: 0,
    image_wrapper_height: 0,
    current_index: 0,
    current_image: false,
    current_description: false,
    nav_display_width: 0,
    settings: false,
    images: false,
    in_transition: false,
    animations: false,
    init: function(wrapper, settings) {
      var context = this;
      this.wrapper = $(wrapper);
      this.settings = settings;
      this.setupElements();
      if ( typeof this.wrapper.data('touch_swipe') !== 'undefined' && this.wrapper.data('touch_swipe') == 'yes' && this.isMobileTablet() ) {
         this.settings.animation_speed = this.settings.touch_animation_speed;
         this.settings.effect = 'slide-hori';
         this.settings.slideshow.speed = this.settings.slideshow.touch_speed;
         this.callTouchSwipeEvent();
         this.settings.display_next_and_prev = false;
      }
      this.setupAnimations();
      if(this.settings.width) {
        this.image_wrapper_width = this.settings.width;
        this.image_wrapper.width(this.settings.width);
        this.wrapper.width(this.settings.width);
      } else {
        this.image_wrapper_width = this.image_wrapper.width();
      };
      if(this.settings.height) {
        this.image_wrapper_height = this.settings.height;
        this.image_wrapper.height(this.settings.height);
      } else {
        this.image_wrapper_height = this.image_wrapper.height();
      };
      this.nav_display_width = this.nav.width();
      this.current_index = 0;
      this.current_image = false;
      this.current_description = false;
      this.in_transition = false;
      this.findImages();
      if(this.settings.display_next_and_prev) {
        this.initNextAndPrev();
      };
      // The slideshow needs a callback to trigger the next image to be shown
      // but we don't want to give it access to the whole gallery instance
      var nextimage_callback = function(callback) {
        return context.nextImage(callback);
      };
      this.slideshow = new AdGallerySlideshow(nextimage_callback, this.settings);
      var slide_ctrl_html = '';
      var show_navbar_control = 'yes';
      if ( typeof this.wrapper.data('show_navbar_control') !== 'undefined' ) {
        show_navbar_control = this.wrapper.data('show_navbar_control');
      }
      if ( show_navbar_control != 'yes' || ( typeof this.wrapper.data('touch_swipe') !== 'undefined' && this.isMobileTablet() ) ) {
        slide_ctrl_html = this.slideshow.create( false, this.isMobileTablet() );
        this.image_wrapper.append(slide_ctrl_html);
        this.nav_control.hide();
      } else {
        slide_ctrl_html = this.slideshow.create( true, false );
        this.nav_control.append(slide_ctrl_html);
      }
      if(this.settings.slideshow.enable) {
        this.slideshow.enable();
      } else {
        this.slideshow.disable();
      };
      if(this.settings.display_back_and_forward) {
        this.initBackAndForward();
      };
      if(this.settings.enable_keyboard_move) {
        this.initKeyEvents();
      };
      var start_at = parseInt(this.settings.start_at_index, 10);
      if(window.location.hash && window.location.hash.indexOf('#a3dg-image') === 0) {
        start_at = window.location.hash.replace(/[^0-9]+/g, '');
        // Check if it's a number
        if((start_at * 1) != start_at) {
          start_at = this.settings.start_at_index;
        };
      };

      this.loading(true);
      this.showImage(start_at,
        function() {
          // We don't want to start the slideshow before the image has been
          // displayed
          if(context.settings.slideshow.autostart) {
            context.preloadImage(start_at + 1);
            context.slideshow.start();
          };
        }
      );
      this.fireCallback(this.settings.callbacks.init);
    },
    setupAnimations: function() {
      this.animations = {
        'slide-vert': VerticalSlideAnimation,
        'slide-hori': HorizontalSlideAnimation,
        'resize': ResizeAnimation,
        'fade': FadeAnimation,
        'none': NoneAnimation
      };
    },
    setupElements: function() {
      this.image_wrapper = this.wrapper.find('.a3dg-image-wrapper');
      this.nav_control = this.wrapper.find('.a3dg-navbar-control');

      this.image_wrapper.empty();
      this.nav = this.wrapper.find('.a3dg-nav');
      this.scroll_back = this.nav.find('.a3dg-back');
      this.scroll_forward = this.nav.find('.a3dg-forward');
      this.thumbs_wrapper = this.nav.find('.a3dg-thumbs');
      this.thumbs_list = this.thumbs_wrapper.find('.a3dg-thumb-list');

      thumb_visible = 3;
      thumb_spacing = 10;
      thumb_show_type = 'slider';
      if ( typeof this.wrapper.data('thumb_visible') !== 'undefined' ) {
        thumb_visible = parseInt( this.wrapper.data('thumb_visible') );
      }
      if ( typeof this.wrapper.data('thumb_spacing') !== 'undefined' ) {
        thumb_spacing = parseInt( this.wrapper.data('thumb_spacing') );
      }
      if ( typeof this.wrapper.data('thumb_show_type') !== 'undefined' ) {
        thumb_show_type = this.wrapper.data('thumb_show_type');
      }

      var thumbs = this.thumbs_list.find('li');
      var thumbs_count = thumbs.length;
      var thumbs_total = 0;
      var current_nav = this.nav;
      var current_thumbs_list = this.thumbs_list;

      if(this.settings.display_back_and_forward && thumb_visible < thumbs_count ) {
        this.nav.css('padding-left', this.scroll_back.outerWidth() ).css( 'padding-right', this.scroll_forward.outerWidth());
      } else {
        this.scroll_back.hide();
        this.scroll_forward.hide();
      }

      visible_width = this.thumbs_wrapper.outerWidth() - ( thumb_spacing * ( thumb_visible - 1 ) );
      item_width = visible_width / thumb_visible;
      thumb_slider_width = 0;
      is_first_item = true;

      thumbs.each( function() {
        thumbs_total++;
        thumb_slider_width += item_width;

        // don't add spacing of end element to thumb slider width
        if ( thumbs_total < thumbs_count ) {
          thumb_slider_width += thumb_spacing;
        }

        $(this).css( 'width', item_width + 'px' );

        if ( thumbs_total == thumbs_count ) {
          if ( 'slider' == thumb_show_type ) {
            current_thumbs_list.css( 'width', thumb_slider_width + 'px' );
          } else {
            current_thumbs_list.css( 'width', '100%' );
          }
          current_nav.css( 'visibility', 'visible' )
        }

      });

      this.preloads = $('<div class="a3dg-preloads"></div>');
      this.loader = $('<img class="a3dg-loader" src="'+ this.settings.loader_image +'" style="width:auto !important;" />');
      this.image_wrapper.append(this.loader);
      this.loader.hide();
      $(document.body).append(this.preloads);
    },
    loading: function(bool) {
      if(bool) {
        this.loader.show();
      } else {
        this.loader.hide();
      };
    },
    addAnimation: function(name, fn) {
      if($.isFunction(fn)) {
        this.animations[name] = fn;
      };
    },
    findImages: function() {
      var context = this;
      this.images = [];
      var thumb_wrapper_width = 0;
      var thumbs_loaded = 0;
      var thumbs = this.thumbs_wrapper.find('a');
      var thumb_count = thumbs.length;
      if(this.settings.thumb_opacity < 1) {
        thumbs.find('img').css('opacity', this.settings.thumb_opacity);
      };
      thumbs.each(
        function(i) {
          var link = $(this);
          var image_src = link.attr('href');
          var thumb = link.find('img');
		      var alt = link.attr('alt');
          // Check if the thumb has already loaded
          if(!context.isImageLoaded(thumb[0])) {
            thumb.load(
              function() {
                thumb_wrapper_width += this.parentNode.parentNode.offsetWidth;
                thumbs_loaded++;
              }
            );
          } else{
            thumb_wrapper_width += thumb[0].parentNode.parentNode.offsetWidth;
            thumbs_loaded++;
          };
          link.addClass('a3dg-thumb'+ i);
          link.click(
            function() {
              context.showImage(i);
              context.slideshow.stop();
              return false;
            }
          ).hover(
            function() {
              if(!$(this).is('.a3dg-active') && context.settings.thumb_opacity < 1) {
                $(this).find('img').fadeTo(300, 1);
              };
              context.preloadImage(i);
            },
            function() {
              if(!$(this).is('.a3dg-active') && context.settings.thumb_opacity < 1) {
                $(this).find('img').fadeTo(300, context.settings.thumb_opacity);
              };
            }
          );
          var link = false;
          if(thumb.data('a3dg-link')) {
            link = thumb.data('a3dg-link');
          } else if(thumb.attr('longdesc') && thumb.attr('longdesc').length) {
            link = thumb.attr('longdesc');
          };
          var desc = false;
          if(thumb.data('a3dg-desc')) {
            desc = thumb.data('a3dg-desc');
          } else if(thumb.attr('data-caption') && thumb.attr('data-caption').length) {
            desc = thumb.attr('data-caption');
          };
          var title = false;
          if(thumb.data('a3dg-title')) {
            title = thumb.data('a3dg-title');
          } else if(thumb.attr('title') && thumb.attr('title').length) {
            title = thumb.attr('title');
          };
          context.images[i] = { thumb: thumb.attr('src'), image: image_src, error: false,
                                preloaded: false, desc: desc, title: title, size: false,
                                link: link,alt:alt, sizes: thumb.attr('org-sizes'), srcset: thumb.attr('org-srcset'), org_width: thumb.attr('org-width'), org_height: thumb.attr('org-height') };
        }
      );
    },
    initKeyEvents: function() {
      var context = this;
      $(document).keydown(
        function(e) {
          if(e.keyCode == 39) {
            // right arrow
            context.nextImage();
            context.slideshow.stop();
          } else if(e.keyCode == 37) {
            // left arrow
            context.prevImage();
            context.slideshow.stop();
          };
        }
      );
    },
    initNextAndPrev: function() {
      this.next_link = $('<div class="fa fa-caret-right a3dg-next"></div>');
      this.prev_link = $('<div class="fa fa-caret-left a3dg-prev"></div>');
      this.image_wrapper.append(this.next_link);
      this.image_wrapper.append(this.prev_link);

      var context = this;
      this.prev_link.add(this.next_link).click(
        function() {
          if($(this).is('.a3dg-next')) {
            context.nextImage();
            context.slideshow.stop();
          } else {
            context.prevImage();
            context.slideshow.stop();
          };
        }
      ).find('div').css('opacity', 0.7);
    },
    isMobileTablet: function() {
      var isMobile = {
          Android: function() {
              return navigator.userAgent.match(/Android/i);
          },
          BlackBerry: function() {
              return navigator.userAgent.match(/BlackBerry/i);
          },
          iOS: function() {
              return navigator.userAgent.match(/iPhone|iPad|iPod/i);
          },
          Opera: function() {
              return navigator.userAgent.match(/Opera Mini/i);
          },
          Windows: function() {
              return navigator.userAgent.match(/IEMobile/i) || navigator.userAgent.match(/WPDesktop/i);
          },
          any: function() {
              return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
          }
      };

      if( isMobile.any() ) {
        return true
      }

      if ( $('html').width() <= 768 ) {
        return true;
      }

      return false;
    },
    callTouchSwipeEvent: function() {
      var context = this;
      this.image_wrapper.on("swipeleft",function(){
        context.nextImage();
        context.slideshow.stop();
      });
      this.image_wrapper.on("swiperight",function(){
        context.prevImage();
        context.slideshow.stop();
      });
    },
    initBackAndForward: function() {
      var context = this;
      var has_scrolled = 0;
      var thumbs_scroll_interval = false;
      $(this.scroll_back).add(this.scroll_forward).click(
        function() {
          // We don't want to jump the whole width, since an image
          // might be cut at the edge
          var width = context.nav_display_width - 50;
          if(context.settings.scroll_jump > 0) {
            var width = context.settings.scroll_jump;
          };
          if($(this).is('.a3dg-forward')) {
            var left = context.thumbs_wrapper.scrollLeft() + width;
          } else {
            var left = context.thumbs_wrapper.scrollLeft() - width;
          };
          if(context.settings.slideshow.stop_on_scroll) {
            context.slideshow.stop();
          };
          context.thumbs_wrapper.animate({scrollLeft: left +'px'});
          return false;
        }
      ).hover(
        function() {
          var direction = 'left';
          if($(this).is('.a3dg-forward')) {
            direction = 'right';
          };
          thumbs_scroll_interval = setInterval(
            function() {
              has_scrolled++;
              // Don't want to stop the slideshow just because we scrolled a pixel or two
              if(has_scrolled > 30 && context.settings.slideshow.stop_on_scroll) {
                context.slideshow.stop();
              };
              var left = context.thumbs_wrapper.scrollLeft() + 1;
              if(direction == 'left') {
                left = context.thumbs_wrapper.scrollLeft() - 1;
              };
              context.thumbs_wrapper.scrollLeft(left);
            },
            10
          );
          $(this).css('opacity', 1);
        },
        function() {
          has_scrolled = 0;
          clearInterval(thumbs_scroll_interval);
          
        }
      );
    },
    _afterShow: function() {
      if(!this.settings.cycle) {
        // Needed for IE
        if(this.current_index == (this.images.length - 1)) {
          this.next_link.hide();
        };
        if(this.current_index == 0) {
          this.prev_link.hide();
        };
      };
	  
      this.fireCallback(this.settings.callbacks.afterImageVisible);
    },
    /**
     * Checks if the image is small enough to fit inside the container
     * If it's not, shrink it proportionally
     */
    _getContainedImageSize: function(image_width, image_height, dynamic_height) {
      var image_wapper_height = this.image_wrapper.height();
      if ( dynamic_height > 0 ) {
        image_wapper_height = dynamic_height;
      }
      if(image_height > image_wapper_height) {
        var ratio = image_width / image_height;
        image_height = parseInt( image_wapper_height );
        image_width = parseInt( image_wapper_height ) * ratio;
      };
      if(image_width > parseInt( this.image_wrapper.width() ) ) {
  	    var ratio = image_height / image_width;
  	    image_width = parseInt( this.image_wrapper.width() );
  	    image_height = parseInt( this.image_wrapper.width() ) * ratio;
  	  };
      return {width: image_width, height: image_height};
    },
    _getContainedImageDynamicHeight: function(image_width, image_height) {
      var image_wrapper_width  = parseInt( this.image_wrapper.outerWidth() );
      if( image_width > image_wrapper_width ) {
        var ratio = image_width / image_wrapper_width;
        image_height = parseInt(image_height) / ratio;
      }
      return image_height;
    },
    /**
     * If the image dimensions are smaller than the wrapper, we position
     * it in the middle anyway
     */
    _centerImage: function(img_container, image_width, image_height, dynamic_height) {
      var image_wapper_height = this.image_wrapper.height();
      if ( dynamic_height > 0 ) {
        image_wapper_height = dynamic_height;
      }
      img_container.css('top', '0px');
      if(image_height < parseInt( image_wapper_height ) ) {
        var dif = parseInt( image_wapper_height ) - image_height;
        img_container.css('top', (dif / 2) +'px');
      };
      img_container.css('left', '0px');
      if(image_width < parseInt( this.image_wrapper.width() ) ) {
        var dif = parseInt( this.image_wrapper.width() ) - image_width;
        //img_container.css('left', (dif / 2) +'px');
		img_container.css('left', (dif / 2) +'px');
      };
    },
    _getDescription: function(image, gallery_id) {
      var desc = false;
	  var links = '';
      if(image.desc.length || image.title.length) {
        var title = '';
        if(image.title.length) {
          title = '<strong class="a3dg-description-title">'+ image.title +'</strong>';
        };
        var desc = '';
        if(image.desc.length) {
		    if(image.link){
				links = '<a href="'+image.link+'">... Read more</a>';
			}
          desc = '<span>'+ image.desc +'</span> ';
		  
        };
        desc = $('<p class="a3dg-image-description a3dg-image-description-'+gallery_id+'">'+ title + desc + links +'</p>');
      };
	  
	  if(!image.desc.length){
		   if(image.link){
				links = '<a href="'+image.link+'">Read more...</a>';
				desc = $('<p class="a3dg-image-description a3dg-image-description-'+gallery_id+'">'+ links +'</p>');
			}
			
	  }
      return desc;
    },
    /**
     * @param function callback Gets fired when the image has loaded, is displaying
     *                          and it's animation has finished
     */
    showImage: function(index, callback) {
	  
      if(this.images[index] && !this.in_transition) {
        var context = this;
        var image = this.images[index];
        this.in_transition = true;
        if(!image.preloaded) {
			
          this.loading(true);
          this.preloadImage(index, function() {
            context.loading(false);		
            context._showWhenLoaded(index, callback);
          });
        } else {
          this._showWhenLoaded(index, callback);
		  
        };
      };
    },
    /**
     * @param function callback Gets fired when the image has loaded, is displaying
     *                          and it's animation has finished
     */
    _showWhenLoaded: function(index, callback) {
      if(this.images[index]) {
        var context = this;
        var image = this.images[index];
	
        var img_container = $(document.createElement('div')).addClass('a3dg-image');
		
		
		
		var id = this.settings.gallery_ID;
		if(image.link){
      var img = $(new Image()).attr('src', image.image).attr('rel','').attr('rel','gallery_'+id);
		}else{
			var img = $(new Image()).attr('src', image.image).attr('rel','').addClass(this.settings.lightbox_class).attr('rel','gallery_'+id);
		}
    img.attr('alt',image.alt);
    img.attr('srcset', image.srcset);
		
		img.attr('idx',index);
    if(image.link) {
      var link = $('<a href="'+ image.link +'" target="_blank"></a>');
      link.append(img);
      img_container.append(link);
    } else {
      img_container.append(img);
    }
		if ($(".icon_zoom_"+id).length < 1 ){
		  this.nav_control.prepend('<div class="icon_zoom '+this.settings.lightbox_class+' icon_zoom_'+id+'" rel="gallery_'+id+'"><span>'+ this.settings.slideshow.zoom_label +'</span></div>');
		}

		var delay_lazy = this.settings.animation_speed;
		if (!this.first_load) {
			var a3dg_nav_height = this.nav.height();
			this.image_wrapper.siblings(".lazy-load").css("width", "100%").animate( { width: "0px" },delay_lazy );
		}
		
		
        this.image_wrapper.prepend(img_container);
        var dynamic_height = 0;
        if ( typeof this.wrapper.data('height_type') !== 'undefined' && this.wrapper.data('height_type') == 'dynamic' ) {
          dynamic_height = this._getContainedImageDynamicHeight(image.size.width, image.size.height);
          this.image_wrapper.animate({ height: dynamic_height }, 1000);
        }

        var size = this._getContainedImageSize(image.size.width, image.size.height, dynamic_height);
		    img.css({width: size.width +'px', height: size.height +'px'});
        img.attr('sizes', '(max-width:' + size.width +'px) 100vw, ' + size.width +'px' );
        img_container.css({width: size.width +'px', height: size.height +'px'});
        this._centerImage(img_container, size.width, size.height, dynamic_height);
		
		img_container.css('left', '0px');
		$('p.a3dg-image-description-'+id).remove();
        var desc = this._getDescription(image, id, img_container);
        if(desc) {
			
          if(!this.settings.description_wrapper) {
            this.image_wrapper.append(desc);
          } else {
            this.settings.description_wrapper.append(desc);
          }
		  $('p.a3dg-image-description-'+id).hide();
        };

		var delay_lazy = this.settings.animation_speed;
		if (!this.first_load) {
			setTimeout(function() {
				$('p.a3dg-image-description-'+id).css('bottom','0').show(1000);
			}, ( parseInt(delay_lazy) + 1) );
		} else {
			setTimeout(function() {
				$('p.a3dg-image-description-'+id).css('bottom','0').show(1000);
			}, ( 1) );
		}
		
		this.first_load = false;
		
		var des_delay = this.settings.slideshow.speed;
		setTimeout(function() {
			$('p.a3dg-image-description-'+id).hide(1000);
		}, ( ( parseInt(delay_lazy) + parseInt(des_delay) ) -1000) );
		
        this.highLightThumb(this.nav.find('.a3dg-thumb'+ index));

        var direction = 'right';
        if(this.current_index < index) {
          direction = 'left';
        };
        this.fireCallback(this.settings.callbacks.beforeImageVisible);
        if(this.current_image || this.settings.animate_first_image) {
          var animation_speed = this.settings.animation_speed;
          var easing = 'swing';
          var animation = this.animations[this.settings.effect].call(this, img_container, direction, desc);
          if(typeof animation.speed != 'undefined') {
            animation_speed = animation.speed;
          };
          if(typeof animation.easing != 'undefined') {
            easing = animation.easing;
          };
		  
          if(this.current_image) {
            var old_image = this.current_image;
            var old_description = this.current_description;
            old_image.animate(animation.old_image, animation_speed, easing,
              function() {
                old_image.remove();
                if(old_description) old_description.remove();
              }
            );
			
          };
          img_container.animate(animation.new_image, animation_speed, easing,
            function() {
              context.current_index = index;
              context.current_image = img_container;
              context.current_description = desc;
              context.in_transition = false;
              context._afterShow();
              context.fireCallback(callback);
            }
          );
        } else {
          this.current_index = index;
          this.current_image = img_container;
          context.current_description = desc;
          this.in_transition = false;
          context._afterShow();
		  
          this.fireCallback(callback);
        };
      };
    },
    nextIndex: function() {
	  
      if(this.current_index == (this.images.length - 1)) {
        if(!this.settings.cycle) {
          return false;
        };
        var next = 0;
      } else {
        var next = this.current_index + 1;
      };
      return next;
    },
    nextImage: function(callback) {
      var next = this.nextIndex();
      if(next === false) return false;
      this.preloadImage(next + 1);
	  var delay_lazy = this.settings.animation_speed;

      this.showImage(next, callback);	
      return true;
    },
    prevIndex: function() {
      if(this.current_index == 0) {
        if(!this.settings.cycle) {
          return false;
        };
        var prev = this.images.length - 1;
      } else {
        var prev = this.current_index - 1;
      };
      return prev;
    },
    prevImage: function(callback) {
      var prev = this.prevIndex();
      if(prev === false) return false;
      this.preloadImage(prev - 1);
      this.showImage(prev, callback);
      return true;
    },
    preloadAll: function() {
      var context = this;
      var i = 0;
      function preloadNext() {
        if(i < context.images.length) {
          i++;
          context.preloadImage(i, preloadNext);
        };
      };
      context.preloadImage(i, preloadNext);
    },
    preloadImage: function(index, callback) {
      if(this.images[index]) {
        var image = this.images[index];
        if(!this.images[index].preloaded) {
          var img = $(new Image());
          img.attr('src', image.image);
          img.attr('srcset', image.srcset);
          var sizes = this._getContainedImageSize(image.org_width, image.org_height);
          img.attr('sizes', '(max-width:' + sizes.width +'px) 100vw, ' + sizes.width +'px' );
          if(!this.isImageLoaded(img[0])) {
            this.preloads.append(img);
            var context = this;
            img.load(
              function() {
                image.preloaded = true;
                image.size = { width: $(this).width(), height: $(this).height() };
                context.fireCallback(callback);
              }
            ).error(
              function() {
                image.error = true;
                image.preloaded = false;
                image.size = false;
              }
            );
          } else {
            image.preloaded = true;
            image.size = { width: img[0].width, height: img[0].height };
            this.fireCallback(callback);
          };
        } else {
          this.fireCallback(callback);
        };
      };
    },
    isImageLoaded: function(img) {
      if(typeof img.complete != 'undefined' && !img.complete) {
        return false;
      };
      if(typeof img.naturalWidth != 'undefined' && img.naturalWidth == 0) {
        return false;
      };
      return true;
    },
    highLightThumb: function(thumb) {
      this.thumbs_wrapper.find('.a3dg-active').removeClass('a3dg-active');
      thumb.addClass('a3dg-active');
      if(this.settings.thumb_opacity < 1) {
        this.thumbs_wrapper.find('a:not(.a3dg-active) img').fadeTo(300, this.settings.thumb_opacity);
        thumb.find('img').fadeTo(300, 1);
      };
      var left = thumb[0].parentNode.offsetLeft;
      left -= (this.nav_display_width / 2) - (thumb[0].offsetWidth / 2);
      this.thumbs_wrapper.animate({scrollLeft: left +'px'});
    },
    fireCallback: function(fn) {
      if($.isFunction(fn)) {
        fn.call(this);
      };
    }
  };

  function AdGallerySlideshow(nextimage_callback, settings) {
    this.init(nextimage_callback, settings);
  };
  AdGallerySlideshow.prototype = {
	first_load_item: true,
    start_link: false,
    stop_link: false,
    countdown: false,
	image_wrapper: false,

    settings: false,
    nextimage_callback: false,
    enabled: false,
    running: false,
    countdown_interval: false,
    init: function(nextimage_callback, settings) {
      var context = this;
      this.nextimage_callback = nextimage_callback;
      this.settings = settings;
    },
    create: function( show_navbar_control, isMobile ) {
	  
    this.countdown = $('<span class="a3dg-slideshow-countdown"></span>');
	  this.slide_ctrl = $('<div class="slide-ctrl"></div>');

    if ( show_navbar_control ) {
      this.start_link = $('<span class="a3dg-slideshow-start-slide">'+ this.settings.slideshow.start_label +'</span>');
      this.stop_link = $('<span class="a3dg-slideshow-stop-slide">'+ this.settings.slideshow.stop_label +'</span>');
    } else {
      this.start_link = $('<span class="fa fa-play a3dg-slideshow-start-slide"></span>');
      this.stop_link = $('<span class="fa fa-pause a3dg-slideshow-stop-slide"></span>');
    }

    if ( isMobile ) {
      this.slide_ctrl.show();
    }

	  this.slide_ctrl.append(this.start_link).append(this.stop_link);
	  
	  var delay_lazy = this.settings.animation_speed;
      this.countdown.hide();
      var context = this;
      this.start_link.click(
        function() {
			     context.start();
        }
      );
	  
      this.stop_link.click(
	  
        function() {
          	context.stop();
          }
      );
	  
      $(document).keydown(
        function(e) {
          if(e.keyCode == 83) {
            // 's'
            if(context.running) {
              context.stop();
            } else {
              context.start();
            };
          };
        }
      );
      return this.slide_ctrl;
    },
    disable: function() {
      this.enabled = false;
      this.stop();
    },
    enable: function() {
      this.enabled = true;
    },
    toggle: function() {
      if(this.enabled) {
        this.disable();
      } else {
        this.enable();
      };
    },
    start: function() {

      if(this.running || !this.enabled) return false;
      var context = this;
      this.running = true;
      this.slide_ctrl.addClass('a3dg-slideshow-running');
      this._next();
	  
      this.fireCallback(this.settings.slideshow.onStart);
      return true;
    },
    stop: function() {
	  
      if(!this.running) return false;
      this.running = false;
      this.countdown.hide();
	  		
      this.slide_ctrl.removeClass('a3dg-slideshow-running');
	  
      clearInterval(this.countdown_interval);
      this.fireCallback(this.settings.slideshow.onStop);

      return true;
    },
    _next: function() {
      var context = this;
      var pre = this.settings.slideshow.countdown_prefix;
      var su = this.settings.slideshow.countdown_sufix;
      clearInterval(context.countdown_interval);
      this.countdown.show().html(pre + ( parseInt(this.settings.slideshow.speed) / 1000) + su);
      var slide_timer = 0;
	  var slide_next_timer = 0;
      this.countdown_interval = setInterval(
        function() {
          slide_timer += 1000;
		  context.first_load_item = false;
          if(slide_timer >= parseInt(context.settings.slideshow.speed) ) {
            var whenNextIsShown = function() {
              // A check so the user hasn't stoped the slideshow during the
              // animation
              if(context.running) {
                context._next();
			  };
              slide_timer = 0;
			  
			 
            };
            if(!context.nextimage_callback(whenNextIsShown)) {
              context.stop();
            };
            slide_timer = 0;
			
          };
          var sec = parseInt(context.countdown.text().replace(/[^0-9]/g, ''), 10);
          sec--;
          if(sec > 0) {
			context.countdown.html(pre + sec + su);
          }
        },
        1000
      );
	  
    },
    fireCallback: function(fn) {
      if($.isFunction(fn)) {
        fn.call(this);
      };
    }
  };
})(jQuery);
