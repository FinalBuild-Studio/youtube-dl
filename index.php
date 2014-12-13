<?php
  $method = $_SERVER['REQUEST_METHOD'];
  if ($method == "POST") {
    set_time_limit(0);
    @$os      = substr(PHP_OS, 0, 3);
    @$tempbat = uniqid(null, true) . ($os == "WIN" ? ".bat" : ".sh");
    @$cache   = dirname(__FILE__) . "/cache/";
    @$urls    = explode(",", $_POST["url"]);

    foreach ($urls as $url) {
      if (@preg_match("/^http(s|)\:/", $url)) {
        @$parsedUrl = parse_url($url);
        @parse_str($parsedUrl["query"], $parsedUrl);
        @$id = $parsedUrl["v"];
      } else {
        @$id = $url;
      }
      @$prefex  = $os == "WIN" ? "SET PATH=%PATH%;C:\\xampp\\php;C:\\php;C:\\Program Files (x86)\\xampp\\php;%cd%\\php; && " : "cwd=\$(pwd) && export path=\"\$path:\$(pwd)+/php\" && ";
      @$command = "{$prefex}php youtube-dl.php -i \"{$id}\" -f \"{$_POST["format"]}\" -p \"{$_POST["location"]}\" -s \"{$_POST["save"]}\" -proxy \"{$_POST["proxy"]}\" && exit";
      @$bat     = $cache . "/" . $tempbat;
      @file_put_contents($bat, $command);
      $execute = "";
      if ($os == "WIN") {
        $execute = "START \"\" \"{$bat}\"";
      } else {
        $execute = "osascript -e \"tell application \\\"Terminal\\\" to do script \\\"{$bat}\\\"";
      }
      @exec($execute);
      @unlink($bat);
    }
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
      <h1>youtube-dl web console</h1>
      <form method="post">
        <div class="form-element">
          <label>下載網址</label>
          <input type="textbox" name="url" placeholder="請輸入youtube網址或ID" required value="<?php echo @$_POST["url"]; ?>">
        </div>
        <div class="form-element">
          <label>儲存位置</label>
          <input type="textbox" name="location" placeholder="請輸入儲存位置" value="D:/" required value="<?php echo @$_POST["location"]; ?>">
        </div>
        <div class="form-element">
          <label>代理設置</label>
          <input type="textbox" name="proxy" placeholder="請輸入代理伺服器, e.g. proxy.hinet.net:80" value="<?php echo @$_POST["proxy"]; ?>">
        </div>
        <div class="form-element">
          <label>提取方式</label>
          <select name="save">
            <option value="">預設</option>
            <option value="audio" <?php echo @$_POST["save"] == "audio" ? "selected" : ""; ?>>音訊軌(mp3)</option>
            <option value="video" <?php echo @$_POST["save"] == "video" ? "selected" : ""; ?>>視訊軌</option>
          </select>
        </div>
        <div class="form-element">
          <label>視訊格式</label>
          <select name="format">
            <option value="">預設</option>
            <option value="mp4" <?php echo @$_POST["format"] == "mp4" ? "selected" : ""; ?>>mp4(推薦)</option>
            <option value="webm" <?php echo @$_POST["format"] == "webm" ? "selected" : ""; ?>>webm(mkv)</option>
          </select>
        </div>
        <input type="submit">
      </form>
    </div>
  </body>
</html>