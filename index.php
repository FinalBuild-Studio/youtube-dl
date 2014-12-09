<?php
  $method = $_SERVER['REQUEST_METHOD'];
  if ($method == "POST") {
    set_time_limit(0);
    @$tempbat   = uniqid(null, true) . ".bat";
    @$cache     = dirname(__FILE__) . "/cache/";
    @$parsedUrl = parse_url($_POST["url"]);
    @parse_str($parsedUrl["query"], $parsedUrl);
    @$id        = $parsedUrl["v"];
    @$command   = "php youtube-dl.php -i \"{$id}\" -f \"{$_POST["format"]}\" -p \"{$_POST["location"]}\" -s \"{$_POST["save"]}\" -proxy \"{$_POST["proxy"]}\" && exit";
    @$bat       = $cache . "/" . $tempbat;
    @file_put_contents($bat, $command);
    @exec("START \"\" \"{$bat}\"");
    @unlink($bat);
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>youtube-dl web console</title>
    <style type="text/css">
      .form-element > input, select {
        margin: 10px;
      }
      
      .form-element > label {
        clear: both;
      }

      #wrapper > form {
        margin: 0 auto;
        width: 60%;
      }

      #wrapper {
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div id="wrapper">
      <h1>youtube-dl web console for Windows</h1>
      <form method="post">
        <div class="form-element">
          <label>下載網址</label>
          <input type="textbox" name="url" placeholder="請輸入youtube網址" required>
        </div>
        <div class="form-element">
          <label>儲存位置</label>
          <input type="textbox" name="location" placeholder="請輸入儲存位置" value="D:/" required>
        </div>
        <div class="form-element">
          <label>代理設置</label>
          <input type="textbox" name="proxy" placeholder="請輸入代理伺服器, e.g. proxy.hinet.net:80">
        </div>
        <div class="form-element">
          <label>提取方式</label>
          <select name="save">
            <option value="">預設</option>
            <option value="audio">音軌(mp3)</option>
            <option value="video">視訊軌</option>
          </select>
        </div>
        <div class="form-element">
          <label>視訊格式</label>
          <select name="format">
            <option value="">預設</option>
            <option value="mp4">mp4(推薦)</option>
            <option value="webm">webm</option>
          </select>
        </div>
        <input type="submit">
      </form>
    </div>
  </body>
</html>