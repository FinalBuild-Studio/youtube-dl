youtube-dl
==========

A downloader for PHP to download best quality youtube video.

##Installation
1. Please unzip 7z file in ffmpeg folder first.  
2. Add new folder then download correspond [ffmpeg](https://www.ffmpeg.org/) if you use other platform(not windows, e.g. MacOSX).
3. Download [wfio](https://github.com/kenjiuno/php-wfio) system to support unicode if you use windows.

##Usage 
	<?php
		require_once dirname(__FILE__) . "/XML/xml2Array.php";
		require_once dirname(__FILE__) . "/Youtube/Curl.php";
		require_once dirname(__FILE__) . "/Youtube/Loader.php";

		$loader = new Youtube\Loader();
		$loader->visit("qQ4fiXa6Y74")->getManifest()->getMedia()->save("D:/");

##Copyright / License

Copyright 2014 CapsLock, Studio.

Licensed under the GNU General Public License Version 2.0 (or later); you may not use this work except in compliance with the License. You may obtain a copy of the License in the LICENSE file, or at:

[http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt](http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt)

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.