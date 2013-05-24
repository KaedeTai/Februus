$(document).on("pageinit",":jqmData(role='page')", function(){
	$(":jqmData(slidemenu)").each(function(){
		var smb = $(this);
		smb.addClass('slidemenu_btn');
		var sm = $(smb.data('slidemenu'));
	    sm.addClass('slidemenu');
	    var smw = $(smb.data('slidemenu')+"_wrapper");
	    smw.addClass('slidemenu-wrapper');
		
		//console.log(smb.parent(":jqmData(role='header')").parent(":jqmData(role='page')").attr("id"));
		
		// slide left 會被 scroll bar 影響改用 tap 
//		smb.parent(":jqmData(role='header')").parent(":jqmData(role='page')").on("swipeleft", function(event){
//			console.log("left");
//			event.stopImmediatePropagation();
//			only_close = true;
//			slidemenu(sm, smb, smw, only_close);
//		});
		
		$('.sliedmain').live('tap', function(event){
			// menu size default 240px
			if (event.pageX > 240) {
				event.stopImmediatePropagation();
				only_close = true;
				slidemenu(sm, smb, smw, only_close);
			}
		});
		
		smb.parent(":jqmData(role='header')").parent(":jqmData(role='page')").on("swiperight", function(event){
			//console.log("right");
			event.stopImmediatePropagation();
			slidemenu(sm, smb, smw);
		});
		
		smb.on("click", function(event) {
			event.stopImmediatePropagation();
			slidemenu(sm, smb, smw);
		});
		
		//add swipe up & down event
		sm.live('swipeup', function(e, start, stop){
			var delta = stop.coords[1] - start.coords[1];
			var origin = smw.css("top");
			var diff = parseFloat(origin) + delta;
			var diffHeight = sm.height() - smw.height();
			if(diff < diffHeight){
				if(origin !== diffHeight+"px"){
					smw.animate({top: diffHeight+"px", avoidTransforms: false, useTranslate3d: true});
				}
			}else{
				smw.animate({top: diff+"px", avoidTransforms: false, useTranslate3d: true});
			}
		});
		
		sm.live('swipedown', function(e, start, stop){
			var delta = stop.coords[1] - start.coords[1];
			var origin = smw.css("top");
			var diff = parseFloat(origin) + delta;
			if(diff > 0){
				smw.animate({top: "0px", avoidTransforms: false, useTranslate3d: true});
			}else{
				smw.animate({top: diff+"px", avoidTransforms: false, useTranslate3d: true});
			}
		});
	});
	
	$(document).on("click", "a:not(:jqmData(slidemenu))", function(e) {
		var smb = $(".ui-page-active").children(":jqmData(role='header')").first().children(".slidemenu_btn").first();
		var sm = $(smb.data('slidemenu'));
		var smw = $(smb.data('slidemenu') + "_wrapper");
		//console.log("close :" + sm.attr('id'));
		only_close = true;
		slidemenu(sm, smb, smw, only_close);
	});

	$(window).on('resize', function(){
		if ($(".ui-page-active").children(":jqmData(role='header')").first().children(":jqmData(slidemenu)").first().data('slideopen')) {
			var sm = $($(".ui-page-active").children(":jqmData(role='header')").first().children(":jqmData(slidemenu)").first().data('slidemenu'));
			var smw = $($(".ui-page-active").children(":jqmData(role='header')").first().children(":jqmData(slidemenu)").first().data('slidemenu') + "_wrapper");
			var w = '240px';

			sm.css('width', w);
			sm.height(viewport().height);

			$(".ui-page-active").css('left', w);
		}

	});

});

function slidemenu(sm, context, smw, only_close) {

	sm.height(viewport().height);

	if (!context.data('slideopen') && !only_close) {
		//console.log("slideopen=false");
		sm.show();
		var w = '240px';
		sm.animate({width: w, avoidTransforms: false, useTranslate3d: true}, 'fast');
		$(".ui-page-active").css('left', w);
		context.data('slideopen', true);
		
		if ($(".ui-page-active").children(":jqmData(role='header')").first().data('position') === 'fixed') {
			console.log("fixed header");
			console.log(parseInt(w.split('px')[0]) + 10 + 'px');
			context.css('margin-left', parseInt(w.split('px')[0]) + 10 + 'px');
		} else {
			context.css('margin-left', '10px');
		}

	} else {
		//console.log("slideopen=true");
		var w = '0px';
		sm.animate({width: w, avoidTransforms: false, useTranslate3d: true}, 'fast', function(){sm.hide()});
		$(".ui-page-active").css('left', w);
		context.data('slideopen', false);
		context.css('margin-left', '0px');
	}
}

function viewport(){
	var e = window;
	var a = 'inner';
	if (!('innerWidth' in window)) {
		a = 'client';
		e = document.documentElement || document.body;
	}
	return { width : e[ a+'Width' ] , height : e[ a+'Height' ] }
}
