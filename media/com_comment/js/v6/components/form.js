Vue.component('ccomment-form', {
  store: store,
  template: '#ccomment-form',
  props: ['focus'],
  data: function () {
    return {
      error: false,
      errorMessage: '',
      info: false,
      infoMessage: '',
      recaptcha: false,
      display: false,
      active: false,
      notify: false,
      itemConfig: this.$store.state.itemConfig,
      config: this.$store.state.config,
      uploadImage: false,
      upload: false,
      isSending: false,
      // sendText: 'test'
    }

  },
  watch: {
    notify: function (val) {
      // If we need to display the name and email form, then set display to true
      if (this.displayingNameFormRequired()) {
        this.display = true
        return
      }

      this.display = val
    }
  },
  computed: {
    getName: function () {
      return this.$store.getters.getName
    },
    getEmail: function () {
      return this.$store.getters.getEmail
    },
    getDefaultName: function () {
      return this.$store.getters.getDefaultName
    },
    getAvatar: function () {
      return this.$store.getters.getAvatar
    },
    page: function () {
      return this.$store.state.pagination.current_page
    }
  },
  created: function () {
    var self = this

    this.$store.watch(function (state) {
      return state.editComment
    }, function () {
      self.handleEdit()
    })

    this.$store.watch(function (state) {
      return state.quoteComment
    }, function () {
      self.handleQuote()
    })

    this.$store.watch(function (state) {
      return state.pagination.current_page
    }, function () {
      self.$store.commit('activeForm', null)
    })

    bus.$on('newComment', function () {
      if (self.isActiveForm()) {
        jQuery('html, body').animate({
          scrollTop: jQuery(self.$el).offset().top,
        }, {
          duration: 400,
          complete: function () {
            var commentTextarea = jQuery(self.$el).find('textarea.js-ccomment-textarea')
            commentTextarea.focus()
          }
        })
      }
    })
  },

  mounted: function () {
    if (this.focus) {
      this.toggle()
    }
  },

  methods: {
    isActiveForm: function () {
      if (this.$store.state.activeForm === null || this.$store.state.activeForm === this._uid) {
        return true
      }

      return false
    },
    handleEdit: function () {
      var state = this.$store.state
      if (this.isActiveForm()) {
        this.reset()
        this.toggle()

        var comment = state.editComment
        var form = jQuery(this.$el)

        if (comment.name == '') {
          comment.name = 'COM_COMMENT_ANONYMOUS'
        }

        Object.keys(comment).forEach(function (key) {
          if (key === 'customfields') {
            comment.customfields.forEach(function (field) {
              var formField = form.find('[name="jform[customfields][' + field.name + ']"]')
              if (formField) {
                formField.val(field.value)
              }
            })
          } else {
            if (key === 'notify') {
              form.find('[name="jform[' + key + ']"]').attr('checked', parseInt(comment.notify) ? true : false)
            } else if (key === 'id') {
              if (form.find('[name="jform[id]"]').length > 0) {
                form.find('[name="jform[id]"]').val(comment.id)
              } else {
                form.append('<input type="hidden" value="' + comment.id + '" name="jform[id]" />')
              }
            }
            else {
              form.find('[name="jform[' + key + ']"]').val(comment[key])
            }
          }
        })
        form.find('textarea.js-ccomment-textarea').focus()
        this.sce.sceditor('instance').val(comment.comment)

        this.loadExistingFiles(comment.id)
      }
    },
    handleQuote: function () {
      if (this.isActiveForm()) {
        var comment = this.$store.state.quoteComment, text
        var commentTextarea = jQuery(this.$el).find('textarea.js-ccomment-textarea')

        commentTextarea.focus()
        if (comment != undefined) {
          if (comment.name == '') {
            comment.name = 'COM_COMMENT_ANONYMOUS'
          }
          text = '[quote=' + comment.name + ']' + comment.comment + '[/quote]'
        } else {
          text = 'failed to fetch comment'
        }

        if (!this.sce) {
          commentTextarea.val(commentTextarea.val() + text)
        }
        else {
          this.sce.sceditor('instance').insert(text)
        }
      }
    },

    displayingNameFormRequired: function () {
      if (this.config.name_required && !this.$store.getters.getName) {
        return true
      }

      if (this.config.email_required && !this.$store.getters.getEmail) {
        return true
      }

      return false
    },

    toggle: function () {
      // Check if we need to display the form for the name and email
      if(this.displayingNameFormRequired()) {
        this.display = true
      }

      this.$store.commit('activeForm', this._uid)
      this.active = true


      if (this.config.support_ubb && !this.sce) {
        this.sce = this.initBBCodeEditor()
      } else {
        autosize(jQuery(this.$el).find('textarea'))
      }

      if (this.config.captcha_pub_key && this.recaptcha === false) {
        this.recaptcha = grecaptcha.render(
          jQuery(this.$el).find('div.ccomment-recaptcha-placeholder')[0],
          {
            sitekey: this.config.captcha_pub_key
          }
        )
      }

      this.fileupload = this.initFileUpload()
    },
    getToken: function () {
      return this.$store.state.token
    },
    updateDefaultName: function (e) {
      this.$store.commit('updateDefaultName', e.target.value)
    },
    updateUserEmail: function (e) {
      this.$store.commit('updateUserEmail', e.target.value)
    },
    onSubmit: function () {
      var self = this
      var url = this.config.baseUrl + '?option=com_comment&task=comment.insert&format=json&' + this.getToken() + '=1&lang='+this.config.langCode

      // Get all form inputs
      var inputArray = jQuery(this.$el).serializeArray()

      jQuery.ajax(url, {
        method: 'POST',
        data: inputArray,
        beforeSend: function () {
          self.isSending = true
          self.error = false
        }
      })
        .done(function (response) {
          self.isSending = false
          if (response.status && response.status === 'error') {
            self.error = true
            self.errorMessage = response.message
            return
          }
          if (response.status && response.status === 'info') {
            self.info = true
            self.infoMessage = response.message

            self.reset()
            return
          }

          if (response.info) {
            self.$store.dispatch('resetComments', response)
            location.hash = '#ccomment-page=' + self.$store.state.pagination.current_page

            self.$nextTick(function () {
              jQuery('html, body').animate({
                scrollTop: jQuery('#ccomment-comment-' + response.info.insertedId).offset().top
              }, 200)
            })
          }
          else {
            self.$store.dispatch('putComment', response)

            self.$store.commit('UPDATE_PAGINATION', Object.assign({}, self.$store.state.pagination, {total: parseInt(self.$store.state.pagination.total_with_children) + 1}))
            self.$nextTick(function () {
              jQuery('html, body').animate({
                scrollTop: jQuery('#ccomment-comment-' + response.id).offset().top
              }, 200)
            })
          }

          self.reset()
        })
    },
    reset: function () {
      this.display = false
      this.active = false
      this.uploadImage = false

      this.$parent.reply = false
      jQuery(this.$el).find('[name="jform[id]"]').remove()
      jQuery(this.$el).trigger('reset')

      // If we have ubb support, destroy the editor
      if (this.config.support_ubb && this.sce) {
        this.sce.sceditor('instance').destroy()
        this.sce = false
      }

      if (this.recaptcha !== false) {
        grecaptcha.reset(this.recaptcha)
      }

      if (this.fileupload) {
        jQuery(this.$el).find('.fileupload').fileupload('destroy')
        jQuery(this.$el).find('.fileupload').replaceWith(this.fileUploadNode)
        this.fileupload = null
      }
    },

    initBBCodeEditor: function () {
      var sce,
        self = this,
        textarea = jQuery(this.$el).find('textarea.js-ccomment-textarea'),
        config = this.config,
        emoticon = config.support_emoticons ? 'emoticon' : '',
        picture = config.support_picture ? 'image' : ''

      jQuery.sceditor.command.set('imageFileUpload', {
        exec: function (caller) {
          var editor = this

          self.uploadImage = !self.uploadImage
        },
        state: function () {
          return self.uploadImage
        }
      })
      var toolbar = emoticon + '|bold,italic,underline,strike|' + picture + ',link,youtube|quote,code|color,size|source|imageFileUpload'

      if (jQuery(window).width() < 380) {
        toolbar = emoticon + '|bold,italic,underline,strike|'
      }

      sce = textarea.sceditor({
        format: 'bbcode',
        plugins: 'autoyoutube,plaintext',
        toolbar: toolbar,
        style: '/media/com_comment/js/vendor/sceditor/jquery.sceditor.default.min.css',
        height: 150,
        width: '100%',
        autofocus: true,
        autoExpand: true,
        autoUpdate: true,
        resizeEnabled: false,
        resizeMaxHeight: 300,
        emoticonsCompat: true,
        emoticonsRoot: config.baseUrl,
        emoticons: config.emoticons_pack,
        emoticonsEnabled: config.support_emoticons
      })

      sce.sceditor('instance').focus(function (e) {
        self.$store.commit('activeForm', self._uid)
      })

      return sce
    },

    initFileUpload: function () {
      var fileupload,
        self = this,
        el = jQuery(this.$el),
        fileUploadEl = el.find('.fileupload'),
        ccommentFileUpload = this.config.file_upload,
        numFilesEl = fileUploadEl.find('.compojoom-max-number-files')

      fileUploadEl.find('.js-file-upload-fake')[0].addEventListener('click', function () {
        fileUploadEl.find('.js-ccomment-file-upload-real')[0].click()  // trigger the click of actual file upload button
      })

      var $ = jQuery

      this.fileUploadNode = fileUploadEl.clone()

      // Initialize the jQuery File Upload widget:
      fileupload = fileUploadEl.fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        formData: {},
        dropZone: el,
        autoUpload: true,
        maxFileSize: ccommentFileUpload.maxSize + 'M',
        maxNumberOfFiles: ccommentFileUpload.maxNumberOfFiles,
        url: ccommentFileUpload.url + '&' + this.$store.state.token + '=1',
        disableImageResize: false,
        imageMaxWidth: ccommentFileUpload.imageSize.x,
        imageMaxHeight: ccommentFileUpload.imageSize.y,
        start: function (e) {
          self.uploadImage = true
        },
        finished: function (e, data) {
          if ($(this).fileupload('option').getNumberOfFiles() >= ccommentFileUpload.maxNumberOfFiles) {
            numFilesEl.removeClass('hide d-none')
          }
          else {
            numFilesEl.addClass('hide d-none')
          }
        },
        destroyed: function (e, data) {
          if ($(this).fileupload('option').getNumberOfFiles() >= ccommentFileUpload.maxNumberOfFiles) {
            numFilesEl.removeClass('hide d-none')
          }
          else {
            numFilesEl.addClass('hide d-none')
          }
        }
      }).on('destroyed', function (e, data) {
        if ($(this).fileupload('option').getNumberOfFiles() >= ccommentFileUpload.maxNumberOfFiles) {
          numFilesEl.removeClass('hide d-none')
        }
        else {
          numFilesEl.addClass('hide d-none')
        }
      }).on('fileuploadadd', function (e, data) {
        $('.fileupload-progress.hide').removeClass('hide d-none')
      })

      return fileupload
    },

    loadExistingFiles: function (id) {
      var self = this, $ = jQuery, fileUploadEl = $(this.$el).find('.fileupload')

      // Load existing files:
      fileUploadEl.addClass('fileupload-processing')
      $.ajax({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: this.config.file_upload.url + '&' + this.$store.state.token + '=1&id=' + id,
        dataType: 'json',
        context: fileUploadEl
      }).always(function () {

        // fileUploadEl.replaceWith(this.fileUploadNode);
        $(this).removeClass('fileupload-processing')
      }).done(function (result) {
        if (result.files.length) {
          self.uploadImage = true
        }

        $(this).fileupload('option', 'done')
          .call(this, $.Event('done'), {result: result})
      })
    }
  }
})

