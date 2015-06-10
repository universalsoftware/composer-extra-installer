## Composer Plugin for installing extra dependencies

This composer plugin installs extra dependencies like fixtures, or OS specified packages.
It's usefull, if you want install specific packages only for Windows or a many binary packages. 

This plugin install only specified packages, and won't download package dependencies.

### Example usage

composer.json

```json
{
    ...
    "require": {
        "unvsoft/composer-extra-installer": "dev-master"
        ...
    },
    "extra": {
        "extra-require": {
            "unvsoft/main-programs": "dev-master"
        },
        "extra-require-unix": {
            "unvsoft/emsow-pacs-bridge": "1.2.1"
        },
        "extra-require-dev": {
            "unvsoft/fixtures/video-samples": "20141117",
            "unvsoft/fixtures/images-samples": "20140622"
        },
        "extra-require-dev-win": {
            "dcmtk/dcmtk-unvsoft-win64": "3.6.1-rc20140821",
            "imagemagick/imagemagick-q16-win32": "6.8.9-patch5"
        }
        ...
    }
    ...
}
```

Where packages specified in
 - `require-extra` will be always installed (like require section),
 - `require-extra-win` install when OS is Windows,
 - `require-extra-unix` install when OS like Unix,
 - `require-extra-dev` installed when option `--no-dev` not specified
 - `require-extra-dev-win` installed when OS is Windows and option `--no-dev` not specified
 - `require-extra-dev-win` installed when OS like Unix and option `--no-dev` not specified
 