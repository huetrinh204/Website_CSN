var store = new Vuex.Store({
  state: {
    comments: [],
    commentsById: {},
    user: {
      name: '',
      username: '',
      email: '',
      notify: 1,
      loggedin: window.compojoom.ccomment.user.loggedin,
      avatar: window.compojoom.ccomment.user.avatar
    },
    pagination: {
      total: 0,
      total_with_children: 0,
      offset: window.compojoom.ccomment.config.comments_per_page,
      current_page: 1, // required
      last_page: 0,    // required
      from: 1,
      to: 9,           // required
    },
    editComment: {},
    quoteComment: {},
    itemConfig: window.compojoom.ccomment.item,
    userConfig: window.compojoom.ccomment.user,
    config: window.compojoom.ccomment.config,
    token: jQuery('#ccomment-token').find('input').attr('name'),
    activeForm: null
  },

  mutations: {
    activeForm: function (state, form) {
      Vue.set(state, 'activeForm', form)
    },
    UPDATE_COMMENTS: function (state, comments) {
      state.comments.splice(0)
      comments.forEach(function (comment) {
        state.commentsById[comment.id] = comment
        state.comments.push(comment)
      })
    },
    UPDATE_PAGINATION: function (state, info) {
      Vue.set(state.pagination, 'total', info.countParents)
      Vue.set(state.pagination, 'total_with_children', info.total)
      Vue.set(state.pagination, 'current_page', info.page)
      // If we don't have offset, then the user doesn't use pagination
      Vue.set(state.pagination, 'last_page', state.pagination.offset ? Math.ceil(info.countParents / state.pagination.offset) : 0)
    },
    editComment: function (state, comment) {
      Vue.set(state, 'editComment', comment)
    },
    quoteComment: function (state, comment) {
      Vue.set(state, 'quoteComment', comment)
    },
    addComment: function (state, comment) {
      if (comment.parentid !== -1) {
        state.commentsById[comment.parentid].children.push(comment.id)
      }

      state.commentsById[comment.id] = comment

      // When sorting by newest first, put new comments on top
      if (state.config.sort === 1) {
        state.comments.unshift(comment)
        return
      }

      state.comments.push(comment)
    },
    removeComment: function (state, id) {
      var index = state.comments.findIndex(function (element) {
        if (element.id === id) {
          return true
        }
      })

      // Remove it from the byId object
      delete state.commentsById[id]

      // Remove it from the array
      if (index > -1) {
        state.comments.splice(index, 1)
      }
    },
    updateComment: function (state, comment) {
      var id = comment.id

      Object.keys(comment).forEach(function (value) {
        Vue.set(state.commentsById[id], value, comment[value])
      })
    },
    updateDefaultName: function (state, name) {
      if (!state.loggedin) {
        Cookies.set('compojoom.ccomment.user.name', name)
        if (state.config.use_name) {
          state.user.name = name
        } else {
          state.user.username = name
        }
      }
    },
    updateUserEmail: function (state, email) {
      // if (email) {
      Cookies.set('compojoom.ccomment.user.email', email)
      state.user.email = email
      // }
    }
  },
  getters: {
    getName: function (state) {
      var name = state.config.use_name ? state.user.name : state.user.username

      if (!name) {
        name = Cookies.get('compojoom.ccomment.user.name')
      }
      // remove the saved cookie if we are logged in
      if (state.loggedin) {
        Cookies.remove('compojoom.ccomment.user.name')
      }
      return name
    },
    getDefaultName: function (state, getters) {
      var name = getters.getName
      if (!name) {
        name = Joomla.JText._('COM_COMMENT_ANONYMOUS', 'Anonymous')
      }

      return name
    },

    getAvatar: function (state, getters) {
      var avatar = state.user.avatar,
        email = getters.getEmail

      if (!state.loggedin) {
        if (state.config.gravatar) {
          if (email) {
            avatar = 'https://www.gravatar.com/avatar/' + md5(email)
          }
        }
      }

      return avatar
    },

    getEmail: function (state) {
      var email = state.user.email

      if (!email) {
        email = Cookies.get('compojoom.ccomment.user.email')
      }
      // remove the saved cookie if we are logged in
      if (state.user.loggedin) {
        Cookies.remove('compojoom.ccomment.user.email')
      }
      return email
    }
  },
  actions: {
    fetchComments: function (props, queryParams) {
      var config = props.state.config,
        itemConfig = props.state.itemConfig
      queryParams = queryParams ? queryParams : {start: props.state.pagination.current_page}

      return jQuery.ajax(config.baseUrl + '?option=com_comment&task=comments.getcomments&format=json&lang=' + config.langCode + '&contentid=' + itemConfig.contentid + '&component=' + itemConfig.component, {
        dataType: 'json',
        data: queryParams
      })
        .done(function (data) {
          props.commit('UPDATE_COMMENTS', data.models)

          props.commit('UPDATE_PAGINATION', data.info)
        })
    },
    putComment: function (props, comment) {
      if (props.state.commentsById[comment.id]) {
        props.commit('updateComment', comment)
      }
      else {
        props.commit('addComment', comment)
      }
    },
    editComment: function (props, comment) {
      props.commit('editComment', comment)
    },
    resetComments: function (props, data) {
      props.commit('UPDATE_COMMENTS', data.models)
      props.commit('UPDATE_PAGINATION', data.info)
    }
  }
})
