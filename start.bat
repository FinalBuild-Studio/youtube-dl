SET PATH=%PATH%;C:\xampp\php;C:\php;C:\Program Files (x86)\xampp\php;C:\Program Files (x86)\Google\Chrome\Application\;C:\Program Files\Google\Chrome\Application\;
START "" php -S 127.0.0.1:55555
START "" "chrome.exe" "http://localhost:55555"
