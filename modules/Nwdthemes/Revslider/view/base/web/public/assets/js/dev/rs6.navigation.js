/********************************************
 * REVOLUTION 6.2.0 EXTENSION - NAVIGATION
 * @version: 6.2.0 (20.18.2020)
 * @requires rs6.main.js
 * @author ThemePunch
*********************************************/
(function($) {
	"use strict";
	var _R = jQuery.fn.revolution,
		_ISM = _R.is_mobile();


///////////////////////////////////////////
// 	EXTENDED FUNCTIONS AVAILABLE GLOBAL  //
///////////////////////////////////////////
jQuery.extend(true,_R, {

	hideUnHideNav : function(id) {
		window.requestAnimationFrame(function() {
			var chng = false;
			if (ckNO(_R[id].navigation.arrows)) chng = biggerNav(_R[id].navigation.arrows,id,chng);
			if (ckNO(_R[id].navigation.bullets)) chng = biggerNav(_R[id].navigation.bullets,id,chng);
			if (ckNO(_R[id].navigation.thumbnails)) chng = biggerNav(_R[id].navigation.thumbnails,id,chng);
			if (ckNO(_R[id].navigation.tabs)) chng = biggerNav(_R[id].navigation.tabs,id,chng);
			setONHeights(id);
			if (chng) _R.manageNavigation(id);


		});
	},

	resizeThumbsTabs : function(id,force) {

		if (_R[id]!==undefined && _R[id].navigation.use && ((_R[id].navigation && _R[id].navigation.bullets.enable) || (_R[id].navigation && _R[id].navigation.tabs.enable) || (_R[id].navigation && _R[id].navigation.thumbnails.enable))) {
			var f = (_R.winW-480) / 500,
				tws = tpGS.gsap.timeline(),
				otab = _R[id].navigation.tabs,
				othu = _R[id].navigation.thumbnails,
				otbu = _R[id].navigation.bullets;

			tws.pause();
			f = f>1 ? 1 : f<0 ? 0 : f;

			if (ckNO(otab) && (force || otab.width>otab.min_width)) rtt(f,tws,_R[id].c,otab,_R[id].slideamount,'tab');
			if (ckNO(othu) && (force || othu.width>othu.min_width)) rtt(f,tws,_R[id].c,othu,_R[id].slideamount,'thumb');
			if (ckNO(otbu) && force) {

				// SET BULLET SPACES AND POSITION
				var bw = _R[id].c.find('.tp-bullets');

				bw.find('.tp-bullet').each(function(i){
					var b = jQuery(this),
						am = i+1,
						w = b.outerWidth()+parseInt((otbu.space===undefined? 0:otbu.space),0),
						h = b.outerHeight()+parseInt((otbu.space===undefined? 0:otbu.space),0);

				if (otbu.direction==="vertical") {
					b.css({top:((am-1)*h)+"px", left:"0px"});
					bw.css({height:(((am-1)*h) + b.outerHeight()),width:b.outerWidth()});
				}
				else {
					b.css({left:((am-1)*w)+"px", top:"0px"});
					bw.css({width:(((am-1)*w) + b.outerWidth()),height:b.outerHeight()});
				}
				});

			}

			tws.play();

			setONHeights(id);
		}

		return true;
	},

	updateNavIndexes : function(id) {
		var _ = _R[id].c;

		function setNavIndex(a) {
			if (_.find(a).lenght>0) {
				_.find(a).each(function(i) {
					jQuery(this).data('liindex',i);
				});
			}
		}
		setNavIndex('rs-tab');
		setNavIndex('rs-bullet');
		setNavIndex('rs-thumb');
		_R.resizeThumbsTabs(id,true);
		_R.manageNavigation(id);

	},

	// PUT NAVIGATION IN POSITION AND MAKE SURE THUMBS AND TABS SHOWING TO THE RIGHT POSITION
	manageNavigation : function(id,movetoposition) {
		if (!_R[id].navigation.use) return;
		if (_R[id].navOutterOffsets===undefined)  _R[id].navOutterOffsets = _R.getHorizontalOffset(_R[id].c,"all");
		var	lof = _R[id].navOutterOffsets.left,
			rof = _R[id].navOutterOffsets.right;

		if (ckNO(_R[id].navigation.bullets)) {
			if (_R[id].sliderLayout!="fullscreen" && _R[id].sliderLayout!="fullwidth") {
				// OFFSET ADJUSTEMENT FOR LEFT ARROWS BASED ON THUMBNAILS AND TABS OUTTER
				_R[id].navigation.bullets.h_offset_old = _R[id].navigation.bullets.h_offset_old === undefined ? parseInt(_R[id].navigation.bullets.h_offset,0) : _R[id].navigation.bullets.h_offset_old;
				_R[id].navigation.bullets.h_offset = _R[id].navigation.bullets.h_align==="center" ? _R[id].navigation.bullets.h_offset_old+lof/2 -rof/2: _R[id].navigation.bullets.h_offset_old+lof-rof;
			}
			setNavElPositions(_R[id].navigation.bullets.c,_R[id].navigation.bullets,id);
		}

		if (ckNO(_R[id].navigation.thumbnails)) setNavElPositions(_R[id].navigation.thumbnails,id);

		if (ckNO(_R[id].navigation.tabs)) setNavElPositions(_R[id].navigation.tabs,id);

		if (ckNO(_R[id].navigation.arrows)) {

			if (_R[id].sliderLayout!="fullscreen" && _R[id].sliderLayout!="fullwidth") {
				// OFFSET ADJUSTEMENT FOR LEFT ARROWS BASED ON THUMBNAILS AND TABS OUTTER
				_R[id].navigation.arrows.left.h_offset_old = _R[id].navigation.arrows.left.h_offset_old === undefined ? parseInt(_R[id].navigation.arrows.left.h_offset,0) : _R[id].navigation.arrows.left.h_offset_old;
				_R[id].navigation.arrows.left.h_offset = _R[id].navigation.arrows.left.h_align==="right" ?  _R[id].navigation.arrows.left.h_offset_old+rof : _R[id].navigation.arrows.left.h_offset_old+lof;

				_R[id].navigation.arrows.right.h_offset_old = _R[id].navigation.arrows.right.h_offset_old === undefined ? parseInt(_R[id].navigation.arrows.right.h_offset,0) : _R[id].navigation.arrows.right.h_offset_old;
				_R[id].navigation.arrows.right.h_offset = _R[id].navigation.arrows.right.h_align==="right" ? _R[id].navigation.arrows.right.h_offset_old+rof : _R[id].navigation.arrows.right.h_offset_old+lof;
			}
			setNavElPositions(_R[id].navigation.arrows.left,id);
			setNavElPositions(_R[id].navigation.arrows.right,id);
		}

		if (movetoposition!==false) {
			if (ckNO(_R[id].navigation.thumbnails)) moveThumbsInPosition(_R[id].navigation.thumbnails,id);
			if (ckNO(_R[id].navigation.tabs)) moveThumbsInPosition(_R[id].navigation.tabs,id);
		}
	},

	showFirstTime : function(id) {
		showNavElements(id);
		if (jQuery.isEmptyObject(_R[id].caches.contWidthManager)) _R.callContWidthManager(id);
		_R.hideUnHideNav(id);
	},
	selectNavElement : function(id,si,cl,callback) {
		var el = _R[id].cpar[0].getElementsByClassName(cl);
		for (var i=0;i<el.length;i++) if (_R.gA(el[i],"key")===si) {
			el[i].classList.add('selected');
			if (callback!==undefined) callback();
		} else el[i].classList.remove('selected');
	},
	transferParams : function(w,p) {
		if (p!==undefined) for (var i in p.params)  w = w.replace(p.params[i].from,p.params[i].to);
		return w;
	},

	// UPDATE CONTENT OF NAVIGATION ELEMENTS
	updateNavElementContent : function(id,_a,_b,_c,_d) {
		if (_R[id].pr_next_key===undefined && _R[id].pr_active_key===undefined) return;

		var key= _R[id].pr_next_key===undefined ? _R[id].pr_cache_pr_next_key===undefined ? _R[id].pr_active_key : _R[id].pr_cache_pr_next_key : _R[id].pr_next_key,
			si = _R.gA(_R[id].slides[key],"key"),ai = 0, f = false;

		if (_b.enable) _R.selectNavElement(id,si,'tp-bullet');
		if (_c.enable) _R.selectNavElement(id,si,'tp-thumb',function() {moveThumbsInPosition(_c,id);});
		if (_d.enable) _R.selectNavElement(id,si,'tp-tab',function() {moveThumbsInPosition(_d,id);});

		for (var i in _R[id].thumbs) {  ai = f===true ? ai : i; f = _R[id].thumbs[i].id===si || i==si ? true : f;}
		ai = parseInt(ai,0);
		var pi = ai>0 ? ai-1 : _R[id].slideamount-1,
			ni = ai+1==_R[id].slideamount ? 0 : ai+1;

		if (_a.enable === true && (_a.pi!== pi && _a.ni!==ni)) {
			_a.pi = pi;
			_a.ni = ni;
			_a.left.c[0].innerHTML = _R.transferParams(_a.tmp,_R[id].thumbs[pi]);
			if (ni>_R[id].slideamount) return;
			_a.right.c[0].innerHTML = _R.transferParams(_a.tmp,_R[id].thumbs[ni]);
			_a.right.iholder = _a.right.c.find('.tp-arr-imgholder');
			_a.left.iholder = _a.left.c.find('.tp-arr-imgholder');

			if (!_a.rtl) {
				if (_R[id].thumbs[pi]!==undefined && _a.left.iholder[0]!==undefined) tpGS.gsap.set(_a.left.iholder,{backgroundImage:"url("+_R[id].thumbs[pi].src+")"});
				if (_a.right.iholder[0]!==undefined) tpGS.gsap.set(_a.right.iholder,{backgroundImage:"url("+_R[id].thumbs[ni].src+")"});
			} else {
				if (_a.left.iholder[0]!==undefined) tpGS.gsap.set(_a.left.iholder,{backgroundImage:"url("+_R[id].thumbs[ni].src+")"});
				if (_R[id].thumbs[pi]!==undefined && _a.right.iholder[0]!==undefined) tpGS.gsap.set(_a.right.iholder,{backgroundImage:"url("+_R[id].thumbs[pi].src+")"});
			}
		}
	},


	// MANAGE THE NAVIGATION
		createNavigation : function(id) {
		var	_a = _R[id].navigation.arrows, _b = _R[id].navigation.bullets, _c = _R[id].navigation.thumbnails, _d = _R[id].navigation.tabs,
			a = ckNO(_a), b = ckNO(_b), c = ckNO(_c), d = ckNO(_d);

		// Initialise Keyboard Navigation if Option set so
		initKeyboard(id);

		// Initialise Mouse Scroll Navigation if _R[id]ion set so
		initMouseScroll(id);

		//Draw the Arrows
		if (a) {
			initArrows(_a,id);
			_a.c = _R[id].cpar.find('.tparrows');
		}

		// BUILD BULLETS, THUMBS and TABS
		for (var i in _R[id].slides) {
			if (!_R[id].slides.hasOwnProperty(i)) continue;
			if (_R.gA(_R[id].slides[i],'not_in_nav')!='true') {
				var li_rtl = jQuery(_R[id].slides[_R[id].slides.length-1-i]),
					li = jQuery(_R[id].slides[i]);
				if (b)
					if (_R[id].navigation.bullets.rtl)
						addBullet(_R[id].c,_b,li_rtl,id);
					else
						addBullet(_R[id].c,_b,li,id);

				if (c)
					if (_R[id].navigation.thumbnails.rtl)
						addThumb(_R[id].c,_c,li_rtl,'tp-thumb',id);
					else
						addThumb(_R[id].c,_c,li,'tp-thumb',id);
				if (d)
					if (_R[id].navigation.tabs.rtl)
						addThumb(_R[id].c,_d,li_rtl,'tp-tab',id);
					else
						addThumb(_R[id].c,_d,li,'tp-tab',id);
			}
		}

		if (b) setNavElPositions(_b,id);
		if (c) setNavElPositions(_c,id);
		if (d) setNavElPositions(_d,id);
		if (c || d) _R.callContWidthManager(id);

		_R[id].navigation.createNavigationDone = true;


		if (c) jQuery.extend(true,_c,navOExt(id,"thumb"));
		if (d) jQuery.extend(true,_d,navOExt(id,"tab"));


		// LISTEN TO SLIDE CHANGE - SET ACTIVE SLIDE BULLET
		_R[id].c.bind('revolution.slide.onafterswap revolution.nextslide.waiting',function() {_R.updateNavElementContent(id,_a,_b,_c,_d)});

		hdResets(_a);
		hdResets(_b);
		hdResets(_c);
		hdResets(_d);

		// HOVER OVER ELEMENTS SHOULD SHOW/HIDE NAVIGATION ELEMENTS
		_R[id].cpar.on("mouseenter mousemove",function(e) {
			if (e.target!==undefined && e.target.className!==undefined && typeof e.target.className==="string" && e.target.className.indexOf("rs-waction")>=0) return;
			if (_R[id].tpMouseOver!==true) {
				if (_R[id].firstSlideAvailable) {
					_R[id].tpMouseOver=true;
					showNavElements(id);
					// ON MOBILE WE NEED TO HIDE ELEMENTS EVEN AFTER TOUCH
					if (_ISM && _R[id].someNavIsDragged!==true) {
						ct(_R[id].hideAllNavElementTimer);
						_R[id].hideAllNavElementTimer = setTimeout(function() {
							_R[id].tpMouseOver=false;
							hidaNavElements(id);
						},150);
					}
				}
			}
		});

		_R[id].cpar.on("mouseleave ",function() {
			_R[id].tpMouseOver=false;
			hidaNavElements(id);
		});

		// Initialise Swipe Navigation
		if (c || d || _R[id].sliderType==="carousel" || ((_R[id].navigation.touch.touchOnDesktop) || (_R[id].navigation.touch.touchenabled && _ISM))) swipeAction(id);
		_R[id].navigation.initialised = true;
		_R.updateNavElementContent(id,_a,_b,_c,_d);
		_R.showFirstTime(id);
	}
});




/////////////////////////////////
//	-	INTERNAL FUNCTIONS	- ///
/////////////////////////////////

function navOExt(id,a) {
	var r = new Object({single:'.tp-'+a,c:_R[id].cpar.find('.tp-'+a+'s')});
	r.mask = r.c.find('.tp-'+a+'-mask');
	r.wrap = r.c.find('.tp-'+a+'s-inner-wrapper');
	return r;
}

var moveThumbsInPosition = function(s,id) {
	if (s===undefined || s.mask==undefined) return;
	var tw = s.direction==="vertical" ?
		s.mask.find(s.single).first().outerHeight(true)+s.space :
		s.mask.find(s.single).first().outerWidth(true)+s.space,
		tmw = s.direction==="vertical" ? s.mask.height() : s.mask.width(),
		ti = s.mask.find(s.single+'.selected').data('liindex');
	ti = s.rtl ? (_R[id].slideamount - ti) : ti;
	ti = ti===undefined ? 0 : ti;
	ti = ti>0 && _R[id].sdir===1 ? s.visibleAmount>1 ? ti-1 : ti  : ti;

	var me = tmw/tw,
		ts = s.direction==="vertical" ? s.mask.height() : s.mask.width(),
		tp = 0-(ti * tw),
		els =  s.direction==="vertical" ? s.wrap.height() : s.wrap.width(),
		curpos = tp < 0-(els-ts) ? 0-(els-ts) : tp,
		elp = _R.gA(s.wrap[0],"offset");

	elp = elp===undefined ? 0 : elp;

	if (me>2) {
		curpos = tp - (elp+tw) <= 0 ? tp - (elp+tw) < 0-tw ? elp : curpos + tw : curpos;
		curpos = ( (tp-tw + elp + tmw)< tw && tp  + (Math.round(me)-2)*tw < elp) ? tp + (Math.round(me)-2)*tw : curpos;
	}

	curpos = (s.direction!=="vertical" && s.mask.width()>=s.wrap.width()  || s.direction==="vertical" && s.mask.height()>=s.wrap.height()) ? 0 : curpos < 0-(els-ts) ? 0-(els-ts) : curpos > 0 ? 0 : curpos;



	if (!s.c.hasClass("dragged")) {
		if (s.direction==="vertical")
			s.wrap.data('tmmove',tpGS.gsap.to(s.wrap,0.5,{top:curpos+"px",ease:"power3.inOut"}));
		else
			s.wrap.data('tmmove',tpGS.gsap.to(s.wrap,0.5,{left:curpos+"px",ease:"power3.inOut"}));
		s.wrap.data('offset',curpos);
	}
};


// RESIZE THE THUMBS BASED ON ORIGINAL SIZE AND CURRENT SIZE OF WINDOW
var rtt = function(f,tws,c,o,lis,wh) {
	var h = c.parent().find('.tp-'+wh+'s'),
		ins = h.find('.tp-'+wh+'s-inner-wrapper'),
		mask = h.find('.tp-'+wh+'-mask'),
		cw = o.width*f < o.min_width ? o.min_width : Math.round(o.width*f),
		ch = Math.round((cw/o.width) * o.height),
		iw = o.direction === "vertical" ? cw : (cw*lis) + ((o.space)*(lis-1)),
		ih = o.direction === "vertical" ? (ch*lis) + ((o.space)*(lis-1)) : ch,
		anm = o.direction === "vertical" ? {width:cw+"px"} : {height:ch+"px"};

	tws.add(tpGS.gsap.set(h,anm));
	tws.add(tpGS.gsap.set(ins,{width:iw+"px",height:ih+"px"}));
	tws.add(tpGS.gsap.set(mask,{width:iw+"px",height:ih+"px"}));

	var fin = ins.find('.tp-'+wh+'');
	if (fin)
		jQuery.each(fin,function(i,el) {
			if (o.direction === "vertical")
				tws.add(tpGS.gsap.set(el,{top:(i*(ch+parseInt((o.space===undefined? 0:o.space),0))),width:cw+"px",height:ch+"px"}));
			else
			if (o.direction === "horizontal")
				tws.add(tpGS.gsap.set(el,{left:(i*(cw+parseInt((o.space===undefined? 0:o.space),0))),width:cw+"px",height:ch+"px"}));
		});
	return tws;
};

// INTERNAL FUNCTIONS
var normalizeWheel = function( event) /*object*/ {
		var sX = 0, sY = 0,       // spinX, spinY
			pX = 0, pY = 0,       // pixelX, pixelY
			PIXEL_STEP = 1,
			LINE_HEIGHT = 1,
			PAGE_HEIGHT = 1;

		// Legacy
		if ('detail'      in event) { sY = event.detail; }
		if ('wheelDelta'  in event) { sY = -event.wheelDelta / 120; }
		if ('wheelDeltaY' in event) { sY = -event.wheelDeltaY / 120; }
		if ('wheelDeltaX' in event) { sX = -event.wheelDeltaX / 120; }


		//sY = navigator.userAgent.match(/mozilla/i) ? sY*10 : sY;


		// side scrolling on FF with DOMMouseScroll
		if ( 'axis' in event && event.axis === event.HORIZONTAL_AXIS ) {
		sX = sY;
		sY = 0;
		}

		pX = sX * PIXEL_STEP;
		pY = sY * PIXEL_STEP;

		if ('deltaY' in event) { pY = event.deltaY; }
		if ('deltaX' in event) { pX = event.deltaX; }



		if ((pX || pY) && event.deltaMode) {
		if (event.deltaMode == 1) {          // delta in LINE units
			pX *= LINE_HEIGHT;
			pY *= LINE_HEIGHT;
		} else {                             // delta in PAGE units
			pX *= PAGE_HEIGHT;
			pY *= PAGE_HEIGHT;
		}
		}

		// Fall-back if spin cannot be determined
		if (pX && !sX) { sX = (pX < 1) ? -1 : 1; }
		if (pY && !sY) { sY = (pY < 1) ? -1 : 1; }

		pY = navigator.userAgent.match(/mozilla/i) ? pY*10 : pY;

		if (pY>300 || pY<-300) pY = pY/10;


		return { spinX  : sX,
				spinY  : sY,
				pixelX : pX,
				pixelY : pY };
};

var initKeyboard = function(id) {
	if (_R[id].navigation.keyboardNavigation!==true)  return;
	_R.document.keydown(function(e){

		if ((_R[id].navigation.keyboard_direction=="horizontal" && e.keyCode == 39) || (_R[id].navigation.keyboard_direction=="vertical" && e.keyCode==40)) {
			if (_R[id].keydown_time_stamp !==undefined && ((new Date().getTime() - _R[id].keydown_time_stamp)<1000)) return;
			_R[id].sc_indicator="arrow";
			_R[id].sc_indicator_dir = 0;
			if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;
			_R.callingNewSlide(id,1,_R[id].sliderType==="carousel");
		}
		if ((_R[id].navigation.keyboard_direction=="horizontal" && e.keyCode == 37) || (_R[id].navigation.keyboard_direction=="vertical" && e.keyCode==38)) {
			if (_R[id].keydown_time_stamp !==undefined && ((new Date().getTime() - _R[id].keydown_time_stamp)<1000)) return;
			_R[id].sc_indicator="arrow";
			_R[id].sc_indicator_dir = 1;
			if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;
			_R.callingNewSlide(id,-1,_R[id].sliderType==="carousel");
		}
		_R[id].keydown_time_stamp = new Date().getTime();
	});
};



var initMouseScroll = function(id) {

	if (_R[id].navigation.mouseScrollNavigation!==true && _R[id].navigation.mouseScrollNavigation!=="on" && _R[id].navigation.mouseScrollNavigation!=="carousel") return;
	_R[id].isIEEleven = !!navigator.userAgent.match(/Trident.*rv\:11\./);
	_R[id].isSafari = !!navigator.userAgent.match(/safari/i);
	_R[id].ischrome = !!navigator.userAgent.match(/chrome/i);


	var bl = _R[id].ischrome ? -49 : _R[id].isIEEleven || _R[id].isSafari ? -9 : navigator.userAgent.match(/mozilla/i) ?  -29 :  -49,
		tl = _R[id].ischrome ? 49 : _R[id].isIEEleven || _R[id].isSafari ? 9 : navigator.userAgent.match(/mozilla/i) ? 29 :  49;

	_R[id].c.on('mousewheel DOMMouseScroll', function(e) {



		var res = normalizeWheel(e.originalEvent),
			ret = true,
			fs = _R[id].pr_active_key==0 || _R[id].pr_processing_key == 0,
			ls = _R[id].pr_active_key==_R[id].slideamount-1 ||  _R[id].pr_processing_key == _R[id].slideamount-1;




		if (_R[id].sliderType==="carousel" && _R[id].carousel.snap===false) {

			ret=false;

			_R.swipeAnimate({id:id,to:(_R[id].carousel.slide_offset+(res.pixelY*5)),direction:res.pixelY<0 ? "left" : "right",easing:"power2.out",phase:"move"});
		} else {

			if (_R[id].isSafari) {
				var dir = res.pixelY<0 ? -1 : 1;
				if (_R[id].doubleScrollEventTimeStamp!==undefined && e.timeStamp - _R[id].doubleScrollEventTimeStamp < 750 && _R[id].doubleScrollEventDir===dir) {e.preventDefault(e); return;}
				_R[id].doubleScrollEventTimeStamp = e.timeStamp;
				_R[id].doubleScrollEventDir = dir;
			}

			if (_R[id].navigation.mouseScrollNavigation=="carousel") fs = ls = false;

			if (_R[id].pr_processing_key===undefined) {

				if (res.pixelY<bl) {
					if (!fs) {
						_R[id].sc_indicator="arrow";
						if (_R[id].navigation.mouseScrollReverse!=="reverse") {
							_R[id].sc_indicator_dir = 1;
							if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;
							_R.callingNewSlide(id,-1,_R[id].sliderType==="carousel");
						}
						ret = false;
					}
					if (!ls) {
						_R[id].sc_indicator="arrow";
						if (_R[id].navigation.mouseScrollReverse==="reverse") {
							_R[id].sc_indicator_dir = 0;
							if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;
							_R.callingNewSlide(id,1,_R[id].sliderType==="carousel");
						}
						ret = false;
					}
				} else
				if (res.pixelY>tl) {
					if (!ls) {
								_R[id].sc_indicator="arrow";
								if (_R[id].navigation.mouseScrollReverse!=="reverse") {
								_R[id].sc_indicator_dir = 0;
								if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;
								_R.callingNewSlide(id,1,_R[id].sliderType==="carousel");
							}
							ret = false;
						}
						if (!fs) {
							_R[id].sc_indicator="arrow";
							if (_R[id].navigation.mouseScrollReverse==="reverse") {
								_R[id].sc_indicator_dir = 1;
								if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;
								_R.callingNewSlide(id,-1,_R[id].sliderType==="carousel");
							}
							ret = false;
						}
				}
			} else ret = false;

			var tc = _R[id].c.offset().top-jQuery('body').scrollTop(),
				bc = tc+_R[id].c.height();
			if (_R[id].navigation.mouseScrollNavigation!="carousel") {
				if (_R[id].navigation.mouseScrollReverse!=="reverse")
					if ((tc>0 && res.pixelY>0) || (bc<_R.getWinH(id) && res.pixelY<0))
						ret = true;
				if (_R[id].navigation.mouseScrollReverse==="reverse")
					if ((tc<0 && res.pixelY<0) || (bc>_R.getWinH(id) && res.pixelY>0))
						ret = true;
			} else {
				ret=false;
			}
		}
		if (!ret) {
			e.preventDefault(e);
			return false;
		} else  return;

	});
};

var getClosest = function (elem, tagName) {
	for ( ; elem && elem !== document; elem = elem.parentNode ) {
		if ( elem.tagName === tagName ) return elem;
	}
	return false;
};

var isme = function (a,e) {
		var ret=false;
		if (e.path===undefined || _ISM) ret = getClosest(e.target,a);
		for (var i in e.path) if (e.path.hasOwnProperty(i) && e.path[i].tagName===a) ret = true;
		return ret;
};
// 	-	SET THE SWIPE FUNCTION //

var swipeAction = function(id) {
	// TOUCH ENABLED SCROLL
	var _ = _R[id].carousel,
		ONANDROID = _R.is_android();

	jQuery(".bullet, .bullets, .tp-bullets, .tparrows").addClass("noSwipe");
	_R[id].navigation.touch = _R[id].navigation.touch===undefined ? {} : _R[id].navigation.touch;
	_R[id].navigation.touch.swipe_direction = _R[id].navigation.touch.swipe_direction===undefined ? "horizontal" : _R[id].navigation.touch.swipe_direction;


	/*
	Swipe over Navigation
	*/
	jQuery('.rs-nav-element').rsswipe({
		allowPageScroll:"vertical",
		triggerOnTouchLeave:true,
		treshold:_R[id].navigation.touch.swipe_treshold,
		fingers:_R[id].navigation.touch.swipe_min_touches>5 ? 1 : _R[id].navigation.touch.swipe_min_touches,
		excludedElements:"button, input, select, textarea, .noSwipe, .rs-waction",
		tap:function(event,target) {
			if (target!==undefined)
				var p = jQuery(target).closest('rs-thumb'); if (p!==undefined && p.length>0) p.trigger('click'); else { p = jQuery(target).closest('rs-tab'); if (p.length>0) p.trigger('click'); else { p = jQuery(target).closest('rs-bullet'); if (p.length>0) p.trigger('click');}}
		},
		swipeStatus:function(event,phase,direction,distance,duration,fingerCount,fingerData) {
			// SWIPE OVER THUMBS OR TABS
			if (phase==="start" || phase==="move" || phase==="end" || phase=="cancel") {
				var ONTHUMBS =  isme('RS-THUMB',event),
					ONTABS =  isme('RS-TAB',event);
				if (ONTHUMBS===false && ONTABS===false) {
					ONTHUMBS =  event.target.tagName === 'RS-THUMBS-WRAP' || event.target.tagName === 'RS-THUMBS' || event.target.className.indexOf('tp-thumb-mask')>=0;
					if (ONTHUMBS!==true) ONTABS =  event.target.tagName === 'RS-TABS-WRAP' || event.target.tagName === 'RS-TABS' ||  event.target.className.indexOf('tp-tab-mask')>=0 ;
				}
				var	distanceX = phase==="start" ? 0 : ONANDROID ? fingerData[0].end.x - fingerData[0].start.x : event.pageX - _.screenX,
					distanceY = phase==="start" ? 0 : ONANDROID ? fingerData[0].end.y - fingerData[0].start.y : event.pageY - _.screenY,
					thumbs = ONTHUMBS ? ".tp-thumbs" : ".tp-tabs",
					thumbmask = ONTHUMBS ? ".tp-thumb-mask" : ".tp-tab-mask",
					thumbsiw = ONTHUMBS ? ".tp-thumbs-inner-wrapper" : ".tp-tabs-inner-wrapper",
					thumb = ONTHUMBS ? ".tp-thumb" : ".tp-tab",
					_o = ONTHUMBS ? _R[id].navigation.thumbnails : _R[id].navigation.tabs,
					t= _R[id].cpar.find(thumbmask),
					el = t.find(thumbsiw),
					tdir = _o.direction,
					els = tdir==="vertical" ? el.height() : el.width(),
					ts =  tdir==="vertical" ? t.height() : t.width(),
					tw = tdir==="vertical" ? t.find(thumb).first().outerHeight(true)+_o.space : t.find(thumb).first().outerWidth(true)+_o.space,
					newpos =  (el.data('offset') === undefined ? 0 : parseInt(el.data('offset'),0)),
					curpos = 0;

				switch (phase) {
					case "start":
						if (tdir==="vertical") event.preventDefault();
						_.screenX = ONANDROID ? fingerData[0].end.x : event.pageX;
						_.screenY = ONANDROID ? fingerData[0].end.y : event.pageY;
						_R[id].cpar.find(thumbs).addClass("dragged");
						newpos = tdir === "vertical" ? el.position().top : el.position().left;
						el.data('offset',newpos);
						if (el.data('tmmove')) el.data('tmmove').pause();
						_R[id].someNavIsDragged = true;
						clearAllHidings(id);
					break;
					case "move":
						if (els<=ts) return false;
						curpos = newpos + (tdir === "vertical" ? distanceY : distanceX);
						curpos = curpos>0 ? tdir==="horizontal" ? curpos - (el.width() * (curpos/el.width() * curpos/el.width())) : curpos - (el.height() * (curpos/el.height() * curpos/el.height())) : curpos;
						var dif = tdir==="vertical" ? 0-(el.height()-t.height()) : 0-(el.width()-t.width());
						curpos = curpos < dif ? tdir==="horizontal" ? curpos + (el.width() * (curpos-dif)/el.width() * (curpos-dif)/el.width()) : curpos + (el.height() * (curpos-dif)/el.height() * (curpos-dif)/el.height()) : curpos;
						if (tdir==="vertical") tpGS.gsap.set(el,{top:curpos+"px"}); else tpGS.gsap.set(el,{left:curpos+"px"});
						ct(_R[id].hideAllNavElementTimer);
					break;
					case "end":
					case "cancel":
						curpos = newpos + (tdir === "vertical" ? distanceY : distanceX);
						curpos = tdir==="vertical" ? curpos < 0-(el.height()-t.height()) ? 0-(el.height()-t.height()) : curpos : curpos < 0-(el.width()-t.width()) ? 0-(el.width()-t.width()) : curpos;
						curpos = curpos > 0 ? 0 : curpos;
						curpos = Math.abs(distance)>tw/10 ? distance<=0 ? Math.floor(curpos/tw)*tw : Math.ceil(curpos/tw)*tw : distance<0 ? Math.ceil(curpos/tw)*tw : Math.floor(curpos/tw)*tw;
						curpos = tdir==="vertical" ? curpos < 0-(el.height()-t.height()) ? 0-(el.height()-t.height()) : curpos : curpos < 0-(el.width()-t.width()) ? 0-(el.width()-t.width()) : curpos;
						curpos = curpos > 0 ? 0 : curpos;
						if (tdir==="vertical") tpGS.gsap.to(el,0.5,{top:curpos+"px",ease:"power3.out"}); else tpGS.gsap.to(el,0.5,{left:curpos+"px",ease:"power3.out"});
						curpos = !curpos ?  tdir==="vertical" ? el.position().top : el.position().left : curpos;
						el.data('offset',curpos);
						el.data('distance',distance);
						_R[id].cpar.find(thumbs).removeClass("dragged");
						_R[id].someNavIsDragged = false;
						return true;
					break;
				}
			} else {
				//if (_R[id].navigation.touch.drag_block_vertical) event.preventDefault();
				return true;
			}
		}
	});

	/*
	SWIPE OVER SLIDER
	*/


	if ((_R[id].sliderType!=="carousel" && ((_ISM && _R[id].navigation.touch.touchenabled) || (_ISM!==true && _R[id].navigation.touch.touchOnDesktop))) ||
		(_R[id].sliderType==="carousel" && ((_ISM && _R[id].navigation.touch.mobileCarousel) || (_ISM!==true && _R[id].navigation.touch.desktopCarousel)))
		)
	_R[id].c.rsswipe({
		allowPageScroll:"vertical",
		triggerOnTouchLeave:true,
		treshold:_R[id].navigation.touch.swipe_treshold,
		fingers:_R[id].navigation.touch.swipe_min_touches>5 ? 1 : _R[id].navigation.touch.swipe_min_touches,
		excludedElements:"label, button, input, select, textarea, .noSwipe, .rs-nav-element, .rs-waction",

		swipeStatus:function(event,phase,direction,distance,duration,fingerCount,fingerData) {

			var	distanceX = phase==="start" ? 0 : ONANDROID ? fingerData[0].end.x - fingerData[0].start.x : event.pageX - _.screenX,
				distanceY = phase==="start" ? 0 : ONANDROID ? fingerData[0].end.x - fingerData[0].start.y : event.pageY - _.screenY;


			if (_R[id].sliderType==="carousel" && _R[id].carousel.wrapwidth>_R[id].carousel.maxwidth && _R[id].carousel.horizontal_align!=="center") return;

			// SWIPE OVER SLIDER, TO SWIPE SLIDES IN CAROUSEL MODE
			if (_R[id].sliderType==="carousel") {

				if (_.preventSwipe || (_ISM && (direction==="left" || direction==="right"))) event.preventDefault();
				if (_.positionanim!==undefined) _.positionanim.pause();
				_.carouselAutomatic = false;

				switch (phase) {
					case "start":
						clearTimeout(_.swipeMainTimer);
						_.beforeSwipeOffet = _.slide_offset;
						_.focusedBeforeSwipe=_.focused;
						_.beforeDragStatus = _R[id].sliderstatus;
						_R[id].c.trigger('stoptimer');
						_.swipeStartPos = ONANDROID ? fingerData[0].start.x : event.pageX;
						_.swipeStartTime = new Date().getTime();
						_.screenX = ONANDROID ? fingerData[0].end.x : event.pageX;
						_.screenY = ONANDROID ? fingerData[0].end.y : event.pageY;
						if (_.positionanim!==undefined) {
								_.positionanim.pause();
								_.carouselAutomatic = false;
						}
						_.overpull = "none";
						_.wrap.addClass("dragged");
					break;
					case "move":

							if (direction==="left" || direction==="right") _.preventSwipe = true;
							_.justDragged = true;
							if (Math.abs(distanceX)>=10 || _R[id].carousel.isDragged) {
								_R[id].carousel.isDragged = true;
								_R[id].c.find('.rs-waction').addClass("tp-temporarydisabled");
								_.CACHE_slide_offset = _.beforeSwipeOffet + distanceX;

								if (!_.infinity) {
									var bb = _.horizontal_align==="center" ? ((_.wrapwidth/2-_.slide_width/2) - _.CACHE_slide_offset) / _.slide_width : (0 - _.CACHE_slide_offset) / _.slide_width;
									if ((_.overpull ==="none" || _.overpull===0)  && (bb<0 || bb>_R[id].slideamount-1)) _.overpull =  distanceX;
									else
									if (bb>=0 && bb<=_R[id].slideamount-1 && ((bb>=0 && distanceX>_.overpull) || (bb<=_R[id].slideamount-1 && distanceX<_.overpull))) _.overpull = 0;
									_.CACHE_slide_offset = bb<0 ? _.CACHE_slide_offset+ (_.overpull-distanceX)/1.5 + Math.sqrt(Math.abs((_.overpull-distanceX)/1.5)) :
										bb>_R[id].slideamount-1 ? _.CACHE_slide_offset+ (_.overpull-distanceX)/1.5 - Math.sqrt(Math.abs((_.overpull-distanceX)/1.5)) : _.CACHE_slide_offset ;
									}
									_R.swipeAnimate({id:id,to:_.CACHE_slide_offset,direction:direction,easing:"power2.out",phase:"move"});

							}
					break;

					case "end":
					case "cancel":
							//duration !!
							clearTimeout(_.swipeMainTimer);
							_.swipeMainTimer = setTimeout(function() {
								_.preventSwipe = false;
							},500);
							_R[id].carousel.isDragged = false;
							_.wrap.removeClass("dragged");
							_.swipeEndPos = ONANDROID ? fingerData[0].end.x : event.pageX;
							_.swipeEndTime = new Date().getTime();
							_.swipeDuration = _.swipeEndTime - _.swipeStartTime;
							_.swipeDistance = _ISM ? (_.swipeEndPos - _.swipeStartPos) : (_.swipeEndPos - _.swipeStartPos)/1.5;
							_.swipePower = _.swipeDistance / _.swipeDuration;
							_.CACHE_slide_offset = _.slide_offset + (_.swipeDistance*Math.abs(_.swipePower));
							_R.swipeAnimate({id:id,to:_.CACHE_slide_offset,direction:direction,fix:true,newSlide:true,easing:"power2.out",phase:"end"});


							if (_.beforeDragStatus ==='playing') _R[id].c.trigger('restarttimer');
							setTimeout(function() {_R[id].c.find('.rs-waction').removeClass("tp-temporarydisabled");},19);

					break;
				}
			}  else {
				if (phase=="end") {
					_R[id].sc_indicator="arrow";
					if ((_R[id].navigation.touch.swipe_direction=="horizontal" && direction == "left") || (_R[id].navigation.touch.swipe_direction=="vertical" && direction == "up")) {
						_R[id].sc_indicator_dir = 0;
						_R.callingNewSlide(id,1);
						return false;
					}
					if ((_R[id].navigation.touch.swipe_direction=="horizontal" && direction == "right") || (_R[id].navigation.touch.swipe_direction=="vertical" && direction == "down")) {
						_R[id].sc_indicator_dir = 1;
						_R.callingNewSlide(id,-1);
						return false;
					}
				}
				//if (_R[id].navigation.touch.drag_block_vertical) event.preventDefault();
				return true;
			}
		}
	});

	// REMOVE CAROUSEL ICONS IF NOT NEEDED
	if (_R[id].sliderType==="carousel" && ((_ISM && _R[id].navigation.touch.mobileCarousel==false) || (_ISM!==true && _R[id].navigation.touch.desktopCarousel===false))) _.wrap.addClass("noswipe");
	//NEW iOS Disable Vertical Scroll Class
	if (_R[id].navigation.touch.drag_block_vertical) _R[id].c.addClass("disableVerticalScroll");
};


// NAVIGATION HELPER FUNCTIONS
var hdResets = function(o) {

	o.hide_delay = !jQuery.isNumeric(parseInt(o.hide_delay,0)) ? 0.2 : o.hide_delay;
	o.hide_delay_mobile = !jQuery.isNumeric(parseInt(o.hide_delay_mobile,0)) ? 0.2 : o.hide_delay_mobile;
};

var ckNO = function(s) {
		return s && s.enable;
};


var ct = function(a) {
	clearTimeout(a);
};

var showNavElements = function(id) {
	var nt = _R[id].navigation.maintypes;
	for (var i in nt)
		if (nt.hasOwnProperty(i))
			if (ckNO(_R[id].navigation[nt[i]]) && _R[id].navigation[nt[i]].c!==undefined) {
				ct(_R[id].navigation[nt[i]].showCall);
				_R[id].navigation[nt[i]].showCall = setTimeout(function(a) {
					ct(a.hideCall);
					if  (!(a.hide_onleave && _R[id].tpMouseOver!==true)) {
						if (a.tween===undefined)
							a.tween = showHideNavElements(a);
						else
							a.tween.play();
					}
				},(_R[id].navigation[nt[i]].hide_onleave && _R[id].tpMouseOver!==true ? 0 : parseInt(_R[id].navigation[nt[i]].animDelay)),_R[id].navigation[nt[i]]);
			}

};
var clearAllHidings = function(id) {
	var nt = _R[id].navigation.maintypes;
	for (var i in nt) if (nt.hasOwnProperty(i)) if (_R[id].navigation[nt[i]]!==undefined && _R[id].navigation[nt[i]].hide_onleave && ckNO(_R[id].navigation[nt[i]])) ct(_R[id].navigation[nt[i]].hideCall);
}

var hidaNavElements = function(id,removeClass) {
	var nt = _R[id].navigation.maintypes;
	for (var i in nt)
		if (nt.hasOwnProperty(i))
			if (_R[id].navigation[nt[i]]!==undefined && _R[id].navigation[nt[i]].hide_onleave && ckNO(_R[id].navigation[nt[i]])) {
				ct(_R[id].navigation[nt[i]].hideCall);
				_R[id].navigation[nt[i]].hideCall = setTimeout(function(a) {
					ct(a.showCall);
					if (a.tween) a.tween.reverse();
				},(_ISM ? parseInt(_R[id].navigation[nt[i]].hide_delay_mobile,0) : parseInt(_R[id].navigation[nt[i]].hide_delay,0)),_R[id].navigation[nt[i]]);
			}
};


var showHideNavElements = function(a) {
	a.speed = a.speed===undefined ? 0.5 : a.speed;
	a.anims = [];
	if (a.anim!==undefined && a.left===undefined) a.anims.push(a.anim);
	if (a.left!==undefined) a.anims.push(a.left.anim);
	if (a.right!==undefined) a.anims.push(a.right.anim);


	var tw = tpGS.gsap.timeline()
	tw.add(tpGS.gsap.to(a.c,a.speed, {opacity:1,ease:"power3.inOut"}),0);
	for (var i in a.anims) {
		if (!a.anims.hasOwnProperty(i)) continue;
		switch (a.anims[i]) {
			case "left":tw.add(tpGS.gsap.fromTo(a.c[i],a.speed, {marginLeft:-50},{marginLeft:0,ease:"power3.inOut"}),0);break;
			case "right":tw.add(tpGS.gsap.fromTo(a.c[i],a.speed, {marginLeft:50},{marginLeft:0,ease:"power3.inOut"}),0);break;
			case "top":tw.add(tpGS.gsap.fromTo(a.c[i],a.speed, {marginTop:-50},{marginTop:0,ease:"power3.inOut"}),0);break;
			case "bottom":tw.add(tpGS.gsap.fromTo(a.c[i],a.speed, {marginTop:50},{marginTop:0,ease:"power3.inOut"}),0);break;
			case "zoomin":tw.add(tpGS.gsap.fromTo(a.c[i],a.speed, {scale:0.5},{scale:1,ease:"power3.inOut"}),0);break;
			case "zoomout":tw.add(tpGS.gsap.fromTo(a.c[i],a.speed, {scale:1.2},{scale:1,ease:"power3.inOut"}),0);break;
		}
	}
	tw.play();
	return tw;
};


// ADD ARROWS
var initArrows = function(o,id) {

	// SET oIONAL CLASSES
	o.style = o.style === undefined ? "" : o.style;
	o.left.style = o.left.style === undefined ? "" : o.left.style;
	o.right.style = o.right.style === undefined ? "" : o.right.style;

	// ADD LEFT AND RIGHT ARROWS
	if (o.left.c===undefined) {
		o.left.c = jQuery('<rs-arrow style="opacity:0" class="tp-leftarrow tparrows '+o.style+' '+o.left.style+'">'+o.tmp+'</rs-arrow>');
		_R[id].c.append(o.left.c);;
	}
	if (o.right.c===undefined) {
		o.right.c= jQuery('<rs-arrow style="opacity:0"  class="tp-rightarrow tparrows '+o.style+' '+o.right.style+'">'+o.tmp+'</rs-arrow>');
		_R[id].c.append(o.right.c);
	}

	o[o.rtl ? "left" : "right"].c.click(function() {  if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;_R[id].sc_indicator="arrow"; _R[id].sc_indicator_dir = 0;_R[id].c.revnext();});
	o[o.rtl ? "right" : "left"].c.click(function() {  if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;_R[id].sc_indicator="arrow"; _R[id].sc_indicator_dir = 1;_R[id].c.revprev();});


	// OUTTUER PADDING DEFAULTS
	o.padding_top = parseInt((_R[id].carousel.padding_top||0),0);
	o.padding_bottom = parseInt((_R[id].carousel.padding_bottom||0),0);

	// POSITION OF ARROWS
	setNavElPositions(o.left,id);
	setNavElPositions(o.right,id);

	if (o.position=="outer-left" || o.position=="outer-right") _R[id].outernav = true;
};


// PUT ELEMENTS VERTICAL / HORIZONTAL IN THE RIGHT POSITION
var putVinPosition = function(el,o,id,elh) {
	var elh = elh===undefined ? el.outerHeight(true) : elh,
		oh = _R[id]== undefined ? 0 : _R[id].conh == 0 ? _R[id].height : _R[id].conh,
		by = o.container=="layergrid" ? _R[id].sliderLayout=="fullscreen" ? _R[id].height/2 - (_R[id].gridheight[_R[id].level]*_R[id].bh)/2 : (_R[id].autoHeight || (_R[id].minHeight!=undefined && _R[id].minHeight>0)) ?
						oh/2 - (_R[id].gridheight[_R[id].level]*_R[id].bh)/2
						: 0
			: 0,
		a = o.v_align === "top" ? {top:"0px",y:Math.round(o.v_offset+by)+"px"} : o.v_align === "center" ? {top:"50%",y:Math.round(((0-elh/2)+o.v_offset))+"px"} : {top:"100%",y:Math.round((0-(elh+o.v_offset+by)))+"px"};
	if (!el.hasClass("outer-bottom")) tpGS.gsap.set(el,a);
};

var putHinPosition = function(el,o,id,elw) {
	var elw = elw===undefined ? el.outerWidth(true) : elw,
		bx = o.container==="layergrid" ? _R[id].width/2 - (_R[id].gridwidth[_R[id].level]*_R[id].bw)/2 : 0,
		a = o.h_align === "left" ? 		{left:"0px",	x:Math.round(o.h_offset+bx) + "px"} :
			o.h_align === "center" ? 	{left:"50%",	x:Math.round(((0-elw/2)+o.h_offset)) + "px" } :
										//{right:elw+"px", left:"auto",		x:Math.round((0-(o.h_offset+bx))) + "px" };
										{left:"100%",	x:Math.round((0-(elw+o.h_offset+bx))) + "px" };
	tpGS.gsap.set(el,a);
};

// SET POSITION OF ELEMENTS
var setNavElPositions = function(o,id) {
	if (o==undefined || o.c===undefined) return;
	var ff = (_R[id].sliderLayout=="fullwidth" || _R[id].sliderLayout=="fullscreen"),
		ww = ff ? _R[id].conw : _R[id].topc.width(),
		ow = o.c.outerWidth(true),
		oh = o.c.outerHeight(true);

	if (ow===0 || oh===0) return;

	putVinPosition(o.c,o,id,oh);
	putHinPosition(o.c,o,id,ow);

	if (o.position==="outer-left" && ff)
		tpGS.gsap.set(o.c,{left:(0-ow)+"px",x:o.h_offset+"px"});
	else
	if (o.position==="outer-right" && ff)
		tpGS.gsap.set(o.c,{right:(0-ow)+"px",x:o.h_offset+"px"});


	// MAX WIDTH AND HEIGHT BASED ON THE SOURROUNDING CONTAINER
	if (o.type==="tp-thumb" || o.type==="tp-tab") {

		var	cpt = parseInt((o.padding_top||0),0),
			cpb = parseInt((o.padding_bottom||0),0),
			_el = {},
			_mask = {};

		if (o.maxw>ww && o.position!=="outer-left" && o.position!=="outer-right") {
			_el.left = "0px";
			_el.x = 0;
			_el.maxWidth = (ww-2*o.wpad)+"px";
			_mask.maxWidth = (ww-2*o.wpad)+"px";
		} else {
			_el.maxWidth = o.maxw;
			_mask.maxWidth = ww+"px";
		}


		if (o.maxh+2*o.wpad>_R[id].conh && o.position!=="outer-bottom" && o.position!=="outer-top") {
			_el.top = "0px";
			_el.y = 0;
			_el.maxHeight = (cpt+cpb+(_R[id].conh-2*o.wpad))+"px";
			_mask.maxHeight = (cpt+cpb+(_R[id].conh-2*o.wpad))+"px";
		} else {
			_el.maxHeight = o.maxh+"px";
			_mask.maxHeight = o.maxh+"px";
		}

		o.mask=o.mask===undefined ? o.c.find('rs-navmask') : o.mask;

		// SPAN IS ENABLED
		if (o.span) {
			if (o.container=="layergrid" && o.position!=="outer-left" && o.position!=="outer-right") cpt = cpb = 0;
			if (o.direction==="vertical") {

				_el.maxHeight = (cpt+cpb+(_R[id].conh-2*o.wpad))+"px";
				_el.height = (cpt+cpb+(_R[id].conh-2*o.wpad))+"px";
				_el.top = 0; //(0-cpt);
				_el.y = 0;
				_mask.maxHeight = 	(cpt+cpb+(Math.min(o.maxh,(_R[id].conh-2*o.wpad))))+"px";
				tpGS.gsap.set(o.c,_el);
				tpGS.gsap.set(o.mask,_mask);
				putVinPosition(o.mask,o,id);
			} else
			if (o.direction==="horizontal") {
				_el.maxWidth = "100%";
				_el.width = (ww-2*o.wpad)+"px";
				_el.left = 0;
				_el.x = 0;
				_mask.maxWidth = o.maxw>=ww ? "100%" : (Math.min(o.maxw,ww))+"px";
				tpGS.gsap.set(o.c,_el);
				tpGS.gsap.set(o.mask,_mask);
				putHinPosition(o.mask,o,id);
			}
		} else {
			tpGS.gsap.set(o.c,_el);
			tpGS.gsap.set(o.mask,_mask);
		}
	}
};

// ADD A BULLET
var addBullet = function(container,o,li,id) {
	// Check if Bullet exists already ?
	if (container.find('.tp-bullets').length===0) {
		o.style = o.style === undefined ? "" : o.style;
		o.c = jQuery('<rs-bullets style="opacity:0"  class="tp-bullets '+o.style+' '+o.direction+' nav-pos-hor-'+o.h_align+'nav-pos-ver-'+o.v_align+'nav-dir-'+o.direction+'"></rs-bullets>');
	}
	// Add Bullet Structure to the Bullet Container
	var linkto = li.data('key'),
			inst = o.tmp;

	if (_R[id].thumbs[li.index()]!==undefined) jQuery.each(_R[id].thumbs[li.index()].params,function(i,obj) { inst = inst.replace(obj.from,obj.to);});
	var b = jQuery('<rs-bullet data-key="'+linkto+'" class="tp-bullet">'+inst+'</rs-bullet>');
	if (_R[id].thumbs[li.index()]!==undefined) b.find('.tp-bullet-image').css({backgroundImage:'url('+_R[id].thumbs[li.index()].src+')'});

	o.c.append(b);
	container.append(o.c);

	// SET BULLET SPACES AND POSITION
	var am = o.c.find('.tp-bullet').length,
		bOW = b.outerWidth(),
		bOH = b.outerHeight(),
		w = bOW+parseInt((o.space===undefined? 0:o.space),0),
		h = bOH+parseInt((o.space===undefined? 0:o.space),0);

	if (o.direction==="vertical") {
		b.css({top:((am-1)*h)+"px", left:"0px"});
		o.c.css({height:(((am-1)*h) + bOH),width:bOW});
	}
	else {
		b.css({left:((am-1)*w)+"px", top:"0px"});
		o.c.css({width:(((am-1)*w) + bOW),height:bOH});
	}

	// SET LINK TO AND LISTEN TO CLICK
	b.click(function() {
		if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;
		_R[id].sc_indicator="bullet";
		container.revcallslidewithid(linkto);
		container.find('.tp-bullet').removeClass("selected");
		jQuery(this).addClass("selected");
	});

	// OUTTUER PADDING DEFAULTS
	o.padding_top = parseInt((_R[id].carousel.padding_top||0),0);
	o.padding_bottom = parseInt((_R[id].carousel.padding_bottom||0),0);

	if (o.position=="outer-left" || o.position=="outer-right") _R[id].outernav = true;
};


// ADD THUMBNAILS
var addThumb = function(container,o,li,what,id) {


	var thumbs = what==="tp-thumb" ? ".tp-thumbs" : ".tp-tabs",
		thumbmask = what==="tp-thumb" ? ".tp-thumb-mask" : ".tp-tab-mask",
		thumbsiw = what==="tp-thumb" ? ".tp-thumbs-inner-wrapper" : ".tp-tabs-inner-wrapper",
		thumb = what==="tp-thumb" ? ".tp-thumb" : ".tp-tab",
		timg = what ==="tp-thumb" ? ".tp-thumb-image" : ".tp-tab-image",
		tag = what==="tp-thumb" ? 'rs-thumb' : 'rs-tab';
	o.type = what;
	o.visibleAmount = o.visibleAmount>_R[id].slideamount ? _R[id].slideamount : o.visibleAmount;
	o.sliderLayout = _R[id].sliderLayout;

	// Check if THUNBS/TABS exists already ?
	if (o.c===undefined) {
		o.wpad = o.wrapper_padding;
		o.c = jQuery('<'+tag+'s style="opacity:0" class="nav-dir-'+o.direction+' nav-pos-ver-'+o.v_align+' nav-pos-hor-'+o.h_align+' rs-nav-element '+what+'s '+(o.span===true ? "tp-span-wrapper" : "")+" "+o.position+" "+(o.style === undefined ? "" : o.style)+'"><rs-navmask class="'+what+'-mask" style="overflow:hidden;position:relative"><'+tag+'s-wrap class="'+what+'s-inner-wrapper" style="position:relative;"></'+tag+'s-wrap></rs-navmask></'+tag+'s>');
		o.c.css({overflow:"visible",position:(o.position === "outer-top" || o.position==="outer-bottom" ? "relative" : "absolute"),background:o.wrapper_color,padding:o.wpad+"px",boxSizing:"contet-box"});
		if (o.position==="outer-top") container.parent().prepend(o.c);
		else if (o.position==="outer-bottom") container.after(o.c);
		else container.append(o.c);

		if (o.position==="outer-left" || o.position==="outer-right") tpGS.gsap.set(_R[id].c,{overflow:"visible"});

		// OUTTUER PADDING DEFAULTS
		o.padding_top = parseInt((_R[id].carousel.padding_top||0),0);
		o.padding_bottom = parseInt((_R[id].carousel.padding_bottom||0),0);

		if (o.position=="outer-left" || o.position=="outer-right") _R[id].outernav = true;
	}

	// Add Thumb/TAB Structure to the THUMB/TAB Container
	var linkto = li.data('key'),
		tm = o.c.find(thumbmask),
		tw = tm.find(thumbsiw),
		inst = o.tmp;

	o.maxw = o.direction==="horizontal" ? (o.width * o.visibleAmount) + (o.space*(o.visibleAmount-1)) : o.width;
	o.maxh = o.direction==="horizontal" ? o.height : (o.height * o.visibleAmount) + (o.space*(o.visibleAmount-1));


	if (_R[id].thumbs[li.index()] !== undefined) jQuery.each(_R[id].thumbs[li.index()].params,function(i,obj) { inst = inst.replace(obj.from,obj.to);});

	var b = jQuery('<'+tag+' data-liindex="'+li.index()+'" data-key="'+linkto+'" class="'+what+'" style="width:'+o.width+'px;height:'+o.height+'px;">'+inst+'<'+tag+'>');
	// FILL CONTENT INTO THE TAB / THUMBNAIL
	if (_R[id].thumbs[li.index()]!==undefined)	b.find(timg).css({backgroundImage:"url("+_R[id].thumbs[li.index()].src+")"});
	tw.append(b);

	// SET BULLET SPACES AND POSITION
	var am = o.c.find(thumb).length,
		bow = b.outerWidth(),
		boh = b.outerHeight(),
		w = bow+parseInt((o.space===undefined? 0:o.space),0),
		h = boh+parseInt((o.space===undefined? 0:o.space),0);

	if (o.direction==="vertical") {
		b.css({top:((am-1)*h)+"px", left:"0px"});
		tw.css({height:(((am-1)*h) + boh),width:bow});
	}
	else {
		b.css({left:((am-1)*w)+"px", top:"0px"});
		tw.css({width:(((am-1)*w) + bow),height:boh});
	}


	tm.css({maxWidth:o.maxw+"px",maxHeight:o.maxh+"px"});
	o.c.css({maxWidth:(o.maxw)+"px", maxHeight:o.maxh+"px"});


	// SET LINK TO AND LISTEN TO CLICK
	b.click(function() {
		_R[id].sc_indicator="bullet";
		if (_R[id].sliderType==="carousel") _R[id].ctNavElement=true;
		var dis = container.parent().find(thumbsiw).data('distance');
		dis = dis === undefined ? 0 : dis;
		if (Math.abs(dis)<10) {
			container.revcallslidewithid(linkto);
			container.parent().find(thumbs).removeClass("selected");
			jQuery(this).addClass("selected");
		}
	});
};

var setONHeights = function(id) {
	var ot = _R[id].cpar.find('.outer-top'),
		ob = _R[id].cpar.find('.outer-bottom'),
		cachea = _R[id].top_outer,
		cacheb = _R[id].bottom_outer;
	_R[id].top_outer = !ot.hasClass("tp-forcenotvisible") ? ot.outerHeight() || 0 : 0;
	_R[id].bottom_outer = !ob.hasClass("tp-forcenotvisible") ? ob.outerHeight() || 0 : 0;
	if (cachea!==undefined && cachea!==_R[id].top_outer || cacheb!==undefined && cacheb!==_R[id].bottom_outer) _R.setSize(id);
};


// HIDE NAVIGATION ON PURPOSE
var biggerNav = function(el,id,chng) {
	if (el==undefined || el.c===undefined) return chng;
	if (el.hide_under>_R[id].conw || _R[id].conw>el.hide_over) {
		if (el.tpForceNotVisible!==true) { el.c.addClass("tp-forcenotvisible");	chng=true;}
		el.tpForceNotVisible = true;
	} else {
		if (el.tpForceNotVisible!==false) {el.c.removeClass("tp-forcenotvisible");	chng=true;}
		el.tpForceNotVisible = false;
	}
	return chng;
}
})(jQuery);