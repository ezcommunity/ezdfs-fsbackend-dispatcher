# DFS Cluster DFS Dispatcher

## What is it ?
*This extension by itself won't provide any new end-user feature.*

This extension provides a custom DFSBackend that dispatches calls to any DFS backend to a backend from a custom list,
based on the path of the file.

It makes it possible to store some storage subdirectories to custom handlers, such as a cloud based one.

## Actual handlers
An AWS-S3 handler, that stores binary files to Amazon S3, is available: http://github.com/ezsystems/ezdfs-fsbackend-aws-s3.

## Requirements
- eZ Publish master (5.4.0-dev or 2014.05).
- eZ DFS configured (NFS itself doesn't matter, a local directory will work just as fine)

## Installation

### Using composer
It can be installed via composer from eZ Publish, new stack or legacy:
```
composer require "ezsystems/ezdfs-fsbackend-dispatcher:1.0-beta"
```
Or by adding `"ezsystems/ezdfs-fsbackend-dispatcher": "1.0-beta"` to your project's composer.json.

### Manually
```
cd ezpublish_legacy/extension
git clone http://github.com/ezsystems/ezsystems/ezdfs-fsbackend-dispatcher.git
```

## Configuration
Due to INI settings loading order limitations, some settings can't be stored into extension INI files but in a global override.

The contents of the settings/file.ini.append.php file must be copied to `settings/override/file.ini.append.php`, or
obviously merged into it, since it should already exist.

Backends are configured by adding their class name to the DFSBackends array in file.ini. This will make the dispatcher send
operations on files with a path starting with `var/ezdemo_site/storage/images` to MyCustomBackend.

```
PathBackends[var/ezdemo_site/storage/images]=MyCustomBackend
```

Priority is a simple first-come, first-served. Path that aren't matched by any path in `PathBackends` are handled by
`DispatchableDFS.DefaultBackend`, by default set to the native `eZDFSFileHandlerDFSBackend`.

## Backends initialization
By default, backends are instanciated with a simple "new $class". But if a backend implements
`eZDFSFileHandlerDFSFactoryBackendInterface` interface, it will be built by calling the static `build()` method.

If any kind of initialization or injection is required, it can be done in this method.

A typical settings/override/file.ini.append.php with a custom handler enabled would look like this:
```ini
[ClusteringSettings]
FileHandler=eZDFSFileHandler

[eZDFSClusteringSettings]
MountPointPath=/media/nfs
DFSBackend=eZDFSFileHandlerDFSDispatcher
DBHost=cluster_server
DBName=db_cluster
DBUser=root
DBPassword=
MetaDataTableNameCache=ezdfsfile_cache

[DispatchableDFS]
DefaultBackend=eZDFSFileHandlerDFSBackend

PathBackends[var/site/storage/images]=MyCustomBackend
```

Remember that `DefaultBackend` *must* be explicitly configured in the global override to be taken into account.
