/**
 * @author Daniel Dimitrov - compojoom.com
 * @date: 22.08.2021
 *
 * @copyright  Copyright (C) 2008 - 2021 compojoom.com . All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */
var ccommentSettings
(function($) {
    var getTemplateParams = function (element, component) {
        var selected = element.val();

        $.ajax("index.php?option=com_comment&task=template.getparams&format=raw&component=" + component, {
            data: 'template=' + selected,
            success: function (data) {
                var result = $('<div />').append(data).html();

                $('#template-params').html(result);

                $('#template-params').on('click', '.btn-group label:not(.active)', function () {

                    var label = $(this);
                    var input = $('#' + label.attr('for'));

                    label.parent('.btn-group').children('input').attr('checked', false)
                    if (!input.attr('checked')) {
                        label.parent('.btn-group').children("label")
                            .removeClass('active btn-success btn-danger btn-primary')
                        if (input.val() == '') {
                            label.addClass('active btn-primary');
                        } else if (input.val() == 0) {
                            label.addClass('active btn-danger');
                        } else {
                            label.addClass('active btn-success');
                        }
                        input.attr('checked', true);
                    }
                });

                // Joomla 3
                $('.radio.btn-group label').addClass('btn');
                // Joomla 3
                $('.btn-group input[checked=checked]').each(function(key, value){
                    var el = $(value)
                    if (el.val() == '') {
                        $("label[for=" + el.attr('id') + "]").addClass('active btn-primary');
                    } else if (el.val() == 0) {
                        $("label[for=" + el.attr('id') + "]").addClass('active btn-danger');
                    } else {
                        $("label[for=" + el.attr('id') + "]").addClass('active btn-success');
                    }
                });
            }
        })
    }
    ccommentSettings = function (element, component) {
        var element = $(element)

        element.on("change", function () {
            getTemplateParams(element, component)
        })

        getTemplateParams(element, component)
    }
})(jQuery)
