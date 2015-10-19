<?php
error_reporting(0);
$lang   = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
$lang   = preg_replace("/\-/", "_", $lang);
$method = $_SERVER['REQUEST_METHOD'];
$config = ($langConfig = "lang/" . $lang . ".php") && file_exists($langConfig) ? include $langConfig : null;

if (!isset($config)) {
    exit();
}

if ($method === "POST") {
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

        $prefex  = $os === "WIN" ? "SET PATH=%PATH%;C:\\xampp\\php;C:\\php;C:\\Program Files (x86)\\xampp\\php;%cd%\\php; && " : "";
        $command = "{$prefex}php $(pwd)/youtube-dl.php " .
                    "-i \"{$id}\" -f \"{$_POST["format"]}\" " .
                    "-p \"{$_POST["location"]}\" " .
                    "-s \"{$_POST["method"]}\" " .
                    "-proxy \"{$_POST["proxy"]}\" " .
                    "-height \"{$_POST["size"]}\" && exit";
        $execute = "";
        if ($os === "WIN") {
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

      center > form {
        margin: 0 auto;
      }

      center {
        position: relative;
      }
    </style>
  </head>
  <body>
    <center>
      <h1>youtube-dl web console</h1>
      <form method="post">
        <table>
          <tbody>
            <tr>
              <td>
                <label><?php echo $config["url"]; ?></label>
              </td>
              <td>
                <input type="text" name="url" placeholder="請輸入youtube網址或ID" required value="<?php echo $_POST["url"]; ?>">
              </td>
            </tr>
            <tr>
              <td>
                <label><?php echo $config["storage"]; ?></label>
              </td>
              <td>
                <input type="text" name="location" placeholder="請輸入儲存位置" value="<?php echo isset($_POST['location']) ? $_POST['location'] : $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "converted" ?>" required>
              </td>
            </tr>
            <tr>
              <td>
                <label><?php echo $config["proxy"]; ?></label>
              </td>
              <td>
                <input type="text" name="proxy" placeholder="請輸入代理伺服器, e.g. proxy.hinet.net:80" value="<?php echo $_POST["proxy"]; ?>">
              </td>
            </tr>
            <tr>
              <td>
                <label><?php echo $config["method"]; ?></label>
              </td>
              <td>
                <select name="save">
                  <option value=""><?php echo $config["default"]; ?></option>
                  <option value="audio" <?php echo $_POST["method"] == "audio" ? "selected" : ""; ?>>Audio(mp3)</option>
                  <option value="video" <?php echo $_POST["method"] == "video" ? "selected" : ""; ?>>Video</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
                <label><?php echo $config["format"]; ?></label>
              </td>
              <td>
                <select name="save">
                  <option value=""><?php echo $config["format"]; ?></option>
                  <option value="audio" <?php echo $_POST["format"] == "mp4" ? "selected" : ""; ?>>MP4</option>
                  <option value="video" <?php echo $_POST["format"] == "webm" ? "selected" : ""; ?>>WEBM</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>
                <label><?php echo $config["size"]; ?></label>
              </td>
              <td>
                <select name="height">
                  <option value=""><?php echo $config["default"]; ?></option>
                  <option value="144" <?php echo $_POST["size"] == "144" ? "selected" : ""; ?>>144p</option>
                  <option value="240" <?php echo $_POST["size"] == "240" ? "selected" : ""; ?>>240p</option>
                  <option value="360" <?php echo $_POST["size"] == "360" ? "selected" : ""; ?>>360p</option>
                  <option value="480" <?php echo $_POST["size"] == "480" ? "selected" : ""; ?>>480p</option>
                  <option value="720" <?php echo $_POST["size"] == "720" ? "selected" : ""; ?>>720p</option>
                  <option value="1080" <?php echo $_POST["size"] == "1080" ? "selected" : ""; ?>>1080p</option>
                  <option value="1440" <?php echo $_POST["size"] == "1440" ? "selected" : ""; ?>>1440p</option>
                  <option value="4096" <?php echo $_POST["size"] == "4096" ? "selected" : ""; ?>>4096p</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
        <input type="submit">
      </form>
    </center>
  </body>
</html>
