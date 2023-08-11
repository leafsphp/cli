<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leafphp.dev/logo-circle.png" height="100"/>
  <br>
</p>

<h1 align="center">Leaf CLI 2</h1>

<p align="center">
	<a href="https://packagist.org/packages/leafs/cli"
		><img
			src="https://poser.pugx.org/leafs/cli/v/stable"
			alt="Latest Stable Version"
	/></a>
	<a href="https://packagist.org/packages/leafs/cli"
		><img
			src="https://poser.pugx.org/leafs/cli/downloads"
			alt="Total Downloads"
	/></a>
	<a href="https://packagist.org/packages/leafs/cli"
		><img
			src="https://poser.pugx.org/leafs/cli/license"
			alt="License"
	/></a>
</p>
<br />
<br />

A simple command line tool for creating and interacting with your leaf projects. You can do stuff like installing packages, interacting with your app, previewing your app, ...

## Installation

You can get this tool up and running on your system using composer:

```bash
composer global require leafs/cli
```

Make sure to place Composer's system-wide vendor bin directory in your `$PATH` so the leaf executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

- Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`
- macOS: `$HOME/.composer/vendor/bin`
- GNU / Linux Distributions: `$HOME/.config/composer/vendor/bin` or `$HOME/.composer/vendor/bin`

You could also find the composer's global installation path by running `composer global about` and looking up from the first line.

Eg (Adding composer bin to path linux):

```sh
export PATH=$PATH:$HOME/.config/composer/vendor/bin
```

Eg (Adding composer bin to path mac):

```sh
export PATH=$PATH:$HOME/.composer/vendor/bin
echo $PATH
```

## Usage Guide

### Creating projects

To start a new project, simply open up your console or terminal in your directory
for projects and enter:

With leaf 3:

```sh
leaf create <project-name>
```

This will now prompt you to select a preset

```sh
Creating a new Leaf app "<project-name>" in ./projects-directory.

* Please pick a preset 
  [0] leaf
  [1] leaf mvc
  [2] leaf api
 > 
```

Selecting a number will generate a leaf app based on the associated preset. As you can see, there are 3 presets:

- **Leaf**: a bare leaf 3 project
- **Leaf MVC**: a leaf MVC project with leaf 3
- **Leaf API**: a leaf API project with leaf 3

You can also pick a preset directly without going through the interactive installer.

**Leaf:**

```bash
leaf create <project-name> --basic
```

**Leaf API:**

```bash
leaf create <project-name> --api
```

**Leaf MVC:**

```bash
leaf create <project-name> --mvc
```

You can also add `--custom` for a fully customisable leaf project.

```bash
leaf create <project-name> --custom
```

### Installing packages

This cli tool also adds a feature to install modules from composer

```sh
leaf install ui
```

This installs the `leafs/ui` package.

You can also install third party packages from packagist

```sh
leaf install psr/log
```

### Interactive Shell

You can also use the interactive shell to interact with your app.

```bash
$ leaf interact
...
>>> $user = new User;
...
>>> $user->name = "Mychi";
...
>>> $user->save();
```

### Previewing your app

This opens up your app on the PHP local server.

```sh
leaf serve
```

You can also specify the port

```bash
leaf serve -p 8000
```

In v2.1, you can also start the leaf server with hot module watching. This reloads your application anytime a change is made to your application code. To get started, simply start the leaf server with the `--watch` flag.

```sh
leaf serve --port 8000 --watch
```

## License

Leaf CLI is open-sourced software licensed under the [MIT license](LICENSE.md).

## 😇 Contributing

We are glad to have you. All contributions are welcome! To get started, familiarize yourself with our [contribution guide](https://leafphp.dev/community/contributing.html) and you'll be ready to make your first pull request 🚀.

To report a security vulnerability, you can reach out to [@mychidarko](https://twitter.com/mychidarko) or [@leafphp](https://twitter.com/leafphp) on twitter. We will coordinate the fix and eventually commit the solution in this project.

### Code contributors

<table>
	<tr>
		<td align="center">
			<a href="https://github.com/mychidarko">
				<img src="https://avatars.githubusercontent.com/u/26604242?v=4" width="120px" alt=""/>
				<br />
				<sub>
					<b>Michael Darko</b>
				</sub>
			</a>
		</td>
		<td align="center">
			<a href="https://github.com/tedtop">
				<img src="https://avatars.githubusercontent.com/u/5151307?v=4" width="120px" alt=""/>
				<br />
				<sub>
					<b>tedtop</b>
				</sub>
			</a>
		</td>
	</tr>
</table>

## Sponsoring Leaf

Your cash contributions go a long way to help us make Leaf even better for you. You can sponsor Leaf and any of our packages on [open collective](https://opencollective.com/leaf) or check the [contribution page](https://leafphp.dev/support/) for a list of ways to contribute.

And to all our existing cash/code contributors, we love you all ❤️

### Cash contributors

You can view all sponsors @ [https://leafphp.dev/#sponsors](https://leafphp.dev/#sponsors)

## 🤯 Links/Projects

- [Leaf Docs](https://leafphp.dev)
- [Leaf MVC](https://mvc.leafphp.dev)
- [Leaf API](https://api.leafphp.dev)
- [Leaf CLI](https://cli.leafphp.dev)
- [Aloe CLI](https://leafphp.dev/aloe-cli/)
