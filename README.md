youtube-dl
==========

A downloader for PHP to download best quality youtube video.

##Installation
**Step1.** Please unzip 7z file to ffmpeg folder first or you can use 'brew' to install to dependency like ffmpeg or php-cli(for Mac users. For unix users, use apt-get).  



	brew install ffmpeg
	brew install php5-cli

or

	apt-get install ffmpeg
	apt-get install php5-cli

**Step2.** Add new folder then download correspond [ffmpeg](https://www.ffmpeg.org/) if you use Windows.  
**Step3.** Download [wfio](https://github.com/kenjiuno/php-wfio) system to support unicode if you use Windows.  
**Step4.** Add permissions 777 to the whole folder if you do not use Windows(this will help you to create executable .command file).  

##Usage
for basic

	php youtube-dl.php -i {YOUTUBE_ID} -p {SAVE_LOCATION}

for more information

	php youtube-dl.php -h

or

	php youtube-dl.php -help

##For Windows / MacOSX Users
###Web console
I wrote a simple web console for windows users, just click **start-youtube-dl.bat** / **start-youtube-dl.command** then system will start php server on port 55555 and open [Google Chrome](https://www.google.com.tw/chrome).

##For Windows Users
###Packed bundles
[PHP 5.6.3](http://goo.gl/JWVzm4), extract it to C:\, it include wfio system.

[ffmpeg](http://goo.gl/OFhRje), extract it to the folder youtube-dl installed in ffmpeg/  

[wfio](https://github.com/kenjiuno/php-wfio), it was contributed by kenjijuno, an extension allows your php can read utf8 file path.

###Binary
[youtube-dl](http://goo.gl/CEYyLq) stable veriosn 0.1.1 download link is here.(include php and ffmpeg)  

##MacOS Users
Preferences > Settings > Shell  
on 'When the shell exits' chosen 'Close the window'

##Issue / Todo
1. Save path do not support unicode now. Please try English path instead.

##Copyright / License

Copyright 2014 CapsLock, Studio.

Licensed under the GNU General Public License Version 2.0 (or later); you may not use this work except in compliance with the License. You may obtain a copy of the License in the LICENSE file, or at:

[http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt](http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
