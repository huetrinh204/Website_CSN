var TF_Justified_Gallery=function(){function t(){this.init(),this.initEvents()}var e=t.prototype;return e.init=function(){var e=this;this.getGalleries().forEach(function(t){e.initGallery(t)})},e.initGallery=function(t){var e={itemSelector:".item:not(.tf-gallery-is-hidden)",transitionDuration:0,gutter:parseInt(window.getComputedStyle(t).gap)};t.parentElement.dataset.itemHeight&&(e.rowHeight=t.parentElement.dataset.itemHeight),fjGallery(t,e)},e.initEvents=function(){var e=this;document.addEventListener("click",function(t){e.onJustifiedFilteringTagsSelection(t)}),window.addEventListener("resize",function(t){e.updateGalleriesGap()},!0)},e.onJustifiedFilteringTagsSelection=function(t){var t=t.target.closest(".tf-gallery-tags--item");t&&(t=t.closest(".nrf-widget.tf-gallery-wrapper"))&&(t=t.querySelector(".gallery-items.justified"))&&(t.fjGallery.destroy(),this.initGallery(t))},e.updateGalleriesGap=function(){this.getGalleries().forEach(function(t){t.fjGallery&&t.fjGallery.updateOptions({gutter:parseInt(window.getComputedStyle(t).gap)})})},e.getGalleries=function(){return document.querySelectorAll(".gallery-items.justified")},t}();document.addEventListener("DOMContentLoaded",function(){new TF_Justified_Gallery});
