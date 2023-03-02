"use strict";

(function () {
  // Multi level menu dropdown
  if ($(".dropdown-menu a.dropdown-toggle").length) {
    $(".dropdown-menu a.dropdown-toggle").on("click", function (e) {
      if (!$(this).next().hasClass("show")) {
        $(this)
          .parents(".dropdown-menu")
          .first()
          .find(".show")
          .removeClass("show");
      }
      var $subMenu = $(this).next(".dropdown-menu");
      $subMenu.toggleClass("show");

      $(this)
        .parents("li.nav-item.dropdown.show")
        .on("hidden.bs.dropdown", function (e) {
          $(".dropdown-submenu .show").removeClass("show");
        });

      return false;
    });
  }

  // Default Tooltip
  if ($('[data-bs-toggle="tooltip"]').length) {
    var tooltipTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // Default Popover
  if ($('[data-bs-toggle="popover"]').length) {
    var popoverTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="popover"]')
    );
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  }

  // Sidenav fixed for docs
  if ($(".sidebar-nav-fixed a").length) {
    $(".sidebar-nav-fixed a")
      // Remove links that don't actually link to anything
      .on("click", function (event) {
        // On-page links
        if (
          location.pathname.replace(/^\//, "") ==
            this.pathname.replace(/^\//, "") &&
          location.hostname == this.hostname
        ) {
          // Figure out element to scroll to
          var target = $(this.hash);
          target = target.length
            ? target
            : $("[name=" + this.hash.slice(1) + "]");
          // Does a scroll target exist?
          if (target.length) {
            // Only prevent default if animation is actually gonna happen
            event.preventDefault();
            $("html, body").animate(
              {
                scrollTop: target.offset().top - 90,
              },
              1000,
              function () {
                // Callback after animation
                // Must change focus!
                var $target = $(target);
                $target.focus();
                if ($target.is(":focus")) {
                  // Checking if the target was focused
                  return false;
                } else {
                  $target.attr("tabindex", "-1"); // Adding tabindex for elements not focusable
                  $target.focus(); // Set focus again
                }
              }
            );
          }
        }
        $(".sidebar-nav-fixed a").each(function () {
          $(this).removeClass("active");
        });
        $(this).addClass("active");
      });
  }

  //  Stopevent for dropdown
  if ($(".stopevent").length) {
    $(document).on("click.bs.collapse.data-api", ".stopevent", function (e) {
      e.stopPropagation();
    });
  }

  // Slick Slider
  if ($(".team-slider").length) {
    $(".team-slider").slick({
      infinite: true,
      slidesToShow: 5,
      slidesToScroll: 1,
      autoplay: true,
      dots: false,
      arrows: true,
      prevArrow:
        '<span class="slick-prev"><i class="feather-icon icon-chevron-left"></i></span>',
      nextArrow:
        '<span class="slick-next "><i class="feather-icon icon-chevron-right "></i></span>',
      responsive: [
        {
          breakpoint: 1024,
          settings: {
            slidesToShow: 3,
            slidesToScroll: 3,
          },
        },
        {
          breakpoint: 820,
          settings: {
            slidesToShow: 2,
            slidesToScroll: 2,
          },
        },
        {
          breakpoint: 480,
          settings: {
            slidesToShow: 1,
            slidesToScroll: 1,
          },
        },
        // You can unslick at a given breakpoint now by adding:
        // settings: "unslick"
        // instead of a settings object
      ],
    });
  }
})();

// Zoom js
function zoom(e) {
  var zoomer = e.currentTarget;
  e.offsetX ? (offsetX = e.offsetX) : (offsetX = e.touches[0].pageX);
  e.offsetY ? (offsetY = e.offsetY) : (offsetX = e.touches[0].pageX);
  x = (offsetX / zoomer.offsetWidth) * 100;
  y = (offsetY / zoomer.offsetHeight) * 100;
  zoomer.style.backgroundPosition = x + "% " + y + "%";
}
