/*
JS for Campaign Pro admin pages

@package Campaign_Pro
@since 1.0
@version 1.0

Campaign Pro (C) 2017-2018 GoingBold, Ltd.
*/

let scrollPosition = 0; //https://stackoverflow.com/a/45230674/6561019

// Variable that's used for the edit screen sidebar
var timer = null, 
	interval = 1500;

// Function that's used for the edit screen sidebar
function myTimer() {
	jQuery('.clicked .acf-fc-layout-handle').click();
	jQuery('.clicked').removeClass('-collapsed');
}

// this targets the div layout that's added by either clicking the 'Add Block' button or the '+' icon (between blocks)
function colourBtnFix(){
	setTimeout(function(){
		jQuery('.wp-picker-container.wp-picker-active').toggleClass('keep-darn-thing-open');
	}, 10);
}

// this targets the div layout that's added by either clicking the 'Add Block' button or the '+' icon (between blocks)
function goDoSomething(identifier) {

	// only do this if screen size is more than 768px as we're using a full screen overlay on mobile
	// so content only has to update when overlay is opened or closed
	if($(window).innerWidth() > 768) {

			setTimeout(function(){
				$(".values > .layout:not(.-collapsed)[data-layout="+$(identifier).data('layout')+"]").addClass('clicked').prepend( '<a id="handle-overlay">Click Here to Close Sidebar</a>' );
				$(".values > .layout:not(.-collapsed)[data-layout="+$(identifier).data('layout')+"] > .acf-fc-layout-handle:not(.clicking,.click-click)").addClass('clicking click-click');
				$('.wp-color-result').attr('onClick', 'colourBtnFix()');
				timer = setInterval(function () {
					myTimer()
				}, interval);
			}, 200);

		// do this on screen size of 768px and below
		} else {

			window.scrollTo(0, scrollPosition); //https://stackoverflow.com/a/45230674/6561019
			const mainEl = document.querySelector('body'); //https://stackoverflow.com/a/45230674/6561019
			mainEl.style.top = 0; //https://stackoverflow.com/a/45230674/6561019
			$('body').addClass('wp-blocks-open');

		}

}

jQuery(function($) {

	// Close all flexible content on edit screen load
	$('.layout').not('.clones .layout').addClass('-collapsed');

	// Comment goes here
	$('.acf-flexible-content').on('click', '.layout.-collapsed > .acf-fc-layout-handle:not(.clicking,.click-click)', function () {

		// only do this if screen size is more than 768px as we're using a full screen overlay on mobile
		// so content only has to update when overlay is opened or closed
		if($(window).innerWidth() > 768) {

			$(this).addClass('clicking click-click');
			$(this).closest('.layout').addClass('clicked');
			$(this).parent().prepend( '<a id="handle-overlay">Click Here to Close Sidebar</a>' );
			timer = setInterval(function () {
				myTimer()
			}, interval);

		// do this on screen size of 768px and below
		} else {

			scrollPosition = window.pageYOffset; //https://stackoverflow.com/a/45230674/6561019
			const mainEl = document.querySelector('body'); //https://stackoverflow.com/a/45230674/6561019
			mainEl.style.top = -scrollPosition + 'px'; //https://stackoverflow.com/a/45230674/6561019
			$('body').addClass('wp-blocks-open');

		}

	});

	// Comment goes here
	$('.acf-flexible-content').on('click', '.layout.-collapsed .acf-icon.-collapse', function () {

		// only do this if screen size is more than 768px as we're using a full screen overlay on mobile
		// so content only has to update when overlay is opened or closed
		if($(window).innerWidth() > 768) {

			$(this).parent().prev('clicking click-click');
			$(this).closest('.layout').addClass('clicked').prepend( '<a id="handle-overlay">Click Here to Close Sidebar</a>' );
			timer = setInterval(function () {
				myTimer()
			}, interval);

		// do this on screen size of 768px and below
		} else {

			scrollPosition = window.pageYOffset; //https://stackoverflow.com/a/45230674/6561019
			const mainEl = document.querySelector('body'); //https://stackoverflow.com/a/45230674/6561019
			mainEl.style.top = -scrollPosition + 'px'; //https://stackoverflow.com/a/45230674/6561019
			$('body').addClass('wp-blocks-open');

		}

	});

	// Comment goes here
	$('.acf-flexible-content').on('click', '.layout:not(.-collapsed) .acf-icon.-collapse', function () {

		// only do this if screen size is more than 768px as we're using a full screen overlay on mobile
		// so content only has to update when overlay is opened or closed
		if($(window).innerWidth() > 768) {

			clearInterval(timer);
			timer = null
			$(this).closest('.layout').removeClass('clicked');	
			$(this).parent().prev().removeClass('clicking click-click');
			$('#handle-overlay').remove();

		// do this on screen size of 768px and below
		} else {

			window.scrollTo(0, scrollPosition); //https://stackoverflow.com/a/45230674/6561019
			const mainEl = document.querySelector('body'); //https://stackoverflow.com/a/45230674/6561019
			mainEl.style.top = 0; //https://stackoverflow.com/a/45230674/6561019
			$('body').removeClass('wp-blocks-open');

		}

	});

	$('.wp-color-result').attr('onClick', 'colourBtnFix()');

	// Comment goes here
	$('#poststuff').on('click', '#handle-overlay', function () {

		// only do this if screen size is more than 768px as we're using a full screen overlay on mobile
		// so content only has to update when overlay is opened or closed
		if($(window).innerWidth() > 768) {

			clearInterval(timer);
			timer = null
			$(this).siblings('.acf-fc-layout-handle').removeClass('clicking click-click');
			$(this).parent().removeClass('clicked');
			$(this).parent().addClass('-collapsed');
			$(this).remove();

		// do this on screen size of 768px and below
		} else {

			window.scrollTo(0, scrollPosition); //https://stackoverflow.com/a/45230674/6561019
			const mainEl = document.querySelector('body'); //https://stackoverflow.com/a/45230674/6561019
			mainEl.style.top = 0; //https://stackoverflow.com/a/45230674/6561019
			$('body').removeClass('wp-blocks-open');

		}

	});

	// Get the post title value and use it in the post title display in the header (so the post title is part of the WYSIWYG experience)
	$('h1.entry-title').text($('input#title').val());

	// 1.2 Listen out for keypress, keyup and focus on 'input#title' and copy it to post title display in the header (so the post title is part of the WYSIWYG experience)
	$('input#title').bind('keypress keyup blur', function() {
		// 1.2.1 Copy input value to all inputs 
		$('h1.entry-title').text($(this).val());
	});

	// Comment goes here
	$(document).on('click', '.acf-actions a, .acf-icon.-plus', function () {
		$('.acf-fc-popup a').attr('onClick', 'goDoSomething(this)');
		setTimeout(function(){
			$('.wp-color-result').attr('onClick', 'colourBtnFix()');
		}, 10);
	});
});