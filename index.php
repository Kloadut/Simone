<?php
    // Include markdown library
    include_once 'markdown.php';

    // Load configuration
    $config = json_decode(file_get_contents('config.json'), true);

    // Get language from browser
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    $suffix = '_'.$lang;
    if ($lang == '' || $lang == $config['defaultLanguage'] || !array_key_exists($lang, $config['languages'])) {
        $suffix = '';
    }

    // Get route
    if (isset($_GET['uri'])) {
        $rURI = $_GET['uri'];
    } else {
        $rURI = $_SERVER["REQUEST_URI"];
    }

    if ($rURI === '/') {
        $uri = 'index'.$suffix;
    } elseif (substr($rURI, -1) === '_')  {
        $uri = substr($rURI, 1, -1);
    } else {
        $uri = substr($rURI, 1);
    }

    // Construct title
    $title = $config['siteName'].' • '.$uri;

    // Try to get markdown file
    $markdown = file_get_contents('_pages/'.$uri.'.md');

    // 404
    if (!$markdown) {
        header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
        die('Not Found');
    }

    // Compile HTML content
    $content = Markdown($markdown);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang ?>">
<head>
<title><?php echo $title ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport" content="user-scalable=no, width=device-width, height=device-height" />

    <link rel="stylesheet" type="text/css" href="_css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="_css/hl.css">
    <link rel="stylesheet" type="text/css" href="_css/solarized_dark.min.css">
    <link rel="stylesheet" type="text/css" href="_css/fonts.css">
    <link rel="stylesheet" type="text/css" href="_css/style.css">
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
  _paq.push(["setCookieDomain", "*.yunohost.org"]);
  _paq.push(["trackPageView"]);
  _paq.push(["enableLinkTracking"]);

  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://piwik.beudibox.fr/";
    _paq.push(["setTrackerUrl", u+"piwik.php"]);
    _paq.push(["setSiteId", "1"]);
    var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
    g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Piwik Code -->
</head>

<body>

    <div id="wrapper">
        <div id="win" class="alert alert-success" style="display: none" data-i18n="modificationSaved"></div>
        <div id="fail" class="alert alert-danger" style="display: none" data-i18n="modificationFailed"></div>
        <div id="form" style="display: none">
            <textarea cols="80" rows="40"></textarea>
        </div>
        <div id="logo"><a href="#/" data-toggle="tooltip" data-placement="auto bottom" title="Index"><img src="logo.png"></a></div>
        <div id="content">
            <?php echo $content ?>
        </div>
    </div>

    <div class="actions" style="display: none">
        <a class="btn btn-default" id="edit">
            <span class="glyphicon glyphicon-pencil"></span>&nbsp; <span data-i18n="edit"></span>
        </a>
        <a class="btn btn-default" id="preview">
            <span class="glyphicon glyphicon-eye-open"></span>&nbsp; <span data-i18n="preview"></span>
        </a>
        <button type="button" class="btn btn-primary" id="send" data-toggle="modal" data-target="#sendModal">
            <span class="glyphicon glyphicon-ok"></span>&nbsp; <span data-i18n="send"></span>
        </button>
        <a class="btn btn-danger" id="back">
            <span class="glyphicon glyphicon-ban-circle"></span>&nbsp; <span data-i18n="revert"></span>
        </a>
    </div>

    <div class="languages" style="display: none">
        <a class="btn btn-default" id="help" target="_blank" href="/help">?</a>
        <div class="btn-group dropup">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-globe"></span>&nbsp; <span data-i18n="languages"></span> &nbsp;<span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
          </ul>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="sendModal" tabindex="-1" role="dialog" aria-labelledby="sendModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3 class="modal-title text-center" id="sendModalLabel" data-i18n="sendModifications"></h3>
          </div>
          <form class="form-horizontal" method="POST" role="form">
            <div class="modal-body">
                <div class="panel-group" id="accordion">
                  <div class="panel panel-default">
                    <div class="panel-heading text-center">
                      <h4 class="panel-title">
                        <span class="glyphicon glyphicon-user"></span>&nbsp;
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" data-i18n="withAccount"></a>
                      </h4>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse">
                      <div class="panel-body">
                        <div class="form-group">
                          <label for="user" class="col-sm-4 control-label" data-i18n="email"></label>
                          <div class="col-sm-8">
                            <input type="email" class="form-control" id="user" name="user" placeholder="john@doe.org" required>
                          </div>
                        </div>
                        <div class="form-group">
                          <label for="password" class="col-sm-4 control-label" data-i18n="password"></label>
                          <div class="col-sm-8">
                            <input type="password" class="form-control" id="password" name="password" placeholder="•••••" required>
                          </div>
                        </div>
                        <small><a class="pull-right" target="_blank" href="#/accounting" data-i18n="youDontHaveAccount"></a></small>
                        <div class="clearfix"></div>
                        <br>
                        <div class="text-center">
                          <button type="button" class="btn btn-primary" id="reallysend" data-i18n="sendChanges"></button>
                        </div>
                        <div class="clearfix"></div>
                      </div>
                    </div>
                  </div>
                  <div class="panel panel-default">
                    <div class="panel-heading text-center">
                      <h4 class="panel-title">
                        <span class="glyphicon glyphicon-send"></span>&nbsp;
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" data-i18n="byEmail"></a>
                      </h4>
                    </div>
                    <div id="collapseTwo" class="panel-collapse collapse">
                      <div class="panel-body">
                          <p data-i18n="launchMail"></p>
                          <br>
                          <div class="text-center">
                            <button type="button" class="btn btn-primary" id="sendMail" data-i18n="sendChanges"></button>
                          </div>
                          <div class="clearfix"></div>
                      </div>
                    </div>
                  </div>
                  <div class="panel panel-default">
                    <div class="panel-heading text-center">
                      <h4 class="panel-title">
                        <span class="glyphicon glyphicon-star-empty"></span>&nbsp;
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" data-i18n="viaGit"></a>
                      </h4>
                    </div>
                    <div id="collapseThree" class="panel-collapse collapse">
                      <div class="panel-body">
                          <p data-i18n="gitInstructions"></p>
                        <br>
                        <textarea id="gitarea"></textarea>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
          </form>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    <script type="text/javascript" src="_js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="_js/sammy-latest.min.js"></script>
    <script type="text/javascript" src="_js/sammy.storage.js"></script>
    <script type="text/javascript" src="_js/highlight.min.js"></script>
    <script type="text/javascript" src="_js/marked.js"></script>
    <script type="text/javascript" src="_js/bootstrap.min.js"></script>
    <script type="text/javascript" src="_js/app.js"></script>
</body>

</html>
