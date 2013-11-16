$(document).ready(function () {

    marked.setOptions({
        highlight: function (code, lang) {
            return hljs.highlight(lang, code).value;
        }
    });

    var store = new Sammy.Store({name: 'storage', type: 'session'});

    var app = Sammy('#content', function(sam) {

        sam.helpers({
            view: function (page) {
                var c = this;
                if (page.substr(page.length - 3, 1) != '_') {
                    currentLang = store.get('lang');
                    if (currentLang != conf.defaultLanguage) {
                        c.redirect('#/'+ page +'_'+ currentLang);
                    }
                } else if (page.substr(page.length - 2) == conf.defaultLanguage) {
                    // Indicate page specifically
                    page = page.substr(0, page.length - 3);
                }
                store.set('page', page);

                var d = store.get('data-'+ page);
                if (d !== null) {
                   loadMD(c, d);
                } else {
                    $.get('_pages/'+ page +'.md', function(data) {
                        loadMD(c, data);
                    }).fail(function() {
                        var append = '';
                        if (store.get('lang') != conf.defaultLanguage) {
                             append = '_'+ store.get('lang');
                        }
                        $.get('_pages/default'+ append +'.md', function(data) {
                            loadMD(c, data);
                        });
                    });
                }
            }
        });

        sam.get('#/', function (c) {
            c.redirect('#', 'index');
        });

        sam.get('#/:name', function (c) {
            c.view(c.params['name']);
            title = conf.siteName;
            $('#sendModal').modal('hide');
            $('.actions').children().hide();
            $('#form').hide();
            $('#edit').attr('href', '#/'+ c.params['name'] +'/edit').fadeIn('fast');
            $('#content').fadeIn('fast');

            defaultLanguage = conf.defaultLanguage;
            languages = conf.languages;
            var href = document.location.href;
            if (href.substr(href.length - 3, 1) == '_') {
                href = href.substr(0, href.length - 3);
            }
            $(".languages ul.dropdown-menu").html('');
            $.each( languages, function( key, val ) {
                if (key == defaultLanguage) { append = '' }
                else { append = '_'+key }
                $(".languages ul.dropdown-menu").append('<li><a class="change-language" data-lang="'+ key +'" href="'+ href+append +'">'+ val +'</a></li>');
            });
            $(".languages").fadeIn('fast');

        });

        sam.get('#/:name/edit', function (c) {
            c.view(c.params['name']);
            document.title = 'Edit '+ c.params['name'];
            $('#sendModal').modal('hide');
            $('.actions').children().hide();
            $('.languages').hide();
            $('#content').hide();
            $('#preview').attr('href', '#/'+ c.params['name'] +'/preview').fadeIn('fast');
            $('#back').attr('href', '#/'+ c.params['name']).fadeIn('fast');
            $('#send').fadeIn('fast');
            $('#form').fadeIn('fast');
            $('#sendModal form').attr('action', '#/'+ c.params['name'] +'/save');
        });

        sam.get('#/:name/preview', function (c) {
            c.view(c.params['name']);
            document.title = 'Preview '+ c.params['name'];
            $('#sendModal').modal('hide');
            $('.actions').children().hide();
            $('.languages').hide();
            $('#form').hide();
            $('#edit').attr('href', '#/'+ c.params['name'] +'/edit').fadeIn('fast');
            $('#back').attr('href', '#/'+ c.params['name']).fadeIn('fast');
            $('#send').fadeIn('fast');
            $('#content').fadeIn('fast');
            $('#sendModal form').attr('action', '#/'+ c.params['name'] +'/save');
        });

    });

    function sendModifications(page) {
        auth = "Basic "+ btoa($('#user').val() +':'+ $('#password').val());
        $('.modal-footer').append('&nbsp;<img src="/ajax-loader.gif" class="ajax-loader">');
        $.ajax({
            url: 'save.php',
            type: 'POST',
            data: { 'page': page, 'content': store.get('data-'+ page) },
            beforeSend: function(req) {
                req.setRequestHeader('Authorization', auth);
            }
        })
        .success(function(data) {
            $('#sendModal').modal('hide');
            $('.ajax-loader').remove();
            $('#win').fadeIn('fast', function() {
                setTimeout(function() {
                    $('#win').fadeOut();
                }, 4000);
            });
            return true;
        })
        .fail(function(xhr) {
            $('.ajax-loader').remove();
            if (xhr.status == 401) {
                $('#sendModal alert p').html('Wrong username/password combination');
            } else {
                $('#sendModal').modal('hide');
                $('#fail').fadeIn('fast', function() {
                    setTimeout(function() {
                        $('#fail').fadeOut();
                    }, 4000);
                });
                return false;
            }
        });
    }

    function loadMD(c, data) {
        html = marked(data);
        $('#form textarea').val(data);
        c.swap(html, function() {
            if ($("h1").length > 0) {
                title = $("h1:first-child").text();
            }
            document.title = title +' | '+ conf.siteName;
        });
    }

    function changeLanguage(lang) {
        $('[data-i18n]').each( function() {
            key = $( this ).attr('data-i18n');
            $( this ).text(i18n[lang][key]);
        });
        store.set('lang', lang);
    }

    $(document).keyup(function(e) {
        if (e.keyCode == 27) {
            page = store.get('page');
            store.set('data-'+ page, $('#form textarea').val());
            href = document.location.href;
            if (href.substr(href.length - 5) == '/edit') {
                document.location.href = '#/'+ page +'/preview';
            } else {
                document.location.href = '#/'+ page +'/edit';
            }
        } else if (e.keyCode == 13 && $('#sendModal input.form-control:focus').length > 0) {
            $('#reallysend').trigger('click');
        }
    });

    $('#back').on('click', function() {
        store.set('data-'+ store.get('page'), null);
    });
    $('#preview').on('click', function() {
        store.set('data-'+ store.get('page'), $('#form textarea').val());
    });
    $('#send').on('click', function() {
        store.set('data-'+ store.get('page'), $('#form textarea').val());
    });
    $('#reallysend').on('click', function() {
        page = store.get('page');
        if (sendModifications(page)) {
            document.location.href = '#/'+ page;
        }
    });

    $('ul.dropdown-menu').on('click', '.change-language', function() {
        changeLanguage($( this ).attr('data-lang'));
        $('.dropdown-toggle').dropdown('toggle');
    });

    $.getJSON('i18n.json', function(lng) {
        i18n = lng;
        $.getJSON('config.json', function(data) {
            conf = data;
            console.log(conf);
            language = window.navigator.language.substr(0, 2);
            if (typeof i18n[language] !== undefined) {
                changeLanguage(language);
            } else {
                changeLanguage(conf.defaultLanguage);
            }
            app.run('#/');
        });
    });
});
