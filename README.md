# DFS Cluster DFS Dispatcher

## What is it ?
This extension provides a custom DFSBackend that dispatches calls to any DFS backend to a backend from a custom list,
based on the path of the file.

It makes it possible to store some storage subdirectories to custom handlers, such as a cloud based one.

*This extension by itself won't provide any new end-user feature.*

## Status
This extension is a working prototype, but close from first release.

## Requirements
- eZ Publish installed from git, with the EZP-22960-configurable_dfs_backend branch checked out.
- eZ DFS configured (NFS itself doesn't matter, a local directory will work just as fine)

## Installation
It can be installed via composer from eZ Publish, new stack or legacy:
```
composer require "bdunogier/ezdispatchabledfs:dev-master"
```

Or by adding "bdunogier/ezdispatchabledfs": "dev-master" to your project's composer.json.

It can also be manually checked out from http://github.com/bdunogier/ezdispatchabledfs.git into the legacy extension directory.

## Configuration
Due to INI settings loading order limitations, some settings can't be stored into extension INI files but in a global override.

The contents of the settings/file.ini.append.php file must be copied to settings/override, or obviously merged into
file.ini.
