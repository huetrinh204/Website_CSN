function _inheritsLoose(t,e){t.prototype=Object.create(e.prototype),_setPrototypeOf(t.prototype.constructor=t,e)}function _setPrototypeOf(t,e){return(_setPrototypeOf=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(t,e){return t.__proto__=e,t})(t,e)}var TF_Vimeo_Video=function(e){function t(t){t=e.call(this,t)||this;return t.player=null,t}_inheritsLoose(t,e);var i=t.prototype;return i.init=function(){this.maybeLoadVimeoAPI()},i.pause=function(){this.player&&this.player.pause()},i.vimeoApiLoaded=function(){return window.Vimeo&&window.Vimeo.Player&&void 0!==window.Vimeo.Player},i.maybeLoadVimeoAPI=function(){function i(t){o.vimeoApiLoaded()?o.initVimeoVideo():setTimeout(function(){return i(t)},350)}var t,e,o=this;document.querySelector(".tf-vimeo-api-script")||this.vimeoApiLoaded()||((t=document.createElement("script")).className="tf-vimeo-api-script",t.src="https://player.vimeo.com/api/player.js",(e=document.getElementsByTagName("script")[0]).parentNode.insertBefore(t,e));return new Promise(function(t,e){i(t)})},i.initVimeoVideo=function(){var e=this,i=this,o=(this.player=new window.Vimeo.Player(this.videoElement,{id:this.dataset.videoId,loop:this.setAttributeBool(this.dataset,"videoLoop"),autoplay:this.setAttributeBool(this.dataset,"videoAutoplay"),controls:this.setAttributeBool(this.dataset,"videoControls"),title:this.setAttributeBool(this.dataset,"videoTitle"),byline:this.setAttributeBool(this.dataset,"videoByline"),portrait:this.setAttributeBool(this.dataset,"videoPortrait"),color:this.dataset.videoColor.substring(1),autopause:!1,muted:this.setAttributeBool(this.dataset,"videoMute"),dnt:this.setAttributeBool(this.dataset,"videoPrivacy"),keyboard:this.setAttributeBool(this.dataset,"videoKeyboard"),pip:this.setAttributeBool(this.dataset,"videoPip")}),this.dataset.videoStart&&this.player.setCurrentTime(this.dataset.videoStart),parseInt(this.dataset.videoStart)),s=(0<o&&this.player.on("play",function(t){0===parseInt(t.seconds)&&this.player.setCurrentTime(o)}),function(t){0<parseInt(e.dataset.videoEnd)&&parseInt(t.seconds)>=parseInt(e.dataset.videoEnd)&&(e.pause(),e.dataset.videoEnd=0)});0<parseInt(this.dataset.videoEnd)&&(this.player.on("timeupdate",s),this.player.on("seeked",function(t){parseInt(t.seconds)<1||i.player.off("timeupdate",s)})),this.player.on("loaded",function(){"true"!==i.video.dataset.readonly&&"true"!==i.video.dataset.disabled&&("true"===i.dataset.videoAutoplay&&i.overlay?i.video.classList.add("hiddenOverlay"):"false"===i.dataset.videoAutoplay&&i.overlay&&(i.player.play(),i.player.on("play",function(){i.video.classList.add("hiddenOverlay")})))})},t}(TF_Video);
