/* ===================================================================================
 * File : global.js
 * Description : JS generic functions
 * Authors : Hamza Iqbal - hiqbal[at]actualys.com
 *			 Mikaël Guillin - mguillin[at]actualys.com
 * Copyright : Actualys
 /* =================================================================================== */


/* =================================================================================== */
/* JQUERY CONTEXT */
/* =================================================================================== */
(function ($)
{
    /* =================================================================================== */
    /* GLOBAL VARS */
    /* =================================================================================== */

    // Anchor
    var _anchor = window.location.hash;
    // Main elements
    var _doc = $(document);
    var _win = $(window);
    var _html = $('html');
    var _body = $('body');
    var _header = $('#header');
    var _navigation = $('#navigation');
    var _content = $('#content');
    var _footer = $('#footer');
    // Carousels
    var _carousels = $('.carousel-content');
    var _classNames =
            {
                active: 'active',
                opened: 'opened',
                disabled: 'disabled'
            };
    // Fancybox - Defaut config
    var _fbConfig =
            {
                padding: 0,
                autoSize: true,
                fitToView: true,
                helpers:
                        {
                            title:
                                    {
                                        type: 'outside',
                                        position: 'top'
                                    }
                        }
            };
    /**
     * Equal heights
     ******************************************/
    $.fn.equalHeights = function ()
    {
        var maxHeight = 0,
                $this = $(this);
        $this.each(function () {
            var height = $(this).innerHeight();
            if (height > maxHeight) {
                maxHeight = height;
            }
        });
        return $this.css('height', maxHeight);
    };
    /**
     * Applique la même hauteur sur tous les élément
     * qui ont la classe .equal-height sur chaque ligne
     * $.initEqualHeight();
     ******************************************/
    $.initEqualHeight = function ()
    {
        if ($('.equal-height').length > 0)
        {
            $('.row').each(function ()
            {
                $(this).find('.equal-height').equalHeights();
            });
        }
    };
    $.fn.initDatePickers = function ()
    {
        var datePickers = $(this).find('.date-picker');
        datePickers.each(function ()
        {
            var currentDp = $(this);
            hasValue = currentDp.find('input').val();
            currentDp.datetimepicker
                    ({
                        language: 'fr',
                        pickTime: false,
                        useCurrent: false,
                        calendarWeeks: true,
                    });
            if (!hasValue) {
                currentDp.find('input').val('');
            }

            currentDp.on('focus', 'input', function ()
            {
                currentDp.data('DateTimePicker').hide();
                currentDp.data('DateTimePicker').show();
            });
        });
        var datePickers = $(this).find('.date-picker-all-days');
        datePickers.each(function ()
        {
            var currentDp = $(this);
            currentDp.datetimepicker
                    ({
                        language: 'fr',
                        pickTime: false
                    });
            currentDp.on('focus', 'input', function ()
            {
                currentDp.data('DateTimePicker').hide();
                currentDp.data('DateTimePicker').show();
            });
        });
        var datePickers = $(this).find('.date-picker-time');
        datePickers.each(function ()
        {
            var currentDp = $(this);
            currentDp.datetimepicker
                    ({
                        language: 'fr',
                        pickDate: false,
                        useCurrent: false,
                        minuteStepping: 5
                    });
            currentDp.on('focus', 'input', function ()
            {
                currentDp.data('DateTimePicker').hide();
                currentDp.data('DateTimePicker').show();
            });
        });
    };
    $.fn.initSelect2Autocomplete = function ()
    {
        $(this).find('.select2autocomplete').select2({allowClear: true, placeholder: true, openOnEnter: true});
    }

    $.fn.initSelect2AutocompleteRemote = function ()
    {
        $(this).find('.select2autocompleteremote').each(function() {
                var element = $(this);
                var defaultValue = $(this).val();
                var defaultValueSplitted = defaultValue.split(',');
                element.select2({
                allowClear: true,
                placeholder: true,
                minimumInputLength: 3,
                initSelection: function (element, callback) {
                    if (defaultValue != '') {
                        callback({id: defaultValueSplitted[0], text: defaultValueSplitted[1]});
                        element.val(defaultValueSplitted[0]);
                    }
                },
                ajax: {
                    url: element.data('url'),
                    dataType: 'json',
                    quietMillis: 250,
                    data: function (term, page) {
                        return {
                            q: term,
                        };
                    },
                    results: function (data, page) {
                        return {results: data};
                    },
                    cache: true
                },
                formatResult: function (item) {
                    if (item.text_html) {

                        return item.text_html;
                    }

                    return item.text;
                }});
        });

        $(this).find(".select2SubmitOnChange").on("change", function (e) {
            if (e.val || $(this).val()) {
                $(this).parents('form').submit();
            }
        });
    }

    $.initSelect2AutocompletePermissif = function ()
    {

        $('.select2autocompletepermissif').select2({
            tags: [],
            tokenSeparators: [','],
            createSearchChoice: function (term) {
                return {
                    id: $.trim(term),
                    text: $.trim(term) + ' (nouveau tag)'
                };
            },
            ajax: {
                url: $('.select2autocompletepermissif').data('url'),
                dataType: 'json',
                data: function (term, page) {
                    return {
                        q: term
                    };
                },
                results: function (data, page) {
                    return {
                        results: data
                    };
                }
            },
            initSelection: function (element, callback) {
                var data = [];
                function splitVal(string, separator) {
                    var val, i, l;
                    if (string === null || string.length < 1)
                        return [];
                    val = string.split(separator);
                    for (i = 0, l = val.length; i < l; i = i + 1)
                        val[i] = $.trim(val[i]);
                    return val;
                }

                $(splitVal(element.val(), ",")).each(function () {
                    data.push({
                        id: this,
                        text: this
                    });
                });



                callback($.map(element.val().split(','), function (id) {
                    return {id: id, text: id};
                }));
            }
        });
    }

    $.initSelect2PermissifNoAjax = function ()
    {
        if ($('.select2permissifNoAjax').length) {
            $('.select2permissifNoAjax').select2({
                data: JSON.parse($('.select2permissifNoAjax').attr('data-choices')),
                multiple: false,
                placeholder: true,
                allowClear: true,
                createSearchChoice: function (term, data) {
                    if ($(data).filter(function () {
                        return this.text.localeCompare(this.text) === 0;
                    }).length === 0) {
                        return {id: term, text: term + ' (nouveau)'};
                    }
                }
            }).on("select2-open", function (e) {
                $('.select2-input').attr('placeholder', 'Rechercher ou ajouter un nouvel élément');
            }).on("select2-close", function (e) {
                $('.select2-input').attr('placeholder', null);
            });
        }
    }

    $.initCheckboxRelations = function ()
    {
        $('.checkbox-relation').click(function () {
            $($(this).attr('data-relation')).toggleClass("hidden");
        })

    }

    $.initCollectionAddTemplate = function (element, regexp_replace, callback)
    {

        $(element).click(function ()
        {
            var bloc_html = $($(this).attr('data-template')).html().replace(regexp_replace, UUID.generate());

            try {
                var params = jQuery.parseJSON($(this).attr('data-template-params'));
            } catch (err) {

            }

            for (key in params) {
                bloc_html = bloc_html.replace(new RegExp(key, "g"), params[key]);
            }

            var bloc = $(bloc_html);

            $($(this).attr('data-container')).append(bloc);

            if (callback) {
                callback(bloc);
            }
            return false;
        });
    }
    $.initBsSwitchCheckbox = function ()
    {
        $.fn.onoff = function (event, state) {
            if (state) {
                $(this).parent().parent().parent().removeClass("bootstrap-switch-off");
                $(this).parent().parent().parent().addClass("bootstrap-switch-on");
                $(this).parent().parent().parent().parent().removeClass("bootstrap-switch-off");
                $(this).parent().parent().parent().parent().addClass("bootstrap-switch-on");
            } else {
                $(this).parent().parent().parent().addClass("bootstrap-switch-off");
                $(this).parent().parent().parent().removeClass("bootstrap-switch-on");
                $(this).parent().parent().parent().parent().addClass("bootstrap-switch-off");
                $(this).parent().parent().parent().parent().removeClass("bootstrap-switch-on");
            }
        };
        $('.bsswitch').on('switchChange.bootstrapSwitch', $.fn.onoff);
        $('.bsswitch').on('init.bootstrapSwitch', $.fn.onoff);
        $('.bsswitch').bootstrapSwitch();

        $('tr td').click(function (event) {
            if (!$(this).hasClass('edit')) {
                var value = ($(this).parent().find('td.bootstrap-switch-off .bsswitch').val() == 'on');
                $(this).parent().find('td .bsswitch').bootstrapSwitch('state', value, false);
            }
        });
    }

    /**
     * Contrôle la bonne saisie de nombres dans
     * un champ
     * $(s).saisieNum(float, callbackKeypress);
     ******************************************/
    $.fn.saisieNum = function (float, callbackKeypress, callbackBlur)
    {
        var champ = $(this);
        var float4 = champ.hasClass('num_float4');
        // A chaque touche pressée
        champ.keypress(function (e)
        {
            var val = $(this).val();
            var touche = e.which;
            var ponctuationPresente = (val.indexOf('.') != -1 || val.indexOf(',') != -1);
            var chiffre = (touche >= 48 && touche <= 57); // Si chiffre

            // touche "entrer"
            if (touche == 13)
                return e;
            if (touche == 0)
                return e;
            // Champ nombre décimal
            if (float)
            {
                // !backspace && !null && !point && !virgule && !chiffre
                if (touche != 8 && touche != 0 && touche != 46 && touche != 44 && !chiffre)
                    return false;
                // point déjà présent
                if (touche == 46 && ponctuationPresente)
                    e.preventDefault();
                // virgule déjà présente
                if (touche == 44 && ponctuationPresente)
                    e.preventDefault();
                // 2 décimales
                if (!float4 && val.match(/[\.\,][0-9][0-9]/) && chiffre && e.currentTarget && e.currentTarget.selectionStart > val.length - 3)
                    e.preventDefault();

                // 4 décimales
                if (float4 && val.match(/[\.\,][0-9]{4}/) && chiffre && e.currentTarget && e.currentTarget.selectionStart > val.length - 5)
                    e.preventDefault();
            }
            // Champ nombre entier
            else
            {
                if (touche != 8 && touche != 0 && !chiffre)
                    e.preventDefault();
            }

            if (callbackKeypress)
                callbackKeypress();
            return e;
        });
        // A chaque touche pressée
        champ.keyup(function (e)
        {
            var touche = e.which;
            // touche "retour"
            if (touche == 8)
            {
                if (callbackKeypress)
                    callbackKeypress();
                return e;
            }
        });
        // A chaque fois que l'on quitte le champ
        champ.blur(function ()
        {
            $(this).nettoyageChamps();
            if (callbackBlur)
                callbackBlur();
        });
    };
    /**
     * Nettoie les champs après la saisie
     * $(champ).nettoyageChamps();
     ******************************************/
    $.fn.nettoyageChamps = function ()
    {
        var champ = $(this);
        var val = champ.val();
        var float = champ.hasClass('num_float');
        var float4 = champ.hasClass('num_float4');
        // Si quelque chose a été saisi
        if (val)
        {
            // Remplacement de toutes les virgules par des points
            if (val.indexOf(',') != -1)
                val = val.replace(',', '.');
            // Si un point a été saisi sans chiffre
            if (val.indexOf('.') != -1 && val.length == 1)
                val = ''; //val = '0';

            // Un nombre commençant par 0 peut être interprété comme étant en octal
            if (val.indexOf('0') == 0 && val.length > 1)
                val = val.substring(1);
            // Comparaison nombre entier / flottant
            if (float || parseInt(val) != parseFloat(val)) {
                if (float4) {
                    val = parseFloat(val).toFixed(4);
                } else {
                    val = parseFloat(val).toFixed(2);
                }

            } else
                val = parseInt(val);
        }
        // Si rien n'a été saisi
        //else val = 0;
        else
            val = '';
        // Si ce n'est pas un nombre (ex : copier/coller d'un texte)
        if (isNaN(val))
            val = ''; //val = 0;

        /*if (val == 0) {
         champ.addClass('num_light');
         } else {
         champ.removeClass('num_light');
         }*/
        champ.val(val);
    };

    $.initCarte = function ()
    {
        $('.carte').each(function () {
            var map = L.map($(this).attr('id'), {minZoom: 6, zoom: 10, }).setView([48.100901, 7.361051], 9);
            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
                attribution: 'Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
            }).addTo(map);
            var points = JSON.parse($(this).attr('data-point'));
            for (point_key in points) {
                L.marker(points[point_key]).addTo(map);
            }
            map.fitBounds(points, {padding: [10, 10], maxZoom: 13});
        });
    };

    $.initModal = function () {
        $('.modal.modal-page').modal({keyboard: false, backdrop: 'static'});
    }

    $.initCheckboxBtnGroup = function() {
        $('.btn-group.select label.btn').on('click', function() {
            if(!$(this).hasClass('active')) {
                $(this).removeClass('btn-default-step');
            } else {
                $(this).addClass('btn-default-step');
            }
        })
    }

    $.initValidationDeclaration = function() {
        $('#submit-confirmation-validation').click(function() {
            $('#validation-form').submit();
        });

        $('#btn-validation-document').click(function() {
            $("input:checkbox[name*=validation]").each(function() {
                    $(this).parent().parent().parent().removeClass("has-error");
            });
            $("#engagements .alert-danger").addClass("hidden");
            if($("input:checkbox[name*=validation]").length != $("input:checkbox[name*=validation]:checked").length) {
                $("#engagements .alert-danger").removeClass("hidden");
                $("input:checkbox[name*=validation]:not(:checked)").each(function() {
                    $(this).parent().parent().parent().addClass("has-error");
                });
                $("input:checkbox[name*=validation]:checked").each(function() {
                    $(this).parent().parent().parent().removeClass("has-error");
                });
                return false;
            }
        });
    }

    $.initDynamicCollection = function() {
        $("#page").on('click', ".dynamic-element-add", function () {
            var content = $($($(this).attr('data-template')).html().replace(/var---nbItem---/g, UUID.generate()));
            $($(this).attr('data-container')).append(content);
            var previous = content.prev();
            content.find('input, select, checkbox').each(function() {
                if($(this).attr('data-copie')) {
                    $(this).val(previous.find('[data-copie='+$(this).attr('data-copie')+']').val());
                }

                if($(this).attr('data-increment')) {
                    var previousValue = parseInt(previous.find('[data-increment='+$(this).attr('data-increment')+']').val());
                    if(!isNaN(previousValue)) {
                        $(this).val(previousValue + 1);
                    }
                }
            });
            content.initAdvancedElements();
        });

        $("#page").on('click', '.dynamic-element-delete', function () {
            if($(this).attr('data-confirm') && !confirm($(this).attr('data-confirm'))) {
                return false;
            }
            $($(this).attr('data-line')).find('input').val("");
            $($(this).attr('data-line')).find('input').trigger('keyup');
            $($(this).attr('data-line')).remove();
            if ($($(this).attr('data-lines')).length < 1 && $(this).attr('data-add')) {
                $($(this).attr('data-add')).trigger('click');
            }
        });

        $('#page').on('change', '.dynamic-element-item:last input, .dynamic-element-item:last select, .dynamic-element-item:last checkbox', function() {
            var item = $(this).parents(".dynamic-element-item");
            var allIsComplete = true;
            item.find("input, select, checkbox").each(function() {
                if($(this).attr('name') && !$(this).attr('data-norequired') && !$(this).val()) {
                    allIsComplete = false;
                }
            });

            if(allIsComplete) {
                $(".dynamic-element-add").click();
            }
        });

        $("#page").on('click', '.btn-dynamic-element-submit', function(e) {
            var vals = $(this).parents('form').serializeArray();

            $(this).parents('form').find('.dynamic-element-delete').last().each(function(){
                var ligne = $($(this).attr('data-line'));
                var hasAllValue = true;
                ligne.find('input, select, textarea').each(function() {
                    if($(this).attr('name') && !$(this).attr('data-norequired') && !$(this).val()) {
                        hasAllValue = false;
                    }
                });

                if(!hasAllValue) {
                    $($(this).attr('data-line')).remove();
                }
            });

            return true;
        });
    }

    $.fn.initBlocCondition = function()
    {
        $(this).find('.bloc_condition').each(function() {
            var blocCondition = $(this);
            var input = blocCondition.find('input');
            if(input.length == 0) {
                input = blocCondition.find('select');
            }
            var blocs = blocCondition.attr('data-condition-cible').split('|');
            var traitement = function(input, blocs) {
                   for (bloc in blocs) {
                       console.log($(blocs[bloc]));
                       if ($(blocs[bloc]).size() > 0) {
                           var values = $(blocs[bloc]).attr('data-condition-value').split('|');
                           for(key in values) {
                               if (input.attr('type') == 'checkbox') {
                                   if (values[key] == 1 && input.is(':checked')) {
                                       $(blocs[bloc]).show();
                                   }
                                   if (values[key] != 1 && !input.is(':checked')) {
                                       $(blocs[bloc]).show();
                                   }

                               }
                               if (values[key] == input.val() && (input.is(':checked')) || input.is(':selected')) {
                                   $(blocs[bloc]).show();
                               }
                           }
                       }
                   }
            }
            if(input.length == 0) {
               for (bloc in blocs) {
                    $(blocs[bloc]).show();
               }
            } else {
               for (bloc in blocs) {
                    $(blocs[bloc]).hide();
               }
            }
            input.each(function() {
                traitement($(this), blocs);
            });

            input.click(function()
            {
               for (bloc in blocs) {
                    $(blocs[bloc]).hide();
               }
               if($(this).is(':checkbox')) {
                   $(this).parent().find('input').each(function() {
                       traitement($(this), blocs);
                   });
               } else {
                   traitement($(this), blocs);
               }
            });
        });
    }

    $.initTypeahead = function() {
        $('.typeahead').typeahead({
            itemLink: function(item) {

                return item[this.$element.data("link")];
            },
            displayText: function(item) {
                if(!item[this.$element.data("text")]) {

                    return item.replace("%query%", this.$element.val());
                }

                return item[this.$element.data("text")];
            },
            source: function (query, process) {
                var params = {};
                params[this.$element.data('queryParam')] = query;
                return $.getJSON(this.$element.data('url'), params, function (data) {
                    return process(data);
                });
            },
            sorter: function(items) { return items },
            matcher: function(item) { return true },
            highlighter: function(item) { return item; },
            updater: function(item) { return this.$element.val(); },
            afterEmptySelect: function() { this.$element.parents('form').submit(); },
            items: 5,
            delay: 200,
            addItem: "<em>Chercher plus de résultats pour \"%query%\"</em>",
            minLength: 3,
            autoSelect: false,
            fitToElement: true,
            followLinkOnSelect: true,
        });
    }

    $.fn.initAdvancedElements = function () {
        $(this).initSelect2Autocomplete();
        $(this).initSelect2AutocompleteRemote();
        $(this).initBlocCondition();
        $(this).initDatePickers();
        $(this).find('input.num_float').saisieNum(true);
        $(this).find('input.num_int').saisieNum(false);
    }

    /* =================================================================================== */
    /* FUNCTIONS CALL */
    /* =================================================================================== */
    _doc.ready(function ()
    {
        $.fn.modal.Constructor.prototype.enforceFocus = function () {};
        _doc.initDatePickers();
        _doc.initAdvancedElements();
        $.initSelect2AutocompletePermissif();
        $.initSelect2PermissifNoAjax();
        $.initCheckboxRelations();
        $.initBsSwitchCheckbox();
        $.initCarte();
        $.initModal();
        $.initDynamicCollection();
        $.initTypeahead();
        $('input.num_float').saisieNum(true);
        $('input.num_int').saisieNum(false);
        $('a[data-toggle=tooltip], button[data-toggle=tooltip], span[data-toggle=tooltip]').tooltip({'container': 'body'});
        $('input[data-toggle=tooltip]').tooltip({'trigger': 'focus', 'container': 'body'});
        $.initEqualHeight();
        $.initCheckboxBtnGroup();
        $.fn.modal.Constructor.prototype.enforceFocus = function () {};
    });
})(jQuery);
