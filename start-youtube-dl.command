cd `dirname $0`
osascript -e "tell application \"Terminal\" to do script \"php -S 127.0.0.1:55555 -t $(pwd)\"" && open "http://localhost:55555"
