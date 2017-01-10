# TiMap

Tagged Images on a Map (TiMap) is an interactive map that allows users to search for locations and check related images posted by other people.

Once a search is made, images are plotted on a map (represented by markers) and when they are clicked, the image gets shown and tagged by image analysis services using AI.

The user then can review the analysis and indicate if the image was correctly tagged or not. That's how the analysis providers get ranked in the system. The more they get it right, more they get used to perform the image analyses.

[![Preview video](https://github.com/marcelopm/TiMap/blob/master/preview.jpg?raw=true)](https://youtu.be/H50SHoXgx3g)

### Providers

TiMap uses a few APIs to work properly:

* [Google maps API]
* [Flickr API]
* Image tagging APIs:
    * [Imagga]
    * [Indico]
    * ~~Clarifai~~ to be added
    * ~~Aylien~~ doesn't offer image tagging for free accounts
    * ~~IBM Watson~~ only offers image tagging as a trial

### Tech

TiMap uses a number of open source projects to work properly:

* [Laravel] - awesome PHP framework!
    * [Composer] - php package manager
    * [npm] - js package manager
    * [Gulp] - the streaming build system
* [MaterializeCSS] - beautiful front-end framework based on Material Design
* [Chart.js] - awesome chart js library
* [jQuery], [LoDash] - js

And of course TiMap itself is open source with a [public repository][timap] on GitHub.

### Todo

[TiMap Project]

### Installation

Checkout the master:

```sh
git clone https://github.com/marcelopm/TiMap.git
```

TiMap requires [npm] and [Composer], so make sure they are installed before trying to install the dependencies:

```sh
$ cd TiMap
$ composer install
$ npm install
```

From the [Laravel]'s doco:
> [..] you may need to configure some permissions. Directories within the  storage and the bootstrap/cache directories should be writable by your web server or Laravel will not run [..]

If you have any issues, this should do it for running TiMap locally:

```sh
$ sudo chmod -R 777 storage bootstrap/cache
```

And run [Gulp] command to compile assets:

```sh
$ gulp
```
Note: if you get an error from the command above, you might need to install gulp manually:

```sh
$ npm install gulp-cli -g
$ npm install gulp -D
```

### Configuration

Create a .env file based on the sample provided:

```sh
$ cp .env.example to .env
$ vim .env
```

Create Laravel's app key:

```sh
$ php artisan key:generate
```

Login into MySQL db console and create a new database for TiMap:

```mysql
mysql> create database timap;
```

And fill up the new DB details values on .env:
```sh
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```
Create tables and populate them by running:

```sh
$ php artisan migrate --seed
```

This will create an user required to access the app with the default credentials: admin@localhost.dev / admin


And make sure to fill in values the for the service providers on your .env file:

```sh
GMAP_KEY=

FLICKR_KEY=
FLICKR_SECRET=

INDICO_KEY=

IMAGGA_KEY=
IMAGGA_SECRET=
```

More information on how to sign up and get APIs key for each of the above here:
* [Google Maps api]
* [Flickr api]
* [Imagga]
* [Indico]

### Running

Finally you should be able to start PHP's built-in dev server by running:

```sh
$ php artisan serve
```

And everything should be good to go with TiMap running on [localhost]

### Development

Want to contribute? Great!

TiMap uses Gulp + Webpack for fast developing.
Make a change in your file and instantaneously see your updates!

Open your favorite Terminal and run these commands.

First Tab:
```sh
$ php artisan serve
```

Second Tab:
```sh
$ gulp watch
```

Then [localhost:3000] should be automatically opened on your browser

License
----

>**MIT License**

>Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

>The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

>THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen. Thanks SO - http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax)

[google maps api]: <https://developers.google.com/maps/>
[flickr api]: <https://www.flickr.com/services/api/>
[imagga]: <http://docs.imagga.com/>
[indico]: <https://indico.io/docs?php>
[localhost]: <http://localhost:8000>
[localhost:3000]: <http://localhost:3000>
[timap]: <https://github.com/marcelopm/TiMap>
[git-repo-url]: <https://github.com/marcelopm/TiMap.git>
[timap project]: <https://github.com/marcelopm/TiMap/projects/1>
[laravel]: <https://github.com/laravel/laravel>
[composer]: <https://github.com/composer/composer>
[npm]: <https://github.com/npm/npm>
[materializecss]: <https://github.com/Dogfalo/materialize>
[chart.js]: <https://github.com/chartjs>
[Lodash]: <https://github.com/lodash/lodash>
[jQuery]: <http://jquery.com>
[Gulp]: <http://gulpjs.com>
