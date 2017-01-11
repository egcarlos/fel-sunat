$(document).ready(function(){

	// $('nav-internacional').on('focus', 'a', function(event) {
	// 	console.log('llego');
	// 	event.preventDefault();
	// 	$(this).trigger('click');
	// });
	setTimeout( function(){

		var hash = window.location.hash;

		if (hash != '') {
			$(hash).trigger('click');
			$('body, html').animate({
				scrollTop: 0},
				600, function() {
				/* stuff to do after animation is complete */
			});
		};

	},0);
	

	// $('.w-item-construc').mouseenter(function() {
	// 	$(this).find('.w-img img').animate({
	// 		marginTop: '0px',
	// 	}, {queue:false, duration: 200});
	// 	$(this).find('.w-texto').animate({
	// 		opacity: 0
	// 		}, {queue:false, duration: 200,  complete: function() {
	// 			$(this).parent().find('.w-texto-interno').animate({
	// 				top: '40%'
	// 				}, {queue:false, duration: 100});
	// 			$(this).parent().find('.linea-izquierda').animate({
	// 				width: '95%',
	// 				}, {queue:false, duration: 200});
	// 			$(this).parent().find('.linea-top').animate({
	// 				height: '180px',
	// 				}, {queue:false, duration: 200});
	// 			$(this).parent().find('.w-texto-2').animate({
	// 				opacity: 1,

	// 				}, {queue:false, duration: 100});
				
	// 			$('.w-texto-interno p').animate({
	// 				opacity: 1,
	// 				}, {queue:false, duration: 100});
	// 	}});
	// });
	// $('.w-item-construc').mouseleave(function(event) {
	// 	$(this).find('.w-img img').animate({
	// 		marginTop: '-10px',
	// 	}, {queue:false, duration: 200});
	// 	$(this).find('.w-texto-2').animate({
	// 		opacity: 0
	// 		}, {queue:false, duration: 200,  complete: function() {
	// 			$('.w-texto-interno').animate({
	// 				top: '35%'
	// 			}, {queue:false, duration: 100});
	// 			$('.w-texto-interno p').animate({
	// 				opacity: 0,
	// 				}, {queue:false, duration: 100});
	// 			$(this).parent().parent().find('.linea-izquierda').animate({
	// 				width: '0px',
	// 				}, {queue:false, duration: 200});
	// 			$(this).parent().parent().find('.linea-top').animate({
	// 				height: '0px',
	// 				}, {queue:false, duration: 200});
	// 			$(this).parent().find('.w-texto').animate({
	// 				opacity: 1,

	// 				}, {queue:false, duration: 100});
				
	// 	}});
	// });
	   var doit;

            var $img = $(".cnt-construccion .construccion .w-item-construc .w-img img").first();

                $img.on('load', function(){
                    var alto_img = $img.height();
                      var alto_negativo = 11;
    		    var alto_ok = alto_img - alto_negativo;
    		    $('.w-item-construc').height(alto_ok);
                });

            function resizedw(){
                var $img = $(".cnt-construccion .construccion .w-item-construc .w-img img").first();
                    var alto_img = $img.height();
                      var alto_negativo = 10;
    		    var alto_ok = alto_img - alto_negativo;
    		    $('.w-item-construc').height(alto_ok);

             }



        /*
	function alto_cnt_img (){
		setTimeout( function(){
			var alto_img = $('.cnt-construccion .construccion .w-item-construc .w-img img').height();
		var alto_negativo = 10;
		var alto_ok = alto_img - alto_negativo;
		$('.w-item-construc').height(alto_ok);

		},0);

		
	}*/
 	$(window).resize(function(event) {
		//alto_cnt_img();
		
		clearTimeout(doit);
                    doit = setTimeout(function() {
                        resizedw();
                    }, 10);
	});
 	$(window).trigger('resize');
            //or
            //handler.apply(window, [' On']);

	$('.w-item-construc').mouseenter(function() {
		$(this).find('.w-img img').animate({
			marginTop: '0px',
		}, {queue:false, duration: 200});
		$(this).find('.w-texto').animate({
			opacity: 0
			}, {queue:false, duration: 200});
		$(this).find('.w-texto-interno').animate({
				top: '40%'
			}, {queue:false, duration: 100});
		$(this).find('.linea-izquierda').animate({
			width: '96%',
			}, {queue:false, duration: 200});
		$(this).find('.linea-top').animate({
			height: '94%',
			}, {queue:false, duration: 200});
		$(this).find('.w-texto-2').animate({
			opacity: 1,

			}, {queue:false, duration: 100});
		
		$('.w-texto-interno p').animate({
			opacity: 1,
			}, {queue:false, duration: 100});
	});
	
	$('.w-item-construc').mouseleave(function(event) {
		$(this).find('.w-img img').animate({
			marginTop: '-10px',
		}, {queue:false, duration: 200});
		$(this).find('.w-texto-2').animate({
			opacity: 0
			}, {queue:false, duration: 200});
		
		$(this).find('.w-texto-interno p').animate({
			opacity: 0,
			}, {queue:false, duration: 100});
		$(this).find('.w-texto-interno').animate({
			top: '35%'
		}, {queue:false, duration: 100});
		$(this).find('.linea-izquierda').animate({
			width: '0px',
			}, {queue:false, duration: 200});
		$(this).find('.linea-top').animate({
			height: '0px',
			}, {queue:false, duration: 200});
		$(this).find('.w-texto').animate({
			opacity: 1,
			}, {queue:false, duration: 100});
	});
	

	/*setTimeout( function(){
		alto_cnt_img();

	},0);*/
	
});

// $(window).load(function(){
// 	$(window).resize();
// });