<?php
  error_reporting(0);
  $method = $_SERVER['REQUEST_METHOD'];
  if ($method == "POST") {
    set_time_limit(0);
    $os      = substr(PHP_OS, 0, 3);
    $urls    = explode(",", $_POST["url"]);

    foreach ($urls as $url) {
      if (preg_match("/^http(s|)\:/", $url)) {
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl["query"], $parsedUrl);
        $id = $parsedUrl["v"];
      } else {
        $id = $url;
      }
      $prefex  = $os == "WIN" ? "SET PATH=%PATH%;C:\\xampp\\php;C:\\php;C:\\Program Files (x86)\\xampp\\php;%cd%\\php; && " : "";
      $command = "{$prefex}php $(pwd)/youtube-dl.php " .
                  "-i \"{$id}\" -f \"{$_POST["format"]}\" " .
                  "-p \"{$_POST["location"]}\" " .
                  "-s \"{$_POST["save"]}\" " .
                  "-proxy \"{$_POST["proxy"]}\" " .
                  "-height \"{$_POST["height"]}\" && exit";
      $execute = "";
      if ($os == "WIN") {
        $execute = "START \"\" \"{$command}\"";
      } else {
        $execute = 'osascript -e "tell application \"Terminal\" to do script \"' . $command . '\""';
      }
      exec($execute);
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
          <input type="text" name="url" placeholder="請輸入youtube網址或ID" required value="<?php echo $_POST["url"]; ?>">
        </div>
        <div class="form-element">
          <label>儲存位置</label>
          <input type="text" name="location" placeholder="請輸入儲存位置" value="<?php echo isset($_POST['location']) ? $_POST['location'] : $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "converted" ?>" required>
        </div>
        <div class="form-element">
          <label>代理設置</label>
          <input type="text" name="proxy" placeholder="請輸入代理伺服器, e.g. proxy.hinet.net:80" value="<?php echo $_POST["proxy"]; ?>">
        </div>
        <div class="form-element">
          <label>提取方式</label>
          <select name="save">
            <option value="">預設</option>
            <option value="audio" <?php echo $_POST["save"] == "audio" ? "selected" : ""; ?>>音訊軌(mp3)</option>
            <option value="video" <?php echo $_POST["save"] == "video" ? "selected" : ""; ?>>視訊軌</option>
          </select>
        </div>
        <div class="form-element">
          <label>視訊格式</label>
          <select name="format">
            <option value="">預設</option>
            <option value="mp4" <?php echo $_POST["format"] == "mp4" ? "selected" : ""; ?>>mp4(推薦)</option>
            <option value="webm" <?php echo $_POST["format"] == "webm" ? "selected" : ""; ?>>webm(mkv)</option>
          </select>
        </div>
        <div class="form-element">
          <label>最大大小</label>
          <select name="height">
            <option value="">預設 (最大)</option>
            <option value="144" <?php echo $_POST["height"] == "144" ? "selected" : ""; ?>>144p</option>
            <option value="240" <?php echo $_POST["height"] == "240" ? "selected" : ""; ?>>240p</option>
            <option value="360" <?php echo $_POST["height"] == "360" ? "selected" : ""; ?>>360p</option>
            <option value="480" <?php echo $_POST["height"] == "480" ? "selected" : ""; ?>>480p</option>
            <option value="720" <?php echo $_POST["height"] == "720" ? "selected" : ""; ?>>720p</option>
            <option value="1080" <?php echo $_POST["height"] == "1080" ? "selected" : ""; ?>>1080p</option>
            <option value="1440" <?php echo $_POST["height"] == "1440" ? "selected" : ""; ?>>1440p</option>
            <option value="4096" <?php echo $_POST["height"] == "4096" ? "selected" : ""; ?>>4096p</option>
          </select>
        </div>
        <input type="submit">
      </form>
    </div>
  </body>
</html>
