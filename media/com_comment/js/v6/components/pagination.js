'use strict'

var pagination = Vue.extend({
  template: '#ccomment-pagination',
  props: {
    pagination: {
      type: Object,
      required: true
    },
    callback: {
      type: Function,
      required: true
    },
    options: {
      type: Object
    },
  },
  computed: {
    array: function array () {
      if (this.pagination.last_page <= 0) {
        return []
      }

      var from = this.pagination.current_page - this.pagination.offset
      if (from < 1) {
        from = 1
      }

      var to = from + this.pagination.to
      if (to >= this.pagination.last_page) {
        to = this.pagination.last_page
      }

      var arr = []
      while (from <= to) {
        arr.push(from)
        from++
      }

      return arr
    },
    config: function config () {
      return Object.assign({
        offset: 3,
        alwaysShowPrevNext: true
      }, this.options)
    }
  },
  methods: {
    showPrevious: function showPrevious () {
      return this.config.alwaysShowPrevNext || this.pagination.current_page > 1
    },
    showNext: function showNext () {
      return this.config.alwaysShowPrevNext || this.pagination.current_page < this.pagination.last_page
    },
    changePage: function changePage (page) {
      if (this.pagination.current_page === page) {
        return
      }

      this.$set(this.pagination, 'current_page', page)
      this.callback()
    }
  }
})