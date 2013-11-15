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
                store.set('page', page);

                var d = store.get('data-'+ page);
                if (d !== null) {
                   loadMD(c, d);
                } else {
                    $.get('_pages/'+ page +'.md', function(data) {
                        loadMD(c, data);
                    }).fail(function() {
                        var href = document.location.href;
                        var append = ''
                        if (href.substr(href.length - 3, 1) == '_') {
                             append = href.substr(href.length - 3);
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
            $('#sendModal').modal('hide');
            $('.actions').children().hide();
            $('#form').hide();
            $('#edit').attr('href', '#/'+ c.params['name'] +'/edit').fadeIn('fast');
            $('#content').fadeIn('fast');

            // Load languages
            $.getJSON('config.json', function(data) {
                defaultLanguage = data.defaultLanguage;
                languages = data.languages;
                console.log(data);
                var href = document.location.href;
                if (href.substr(href.length - 3, 1) == '_') {
                    href = href.substr(0, href.length - 3);
                }
                $(".languages ul.dropdown-menu").html('');
                $.each( languages, function( key, val ) {
                    if (key == defaultLanguage) { append = '' }
                    else { append = '_'+key }
                    $(".languages ul.dropdown-menu").append('<li><a href="'+ href+append +'">'+ val +'</a></li>');
                });
                $(".languages").fadeIn('fast');
            });

        });

        sam.get('#/:name/edit', function (c) {
            c.view(c.params['name']);
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
            $('#win').fadeIn('fast', function() {
                setTimeout(function() {
                    $('#win').fadeOut();
                }, 4000);
            });
            return true;
        })
        .fail(function(xhr) {
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
        c.swap(html);
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
    $('#reallysend').on('click', function() {
        page = store.get('page');
        if (sendModifications(page)) {
            document.location.href = '#/'+ page;
        }
    });

    app.run('#/');
});
