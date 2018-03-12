'use strict';

var app = function () {
	var body = undefined;
	var menu = undefined;
	var menuItems = undefined;

	var init = function init() {
		body = document.querySelector('body');
		menu = document.querySelector('.menu-icon');
		menuItems = document.querySelectorAll('.nav__list-item');

		applyListeners();
	};

	var applyListeners = function applyListeners() {
		menu.addEventListener('click', function () {
			return toggleClass(body, 'nav-active');
		});


		menuItems.forEach(function(element){
			element.addEventListener('click', function () {
				body.classList.remove('nav-active');
				$(".site-content").children().fadeOut().removeClass('isActive');
				switch($(this).data("item")){
					case "H":
						$(".home").fadeIn("slow").addClass('isActive');
						break;
					case "R":
						if(!$(".reports").hasClass('loggued')){
							document.getElementById('id01').style.display='block';
						}else{
							$(".reports").fadeIn("slow").addClass('isActive');
						}
						break;
					case "E":
						$(".evidencias").fadeIn("slow").addClass('isActive');
						break;
					case "X":
						$(".reports").removeClass('loggued');
						$(".home").fadeIn("slow").addClass('isActive');
						break;
				}
			});
		});
	};

	var toggleClass = function toggleClass(element, stringClass) {
		if (element.classList.contains(stringClass)) element.classList.remove(stringClass);else element.classList.add(stringClass);
	};

	init();
}();