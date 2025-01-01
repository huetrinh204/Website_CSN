var bus = new Vue();
// boot up the App
var demo = new Vue({
	el: '#ccomment',
	store: store,
	data: {
		comments: store.state.comments,
		pagination: store.state.pagination,
		paginationOptions: {}
	},

	created: function () {
		var self = this;
		this.$store.dispatch('fetchComments', this.getQueryHashParam())
			.then(function(){

				if (location.hash.indexOf('#ccomment-comment=') === 0) {
					var comment = location.hash.replace('#ccomment-comment=', '');

					// We have to wait for the comments to be rendered in the dom
					self.$nextTick(function () {
						jQuery('html, body').animate({
							scrollTop: jQuery('#ccomment-comment-' + comment).offset().top
						}, 200);
					})				}
			});
	},

	components: {
		'pagination': pagination
	},

	methods: {
		newComment: function() {
			console.log('capture new comment in parent');
		},
		loadData: function () {
			this.$store.dispatch('fetchComments', {start: this.pagination.current_page});
			location.hash = '#ccomment-page=' + this.pagination.current_page;

			jQuery('html, body').animate({
				scrollTop: jQuery(".ccomment").offset().top
			}, 200);
		},

		getQueryHashParam: function() {
			if (location.hash.indexOf('#ccomment-page=') === 0) {
				return {start: location.hash.replace('#ccomment-page=', '')};
			}

			if (location.hash.indexOf('#ccomment-comment=') === 0) {
				return {comment: location.hash.replace('#ccomment-comment=', '')};
			}
		},
	},

});
