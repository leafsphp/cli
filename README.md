# Leaf CLI

[![Latest Stable Version](https://poser.pugx.org/leafs/leaf/v/stable)](https://packagist.org/packages/leafs/leaf)
[![Total Downloads](https://poser.pugx.org/leafs/leaf/downloads)](https://packagist.org/packages/leafs/leaf)
[![License](https://poser.pugx.org/leafs/leaf/license)](https://packagist.org/packages/leafs/leaf)

A simple command line tool for creating  and interacting with your leaf projects. You can do stuff like installing packages, interacting with your app, previewing your app...

## Installation

You can get this tool up and running on your system using composer:

```bash
composer global require leafs/cli
```

Make sure to place Composer's system-wide vendor bin directory in your `$PATH` so the leaf executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

- Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`
- macOS: `$HOME/.composer/vendor/bin`
- GNU / Linux Distributions: `$HOME/.config/composer/vendor/bin or $HOME/.composer/vendor/bin`

You could also find the composer's global installation path by running `composer global about` and looking up from the first line.

## Usage Guide

### Creating projects

To start a new project, simply open up your console or terminal in your directory
for projects and enter:

```bash
leaf create <project-name>
```

This will generate a new Leaf PHP app in the `<project-name>` directory. You can also create [Leaf API](https://github.com/leafsphp/leafAPI) and [Leaf MVC](https://github.com/leafsphp/leafmvc) apps from the cli.

**Leaf API:**

```bash
leaf create <project-name> --api
```

or

```bash
leaf create <project-name> -a
```

**Leaf MVC:**

```bash
leaf create <project-name> --mvc
```

or

```bash
leaf create <project-name> -m
```

### Installing packages

This cli tool also adds a feature to install packages from composer

```bash
leaf install leafs/ui
```

### Interactive Shell

You can also use the interactive shell to interact with your app.

```bash
$ leaf app:interact
...
>>> $user = new User;
...
>>> $user->name = "Mychi";
...
>>> $user->save();
```

### Previewing your app

This opens up your app on the PHP local server.

```bash
leaf app:serve
```

You can also specify the port

```bash
leaf app:serve -p 8000
```

## License

Leaf CLI is open-sourced software licensed under the [MIT license](LICENSE.md).
