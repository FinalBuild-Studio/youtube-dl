cwd=$(pwd)
export path="$path:$(pwd)+/php"
osascript -e "tell application \"Terminal\" to do script \"php -S 127.0.0.1:55555\""
open -a Google\ Chrome "http://localhost:55555"