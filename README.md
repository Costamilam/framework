## About

PHP framework, routes, controllers and views

## Requirements

1. PHP 5.3.0+
1. Multibyte String (GD also) (optional, only used in `Inphinit\Helper::toAscii`)
1. libiconv (optional, only used in `Inphinit\Helper::toAscii`)
1. fileinfo (optional, only used in `Inphinit\File::mimeType`)

## Getting start

This repository is core code of the Inphinit framework, to build an application visit the main [repository](https://github.com/inphinit/inphinit).

Inhpinit is a minimalist framework based on the syntax of other popular frameworks, to make learning easier. The core of the framework is divided into two parts: [Inphinit](https://github.com/inphinit/framework/tree/master/src/Inphinit) and [Inphinit\Experimental](https://github.com/inphinit/framework/tree/master/src/Experimental).

- `Inphinit` namespace contains all defined classes that will hardly change their behavior.

- `Inphinit\Experimental` namespace contains classes that are being designed and tested, some of them already work very well, others are not yet fully defined, if the class has all its functionalities defined and tested in the future it will be moved to the `Inphinit` namespace.

To start the framework see the wiki:

- [Directory structure](https://github.com/inphinit/inphinit/wiki/Directory-Structure)
- [Routing](https://github.com/inphinit/inphinit/wiki/Routing)
- [Controllers](https://github.com/inphinit/inphinit/wiki/Controllers)
- [API doc](http://inphinit.github.io/api/)

## TODO

- [ ] Auth
- [ ] Redirect
- [ ] ORM
- [ ] Unit testing
